<?php

namespace App\Http\Requests\BaseExterna;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInserirProcessoRequest extends FormRequest
{
    /**
     * @var array<int, string>
     */
    private array $ufs = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG',
        'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
    ];

    /**
     * @var array<int, string>
     */
    private array $tiposProcesso = [
        'Concessão',
        'Importação',
        'Reconsideração',
        'Recurso de Revisão',
        'Recurso MPS',
        'Renovação',
        'Representação',
        'Revisão',
        'Supervisão Extraordinária',
        'Supervisão Extraordinária Videoconferência',
        'Supervisão Ordinária',
        'Supervisão Ordinária in loco',
    ];

    /**
     * @var array<int, string>
     */
    private array $orgaosOrigem = ['DEPAD', 'MEC', 'MS', 'Não se aplica'];

    /**
     * @var array<int, string>
     */
    private array $motivosRecebimento = [
        'Competência para julgamento',
        'Manif ADIN',
        'Manifestação',
        'Manifestação em fase recursal',
        'Sem opção',
    ];

    /**
     * @var array<int, string>
     */
    private array $fasesProcesso = [
        'DEFERIDO',
        'AGUARDANDO DECISÃO ANTERIOR',
        'ARQUIVAMENTO',
        'INDEFERIDO',
        'ANÁLISE TÉCNICA',
        'ENCAMINHADO',
        'MANUTENÇÃO DA DECISÃO',
        'AGUARDANDO ANÁLISE DO RECURSO SNAS',
        'FINALIZADO',
        'AGUARDANDO MANIFESTAÇÃO',
        'AGUARDANDO ANÁLISE DO RECURSO PELO MINISTRO',
        'AGUARDANDO ANÁLISE',
        'CANCELADO',
        'AGUARDANDO PRAZO RECURSAL',
        'APRECIAÇÃO',
        'EM DILIGÊNCIA',
        'ACATADA',
        'MODULAÇÃO DOS EFEITOS',
        'MODULAÇÃO',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                $data[$key] = $value === '' ? null : $value;
            }
        }

        if (array_key_exists('CNPJ', $data) && $data['CNPJ'] !== null) {
            $data['CNPJ'] = preg_replace('/\D+/', '', (string) $data['CNPJ']);
        }

        if (array_key_exists('UF', $data) && $data['UF'] !== null) {
            $data['UF'] = mb_strtoupper((string) $data['UF']);
        }

        if (array_key_exists('MUNICIPIO', $data) && $data['MUNICIPIO'] !== null) {
            $data['MUNICIPIO'] = $this->normalizeUpperAscii((string) $data['MUNICIPIO']);
        }

        $this->merge($data);
    }

    private function normalizeUpperAscii(string $value): string
    {
        $normalized = \Normalizer::normalize($value, \Normalizer::FORM_D);
        $withoutAccents = preg_replace('/\p{Mn}+/u', '', $normalized === false ? $value : $normalized);

        return mb_strtoupper($withoutAccents ?? $value);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'TIPO_PROCESSO' => ['required', 'string', Rule::in($this->tiposProcesso)],
            'PROTOCOLO' => ['nullable', 'required_without:PROTOCOLO_SEI', 'string', 'max:80'],
            'PROTOCOLO_SEI' => ['nullable', 'required_without:PROTOCOLO', 'string', 'max:80'],
            'CNPJ' => ['required', 'digits:14'],
            'UF' => ['required', Rule::in($this->ufs)],
            'MUNICIPIO' => ['required', 'string', 'max:120'],
            'ORGAO_ORIGEM' => ['required', 'string', Rule::in($this->orgaosOrigem)],
            'DT_PROTOCOLO' => ['required', 'date'],
            'DT_RECEBIMENTO_MDS' => ['required', 'date'],
            'MOTIVO_RECEBIMENTO' => ['required', 'string', Rule::in($this->motivosRecebimento)],
            'DT_CERTIFICACAO_ANTERIOR_INICIO' => ['nullable', 'date'],
            'DT_CERTIFICACAO_ANTERIOR_FIM' => ['nullable', 'date', 'after_or_equal:DT_CERTIFICACAO_ANTERIOR_INICIO'],
            'DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU' => ['nullable', 'date'],
            'TEMPESTIVIDADE' => ['nullable', 'string', Rule::in(['Tempestivo'])],
            'FASE_PROCESSO' => ['required', 'string', Rule::in($this->fasesProcesso)],
            'SITUACAO_CNEAS' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'TIPO_PROCESSO' => 'tipo do processo',
            'PROTOCOLO' => 'protocolo',
            'PROTOCOLO_SEI' => 'protocolo SEI',
            'CNPJ' => 'CNPJ',
            'UF' => 'UF',
            'MUNICIPIO' => 'município',
            'ORGAO_ORIGEM' => 'órgão de origem',
            'DT_PROTOCOLO' => 'data do protocolo',
            'DT_RECEBIMENTO_MDS' => 'data de recebimento no MDS',
            'MOTIVO_RECEBIMENTO' => 'motivo do recebimento',
            'DT_CERTIFICACAO_ANTERIOR_INICIO' => 'início da certificação anterior',
            'DT_CERTIFICACAO_ANTERIOR_FIM' => 'fim da certificação anterior',
            'DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU' => 'publicação da certificação anterior no DOU',
            'TEMPESTIVIDADE' => 'tempestividade',
            'FASE_PROCESSO' => 'fase do processo',
            'SITUACAO_CNEAS' => 'situação CNEAS',
        ];
    }
}
