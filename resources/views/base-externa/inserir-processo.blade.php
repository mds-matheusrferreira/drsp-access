@extends('layouts.app')

@section('title', 'Base externa - Inserir Processo')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-700';
    $errorClass = 'mt-1 text-sm text-red-600';
    $tiposProcesso = [
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
    $orgaosOrigem = ['DEPAD', 'MEC', 'MS', 'Não se aplica'];
    $motivosRecebimento = [
        'Competência para julgamento',
        'Manif ADIN',
        'Manifestação',
        'Manifestação em fase recursal',
    ];
    $fasesProcesso = [
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
@endphp

<div class="space-y-6">
    <div class="space-y-2">
        <div class="text-sm font-semibold uppercase tracking-wide text-blue-600">Base Externa</div>
        <h1 class="text-3xl font-semibold text-gray-900">Inserir Processo</h1>
        <p class="text-gray-600">Cadastre processos criados fora da plataforma Lecom.</p>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('base-externa.processos.store') }}" class="space-y-6">
        @csrf

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="identificacao-title">
            <div class="border-b border-blue-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5m-7.5 3h10.5A2.25 2.25 0 0 0 18 18.75V9.75L10.5 2.25H5.25A2.25 2.25 0 0 0 3 4.5v14.25A2.25 2.25 0 0 0 5.25 21Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="identificacao-title" class="text-lg font-semibold text-gray-900">Identificação do Processo</h2>
                        <p class="text-sm text-gray-600">Informações básicas e protocolos</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid gap-5 lg:grid-cols-3">
                    <div>
                        <label for="tipo_processo" class="{{ $labelClass }}">Tipo do Processo</label>
                        <select id="tipo_processo" name="tipo_processo" class="{{ $inputClass }}" required>
                            <option value="">Selecione</option>
                            @foreach ($tiposProcesso as $tipoProcesso)
                                <option value="{{ $tipoProcesso }}" @selected(old('tipo_processo') === $tipoProcesso)>{{ $tipoProcesso }}</option>
                            @endforeach
                        </select>
                        @error('tipo_processo') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="protocolo" class="{{ $labelClass }}">Protocolo</label>
                        <input id="protocolo" name="protocolo" type="text" value="{{ old('protocolo') }}" class="{{ $inputClass }}" placeholder="Número do protocolo">
                        @error('protocolo') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="protocolo_sei" class="{{ $labelClass }}">Protocolo SEI</label>
                        <input id="protocolo_sei" name="protocolo_sei" type="text" value="{{ old('protocolo_sei') }}" class="{{ $inputClass }}" placeholder="Número SEI">
                        @error('protocolo_sei') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="cnpj" class="{{ $labelClass }}">cnpj</label>
                        <input id="cnpj" name="cnpj" type="text" inputmode="numeric" value="{{ old('cnpj') }}" class="{{ $inputClass }}" placeholder="00.000.000/0000-00" required>
                        @error('cnpj') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="dt_protocolo" class="{{ $labelClass }}">Data do Protocolo</label>
                        <input id="dt_protocolo" name="dt_protocolo" type="date" value="{{ old('dt_protocolo') }}" class="{{ $inputClass }}" required>
                        @error('dt_protocolo') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tempestividade" class="{{ $labelClass }}">Tempestividade</label>
                        <select id="tempestividade" name="tempestividade" class="{{ $inputClass }} bg-white">
                            <option value="">Selecione</option>
                            <option value="Tempestivo" @selected(old('tempestividade') === 'Tempestivo')>Tempestivo</option>
                        </select>
                        @error('tempestividade') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="dt_certificacao_anterior_inicio" class="{{ $labelClass }}">Início da Certificação Anterior</label>
                        <input id="dt_certificacao_anterior_inicio" name="dt_certificacao_anterior_inicio" type="date" value="{{ old('dt_certificacao_anterior_inicio') }}" class="{{ $inputClass }}">
                        @error('dt_certificacao_anterior_inicio') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="dt_certificacao_anterior_fim" class="{{ $labelClass }}">Fim da Certificação Anterior</label>
                        <input id="dt_certificacao_anterior_fim" name="dt_certificacao_anterior_fim" type="date" value="{{ old('dt_certificacao_anterior_fim') }}" class="{{ $inputClass }}">
                        @error('dt_certificacao_anterior_fim') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="dt_publicacao_certificacao_anterior_dou" class="{{ $labelClass }}">Publicação Certificação Anterior DOU</label>
                        <input id="dt_publicacao_certificacao_anterior_dou" name="dt_publicacao_certificacao_anterior_dou" type="date" value="{{ old('dt_publicacao_certificacao_anterior_dou') }}" class="{{ $inputClass }}">
                        @error('dt_publicacao_certificacao_anterior_dou') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="localizacao-title">
            <div class="border-b border-green-200 bg-gradient-to-r from-green-50 to-green-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-600 text-white">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="localizacao-title" class="text-lg font-semibold text-gray-900">Localização</h2>
                        <p class="text-sm text-gray-600">Dados geográficos e jurisdição</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <label for="uf" class="{{ $labelClass }}">uf</label>
                        <select id="uf" name="uf" class="{{ $inputClass }} bg-white" required>
                            <option value="">Selecione</option>
                            @foreach ($ufs as $uf)
                                <option value="{{ $uf }}" @selected(old('uf') === $uf)>{{ $uf }}</option>
                            @endforeach
                        </select>
                        @error('uf') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="municipio" class="{{ $labelClass }}">Município</label>
                        <select id="municipio" name="municipio" class="{{ $inputClass }} bg-white" data-selected-municipio="{{ old('municipio') }}" disabled required>
                            <option value="">Selecione a uf primeiro</option>
                        </select>
                        @error('municipio') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="origem-datas-title">
            <div class="border-b border-purple-200 bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-600 text-white">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0l-7.5-4.615A2.25 2.25 0 0 1 2.25 6.993V6.75" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="origem-datas-title" class="text-lg font-semibold text-gray-900">Recebimentos</h2>
                        <p class="text-sm text-gray-600">Informações de Processos recebidos de outros órgãos</p>
                    </div>
                </div>
            </div>
            <div class="space-y-5 p-6">
                <div class="grid gap-5 lg:grid-cols-3">
                    <div>
                        <label for="orgao_origem" class="{{ $labelClass }}">Órgão de Origem</label>
                        <select id="orgao_origem" name="orgao_origem" class="{{ $inputClass }} bg-white" required>
                            <option value="">Selecione</option>
                            @foreach ($orgaosOrigem as $orgaoOrigem)
                                <option value="{{ $orgaoOrigem }}" @selected(old('orgao_origem') === $orgaoOrigem)>{{ $orgaoOrigem }}</option>
                            @endforeach
                        </select>
                        @error('orgao_origem') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="dt_recebimento_mds" class="{{ $labelClass }}">Data de Recebimento no MDS</label>
                        <input id="dt_recebimento_mds" name="dt_recebimento_mds" type="date" value="{{ old('dt_recebimento_mds') }}" class="{{ $inputClass }}" required>
                        @error('dt_recebimento_mds') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid gap-5 lg:grid-cols-3">
                    <div>
                        <label for="motivo_recebimento" class="{{ $labelClass }}">Motivo do Recebimento</label>
                        <select id="motivo_recebimento" name="motivo_recebimento" class="{{ $inputClass }} bg-white" required>
                            <option value="">Selecione</option>
                            @foreach ($motivosRecebimento as $motivoRecebimento)
                                <option value="{{ $motivoRecebimento }}" @selected(old('motivo_recebimento') === $motivoRecebimento)>{{ $motivoRecebimento }}</option>
                            @endforeach
                        </select>
                        @error('motivo_recebimento') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="situacao-title">
            <div class="border-b border-orange-200 bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-600 text-white">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="situacao-title" class="text-lg font-semibold text-gray-900">Situação do Processo</h2>
                        <p class="text-sm text-gray-600">Status e classificação</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <label for="fase_processo" class="{{ $labelClass }}">Fase do Processo</label>
                        <select id="fase_processo" name="fase_processo" class="{{ $inputClass }} bg-white" required>
                            <option value="">Selecione</option>
                            @foreach ($fasesProcesso as $faseProcesso)
                                <option value="{{ $faseProcesso }}" @selected(old('fase_processo') === $faseProcesso)>{{ $faseProcesso }}</option>
                            @endforeach
                        </select>
                        @error('fase_processo') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="situacao_cneas" class="{{ $labelClass }}">Situação CNEAS</label>
                        <input id="situacao_cneas" name="situacao_cneas" type="text" value="{{ old('situacao_cneas') }}" class="{{ $inputClass }}" placeholder="Concluído em..." required>
                        @error('situacao_cneas') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-col items-stretch justify-end gap-4 pt-4 sm:flex-row sm:items-center">
            <a href="{{ route('base-externa.processos.create') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-8 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 text-sm font-medium text-white shadow-md transition-colors hover:bg-blue-700 hover:shadow-lg">Salvar Processo</button>
        </div>
    </form>
</div>
@endsection
