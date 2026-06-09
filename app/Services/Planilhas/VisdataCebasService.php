<?php

namespace App\Services\Planilhas;

use DateInterval;
use DateTime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class VisdataCebasService
{
    public const TABLE = 'cebas_suas';
    public const SHEET_NAME = 'SITUAÇÃO CNPJ CEBAS (VISDATA)';

    public const HEADERS = [
        'protocolo',
        'cnpj',
        'entidade',
        'municipio',
        'uf',
        'cod_status_de_certificacao',
        'status_de_certificacao',
        'cod_ibge',
        'cod_cneas',
        'cneas',
        'ano_conclusao_cneas',
        'dt_inicio_certificacao_atual',
        'dt_fim_certificacao_atual',
        'receita_bruta',
        'dt_referencia',
        'ofertas',
    ];

    public function stats(): array
    {
        if (! Schema::hasTable(self::TABLE)) {
            return ['total' => 0, 'updated_at' => null];
        }

        $columns = $this->actualColumns();
        $referenceColumn = $this->resolveColumn($columns, 'dt_referencia');

        $updatedAt = null;
        if ($referenceColumn) {
            $updatedAt = DB::table(self::TABLE)
                ->whereNotNull($referenceColumn)
                ->orderByDesc($referenceColumn)
                ->value($referenceColumn);
        }

        return [
            'total' => DB::table(self::TABLE)->count(),
            'updated_at' => $updatedAt ? $this->formatDisplayDate($updatedAt) : null,
        ];
    }

    public function import(UploadedFile $file): array
    {
        if (! Schema::hasTable(self::TABLE)) {
            throw new RuntimeException('A tabela cebas_suas não foi encontrada.');
        }

        $columns = $this->actualColumns();
        $columnMap = $this->columnMap($columns);
        $missing = array_values(array_filter(self::HEADERS, fn ($header) => ! isset($columnMap[$header])));

        if ($missing !== []) {
            throw new RuntimeException('Colunas ausentes na tabela cebas_suas: ' . implode(', ', $missing));
        }

        $rows = $this->readXlsxRows($file->getRealPath() ?: $file->path());
        $records = $this->recordsFromRows($rows, $columnMap);

        DB::transaction(function () use ($records) {
            DB::table(self::TABLE)->delete();

            foreach (array_chunk($records, 500) as $chunk) {
                DB::table(self::TABLE)->insert($chunk);
            }
        });

        return ['inserted_rows' => count($records)];
    }

    public function recordsForDownload(): iterable
    {
        if (! Schema::hasTable(self::TABLE)) {
            return [];
        }

        return DB::table(self::TABLE)->cursor();
    }

    public function downloadColumns(): array
    {
        return Schema::hasTable(self::TABLE) ? Schema::getColumnListing(self::TABLE) : [];
    }

    public function templateHtmlTable(): string
    {
        $cells = array_map(fn ($header) => '<th>' . htmlspecialchars($header, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</th>', self::HEADERS);

        return "\xEF\xBB\xBF" . '<table border="1"><thead><tr>' . implode('', $cells) . '</tr></thead><tbody></tbody></table>';
    }

    private function readXlsxRows(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Não foi possível abrir o arquivo. Salve a planilha como .xlsx antes de importar.');
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $workbookRelsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if (! $workbookXml || ! $workbookRelsXml) {
            $zip->close();
            throw new RuntimeException('Erro ao localizar a estrutura de abas do arquivo XLSX.');
        }

        $worksheetPath = $this->worksheetPath($workbookXml, $workbookRelsXml);
        $worksheetXml = $zip->getFromName($worksheetPath);
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();

        if (! $worksheetXml) {
            throw new RuntimeException('Erro ao ler a planilha do arquivo XLSX.');
        }

        $sharedStrings = $this->sharedStrings($sharedStringsXml ?: '');
        $sheet = $this->xml($worksheetXml, 'Erro ao processar planilha.');
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $cells = array_fill(0, count(self::HEADERS), '');
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                $column = preg_replace('/\d+/', '', $ref);
                $index = $this->columnIndex($column);

                if ($index < 0 || $index >= count($cells)) {
                    continue;
                }

                $cells[$index] = $this->cellValue($cell, $sharedStrings);
            }
            $rows[] = $cells;
        }

        if ($rows === []) {
            throw new RuntimeException('Planilha vazia ou sem dados.');
        }

        return $rows;
    }

    private function worksheetPath(string $workbookXml, string $workbookRelsXml): string
    {
        $workbook = $this->xml($workbookXml, 'Erro ao processar a estrutura de abas do arquivo XLSX.');
        $rels = $this->xml($workbookRelsXml, 'Erro ao processar os relacionamentos das abas.');
        $relationshipId = null;

        foreach ($workbook->sheets->sheet as $sheet) {
            if (trim((string) $sheet['name']) === self::SHEET_NAME) {
                $attributes = $sheet->attributes('r', true);
                $relationshipId = (string) $attributes['id'];
                break;
            }
        }

        if (! $relationshipId) {
            throw new RuntimeException('A aba "' . self::SHEET_NAME . '" não foi encontrada no arquivo.');
        }

        foreach ($rels->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $relationshipId) {
                $target = ltrim(str_replace('\\', '/', (string) $relationship['Target']), '/');
                return str_starts_with($target, 'xl/') ? $target : 'xl/' . $target;
            }
        }

        throw new RuntimeException('Não foi possível localizar a planilha da aba "' . self::SHEET_NAME . '".');
    }

    private function sharedStrings(string $xml): array
    {
        if ($xml === '') {
            return [];
        }

        $shared = $this->xml($xml, 'Erro ao processar strings compartilhadas.');
        $strings = [];

        foreach ($shared->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $value = '';
            foreach ($item->r as $run) {
                $value .= (string) $run->t;
            }
            $strings[] = $value;
        }

        return $strings;
    }

    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];

        if ($type === 's') {
            return $sharedStrings[(int) $cell->v] ?? '';
        }

        if ($type === 'inlineStr') {
            if (isset($cell->is->t)) {
                return (string) $cell->is->t;
            }

            $value = '';
            foreach ($cell->is->r as $run) {
                $value .= (string) $run->t;
            }

            return $value;
        }

        return (string) $cell->v;
    }

    private function recordsFromRows(array $rows, array $columnMap): array
    {
        $actualHeaders = $rows[0];
        $errors = [];

        foreach (self::HEADERS as $index => $header) {
            if (strtoupper(trim((string) ($actualHeaders[$index] ?? ''))) !== strtoupper($header)) {
                $errors[] = 'Coluna ' . ($index + 1) . ": esperado '{$header}', encontrado '" . ($actualHeaders[$index] ?? '') . "'";
            }
        }

        if ($errors !== []) {
            throw new RuntimeException('Cabeçalhos da planilha incorretos: ' . implode('; ', $errors));
        }

        $records = [];
        foreach (array_slice($rows, 1) as $row) {
            if ($this->blankRow($row)) {
                continue;
            }

            if (trim((string) ($row[0] ?? '')) === '') {
                continue;
            }

            $record = [];
            foreach (self::HEADERS as $index => $header) {
                $value = $row[$index] ?? null;

                if (in_array($header, ['dt_inicio_certificacao_atual', 'dt_fim_certificacao_atual', 'dt_referencia'], true)) {
                    $value = $this->parseDateValue($value);
                } elseif ($header === 'receita_bruta') {
                    $value = $this->formatMoneyValue($value);
                } else {
                    $value = $this->blankToNull($value);
                }

                $record[$columnMap[$header]] = $value;
            }
            $records[] = $record;
        }

        return $records;
    }

    private function columnMap(array $columns): array
    {
        $map = [];
        foreach (self::HEADERS as $header) {
            $column = $this->resolveColumn($columns, $header);
            if ($column) {
                $map[$header] = $column;
            }
        }

        return $map;
    }

    private function resolveColumn(array $columns, string $header): ?string
    {
        $aliases = [
            'municipio' => ['municipio', 'MUNICIPIO', 'MUNICÍPIO'],
            'dt_referencia' => ['dt_referencia', 'DT_REFERENCIA', 'DT_REFERÊNCIA'],
        ];

        $candidates = $aliases[$header] ?? [$header, strtoupper($header)];
        $normalized = [];
        foreach ($columns as $column) {
            $normalized[$this->normalizeName($column)] = $column;
        }

        foreach ($candidates as $candidate) {
            $key = $this->normalizeName($candidate);
            if (isset($normalized[$key])) {
                return $normalized[$key];
            }
        }

        return null;
    }

    private function actualColumns(): array
    {
        return Schema::getColumnListing(self::TABLE);
    }

    private function normalizeName(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;

        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $value) ?: '');
    }

    private function parseDateValue(mixed $value): ?string
    {
        $value = $this->blankToNull($value);
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $number = (int) $value;
            if ($number >= 25000) {
                $date = new DateTime('1899-12-30');
                $date->add(new DateInterval('P' . $number . 'D'));
                return $date->format('Y-m-d');
            }

            if ($number >= 1900 && $number <= 2100) {
                return sprintf('%04d-01-01', $number);
            }

            return null;
        }

        $value = trim((string) $value);
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $value, $match)) {
            $year = (int) $match[3];
            if ($year < 100) {
                $year = $year >= 70 ? 1900 + $year : 2000 + $year;
            }

            return sprintf('%04d-%02d-%02d', $year, (int) $match[2], (int) $match[1]);
        }

        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $value, $match)) {
            return sprintf('%04d-%02d-%02d', (int) $match[1], (int) $match[2], (int) $match[3]);
        }

        return null;
    }

    private function formatMoneyValue(mixed $value): ?float
    {
        $value = $this->blankToNull($value);
        if ($value === null) {
            return null;
        }

        $clean = preg_replace('/[^0-9.,\-]/', '', (string) $value) ?: '';
        if (str_contains($clean, ',') && ! str_contains($clean, '.')) {
            $clean = str_replace(',', '.', $clean);
        } elseif (str_contains($clean, ',') && str_contains($clean, '.')) {
            $clean = str_replace(',', '', $clean);
        }

        return (float) $clean;
    }

    private function blankToNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function blankRow(array $row): bool
    {
        return array_filter($row, fn ($value) => trim((string) $value) !== '') === [];
    }

    private function columnIndex(string $letters): int
    {
        $index = 0;
        $letters = strtoupper($letters);

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    private function xml(string $xml, string $message): SimpleXMLElement
    {
        $parsed = simplexml_load_string($xml);
        if ($parsed === false) {
            throw new RuntimeException($message);
        }

        return $parsed;
    }

    private function formatDisplayDate(mixed $value): string
    {
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}
