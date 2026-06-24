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

class ExternoService
{
    public const TABLE = 'processos_sei';

    public const HEADERS = [
        'PROTOCOLO',
        'ENTIDADE',
        'CNPJ',
        'MUNICIPIO',
        'UF',
        'COD_LOCALIZACAO',
        'PROCESSOS_ANEXADOS',
        'DT_PROTOCOLO',
        'ORGAO_ORIGEM',
        'DT_RECEBIMENTO_MDS',
        'MOTIVO_RECEBIMENTO',
        'TIPO_PROCESSO',
        'DT_CERTIFICACAO_ANTERIOR_INICIO',
        'DT_CERTIFICACAO_ANTERIOR_FIM',
        'TEMPESTIVIDADE',
        'DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU',
        'ORGAO_CERTIFICACAO_ANTERIOR',
        'ORGAO_ENCAMINHAMENTO',
        'OFICIO_ENCAMINHAMENTO',
        'DT_ENCAMINHAMENTO',
        'MOTIVO_ENCAMINHAMENTO',
        'DT_RETORNO_MDS',
        'OFICIO_RETORNO',
        'OFICIO_DILIGENCIA',
        'DT_OFICIO_DILIGENCIA',
        'DT_ENVIO_OFICIO',
        'DT_RECEBIMENTO_AR_DILIGENCIA',
        'DT_PROTOCOLO_RESPOSTA_DILIGENCIA',
        'OFICIO_COMPLEMENTAR',
        'DT_OFICIO_COMPLEMENTAR',
        'DT_ENVIO_OFICIO_COMPLEMENTAR',
        'DT_RECEBIMENTO_AR_COMPLEMENTAR',
        'DT_PROTOCOLO_RESPOSTA_COMPLEMENTAR',
        'PARECER_NOTA_TECNICA',
        'DECISAO_PARECER',
        'MOTIVO_INDEFERIMENTO',
        'PORTARIAS_SNAS',
        'DT_DECISAO_SNAS',
        'DT_PUBICACAO_PORTARIA_SNAS_DOU',
        'ITEM_PORTARIA_DECISAO_SNAS',
        'PAGINA_DECISAO_SNAS_DOU',
        'MINISTERIO_CERTIFICADOR_COMPETENTE',
        'JUIZO_ACAO_JUDICIAL',
        'ACAO_JUDICIAL',
        'PROTOCOLO_RECURSO_SNAS',
        'DT_PROTOCOLO_RECURSO_SNAS',
        'PARECER_NOTA_TECNICA_RECURSO_SNAS',
        'DECISAO_RECONSIDERACAO_SNAS',
        'PORTARIA_DECISAO_RECURSO_SNAS',
        'DT_PORTARIA_RECONSIDERACAO_SNAS',
        'DT_PUBLICACAO_DOU_RECONSIDERACAO_SNAS',
        'DECISAO_RECURSO_GM',
        'MOTIVO_INDEFERMINENTO_GM',
        'PORTARIA_DECISAO_RECURSO_GM',
        'DT_PORTARIA_DECISAO_RECURSO_GM',
        'DT_PUBLICACAO_DOU_PORTARIA_DECISAO_RECURSO_GM',
        'STATUS_PROCESSO',
        'FASE_PROCESSO',
        'DT_INICIO_CERTIFICACAO_ATUAL',
        'DT_FIM_CERTIFICACAO_ATUAL',
        'PERFIL_RISCO',
        'COMPROVANTE_INSCRICAO_CNPJ',
        'COMPROVANTE_INSCRICAO_CNPJ_FLS',
        'ESTATUTO_LEGAL',
        'ESTATUTO_LEGAL_FLS',
        'DESTINO_PATRIMONIO_CASO_DISSOLUCAO',
        'ATA_ELEICAO',
        'ATA_ELEICAO_FLS',
        'COMPROVANTE_INSCRICAO_CMAS',
        'COMPROVANTE_INSCRICAO_CMAS_FLS',
        'RELATORIO_ATIVIDADES',
        'RELATORIO_ATIVIDADES_FLS',
        'GRATUIDADE',
        'GRATUIDADE_FLS',
        'REDE_ASSISITENCIA_SOCIAL',
        'ACOLHIMENTO_IDOSOS',
        'RECEITA_BRUTA_ANUAL',
        'DT_REQUISITOS_LEGAIS',
        'DILIGENCIA_EMAIL',
        'DT_ENVIO_DILIGENCIA',
        'ANALISTA_REQUISITOS_LEGAIS',
        'DOCUMENTOS_OBRIGATORIOS',
        'DOCUMENTOS_PENDENTES',
        'COMPATIBILIDADE_ESTATUTO_LOAS',
        'ATIVIDADES_RELATORIO',
        'GRATUIDADE_PARECER',
        'CONTINUIDADE_PLANEJAMENTO_UNIVERSALIDADE',
        'CARACTERISTICA_I',
        'OFERTA_I',
        'USUARIO_I',
        'CARACTERISTICA_II',
        'OFERTA_II',
        'USUARIO_II',
        'CARACTERISTICA_III',
        'OFERTA_III',
        'USUARIO_III',
        'CARACTERISTICA_IV',
        'OFERTA_IV',
        'USUARIO_IV',
        'CARACTERISTICA_V',
        'OFERTA_V',
        'USUARIO_V',
        'OFERTA_VI',
        'USUARIO_VI',
        'OFERTA_VII',
        'USUARIO_VII',
        'VAGAS_VI',
        'VAGAS_VII',
        'OUTRAS_OFERTAS',
        'OUTRAS_OFERTAS_I',
        'OFERTAS_OUTRAS_AREAS',
        'CNAE',
        'ANALISTA_MANIFESTACAO',
        'CGCEB_MANIFESTACACAO',
        'DRSP_MANIFESTACAO',
        'DT_PEDIDO_MANIFESTACAO',
        'MANIFESTACAO_OUTRO_MINISTERIO',
        'NOTA_TECNICA_OUTRO_ORGAO',
        'ANALISTA_PARECER',
        'CGCEB_PARECER',
        'DRSP_PARECER',
        'DT_PARECER',
        'ATIVIDADES_PARECER_MISTO',
        'PEDIDO_MANIFESTACAO_ENCAMINHAMENTO',
        'NUMERO_PARECER_NT',
        'JUSTIFICATIVA_INDEFERIMENTO',
        'DT_DILIGENCIA_EMAIL',
        'CONTINUIDADE',
        'PLANEJAMENTO',
        'UNIVERSALIDADE',
        'JUSTIFICATIVA_INDEFERIMENTO_NT',
        'RESPONSAVEL_ANALISE',
        'RESPONSAVEL_APRECIACAO',
        'DT_APRECIACAO',
        'ENCAMINHAMENTO_MANIFESTACAO',
        'RESPONSAVEL_NOTA_TECNICA',
        'REPONSAVEL_ENC',
        'QUALIFICACAO_USUARIO_I',
        'QUALIFICACAO_USUARIO_II',
        'QUALIFICACAO_USUARIO_III',
        'QUALIFICACAO_USUARIO_Iv',
        'QUALIFICACAO_USUARIO_V',
        'QUALIFICACAO_USUARIO_VI',
        'QUALIFICACAO_USUARIO_VII',
        'SUPERVISAO_RECOMENDADA',
        'OBS_CCEB_I',
        'OBS_CCEB_II',
        'CARACTERISITICAS_OFERTAS',
        'EMAIL_ENTIDADE',
        'PROTOCOLO_SEI',
        'FASE_RECURSO',
        'MOTIVO_ARQUIVAMENTO',
        'DT_ARQUIVAMENTO',
        'ISENCAO_USUFRUIDA',
        'OBS_PEDIDO_MANIFESTACAO',
        'ASS SNAS',
        'RESPONSAVEL_SUPERVISAO',
        'VAGAS_I',
        'VAGAS_II',
        'VAGAS_III',
        'VAGAS_IV',
        'VAGAS_V',
        'OUTRAS_ATIVIDADES',
        'DISCREPANCIA',
        'PASSIVO',
        'MANIFESTACAO_MEC_MS_FLS',
        'TIPO_DISTRIBUICAO',
        'DT_ATUALIZACAO',
        'DT_DISTRIBUICAO',
        'OBS_EMAIL_DILIGENCIA',
        'SITUAÇÃO_CNEAS',
        'BASE_ORGAO',
        'ATIVO',
    ];

