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
                    <p class="text-sm text-gray-600">Busca por Protocolo, Protocolo SEI, Entidade, CNPJ, Município ou UF.</p>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('base-externa.analise-processo.index') }}" class="flex flex-col gap-3 p-6 lg:flex-row" role="search">
            <label for="search" class="sr-only">Pesquisar</label>
            <input id="search" name="search" type="search" value="{{ $search }}" class="{{ $inputClass }}" placeholder="Digite protocolo, SEI, entidade, CNPJ, município ou UF">
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
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.723 7.723 0 0 1 0 .255c-.007.378.138.751.431.992l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.592c-.55 0-1.02-.397-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.645-.87a6.52 6.52 0 0 1-.22-.127c-.324-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 0 1-1.37-.49l-1.296-2.247a1.125 1.125 0 0 1 .26-1.431l1.003-.827c.293-.24.438-.613.431-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.751-.431-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.298-2.247a1.125 1.125 0 0 1 1.369-.491l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.086.22-.128.331-.183.581-.495.644-.869l.213-1.281Z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                        </svg>
                                                    </summary>
                                                    <div class="mt-2 w-44 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                                                        <a href="{{ route('base-externa.analise-processo.edit', ['protocolo' => $row['PROTOCOLO']]) }}" class="block px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                                            Editar banco
                                                        </a>
                                                        <a href="{{ route('base-externa.analise-processo.parecer.edit', ['protocolo' => $row['PROTOCOLO']]) }}" class="block px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
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
                                            <td class="whitespace-nowrap px-6 py-4 text-gray-700">{{ $row[$column] ?? '' }}</td>
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
