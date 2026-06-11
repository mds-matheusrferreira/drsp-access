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
    public const TABLE = 'access';

    public const HEADERS = [
        'protocolo',
        'entidade',
        'cnpj',
        'municipio',
        'uf',
        'cod_localizacao',
        'processos_anexados',
        'dt_protocolo',
        'orgao_origem',
        'dt_recebimento_mds',
        'motivo_recebimento',
        'tipo_processo',
        'dt_certificacao_anterior_inicio',
        'dt_certificacao_anterior_fim',
        'tempestividade',
        'dt_publicacao_certificacao_anterior_dou',
        'orgao_certificacao_anterior',
        'orgao_encaminhamento',
        'oficio_encaminhamento',
        'dt_encaminhamento',
        'motivo_encaminhamento',
        'dt_retorno_mds',
        'oficio_retorno',
        'oficio_diligencia',
        'dt_oficio_diligencia',
        'dt_envio_oficio',
        'dt_recebimento_ar_diligencia',
        'dt_protocolo_resposta_diligencia',
        'oficio_complementar',
        'dt_oficio_complementar',
        'dt_envio_oficio_complementar',
        'dt_recebimento_ar_complementar',
        'dt_protocolo_resposta_complementar',
        'parecer_nota_tecnica',
        'decisao_parecer',
        'motivo_indeferimento',
        'portarias_snas',
        'dt_decisao_snas',
        'dt_publicacao_portaria_snas_dou',
        'item_portaria_decisao_snas',
        'pagina_decisao_snas_dou',
        'ministerio_certificador_competente',
        'juizo_acao_judicial',
        'acao_judicial',
        'protocolo_recurso_snas',
        'dt_protocolo_recurso_snas',
        'parecer_nota_tecnica_recurso_snas',
        'decisao_reconsideracao_snas',
        'portaria_decisao_recurso_snas',
        'dt_portaria_reconsideracao_snas',
        'dt_publicacao_dou_reconsideracao_snas',
        'decisao_recurso_gm',
        'motivo_indeferimento_gm',
        'portaria_decisao_recurso_gm',
        'dt_portaria_decisao_recurso_gm',
        'dt_publicacao_dou_portaria_decisao_recurso_gm',
        'status_processo',
        'fase_processo',
        'dt_inicio_certificacao_atual',
        'dt_fim_certificacao_atual',
        'perfil_risco',
        'comprovante_inscricao_cnpj',
        'comprovante_inscricao_cnpj_fls',
        'estatuto_legal',
        'estatuto_legal_fls',
        'destino_patrimonio_caso_dissolucao',
        'ata_eleicao',
        'ata_eleicao_fls',
        'comprovante_inscricao_cmas',
        'comprovante_inscricao_cmas_fls',
        'relatorio_atividades',
        'relatorio_atividades_fls',
        'gratuidade',
        'gratuidade_fls',
        'rede_assistencia_social',
        'acolhimento_idosos',
        'receita_bruta_anual',
        'dt_requisitos_legais',
        'diligencia_email',
        'dt_envio_diligencia',
        'analista_requisitos_legais',
        'documentos_obrigatorios',
        'documentos_pendentes',
        'compatibilidade_estatuto_loas',
        'atividades_relatorio',
        'gratuidade_parecer',
        'continuidade_planejamento_universalidade',
        'caracteristica_i',
        'oferta_i',
        'usuario_i',
        'caracteristica_ii',
        'oferta_ii',
        'usuario_ii',
        'caracteristica_iii',
        'oferta_iii',
        'usuario_iii',
        'caracteristica_iv',
        'oferta_iv',
        'usuario_iv',
        'caracteristica_v',
        'oferta_v',
        'usuario_v',
        'oferta_vi',
        'usuario_vi',
        'oferta_vii',
        'usuario_vii',
        'vagas_vi',
        'vagas_vii',
        'outras_ofertas',
        'outras_ofertas_i',
        'ofertas_outras_areas',
        'cnae',
        'analista_manifestacao',
        'cgceb_manifestacao',
        'drsp_manifestacao',
        'dt_pedido_manifestacao',
        'manifestacao_outro_ministerio',
        'nota_tecnica_outro_orgao',
        'analista_parecer',
        'cgceb_parecer',
        'drsp_parecer',
        'dt_parecer',
        'atividades_parecer_misto',
        'pedido_manifestacao_encaminhamento',
        'numero_parecer_nt',
        'justificativa_indeferimento',
        'dt_diligencia_email',
        'continuidade',
        'planejamento',
        'universalidade',
        'justificativa_indeferimento_nt',
        'responsavel_analise',
        'responsavel_apreciacao',
        'dt_apreciacao',
        'encaminhamento_manifestacao',
        'responsavel_nota_tecnica',
        'responsavel_enc',
        'qualificacao_usuario_i',
        'qualificacao_usuario_ii',
        'qualificacao_usuario_iii',
        'qualificacao_usuario_iv',
        'qualificacao_usuario_v',
        'qualificacao_usuario_vi',
        'qualificacao_usuario_vii',
        'supervisao_recomendada',
        'obs_cceb_i',
        'obs_cceb_ii',
        'caracteristicas_ofertas',
        'email_entidade',
        'protocolo_sei',
        'fase_recurso',
        'motivo_arquivamento',
        'dt_arquivamento',
        'isencao_usufruida',
        'obs_pedido_manifestacao',
        'ass_snas',
        'responsavel_supervisao',
        'vagas_i',
        'vagas_ii',
        'vagas_iii',
        'vagas_iv',
        'vagas_v',
        'outras_atividades',
        'discrepancia',
        'passivo',
        'manifestacao_mec_ms_fls',
        'tipo_distribuicao',
        'dt_atualizacao',
        'dt_distribuicao',
        'obs_email_diligencia',
        'situacao_cneas',
        'base_orgao',
        'ativo',
    ];

    public function stats(): array
    {
        if (! Schema::hasTable(self::TABLE)) {
            return ['total' => 0, 'updated_at' => null];
        }

        $updatedAt = null;
        if (Schema::hasColumn(self::TABLE, 'dt_atualizacao')) {
            $updatedAt = DB::table(self::TABLE)
                ->whereNotNull('dt_atualizacao')
                ->orderByDesc('dt_atualizacao')
                ->value('dt_atualizacao');
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
                $record[$header] = str_starts_with($header, 'dt_') ? $this->parseDateValue($value) : $this->blankToNull($value);
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
