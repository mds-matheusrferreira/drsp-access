@extends('layouts.app')

@section('title', 'Painel - Principal')

@section('content')
<div
    class="space-y-6"
    data-principal-dashboard
    data-updated-at-url="{{ route('principal.updated-at') }}"
    data-search-url="{{ route('principal.search') }}"
    data-state-totals-url="{{ route('principal.state-totals') }}"
    data-state-url="{{ route('principal.states.show', ['uf' => '__UF__']) }}"
    data-state-download-url="{{ route('principal.states.download', ['uf' => '__UF__']) }}"
>
    <h1 class="text-2xl font-semibold text-gray-900 sm:text-3xl">Painel - Principal</h1>

    <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="filtros-title">
        <h2 id="filtros-title" class="sr-only">Filtros e pesquisa</h2>

        <div class="flex flex-col gap-4">
            <form id="principal-search-form" class="flex flex-col gap-3 lg:flex-row" role="search">
                <label for="principal-search" class="sr-only">Pesquisar</label>
                <div class="relative min-w-0 flex-1">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input id="principal-search" name="search" type="search" placeholder="Pesquise por Base, Nome da Entidade, CNPJ, Processo, Protocolo..." class="w-full rounded border border-gray-300 py-3 pl-12 pr-4 text-gray-700 placeholder:text-gray-500 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded bg-blue-600 px-6 py-3 text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 lg:w-20" aria-label="Pesquisar">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </form>

            <p id="principal-updated-at" class="text-right text-xs text-gray-500">Carregando atualização...</p>
        </div>

        <div id="principal-search-results" class="mt-5" aria-live="polite"></div>
    </section>

    <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="mapa-title">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 id="mapa-title" class="text-xl font-semibold text-gray-900">Confira o Mapa CEBAS</h2>
            <a href="{{ route('principal.download') }}" class="inline-flex items-center justify-center gap-2 rounded bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0 4.5-4.5M12 16.5V3" />
                </svg>
                Baixar Tabela Completa
            </a>
        </div>

        <p id="map-status" class="mt-3 text-sm text-gray-600" aria-live="polite">Clique em uma UF para ver os registros CEBAS.</p>

        <div class="mt-6 overflow-x-auto rounded-lg bg-white p-4 sm:p-6">
            @include('partials.brazil-map')
        </div>
    </section>

    <div id="state-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="state-modal-title">
        <div class="max-h-[90vh] w-full max-w-6xl overflow-hidden rounded-lg bg-white shadow-xl">
            <div class="flex flex-col gap-3 border-b border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 id="state-modal-title" class="text-xl font-semibold text-gray-900">CEBAS</h3>
                    <p id="state-modal-summary" class="mt-1 text-sm text-gray-600" aria-live="polite"></p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <a id="state-download" href="#" class="inline-flex items-center justify-center rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Baixar UF</a>
                    <button type="button" data-state-close class="inline-flex items-center justify-center rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Fechar</button>
                </div>
            </div>
            <div class="p-4">
                <div id="state-modal-table"></div>
                <div id="state-modal-pagination" class="mt-4 flex justify-end gap-2"></div>
            </div>
        </div>
    </div>
</div>
@endsection
