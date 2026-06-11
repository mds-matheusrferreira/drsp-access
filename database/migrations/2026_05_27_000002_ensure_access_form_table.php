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
        'tipo_processo' => 'string',
        'protocolo' => 'string',
        'protocolo_sei' => 'string',
        'cnpj' => 'string',
        'uf' => 'string',
        'municipio' => 'string',
        'orgao_origem' => 'string',
        'dt_protocolo' => 'date',
        'dt_recebimento_mds' => 'date',
        'motivo_recebimento' => 'text',
        'dt_certificacao_anterior_inicio' => 'date',
        'dt_certificacao_anterior_fim' => 'date',
        'dt_publicacao_certificacao_anterior_dou' => 'date',
        'tempestividade' => 'string',
        'fase_processo' => 'string',
        'situacao_cneas' => 'text',
    ];

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->id();
                $this->addAccessColumns($table);
            });

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

    private function addAccessColumns(Blueprint $table): void
    {
        foreach ($this->columns as $column => $type) {
            $this->addColumn($table, $column, $type);
        }
    }

    private function addColumn(Blueprint $table, string $column, string $type): void
    {
        match ($type) {
            'date' => $table->date($column)->nullable(),
            'text' => $table->text($column)->nullable(),
            default => $table->string($column)->nullable(),
        };
    }
};
