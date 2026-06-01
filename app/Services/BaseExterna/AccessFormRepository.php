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
            'TIPO_PROCESSO' => $data['TIPO_PROCESSO'] ?? null,
            'PROTOCOLO' => $data['PROTOCOLO'] ?? null,
            'PROTOCOLO_SEI' => $data['PROTOCOLO_SEI'] ?? null,
            'CNPJ' => $data['CNPJ'] ?? null,
            'UF' => $data['UF'] ?? null,
            'MUNICIPIO' => $data['MUNICIPIO'] ?? null,
            'ORGAO_ORIGEM' => $data['ORGAO_ORIGEM'] ?? null,
            'DT_PROTOCOLO' => $data['DT_PROTOCOLO'] ?? null,
            'DT_RECEBIMENTO_MDS' => $data['DT_RECEBIMENTO_MDS'] ?? null,
            'MOTIVO_RECEBIMENTO' => $data['MOTIVO_RECEBIMENTO'] ?? null,
            'DT_CERTIFICACAO_ANTERIOR_INICIO' => $data['DT_CERTIFICACAO_ANTERIOR_INICIO'] ?? null,
            'DT_CERTIFICACAO_ANTERIOR_FIM' => $data['DT_CERTIFICACAO_ANTERIOR_FIM'] ?? null,
            'DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU' => $data['DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU'] ?? null,
            'TEMPESTIVIDADE' => $data['TEMPESTIVIDADE'] ?? null,
            'FASE_PROCESSO' => $data['FASE_PROCESSO'] ?? null,
            'SITUAÇÃO_CNEAS' => $data['SITUACAO_CNEAS'] ?? null,
        ]);
    }
}
