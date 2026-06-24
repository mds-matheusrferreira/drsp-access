<?php

namespace App\Services\BaseExterna;

use Illuminate\Support\Facades\DB;

class AccessFormRepository
{
    private const TABLE = 'processos_sei';

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): void
    {
        DB::table(self::TABLE)->insert([
            'TIPO_PROCESSO' => $data['tipo_processo'] ?? null,
            'PROTOCOLO' => $data['protocolo'] ?? null,
            'PROTOCOLO_SEI' => $data['protocolo_sei'] ?? null,
            'CNPJ' => $data['cnpj'] ?? null,
            'UF' => $data['uf'] ?? null,
            'MUNICIPIO' => $data['municipio'] ?? null,
            'ORGAO_ORIGEM' => $data['orgao_origem'] ?? null,
            'DT_PROTOCOLO' => $data['dt_protocolo'] ?? null,
            'DT_RECEBIMENTO_MDS' => $data['dt_recebimento_mds'] ?? null,
            'MOTIVO_RECEBIMENTO' => $data['motivo_recebimento'] ?? null,
            'DT_CERTIFICACAO_ANTERIOR_INICIO' => $data['dt_certificacao_anterior_inicio'] ?? null,
            'DT_CERTIFICACAO_ANTERIOR_FIM' => $data['dt_certificacao_anterior_fim'] ?? null,
            'DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU' => $data['dt_publicacao_certificacao_anterior_dou'] ?? null,
            'TEMPESTIVIDADE' => $data['tempestividade'] ?? null,
            'FASE_PROCESSO' => $data['fase_processo'] ?? null,
            'SITUAÇÃO_CNEAS' => $data['situacao_cneas'] ?? null,
        ]);
    }
}
