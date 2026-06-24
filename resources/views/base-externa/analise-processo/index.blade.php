@extends('layouts.app')

@section('title', 'Base externa - Análise de Processo')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500';
    $visibleColumns = array_values(array_intersect($summaryColumns, $results['columns']));
    $canEditBank = in_array((string) (auth()->user()?->permission ?? ''), ['1', '2'], true);

    $columnLabels = [
        'PROTOCOLO'      => 'Protocolo',
        'DT_PROTOCOLO'   => 'Data Protocolo',
        'PROTOCOLO_SEI'  => 'Protocolo SEI',
        'ENTIDADE'       => 'Entidade',
        'CNPJ'           => 'CNPJ',
        'MUNICIPIO'      => 'Município',
        'UF'             => 'UF',
        'ORGAO_ORIGEM'   => 'Órgão Origem',
    ];
@endphp

<div class="space-y-6">
    <div class="space-y-2">
        <div class="text-sm font-semibold uppercase tracking-wide text-blue-600">Base Externa</div>
        <h1 class="text-3xl font-semibold text-gray-900">Análise de Processo</h1>
        <p class="text-gray-600">Pesquise e edite processos cadastrados na tabela processos_sei.</p>
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
                <p class="mt-1 text-sm text-gray-600">
                    {{ number_format($results['count_total'], 0, ',', '.') }} resultado(s){{ $results['count_total'] > 100 ? ' — exibindo os primeiros 100' : '' }}.
                </p>
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
                                        <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                            {{ $columnLabels[$column] ?? $column }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($results['data'] as $row)
                                    @php
                                        $canEdit = $row['_can_edit'] ?? false;
                                        $blockReason = $row['_edit_block_reason'] ?? null;
                                    @endphp
                                    <tr class="{{ $canEdit ? '' : 'bg-amber-50' }}">
                                        <td class="whitespace-nowrap px-6 py-4 align-top">
                                            @if (! $canEdit)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700"
                                                    title="{{ $blockReason }}"
                                                >
                                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                                    </svg>
                                                    {{ $blockReason }}
                                                </span>
                                            @else
                                                <details class="group inline-block text-left" data-dropdown>
                                                    <summary class="inline-flex cursor-pointer list-none items-center justify-center rounded bg-blue-600 p-2 text-white transition-colors hover:bg-blue-700 [&::-webkit-details-marker]:hidden" aria-label="Abrir ações">
                                                        <span class="sr-only">Abrir ações</span>
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                                                        </svg>
                                                    </summary>
                                                    <div class="mt-2 w-44 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                                                        @if ($canEditBank)
                                                            <a href="{{ route('base-externa.analise-processo.edit', ['protocolo' => $row['PROTOCOLO']]) }}" class="block px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                                                Editar banco
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('base-externa.analise-processo.parecer.edit', ['protocolo' => $row['PROTOCOLO']]) }}" class="block px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                                            Parecer Técnico
                                                        </a>
                                                        <a href="{{ route('base-externa.analise-processo.nota-tecnica.edit', ['protocolo' => $row['PROTOCOLO']]) }}" class="block px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                                            Nota técnica
                                                        </a>
                                                        <a href="#" class="block px-4 py-2 text-sm font-medium text-gray-400" aria-disabled="true" title="Funcionalidade ainda não implementada">
                                                            Manifestação
                                                        </a>
                                                    </div>
                                                </details>
                                            @endif
                                        </td>
                                        @foreach ($visibleColumns as $column)
                                            @php
                                                $value = $row[$column] ?? '';
                                                if (str_starts_with($column, 'DT_') && preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $value)) {
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

<script>
document.addEventListener('click', function (e) {
    document.querySelectorAll('details[data-dropdown][open]').forEach(function (d) {
        if (!d.contains(e.target)) {
            d.removeAttribute('open');
        }
    });
});
</script>
@endsection
