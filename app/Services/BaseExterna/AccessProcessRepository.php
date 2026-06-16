<?php

namespace App\Services\BaseExterna;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AccessProcessRepository
{
    private const TABLE = 'access';

    /**
     * @var array<int, string>
     */
    private const SEARCH_COLUMNS = ['protocolo', 'protocolo_sei', 'entidade', 'cnpj', 'municipio', 'uf'];

    /**
     * @var array<int, string>
     */
    private const TECHNICAL_COLUMNS = ['id'];

    /**
     * @var array<int, string>
     */
    private const BOOLEAN_COLUMNS = [
        'comprovante_inscricao_cnpj',
        'estatuto_legal',
        'ata_eleicao',
        'comprovante_inscricao_cmas',
        'relatorio_atividades',
        'acolhimento_idosos',
        'diligencia_email',
    ];

    /**
     * @var array<int, string>
     */
    private const TEXTAREA_KEYWORDS = [
        'obs',
        'justificativa',
        'documentos',
        'atividades',
        'caracteristica',
        'caracteristicas',
        'oferta',
        'usuario',
        'motivo',
        'parecer',
        'nota_tecnica',
    ];

    /**
     * @var array<int, string>
     */
    private const PARECER_HEADER_COLUMNS = [
        'entidade',
        'protocolo',
        'protocolo_sei',
        'tipo_processo',
        'cnpj',
        'dt_protocolo',
        'situacao_cneas',
        'municipio',
        'uf',
        'dt_certificacao_anterior_inicio',
        'dt_certificacao_anterior_fim',
        'fase_processo',
        'status_processo',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const PARECER_SECTION_DEFINITIONS = [
        'Análise técnica' => [
            'documentos_obrigatorios',
            'documentos_pendentes',
            'compatibilidade_estatuto_loas',
            'destino_patrimonio_caso_dissolucao',
        ],
        'Atividades do relatório' => [
            'oferta_i',
            'vagas_i',
            'usuario_i',
            'qualificacao_usuario_i',
            'oferta_ii',
            'vagas_ii',
            'usuario_ii',
            'qualificacao_usuario_ii',
            'oferta_iii',
            'vagas_iii',
            'usuario_iii',
            'qualificacao_usuario_iii',
            'oferta_iv',
            'vagas_iv',
            'usuario_iv',
            'qualificacao_usuario_iv',
            'oferta_v',
            'vagas_v',
            'usuario_v',
            'qualificacao_usuario_v',
            'oferta_vi',
            'vagas_vi',
            'usuario_vi',
            'qualificacao_usuario_vi',
            'oferta_vii',
            'vagas_vii',
            'usuario_vii',
            'qualificacao_usuario_vii',
            'outras_atividades',
        ],
        'Gratuidade e manifestações' => [
            'gratuidade_parecer',
            'orgao_encaminhamento',
            'nota_tecnica_outro_orgao',
            'manifestacao_outro_ministerio',
            'ofertas_outras_areas',
        ],
        'Princípios de Atendimento da Assistência Social' => [
            'continuidade',
            'planejamento',
            'universalidade',
        ],
        'Conclusão do parecer' => [
            'decisao_parecer',
            'motivo_indeferimento',
            'justificativa_indeferimento',
        ],
        'Assinaturas' => [
            'cgceb_parecer',
            'drsp_parecer',
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const PARECER_LABELS = [
        'protocolo' => 'Protocolo',
        'protocolo_sei' => 'Protocolo SEI',
        'tipo_processo' => 'Tipo de Processo',
        'cnpj' => 'C.N.P.J.',
        'dt_protocolo' => 'Data de Protocolo',
        'entidade' => 'Entidade',
        'municipio' => 'Município',
        'uf' => 'uf',
        'dt_certificacao_anterior_inicio' => 'Última Certificação (início)',
        'dt_certificacao_anterior_fim' => 'Última Certificação (fim)',
        'documentos_obrigatorios' => 'Documentos Obrigatórios',
        'documentos_pendentes' => 'Documentos pendentes',
        'compatibilidade_estatuto_loas' => 'Compatibilidade do estatuto com LOAS',
        'destino_patrimonio_caso_dissolucao' => 'Destino do patrimônio em caso de dissolução',
        'outras_atividades' => 'Atividades de outras áreas não certificáveis',
        'gratuidade_parecer' => 'Gratuidade',
        'orgao_encaminhamento' => 'Manifestação de outro órgão',
        'nota_tecnica_outro_orgao' => 'Número(s)',
        'manifestacao_outro_ministerio' => 'Manifestação de outro ministério',
        'ofertas_outras_areas' => 'Outras atividades (saúde e/ou educação)',
        'continuidade' => 'Continuidade',
        'planejamento' => 'Planejamento',
        'universalidade' => 'Universalidade',
        'decisao_parecer' => 'Conclusão do parecer',
        'motivo_indeferimento' => 'Motivos de indeferimento',
        'justificativa_indeferimento' => 'Exposição de motivos',
        'justificativa_indeferimento_nt' => 'Motivos de indeferimento',
        'analista_parecer' => 'Analista',
        'cgceb_parecer' => 'CGCEB/DRSP/SNAS/MDS',
        'drsp_parecer' => 'DRSP/SNAS/MDS',
        'responsavel_nota_tecnica' => 'Responsável Nota Técnica',
    ];

    public function tableExists(): bool
    {
        return Schema::hasTable(self::TABLE);
    }

    /**
     * @return array<int, string>
     */
    public function columns(): array
    {
        if (! $this->tableExists()) {
            return [];
        }

        return array_values(array_filter(
            Schema::getColumnListing(self::TABLE),
            fn (string $column) => ! in_array($column, self::TECHNICAL_COLUMNS, true)
        ));
    }

    /**
     * @return array<string, string>
     */
    public function columnTypes(): array
    {
        $types = [];

        foreach ($this->columns() as $column) {
            try {
                $types[$column] = Schema::getColumnType(self::TABLE, $column);
            } catch (\Throwable) {
                $types[$column] = 'string';
            }
        }

        return $types;
    }

    /**
     * @return array{search: string, columns: array<int, string>, data: array<int, array<string, mixed>>, count_total: int}
     */
    public function search(string $term, int $limit = 100): array
    {
        $term = trim($term);
        $columns = $this->columns();
        $searchColumns = array_values(array_intersect(self::SEARCH_COLUMNS, $columns));

        if (! $this->tableExists() || $term === '' || $searchColumns === []) {
            return [
                'search' => $term,
                'columns' => $columns,
                'data' => [],
                'count_total' => 0,
            ];
        }

        $query = DB::table(self::TABLE)->where(function ($query) use ($searchColumns, $term) {
            foreach ($searchColumns as $column) {
                $query->orWhere($column, 'like', '%'.$term.'%');
            }
        });

        $count = (clone $query)->count();
        $rows = $query
            ->limit(max(1, min($limit, 100)))
            ->get()
            ->map(function ($row) {
                $data = (array) $row;
                $protocol = trim((string) ($data['protocolo'] ?? ''));
                $protocolCount = $protocol === '' ? 0 : $this->protocolCount($protocol);

                $data['_can_edit'] = $protocol !== '' && $protocolCount === 1;
                $data['_edit_block_reason'] = match (true) {
                    $protocol === '' => 'Sem protocolo',
                    $protocolCount > 1 => 'Protocolo duplicado',
                    default => null,
                };

                return $data;
            })
            ->all();

        return [
            'search' => $term,
            'columns' => $columns,
            'data' => $rows,
            'count_total' => $count,
        ];
    }

    public function protocolCount(string $protocolo): int
    {
        $protocolo = trim($protocolo);

        if (! $this->tableExists() || $protocolo === '' || ! Schema::hasColumn(self::TABLE, 'protocolo')) {
            return 0;
        }

        return DB::table(self::TABLE)->where('protocolo', $protocolo)->count();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByProtocolo(string $protocolo): ?array
    {
        $protocolo = trim($protocolo);

        if ($this->protocolCount($protocolo) !== 1) {
            return null;
        }

        $row = DB::table(self::TABLE)->where('protocolo', $protocolo)->first();

        return $row ? (array) $row : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateByProtocolo(string $originalProtocolo, array $data): int
    {
        $originalProtocolo = trim($originalProtocolo);

        if ($this->protocolCount($originalProtocolo) !== 1) {
            return 0;
        }

        $sanitized = $this->sanitizeForUpdate($data);

        if ($sanitized === []) {
            return 0;
        }

        return DB::table(self::TABLE)
            ->where('protocolo', $originalProtocolo)
            ->update($sanitized);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function sanitizeForUpdate(array $data): array
    {
        $columns = $this->columns();
        $types = $this->columnTypes();
        $sanitized = [];

        if (($data['documentos_obrigatorios'] ?? null) !== 'Não apresentou todos os documentos') {
            $data['documentos_pendentes'] = null;
        }

        foreach ($columns as $column) {
            if (! array_key_exists($column, $data)) {
                continue;
            }

            $value = $data[$column];

            if ($column === 'documentos_pendentes' && ($data['documentos_obrigatorios'] ?? null) !== 'Não apresentou todos os documentos') {
                $value = null;
            }

            if ($column === 'motivo_indeferimento' && ($data['decisao_parecer'] ?? null) !== 'INDEFERIDO') {
                $value = null;
            }

            if (is_array($value)) {
                $value = implode("\n", array_filter(array_map('trim', $value), fn ($item) => $item !== ''));
            }

            if (is_string($value)) {
                $value = trim($value);
                $value = $value === '' ? null : $value;
            }

            if ($value !== null && str_starts_with($column, 'dt_') && is_string($value) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                $value = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            if ($value !== null && $this->isBooleanColumn($column, $types[$column] ?? null)) {
                $value = (int) $value;
            }

            if ($value !== null && $this->isNumericColumn($column, $types[$column] ?? null)) {
                $value = str_replace(',', '.', (string) $value);
            }

            $sanitized[$column] = $value;
        }

        return $sanitized;
    }

    /**
     * @return array<int, array{title: string, fields: array<int, string>}>
     */
    public function fieldSections(): array
    {
        $remaining = $this->columns();
        $sections = [];

        foreach ($this->sectionDefinitions() as $title => $fields) {
            $present = array_values(array_intersect($fields, $remaining));

            if ($present === []) {
                continue;
            }

            $sections[] = ['title' => $title, 'fields' => $present];
            $remaining = array_values(array_diff($remaining, $present));
        }

        $automaticGroups = [
            'Datas e recebimento' => fn (string $field) => str_starts_with($field, 'dt_') || str_contains($field, 'recebimento') || str_contains($field, 'tempestividade'),
            'Encaminhamentos' => fn (string $field) => str_contains($field, 'encaminhamento') || str_contains($field, '_enc'),
            'Diligências' => fn (string $field) => str_contains($field, 'diligencia') || str_contains($field, 'complementar'),
            'Parecer/decisão' => fn (string $field) => str_contains($field, 'parecer') || str_contains($field, 'decisao') || str_contains($field, 'portaria') || str_contains($field, 'indefer'),
            'Requisitos legais' => fn (string $field) => str_contains($field, 'cnpj') || str_contains($field, 'estatuto') || str_contains($field, 'ata_') || str_contains($field, 'cmas') || str_contains($field, 'relatorio') || str_contains($field, 'gratuidade') || str_contains($field, 'loas') || str_contains($field, 'cneas'),
            'Ofertas/usuários/vagas' => fn (string $field) => str_contains($field, 'oferta') || str_contains($field, 'usuario') || str_contains($field, 'vagas') || str_contains($field, 'acolhimento') || str_contains($field, 'rede_assistencia'),
        ];

        foreach ($automaticGroups as $title => $matches) {
            $present = array_values(array_filter($remaining, $matches));

            if ($present === []) {
                continue;
            }

            $sections[] = ['title' => $title, 'fields' => $present];
            $remaining = array_values(array_diff($remaining, $present));
        }

        if ($remaining !== []) {
            $sections[] = ['title' => 'Campos adicionais', 'fields' => $remaining];
        }

        return $sections;
    }

    /**
     * @return array<int, string>
     */
    public function parecerTecnicoHeaderColumns(): array
    {
        return array_values(array_intersect(self::PARECER_HEADER_COLUMNS, $this->columns()));
    }

    /**
     * @return array<int, string>
     */
    public function parecerTecnicoColumns(): array
    {
        $columns = array_values(array_diff(self::PARECER_HEADER_COLUMNS, ['protocolo']));

        foreach (self::PARECER_SECTION_DEFINITIONS as $fields) {
            $columns = array_merge($columns, $fields);
        }

        return array_values(array_intersect(array_unique($columns), $this->columns()));
    }

    /**
     * @return array<int, array{title: string, fields: array<int, string>}>
     */
    public function parecerTecnicoSections(): array
    {
        $columns = $this->columns();
        $sections = [];

        foreach (self::PARECER_SECTION_DEFINITIONS as $title => $fields) {
            $present = array_values(array_intersect($fields, $columns));

            if ($present !== []) {
                $sections[] = ['title' => $title, 'fields' => $present];
            }
        }

        return $sections;
    }

    public function parecerTecnicoLabel(string $field): string
    {
        return self::PARECER_LABELS[$field] ?? $this->fieldLabel($field);
    }

    public function fieldLabel(string $field): string
    {
        return Str::of($field)
            ->replace('_', ' ')
            ->lower()
            ->ucfirst()
            ->toString();
    }

    public function inputType(string $field, ?string $databaseType = null): string
    {
        if ($this->isBooleanColumn($field, $databaseType)) {
            return 'boolean';
        }

        if ($this->isNumericColumn($field, $databaseType)) {
            return 'number';
        }

        if (str_starts_with($field, 'dt_')) {
            return 'date';
        }

        foreach (self::TEXTAREA_KEYWORDS as $keyword) {
            if (str_contains($field, $keyword)) {
                return 'textarea';
            }
        }

        return 'text';
    }

    public function formatValueForInput(mixed $value, string $inputType): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($inputType === 'date' && is_string($value)) {
            return substr($value, 0, 10);
        }

        return $value;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function sectionDefinitions(): array
    {
        return [
            'Identificação' => [
                'protocolo',
                'protocolo_sei',
                'tipo_processo',
                'entidade',
                'cnpj',
                'email_entidade',
                'municipio',
                'uf',
                'cod_localizacao',
                'processos_anexados',
                'status_processo',
                'fase_processo',
                'fase_recurso',
                'base_orgao',
                'ativo',
                'passivo',
                'tipo_distribuicao',
            ],
            'Datas e recebimento' => [
                'dt_protocolo',
                'orgao_origem',
                'dt_recebimento_mds',
                'motivo_recebimento',
                'dt_certificacao_anterior_inicio',
                'dt_certificacao_anterior_fim',
                'tempestividade',
                'dt_publicacao_certificacao_anterior_dou',
                'orgao_certificacao_anterior',
                'dt_inicio_certificacao_atual',
                'dt_fim_certificacao_atual',
                'dt_atualizacao',
                'dt_distribuicao',
            ],
            'Encaminhamentos' => [
                'orgao_encaminhamento',
                'oficio_encaminhamento',
                'dt_encaminhamento',
                'motivo_encaminhamento',
                'dt_retorno_mds',
                'oficio_retorno',
                'pedido_manifestacao_encaminhamento',
                'encaminhamento_manifestacao',
                'responsavel_enc',
            ],
            'Diligências' => [
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
                'diligencia_email',
                'dt_envio_diligencia',
                'dt_diligencia_email',
                'obs_email_diligencia',
            ],
            'Parecer/decisão' => [
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
                'justificativa_indeferimento',
                'justificativa_indeferimento_nt',
                'responsavel_nota_tecnica',
            ],
            'Requisitos legais' => [
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
                'receita_bruta_anual',
                'dt_requisitos_legais',
                'analista_requisitos_legais',
                'documentos_obrigatorios',
                'documentos_pendentes',
                'compatibilidade_estatuto_loas',
                'atividades_relatorio',
                'gratuidade_parecer',
                'continuidade_planejamento_universalidade',
                'continuidade',
                'planejamento',
                'universalidade',
                'situacao_cneas',
                'supervisao_recomendada',
                'responsavel_supervisao',
            ],
            'Ofertas/usuários/vagas' => [
                'rede_assistencia_social',
                'acolhimento_idosos',
                'caracteristica_i',
                'oferta_i',
                'usuario_i',
                'vagas_i',
                'qualificacao_usuario_i',
                'caracteristica_ii',
                'oferta_ii',
                'usuario_ii',
                'vagas_ii',
                'qualificacao_usuario_ii',
                'caracteristica_iii',
                'oferta_iii',
                'usuario_iii',
                'vagas_iii',
                'qualificacao_usuario_iii',
                'caracteristica_iv',
                'oferta_iv',
                'usuario_iv',
                'vagas_iv',
                'qualificacao_usuario_iv',
                'caracteristica_v',
                'oferta_v',
                'usuario_v',
                'vagas_v',
                'qualificacao_usuario_v',
                'oferta_vi',
                'usuario_vi',
                'vagas_vi',
                'qualificacao_usuario_vi',
                'oferta_vii',
                'usuario_vii',
                'vagas_vii',
                'qualificacao_usuario_vii',
                'outras_ofertas',
                'outras_ofertas_i',
                'ofertas_outras_areas',
                'outras_atividades',
                'caracteristicas_ofertas',
            ],
        ];
    }

    private function isBooleanColumn(string $field, ?string $databaseType): bool
    {
        return in_array($field, self::BOOLEAN_COLUMNS, true)
            || in_array($databaseType, ['boolean', 'tinyint'], true);
    }

    private function isNumericColumn(string $field, ?string $databaseType): bool
    {
        if ($this->isBooleanColumn($field, $databaseType)) {
            return false;
        }

        return in_array($databaseType, ['integer', 'bigint', 'float', 'double', 'decimal'], true)
            || str_starts_with((string) $databaseType, 'decimal')
            || in_array($field, [
                'cod_localizacao',
                'item_portaria_decisao_snas',
                'pagina_decisao_snas_dou',
                'receita_bruta_anual',
                'analista_manifestacao',
                'cgceb_manifestacao',
                'drsp_manifestacao',
                'manifestacao_outro_ministerio',
                'analista_parecer',
                'cgceb_parecer',
                'drsp_parecer',
                'isencao_usufruida',
                'ass_snas',
                'responsavel_supervisao',
                'vagas_i',
                'vagas_ii',
                'vagas_iii',
                'vagas_iv',
                'vagas_v',
                'vagas_vi',
                'vagas_vii',
            ], true);
    }
}
