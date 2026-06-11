<?php

namespace App\Services\BaseExterna;

use Illuminate\Support\Facades\DB;

class AccessFormRepository
{
    private const TABLE = 'access';

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): void
    {
        DB::table(self::TABLE)->insert([
            'tipo_processo' => $data['tipo_processo'] ?? null,
            'protocolo' => $data['protocolo'] ?? null,
            'protocolo_sei' => $data['protocolo_sei'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
            'uf' => $data['uf'] ?? null,
            'municipio' => $data['municipio'] ?? null,
            'orgao_origem' => $data['orgao_origem'] ?? null,
            'dt_protocolo' => $data['dt_protocolo'] ?? null,
            'dt_recebimento_mds' => $data['dt_recebimento_mds'] ?? null,
            'motivo_recebimento' => $data['motivo_recebimento'] ?? null,
            'dt_certificacao_anterior_inicio' => $data['dt_certificacao_anterior_inicio'] ?? null,
            'dt_certificacao_anterior_fim' => $data['dt_certificacao_anterior_fim'] ?? null,
            'dt_publicacao_certificacao_anterior_dou' => $data['dt_publicacao_certificacao_anterior_dou'] ?? null,
            'tempestividade' => $data['tempestividade'] ?? null,
            'fase_processo' => $data['fase_processo'] ?? null,
            'situacao_cneas' => $data['situacao_cneas'] ?? null,
        ]);
    }
}
