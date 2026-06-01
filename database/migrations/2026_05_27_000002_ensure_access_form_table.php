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
        'TIPO_PROCESSO' => 'string',
        'PROTOCOLO' => 'string',
        'PROTOCOLO_SEI' => 'string',
        'CNPJ' => 'string',
        'UF' => 'string',
        'MUNICIPIO' => 'string',
        'ORGAO_ORIGEM' => 'string',
        'DT_PROTOCOLO' => 'date',
        'DT_RECEBIMENTO_MDS' => 'date',
        'MOTIVO_RECEBIMENTO' => 'text',
        'DT_CERTIFICACAO_ANTERIOR_INICIO' => 'date',
        'DT_CERTIFICACAO_ANTERIOR_FIM' => 'date',
        'DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU' => 'date',
        'TEMPESTIVIDADE' => 'string',
        'FASE_PROCESSO' => 'string',
        'SITUAÇÃO_CNEAS' => 'text',
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
