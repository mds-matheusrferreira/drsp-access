<?php

namespace App\Services\BaseExterna;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AccessProcessRepository
{
    private const TABLE = 'processos_sei';

    /**
     * @var array<int, string>
     */
    private const SEARCH_COLUMNS = ['PROTOCOLO', 'PROTOCOLO_SEI', 'ENTIDADE', 'CNPJ', 'MUNICIPIO', 'UF'];

    /**
     * @var array<int, string>
     */
    private const TECHNICAL_COLUMNS = ['id'];

    /**
     * @var array<int, string>
     */
    private const BOOLEAN_COLUMNS = [
        'COMPROVANTE_INSCRICAO_CNPJ',
        'ESTATUTO_LEGAL',
        'ATA_ELEICAO',
        'COMPROVANTE_INSCRICAO_CMAS',
        'RELATORIO_ATIVIDADES',
        'ACOLHIMENTO_IDOSOS',
        'DILIGENCIA_EMAIL',
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
        'ENTIDADE',
        'PROTOCOLO',
        'PROTOCOLO_SEI',
        'TIPO_PROCESSO',
        'CNPJ',
        'DT_PROTOCOLO',
        'SITUAÇÃO_CNEAS',
        'MUNICIPIO',
        'UF',
        'DT_CERTIFICACAO_ANTERIOR_INICIO',
        'DT_CERTIFICACAO_ANTERIOR_FIM',
        'FASE_PROCESSO',
        'STATUS_PROCESSO',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const NOTA_TECNICA_SECTION_DEFINITIONS = [
        'Análise técnica' => [
            'DOCUMENTOS_OBRIGATORIOS',
            'DOCUMENTOS_PENDENTES',
            'COMPATIBILIDADE_ESTATUTO_LOAS',
            'DESTINO_PATRIMONIO_CASO_DISSOLUCAO',
        ],
        'Atividades do relatório' => [
            'OFERTA_I',
            'VAGAS_I',
            'USUARIO_I',
            'QUALIFICACAO_USUARIO_I',
            'OFERTA_II',
            'VAGAS_II',
            'USUARIO_II',
            'QUALIFICACAO_USUARIO_II',
            'OFERTA_III',
            'VAGAS_III',
            'USUARIO_III',
            'QUALIFICACAO_USUARIO_III',
            'OFERTA_IV',
            'VAGAS_IV',
            'USUARIO_IV',
            'QUALIFICACAO_USUARIO_Iv',
            'OFERTA_V',
            'VAGAS_V',
            'USUARIO_V',
            'QUALIFICACAO_USUARIO_V',
            'OFERTA_VI',
            'VAGAS_VI',
            'USUARIO_VI',
            'QUALIFICACAO_USUARIO_VI',
            'OFERTA_VII',
            'VAGAS_VII',
            'USUARIO_VII',
            'QUALIFICACAO_USUARIO_VII',
            'OUTRAS_ATIVIDADES',
        ],
        'Gratuidade e manifestações' => [
            'GRATUIDADE_PARECER',
            'PEDIDO_MANIFESTACAO_ENCAMINHAMENTO',
            'ORGAO_ENCAMINHAMENTO',
            'NOTA_TECNICA_OUTRO_ORGAO',
            'OFERTAS_OUTRAS_AREAS',
        ],
        'Princípios de Atendimento da Assistência Social' => [
            'CONTINUIDADE',
            'PLANEJAMENTO',
            'UNIVERSALIDADE',
        ],
        'Conclusão do parecer' => [
            'DECISAO_PARECER',
            'MOTIVO_INDEFERIMENTO',
            'JUSTIFICATIVA_INDEFERIMENTO_NT',
        ],
        'Assinaturas' => [
            'CGCEB_PARECER',
            'DRSP_PARECER',
        ],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const PARECER_SECTION_DEFINITIONS = [
        'Análise técnica' => [
            'DOCUMENTOS_OBRIGATORIOS',
            'DOCUMENTOS_PENDENTES',
            'COMPATIBILIDADE_ESTATUTO_LOAS',
            'DESTINO_PATRIMONIO_CASO_DISSOLUCAO',
        ],
        'Atividades do relatório' => [
            'OFERTA_I',
            'VAGAS_I',
            'USUARIO_I',
            'QUALIFICACAO_USUARIO_I',
            'OFERTA_II',
            'VAGAS_II',
            'USUARIO_II',
            'QUALIFICACAO_USUARIO_II',
            'OFERTA_III',
            'VAGAS_III',
            'USUARIO_III',
            'QUALIFICACAO_USUARIO_III',
            'OFERTA_IV',
            'VAGAS_IV',
            'USUARIO_IV',
            'QUALIFICACAO_USUARIO_Iv',
            'OFERTA_V',
            'VAGAS_V',
            'USUARIO_V',
            'QUALIFICACAO_USUARIO_V',
            'OFERTA_VI',
            'VAGAS_VI',
            'USUARIO_VI',
            'QUALIFICACAO_USUARIO_VI',
            'OFERTA_VII',
            'VAGAS_VII',
            'USUARIO_VII',
            'QUALIFICACAO_USUARIO_VII',
            'OUTRAS_ATIVIDADES',
        ],
        'Gratuidade e manifestações' => [
            'GRATUIDADE_PARECER',
            'ORGAO_ENCAMINHAMENTO',
            'NOTA_TECNICA_OUTRO_ORGAO',
            'OFERTAS_OUTRAS_AREAS',
        ],
        'Princípios de Atendimento da Assistência Social' => [
            'CONTINUIDADE',
            'PLANEJAMENTO',
            'UNIVERSALIDADE',
        ],
        'Conclusão do parecer' => [
            'DECISAO_PARECER',
            'MOTIVO_INDEFERIMENTO',
            'JUSTIFICATIVA_INDEFERIMENTO',
        ],
        'Assinaturas' => [
            'CGCEB_PARECER',
            'DRSP_PARECER',
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const PARECER_LABELS = [
        'PROTOCOLO' => 'Protocolo',
        'PROTOCOLO_SEI' => 'Protocolo SEI',
        'TIPO_PROCESSO' => 'Tipo de Processo',
        'CNPJ' => 'C.N.P.J.',
        'DT_PROTOCOLO' => 'Data de Protocolo',
        'ENTIDADE' => 'Entidade',
        'MUNICIPIO' => 'Município',
        'UF' => 'uf',
        'DT_CERTIFICACAO_ANTERIOR_INICIO' => 'Última Certificação (início)',
        'DT_CERTIFICACAO_ANTERIOR_FIM' => 'Última Certificação (fim)',
        'DOCUMENTOS_OBRIGATORIOS' => 'Documentos Obrigatórios',
        'DOCUMENTOS_PENDENTES' => 'Documentos pendentes',
        'COMPATIBILIDADE_ESTATUTO_LOAS' => 'Compatibilidade do estatuto com LOAS',
        'DESTINO_PATRIMONIO_CASO_DISSOLUCAO' => 'Destino do patrimônio em caso de dissolução',
        'OUTRAS_ATIVIDADES' => 'Atividades de outras áreas não certificáveis',
        'GRATUIDADE_PARECER' => 'Gratuidade',
        'PEDIDO_MANIFESTACAO_ENCAMINHAMENTO' => 'Tipo de encaminhamento',
        'ORGAO_ENCAMINHAMENTO' => 'Manifestação de outro órgão',
        'NOTA_TECNICA_OUTRO_ORGAO' => 'Número(s)',
        'MANIFESTACAO_OUTRO_MINISTERIO' => 'Manifestação de outro ministério',
        'OFERTAS_OUTRAS_AREAS' => 'Outras atividades (saúde e/ou educação)',
        'CONTINUIDADE' => 'Continuidade',
        'PLANEJAMENTO' => 'Planejamento',
        'UNIVERSALIDADE' => 'Universalidade',
        'DECISAO_PARECER' => 'Conclusão do parecer',
        'MOTIVO_INDEFERIMENTO' => 'Motivos de indeferimento',
        'JUSTIFICATIVA_INDEFERIMENTO' => 'Exposição de motivos',
        'JUSTIFICATIVA_INDEFERIMENTO_NT' => 'Observações',
        'ANALISTA_PARECER' => 'Analista',
        'CGCEB_PARECER' => 'CGCEB/DRSP/SNAS/MDS',
        'DRSP_PARECER' => 'DRSP/SNAS/MDS',
        'RESPONSAVEL_NOTA_TECNICA' => 'Responsável Nota Técnica',
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

        $strippedTerm = preg_replace('/[^0-9]/', '', $term);

        $query = DB::table(self::TABLE)->where(function ($query) use ($searchColumns, $term, $strippedTerm) {
            foreach ($searchColumns as $column) {
                $query->orWhere($column, 'like', '%'.$term.'%');
                if ($column === 'CNPJ' && $strippedTerm !== '' && $strippedTerm !== $term) {
                    $query->orWhere($column, 'like', '%'.$strippedTerm.'%');
                }
            }
        });

        $count = (clone $query)->count();
        $rows = $query
            ->limit(max(1, min($limit, 100)))
            ->get()
            ->map(function ($row) {
                $data = (array) $row;
                $protocol = trim((string) ($data['PROTOCOLO'] ?? ''));
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

        if (! $this->tableExists() || $protocolo === '' || ! Schema::hasColumn(self::TABLE, 'PROTOCOLO')) {
            return 0;
        }

        return DB::table(self::TABLE)->where('PROTOCOLO', $protocolo)->count();
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

        $row = DB::table(self::TABLE)->where('PROTOCOLO', $protocolo)->first();

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
            ->where('PROTOCOLO', $originalProtocolo)
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

        if (($data['DOCUMENTOS_OBRIGATORIOS'] ?? null) !== 'Não apresentou todos os documentos') {
            $data['DOCUMENTOS_PENDENTES'] = null;
        }

        foreach ($columns as $column) {
            if (! array_key_exists($column, $data)) {
                continue;
            }

            $value = $data[$column];

            if ($column === 'DOCUMENTOS_PENDENTES' && ($data['DOCUMENTOS_OBRIGATORIOS'] ?? null) !== 'Não apresentou todos os documentos') {
                $value = null;
            }

            if ($column === 'MOTIVO_INDEFERIMENTO' && ($data['DECISAO_PARECER'] ?? null) !== 'INDEFERIDO') {
                $value = null;
            }

            if (is_array($value)) {
                $value = implode("\n", array_filter(array_map('trim', $value), fn ($item) => $item !== ''));
            }

            if (is_string($value)) {
                $value = str_replace('_x000D_', "\n", $value);
                $value = trim($value);
                $value = $value === '' ? null : $value;
            }

            if ($value !== null && str_starts_with($column, 'DT_') && is_string($value) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
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
            'Datas e recebimento' => fn (string $field) => str_starts_with($field, 'DT_') || str_contains($field, 'RECEBIMENTO') || str_contains($field, 'TEMPESTIVIDADE'),
            'Encaminhamentos' => fn (string $field) => str_contains($field, 'ENCAMINHAMENTO') || str_contains($field, '_ENC'),
            'Diligências' => fn (string $field) => str_contains($field, 'DILIGENCIA') || str_contains($field, 'COMPLEMENTAR'),
            'Parecer/decisão' => fn (string $field) => str_contains($field, 'PARECER') || str_contains($field, 'DECISAO') || str_contains($field, 'PORTARIA') || str_contains($field, 'INDEFER'),
            'Requisitos legais' => fn (string $field) => str_contains($field, 'CNPJ') || str_contains($field, 'ESTATUTO') || str_contains($field, 'ATA_') || str_contains($field, 'CMAS') || str_contains($field, 'RELATORIO') || str_contains($field, 'GRATUIDADE') || str_contains($field, 'LOAS') || str_contains($field, 'CNEAS'),
            'Ofertas/usuários/vagas' => fn (string $field) => str_contains($field, 'OFERTA') || str_contains($field, 'USUARIO') || str_contains($field, 'VAGAS') || str_contains($field, 'ACOLHIMENTO') || str_contains($field, 'REDE_ASSISITENCIA'),
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
        $columns = array_values(array_diff(self::PARECER_HEADER_COLUMNS, ['PROTOCOLO']));

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

    /**
     * @return array<int, string>
     */
    public function notaTecnicaHeaderColumns(): array
    {
        return array_values(array_intersect(self::PARECER_HEADER_COLUMNS, $this->columns()));
    }

    /**
     * @return array<int, string>
     */
    public function notaTecnicaColumns(): array
    {
        $columns = array_values(array_diff(self::PARECER_HEADER_COLUMNS, ['PROTOCOLO']));

        foreach (self::NOTA_TECNICA_SECTION_DEFINITIONS as $fields) {
            $columns = array_merge($columns, $fields);
        }

        return array_values(array_intersect(array_unique($columns), $this->columns()));
    }

    /**
     * @return array<int, array{title: string, fields: array<int, string>}>
     */
    public function notaTecnicaSections(): array
    {
        $columns = $this->columns();
        $sections = [];

        foreach (self::NOTA_TECNICA_SECTION_DEFINITIONS as $title => $fields) {
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

        if (str_starts_with($field, 'DT_')) {
            return 'date';
        }

        $fieldLower = strtolower($field);
        foreach (self::TEXTAREA_KEYWORDS as $keyword) {
            if (str_contains($fieldLower, $keyword)) {
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
                'PROTOCOLO',
                'PROTOCOLO_SEI',
                'TIPO_PROCESSO',
                'ENTIDADE',
                'CNPJ',
                'EMAIL_ENTIDADE',
                'MUNICIPIO',
                'UF',
                'COD_LOCALIZACAO',
                'PROCESSOS_ANEXADOS',
                'STATUS_PROCESSO',
                'FASE_PROCESSO',
                'FASE_RECURSO',
                'BASE_ORGAO',
                'ATIVO',
                'PASSIVO',
                'TIPO_DISTRIBUICAO',
            ],
            'Datas e recebimento' => [
                'DT_PROTOCOLO',
                'ORGAO_ORIGEM',
                'DT_RECEBIMENTO_MDS',
                'MOTIVO_RECEBIMENTO',
                'DT_CERTIFICACAO_ANTERIOR_INICIO',
                'DT_CERTIFICACAO_ANTERIOR_FIM',
                'TEMPESTIVIDADE',
                'DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU',
                'ORGAO_CERTIFICACAO_ANTERIOR',
                'DT_INICIO_CERTIFICACAO_ATUAL',
                'DT_FIM_CERTIFICACAO_ATUAL',
                'DT_ATUALIZACAO',
                'DT_DISTRIBUICAO',
            ],
            'Encaminhamentos' => [
                'ORGAO_ENCAMINHAMENTO',
                'OFICIO_ENCAMINHAMENTO',
                'DT_ENCAMINHAMENTO',
                'MOTIVO_ENCAMINHAMENTO',
                'DT_RETORNO_MDS',
                'OFICIO_RETORNO',
                'PEDIDO_MANIFESTACAO_ENCAMINHAMENTO',
                'ENCAMINHAMENTO_MANIFESTACAO',
                'REPONSAVEL_ENC',
            ],
            'Diligências' => [
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
                'DILIGENCIA_EMAIL',
                'DT_ENVIO_DILIGENCIA',
                'DT_DILIGENCIA_EMAIL',
                'OBS_EMAIL_DILIGENCIA',
            ],
            'Parecer/decisão' => [
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
                'JUSTIFICATIVA_INDEFERIMENTO',
                'JUSTIFICATIVA_INDEFERIMENTO_NT',
                'RESPONSAVEL_NOTA_TECNICA',
            ],
            'Requisitos legais' => [
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
                'RECEITA_BRUTA_ANUAL',
                'DT_REQUISITOS_LEGAIS',
                'ANALISTA_REQUISITOS_LEGAIS',
                'DOCUMENTOS_OBRIGATORIOS',
                'DOCUMENTOS_PENDENTES',
                'COMPATIBILIDADE_ESTATUTO_LOAS',
                'ATIVIDADES_RELATORIO',
                'GRATUIDADE_PARECER',
                'CONTINUIDADE_PLANEJAMENTO_UNIVERSALIDADE',
                'CONTINUIDADE',
                'PLANEJAMENTO',
                'UNIVERSALIDADE',
                'SITUAÇÃO_CNEAS',
                'SUPERVISAO_RECOMENDADA',
                'RESPONSAVEL_SUPERVISAO',
            ],
            'Ofertas/usuários/vagas' => [
                'REDE_ASSISITENCIA_SOCIAL',
                'ACOLHIMENTO_IDOSOS',
                'CARACTERISTICA_I',
                'OFERTA_I',
                'USUARIO_I',
                'VAGAS_I',
                'QUALIFICACAO_USUARIO_I',
                'CARACTERISTICA_II',
                'OFERTA_II',
                'USUARIO_II',
                'VAGAS_II',
                'QUALIFICACAO_USUARIO_II',
                'CARACTERISTICA_III',
                'OFERTA_III',
                'USUARIO_III',
                'VAGAS_III',
                'QUALIFICACAO_USUARIO_III',
                'CARACTERISTICA_IV',
                'OFERTA_IV',
                'USUARIO_IV',
                'VAGAS_IV',
                'QUALIFICACAO_USUARIO_Iv',
                'CARACTERISTICA_V',
                'OFERTA_V',
                'USUARIO_V',
                'VAGAS_V',
                'QUALIFICACAO_USUARIO_V',
                'OFERTA_VI',
                'USUARIO_VI',
                'VAGAS_VI',
                'QUALIFICACAO_USUARIO_VI',
                'OFERTA_VII',
                'USUARIO_VII',
                'VAGAS_VII',
                'QUALIFICACAO_USUARIO_VII',
                'OUTRAS_OFERTAS',
                'OUTRAS_OFERTAS_I',
                'OFERTAS_OUTRAS_AREAS',
                'OUTRAS_ATIVIDADES',
                'CARACTERISITICAS_OFERTAS',
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
                'COD_LOCALIZACAO',
                'ITEM_PORTARIA_DECISAO_SNAS',
                'PAGINA_DECISAO_SNAS_DOU',
                'RECEITA_BRUTA_ANUAL',
                'ANALISTA_MANIFESTACAO',
                'CGCEB_MANIFESTACACAO',
                'DRSP_MANIFESTACAO',
                'MANIFESTACAO_OUTRO_MINISTERIO',
                'ANALISTA_PARECER',
                'CGCEB_PARECER',
                'DRSP_PARECER',
                'ISENCAO_USUFRUIDA',
                'ASS SNAS',
                'RESPONSAVEL_SUPERVISAO',
                'VAGAS_I',
                'VAGAS_II',
                'VAGAS_III',
                'VAGAS_IV',
                'VAGAS_V',
                'VAGAS_VI',
                'VAGAS_VII',
            ], true);
    }
}
