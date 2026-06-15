@extends('layouts.app')

@section('title', 'VISDATA (CEBAS)')

@section('content')
@php
    $total = number_format((int) ($stats['total'] ?? 0), 0, ',', '.');
    $updatedAt = $stats['updated_at'] ?? 'Sem data de referência';
@endphp

<div class="space-y-6" data-visdata-cebas data-import-url="{{ route('coordenacao.planilhas.visdata-cebas.import') }}" data-csrf-token="{{ csrf_token() }}">
    <div class="space-y-2">
        <h1 class="text-3xl font-semibold text-gray-900">Planilha Visdata - Upload e download de Dados</h1>
        <p class="text-base text-gray-600">Sistema de importação de planilha Visdata CEBAS</p>
    </div>

    <section class="rounded-lg bg-blue-50 px-5 py-4 text-blue-700" aria-labelledby="informacoes-visdata-title">
        <div class="flex gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
            <div>
                <h2 id="informacoes-visdata-title" class="font-semibold">Informações Importantes</h2>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    <li>Formato aceito: Excel .xlsx. Arquivos .xls antigos devem ser salvos como .xlsx antes da importação.</li>
                    <li>Tamanho máximo: 50MB</li>
                    <li>A aba deve se chamar <strong>SITUAÇÃO CNPJ CEBAS (VISDATA)</strong></li>
                    <li>A importação substitui todos os dados atuais da tabela <strong>cebas_suas</strong> após validar a planilha.</li>
                </ul>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('coordenacao.planilhas.visdata-cebas.backup') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0 4.5-4.5M12 16.5V3" />
            </svg>
            Baixar tabela atual
        </a>
    </div>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="importar-visdata-title">
        <div class="border-b border-green-200 bg-green-100 px-6 py-5">
            <div class="flex items-center gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-600 text-white">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-.989-2.386l-4.5-4.5a3.375 3.375 0 0 0-2.386-.989H8.25A2.25 2.25 0 0 0 6 6v12a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 18v-3.75M12 8.25V3.75M9.75 14.25h4.5m-4.5 3h4.5" />
                    </svg>
                </div>
                <div>
                    <h2 id="importar-visdata-title" class="text-xl font-semibold text-gray-900">Importar Planilha Visdata CEBAS</h2>
                    <p class="text-sm text-gray-600">Selecione o arquivo Excel para importar os dados</p>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mx-6 mt-6 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mx-6 mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('coordenacao.planilhas.visdata-cebas.import') }}" class="space-y-5 p-6" enctype="multipart/form-data">
            @csrf
            <div>
                <label for="visdata-file" class="mb-2 block text-sm font-medium text-gray-700">Selecione o arquivo Excel:</label>
                <input id="visdata-file" name="excelFile" type="file" class="block w-full rounded-lg border border-dashed border-gray-300 bg-white px-3 py-3 text-sm text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200" accept=".xlsx,.xls" required>
                @error('excelFile')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-4 text-base font-semibold text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 7.5 12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
                Enviar CEBAS
            </button>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="uploads-recentes-title">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 id="uploads-recentes-title" class="text-xl font-semibold text-gray-900">Situação atual da tabela</h2>
        </div>
        <div class="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-.989-2.386l-4.5-4.5a3.375 3.375 0 0 0-2.386-.989H8.25A2.25 2.25 0 0 0 6 6v12a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 18v-3.75M12 8.25V3.75M9.75 14.25h4.5m-4.5 3h4.5" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">cebas_suas</h3>
                    <p class="text-sm text-gray-600">Referência: {{ $updatedAt }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-semibold text-gray-900"><span data-current-total>{{ $total }}</span> registros</p>
                <p class="text-sm text-green-600">Base atual</p>
            </div>
        </div>
    </section>
</div>
@endsection
