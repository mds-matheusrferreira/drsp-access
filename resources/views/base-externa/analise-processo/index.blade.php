@extends('layouts.app')

@section('title', 'Base externa - Análise de Processo')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500';
    $visibleColumns = array_values(array_intersect($summaryColumns, $results['columns']));
@endphp

<div class="space-y-6">
    <div class="space-y-2">
        <div class="text-sm font-semibold uppercase tracking-wide text-blue-600">Base Externa</div>
        <h1 class="text-3xl font-semibold text-gray-900">Análise de Processo</h1>
        <p class="text-gray-600">Pesquise e edite processos cadastrados na tabela access.</p>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="pesquisa-title">
        <div class="border-b border-blue-200 bg-blue-50 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-white">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <div>
                    <h2 id="pesquisa-title" class="text-lg font-semibold text-gray-900">Pesquisar processo externo</h2>
                    <p class="text-sm text-gray-600">Busca por Protocolo, Protocolo SEI, Entidade, cnpj, Município ou uf.</p>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('base-externa.analise-processo.index') }}" class="flex flex-col gap-3 p-6 lg:flex-row" role="search">
            <label for="search" class="sr-only">Pesquisar</label>
            <input id="search" name="search" type="search" value="{{ $search }}" class="{{ $inputClass }}" placeholder="Digite protocolo, SEI, entidade, cnpj, município ou uf">
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Pesquisar
            </button>
        </form>
    </section>

    @if (trim((string) $search) !== '')
        <section class="rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="resultados-title">
            <div class="border-b border-gray-200 px-4 py-4 sm:px-6">
                <h2 id="resultados-title" class="text-lg font-semibold text-gray-900">Resultados</h2>
                <p class="mt-1 text-sm text-gray-600">{{ number_format($results['count_total'], 0, ',', '.') }} resultado(s). Exibindo até 100 registros.</p>
            </div>

            <div>
                @if ($results['data'] === [])
                    <div class="p-6">
                        <p class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">Nenhum registro encontrado.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Ações</th>
                                    @foreach ($visibleColumns as $column)
                                        <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($results['data'] as $row)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 align-top">
                                            @if ($row['_can_edit'])
                                                <details class="group inline-block text-left">
                                                    <summary class="inline-flex cursor-pointer list-none items-center justify-center rounded bg-blue-600 p-2 text-white transition-colors hover:bg-blue-700 [&::-webkit-details-marker]:hidden" aria-label="Abrir ações">
                                                        <span class="sr-only">Abrir ações</span>
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                                                        </svg>
                                                    </summary>
                                                    <div class="mt-2 w-44 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                                                        <a href="{{ route('base-externa.analise-processo.edit', ['protocolo' => $row['protocolo']]) }}" class="block px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                                            Editar banco
                                                        </a>
                                                        <a href="{{ route('base-externa.analise-processo.parecer.edit', ['protocolo' => $row['protocolo']]) }}" class="block px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                                            Parecer Técnico
                                                        </a>
                                                        <a href="#" class="block px-4 py-2 text-sm font-medium text-gray-400" aria-disabled="true" title="Funcionalidade ainda não implementada">
                                                            Nota técnica
                                                        </a>
                                                        <a href="#" class="block px-4 py-2 text-sm font-medium text-gray-400" aria-disabled="true" title="Funcionalidade ainda não implementada">
                                                            Manifestação
                                                        </a>
                                                    </div>
                                                </details>
                                            @else
                                                <span class="rounded bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-600">{{ $row['_edit_block_reason'] }}</span>
                                            @endif
                                        </td>
                                        @foreach ($visibleColumns as $column)
                                            @php
                                                $value = $row[$column] ?? '';

                                                if ($column === 'dt_protocolo' && preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $value)) {
                                                    $value = \Carbon\Carbon::parse($value)->format('d/m/Y');
                                                }
                                            @endphp
                                            <td class="whitespace-nowrap px-6 py-4 text-gray-700">{{ $value }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    @endif
</div>
@endsection