    public function stats(): array
    {
        if (! Schema::hasTable(self::TABLE)) {
            return ['total' => 0, 'updated_at' => null];
        }

        $updatedAt = null;
        if (Schema::hasColumn(self::TABLE, 'DT_ATUALIZACAO')) {
            $updatedAt = DB::table(self::TABLE)
                ->whereNotNull('DT_ATUALIZACAO')
                ->orderByDesc('DT_ATUALIZACAO')
                ->value('DT_ATUALIZACAO');
        }

        return [
            'total' => DB::table(self::TABLE)->count(),
            'updated_at' => $updatedAt ? $this->formatDisplayDate($updatedAt) : null,
        ];
    }

    public function import(UploadedFile $file): array
    {
        $this->assertTableReady();

        $rows = $this->readXlsxRows($file->getRealPath() ?: $file->path());
        $records = $this->recordsFromRows($rows);

        DB::transaction(function () use ($records) {
            DB::table(self::TABLE)->delete();

            foreach (array_chunk($records, 100) as $chunk) {
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

        return DB::table(self::TABLE)->select(self::HEADERS)->cursor();
    }

    public function downloadColumns(): array
    {
        return self::HEADERS;
    }

    public function templateHtmlTable(): string
    {
        $cells = array_map(fn ($header) => '<th>' . htmlspecialchars($header, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</th>', self::HEADERS);

        return "\xEF\xBB\xBF" . '<table border="1"><thead><tr>' . implode('', $cells) . '</tr></thead><tbody></tbody></table>';
    }

    private function assertTableReady(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            throw new RuntimeException('A tabela access não foi encontrada.');
        }

        $columns = Schema::getColumnListing(self::TABLE);
        $missing = array_values(array_diff(self::HEADERS, $columns));

        if ($missing !== []) {
            throw new RuntimeException('Colunas ausentes na tabela access: ' . implode(', ', $missing));
        }
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

        $worksheetPath = $this->firstWorksheetPath($workbookXml, $workbookRelsXml);
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

    private function firstWorksheetPath(string $workbookXml, string $workbookRelsXml): string
    {
        $workbook = $this->xml($workbookXml, 'Erro ao processar a estrutura de abas do arquivo XLSX.');
        $rels = $this->xml($workbookRelsXml, 'Erro ao processar os relacionamentos das abas.');
        $relationshipId = null;

        foreach ($workbook->sheets->sheet as $sheet) {
            $attributes = $sheet->attributes('r', true);
            $relationshipId = (string) $attributes['id'];
            break;
        }

        if (! $relationshipId) {
            throw new RuntimeException('Nenhuma aba foi encontrada no arquivo.');
        }

        foreach ($rels->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $relationshipId) {
                $target = ltrim(str_replace('\\', '/', (string) $relationship['Target']), '/');
                return str_starts_with($target, 'xl/') ? $target : 'xl/' . $target;
            }
        }

        throw new RuntimeException('Não foi possível localizar a primeira planilha do arquivo.');
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

        return isset($cell->v) ? (string) $cell->v : '';
    }

    private function recordsFromRows(array $rows): array
    {
        $actualHeaders = $rows[0];
        $errors = [];

        foreach (self::HEADERS as $index => $header) {
            $actual = trim((string) ($actualHeaders[$index] ?? ''));

            if ($actual !== $header) {
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

            $record = [];
            foreach (self::HEADERS as $index => $header) {
                $value = $row[$index] ?? null;
                $record[$header] = str_starts_with($header, 'DT_') ? $this->parseDateValue($value) : $this->blankToNull($value);
            }
            $records[] = $record;
        }

        return $records;
    }

    private function blankRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function blankToNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function parseDateValue(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            $number = (float) $value;

            if ($number > 25569 && $number < 60000) {
                $date = new DateTime('1899-12-30');
                $date->add(new DateInterval('P' . (int) floor($number) . 'D'));
                return $date->format('Y-m-d');
            }

            if ($number >= 1900 && $number <= 2100) {
                return sprintf('%04d-01-01', (int) $number);
            }
        }

        $value = trim((string) $value);

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $value, $match)) {
            $year = (int) $match[3];
            if ($year < 100) {
                $year += 2000;
            }

            return sprintf('%04d-%02d-%02d', $year, (int) $match[2], (int) $match[1]);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return substr($value, 0, 10);
        }

        return $value;
    }

    private function formatDisplayDate(mixed $value): string
    {
        $value = (string) $value;

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return (new DateTime(substr($value, 0, 10)))->format('d/m/Y');
        }

        return $value;
    }

    private function xml(string $xml, string $message): SimpleXMLElement
    {
        $element = simplexml_load_string($xml);

        if ($element === false) {
            throw new RuntimeException($message);
        }

        return $element;
    }

    private function columnIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }
}
