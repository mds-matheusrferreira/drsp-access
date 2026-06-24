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

        if (array_key_exists('cnpj', $data) && $data['cnpj'] !== null) {
            $data['cnpj'] = preg_replace('/\D+/', '', (string) $data['cnpj']);
        }

        if (array_key_exists('uf', $data) && $data['uf'] !== null) {
            $data['uf'] = mb_strtoupper((string) $data['uf']);
        }

        if (array_key_exists('municipio', $data) && $data['municipio'] !== null) {
            $data['municipio'] = $this->normalizeUpperAscii((string) $data['municipio']);
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
            'tipo_processo' => ['required', 'string', Rule::in($this->tiposProcesso)],
            'protocolo' => ['nullable', 'required_without:protocolo_sei', 'string', 'max:80'],
            'protocolo_sei' => ['nullable', 'required_without:protocolo', 'string', 'max:80'],
            'cnpj' => ['required', 'digits:14'],
            'uf' => ['required', Rule::in($this->ufs)],
            'municipio' => ['required', 'string', 'max:120'],
            'orgao_origem' => ['required', 'string', Rule::in($this->orgaosOrigem)],
            'dt_protocolo' => ['required', 'date'],
            'dt_recebimento_mds' => ['required', 'date'],
            'motivo_recebimento' => ['required', 'string', Rule::in($this->motivosRecebimento)],
            'dt_certificacao_anterior_inicio' => ['nullable', 'date'],
            'dt_certificacao_anterior_fim' => ['nullable', 'date', 'after_or_equal:dt_certificacao_anterior_inicio'],
            'dt_publicacao_certificacao_anterior_dou' => ['nullable', 'date'],
            'tempestividade' => ['nullable', 'string', Rule::in(['Tempestivo'])],
            'fase_processo' => ['required', 'string', Rule::in($this->fasesProcesso)],
            'situacao_cneas' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tipo_processo' => 'tipo do processo',
            'protocolo' => 'protocolo',
            'protocolo_sei' => 'protocolo SEI',
            'cnpj' => 'cnpj',
            'uf' => 'uf',
            'municipio' => 'município',
            'orgao_origem' => 'órgão de origem',
            'dt_protocolo' => 'data do protocolo',
            'dt_recebimento_mds' => 'data de recebimento no MDS',
            'motivo_recebimento' => 'motivo do recebimento',
            'dt_certificacao_anterior_inicio' => 'início da certificação anterior',
            'dt_certificacao_anterior_fim' => 'fim da certificação anterior',
            'dt_publicacao_certificacao_anterior_dou' => 'publicação da certificação anterior no DOU',
            'tempestividade' => 'tempestividade',
            'fase_processo' => 'fase do processo',
            'situacao_cneas' => 'situação CNEAS',
        ];
    }
}
