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

    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="pesquisa-title">
        <h2 id="pesquisa-title" class="text-lg font-semibold text-gray-900">Pesquisar processo externo</h2>
        <p class="mt-1 text-sm text-gray-600">Busca por Protocolo, Protocolo SEI, Entidade, CNPJ, Município ou UF.</p>

        <form method="GET" action="{{ route('base-externa.analise-processo.index') }}" class="mt-5 flex flex-col gap-3 lg:flex-row" role="search">
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

            <div class="p-4 sm:p-6">
                @if ($results['data'] === [])
                    <p class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">Nenhum registro encontrado.</p>
                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="whitespace-nowrap px-3 py-2 text-left font-semibold text-gray-700">Ações</th>
                                    @foreach ($visibleColumns as $column)
                                        <th class="whitespace-nowrap px-3 py-2 text-left font-semibold text-gray-700">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($results['data'] as $row)
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-2">
                                            @if ($row['_can_edit'])
                                                <a href="{{ route('base-externa.analise-processo.edit', ['protocolo' => $row['PROTOCOLO']]) }}" class="inline-flex rounded bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-blue-700">
                                                    Editar
                                                </a>
                                            @else
                                                <span class="rounded bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-600">{{ $row['_edit_block_reason'] }}</span>
                                            @endif
                                        </td>
                                        @foreach ($visibleColumns as $column)
                                            <td class="whitespace-nowrap px-3 py-2 text-gray-700">{{ $row[$column] ?? '' }}</td>
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
