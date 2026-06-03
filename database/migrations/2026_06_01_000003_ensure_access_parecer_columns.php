<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'access';

    /**
     * @var array<string, string>
     */
    private array $columns = [
        'ENTIDADE' => 'string',
        'STATUS_PROCESSO' => 'string',
        'DOCUMENTOS_OBRIGATORIOS' => 'text',
        'DOCUMENTOS_PENDENTES' => 'text',
        'COMPATIBILIDADE_ESTATUTO_LOAS' => 'text',
        'DESTINO_PATRIMONIO_CASO_DISSOLUCAO' => 'text',
        'OFERTA_I' => 'text',
        'VAGAS_I' => 'integer',
        'USUARIO_I' => 'text',
        'QUALIFICACAO_USUARIO_I' => 'text',
        'OFERTA_II' => 'text',
        'VAGAS_II' => 'integer',
        'USUARIO_II' => 'text',
        'QUALIFICACAO_USUARIO_II' => 'text',
        'OFERTA_III' => 'text',
        'VAGAS_III' => 'integer',
        'USUARIO_III' => 'text',
        'QUALIFICACAO_USUARIO_III' => 'text',
        'OFERTA_IV' => 'text',
        'VAGAS_IV' => 'integer',
        'USUARIO_IV' => 'text',
        'QUALIFICACAO_USUARIO_Iv' => 'text',
        'OFERTA_V' => 'text',
        'VAGAS_V' => 'integer',
        'USUARIO_V' => 'text',
        'QUALIFICACAO_USUARIO_V' => 'text',
        'OFERTA_VI' => 'text',
        'VAGAS_VI' => 'integer',
        'USUARIO_VI' => 'text',
        'QUALIFICACAO_USUARIO_VI' => 'text',
        'OFERTA_VII' => 'text',
        'VAGAS_VII' => 'integer',
        'USUARIO_VII' => 'text',
        'QUALIFICACAO_USUARIO_VII' => 'text',
        'OUTRAS_ATIVIDADES' => 'text',
        'GRATUIDADE_PARECER' => 'text',
        'ORGAO_ENCAMINHAMENTO' => 'text',
        'NOTA_TECNICA_OUTRO_ORGAO' => 'text',
        'MANIFESTACAO_OUTRO_MINISTERIO' => 'integer',
        'OFERTAS_OUTRAS_AREAS' => 'text',
        'CONTINUIDADE' => 'string',
        'PLANEJAMENTO' => 'string',
        'UNIVERSALIDADE' => 'string',
        'DECISAO_PARECER' => 'string',
        'MOTIVO_INDEFERIMENTO' => 'text',
        'JUSTIFICATIVA_INDEFERIMENTO' => 'text',
        'JUSTIFICATIVA_INDEFERIMENTO_NT' => 'text',
        'ANALISTA_PARECER' => 'integer',
        'CGCEB_PARECER' => 'integer',
        'DRSP_PARECER' => 'integer',
        'RESPONSAVEL_NOTA_TECNICA' => 'string',
    ];

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            foreach ($this->columns as $column => $type) {
                if (! Schema::hasColumn(self::TABLE, $column)) {
                    $this->addColumn($table, $column, $type);
                }
            }
        });
    }

    public function down(): void
    {
        // Conservador de propósito: a tabela pode conter dados legados importados do Access.
    }

    private function addColumn(Blueprint $table, string $column, string $type): void
    {
        match ($type) {
            'boolean' => $table->boolean($column)->nullable(),
            'date' => $table->date($column)->nullable(),
            'integer' => $table->integer($column)->nullable(),
            'text' => $table->text($column)->nullable(),
            default => $table->string($column)->nullable(),
        };
    }
};
