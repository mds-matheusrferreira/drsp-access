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
        'entidade' => 'string',
        'status_processo' => 'string',
        'documentos_obrigatorios' => 'text',
        'documentos_pendentes' => 'text',
        'compatibilidade_estatuto_loas' => 'text',
        'destino_patrimonio_caso_dissolucao' => 'text',
        'oferta_i' => 'text',
        'vagas_i' => 'integer',
        'usuario_i' => 'text',
        'qualificacao_usuario_i' => 'text',
        'oferta_ii' => 'text',
        'vagas_ii' => 'integer',
        'usuario_ii' => 'text',
        'qualificacao_usuario_ii' => 'text',
        'oferta_iii' => 'text',
        'vagas_iii' => 'integer',
        'usuario_iii' => 'text',
        'qualificacao_usuario_iii' => 'text',
        'oferta_iv' => 'text',
        'vagas_iv' => 'integer',
        'usuario_iv' => 'text',
        'qualificacao_usuario_iv' => 'text',
        'oferta_v' => 'text',
        'vagas_v' => 'integer',
        'usuario_v' => 'text',
        'qualificacao_usuario_v' => 'text',
        'oferta_vi' => 'text',
        'vagas_vi' => 'integer',
        'usuario_vi' => 'text',
        'qualificacao_usuario_vi' => 'text',
        'oferta_vii' => 'text',
        'vagas_vii' => 'integer',
        'usuario_vii' => 'text',
        'qualificacao_usuario_vii' => 'text',
        'outras_atividades' => 'text',
        'gratuidade_parecer' => 'text',
        'orgao_encaminhamento' => 'text',
        'nota_tecnica_outro_orgao' => 'text',
        'manifestacao_outro_ministerio' => 'integer',
        'ofertas_outras_areas' => 'text',
        'continuidade' => 'string',
        'planejamento' => 'string',
        'universalidade' => 'string',
        'decisao_parecer' => 'string',
        'motivo_indeferimento' => 'text',
        'justificativa_indeferimento' => 'text',
        'justificativa_indeferimento_nt' => 'text',
        'analista_parecer' => 'integer',
        'cgceb_parecer' => 'integer',
        'drsp_parecer' => 'integer',
        'responsavel_nota_tecnica' => 'string',
        'legislacao_parecer' => 'string',
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
