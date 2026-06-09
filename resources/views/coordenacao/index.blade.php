@extends('layouts.app')

@section('title', 'Coordenação')

@section('content')
@php
    $sections = [
        [
            'title' => 'Analistas',
            'icon' => 'document',
            'theme' => 'blue',
            'items' => [
                ['label' => 'Relatório de Análises', 'href' => '#', 'disabled' => true, 'icon' => 'clipboard'],
                ['label' => 'Inserir Processo(s)', 'href' => route('base-externa.processos.create'), 'icon' => 'plus'],
                ['label' => 'Processos de access', 'href' => route('base-externa.analise-processo.index'), 'icon' => 'folder'],
                ['label' => 'Levantamentos', 'href' => '#', 'disabled' => true, 'icon' => 'trend'],
            ],
        ],
        [
            'title' => 'Viagens',
            'icon' => 'plane',
            'theme' => 'purple',
            'items' => [
                ['label' => 'Inserir Viagem', 'href' => '#', 'disabled' => true, 'icon' => 'plus'],
                ['label' => 'Relatório de Viagens', 'href' => '#', 'disabled' => true, 'icon' => 'clipboard'],
            ],
        ],
        [
            'title' => 'Usuários',
            'icon' => 'users',
            'theme' => 'green',
            'items' => [
                ['label' => 'Listagem de Usuários', 'href' => '#', 'disabled' => true, 'icon' => 'users'],
                ['label' => 'Cadastrar Usuário', 'href' => '#', 'disabled' => true, 'icon' => 'user-plus'],
                ['label' => 'Resetar Senha', 'href' => '#', 'disabled' => true, 'icon' => 'key'],
            ],
        ],
        [
            'title' => 'Documentos',
            'icon' => 'check-document',
            'theme' => 'orange',
            'items' => [
                ['label' => 'Inserir Doc. Validação', 'href' => '#', 'disabled' => true, 'icon' => 'check-document'],
            ],
        ],
        [
            'title' => 'Registros',
            'icon' => 'database',
            'theme' => 'red',
            'items' => [
                ['label' => 'Registros do OSCas', 'href' => '#', 'disabled' => true, 'icon' => 'database'],
                ['label' => 'Registros do indicadores', 'href' => '#', 'disabled' => true, 'icon' => 'trend'],
                ['label' => 'Registros de Atividade', 'href' => '#', 'disabled' => true, 'icon' => 'activity'],
            ],
        ],
        [
            'title' => 'Planilha',
            'icon' => 'table',
            'theme' => 'indigo',
            'items' => [
                ['label' => 'VISDATA (CEBAS)', 'href' => route('coordenacao.planilhas.visdata-cebas'), 'icon' => 'table'],
                ['label' => 'PROCESSOS', 'href' => route('coordenacao.planilhas.processos'), 'icon' => 'table'],
                ['label' => 'CNEAS', 'href' => route('coordenacao.planilhas.cneas'), 'icon' => 'table'],
                ['label' => 'EXTERNO', 'href' => route('coordenacao.planilhas.externo'), 'icon' => 'table'],
            ],
        ],
    ];

    $themes = [
        'blue' => ['border' => 'border-blue-200', 'header' => 'bg-blue-50 border-blue-200', 'text' => 'text-blue-700', 'icon' => 'bg-blue-100 border-blue-200 text-blue-700'],
        'purple' => ['border' => 'border-purple-200', 'header' => 'bg-purple-50 border-purple-200', 'text' => 'text-purple-700', 'icon' => 'bg-purple-100 border-purple-200 text-purple-700'],
        'green' => ['border' => 'border-green-200', 'header' => 'bg-green-50 border-green-200', 'text' => 'text-green-700', 'icon' => 'bg-green-100 border-green-200 text-green-700'],
        'orange' => ['border' => 'border-orange-200', 'header' => 'bg-orange-50 border-orange-200', 'text' => 'text-orange-700', 'icon' => 'bg-orange-100 border-orange-200 text-orange-700'],
        'red' => ['border' => 'border-red-200', 'header' => 'bg-red-50 border-red-200', 'text' => 'text-red-700', 'icon' => 'bg-red-100 border-red-200 text-red-700'],
        'indigo' => ['border' => 'border-indigo-200', 'header' => 'bg-indigo-50 border-indigo-200', 'text' => 'text-indigo-700', 'icon' => 'bg-indigo-100 border-indigo-200 text-indigo-700'],
    ];
@endphp

<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-950">Coordenação</h1>
        <p class="mt-2 text-base text-gray-600">Gerencie análises, viagens, usuários, documentos e registros do sistema.</p>
    </div>

    <section class="grid gap-6 xl:grid-cols-3 lg:grid-cols-2" aria-label="Áreas da Coordenação">
        @foreach ($sections as $section)
            @php($theme = $themes[$section['theme']])
            <article class="overflow-hidden rounded-2xl border-2 {{ $theme['border'] }} bg-white shadow-sm transition-shadow hover:shadow-md">
                <div class="flex items-center gap-4 border-b-2 px-6 py-5 {{ $theme['header'] }}">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl border {{ $theme['icon'] }}">
                        @include('partials.coordenacao-icon', ['name' => $section['icon']])
                    </div>
                    <h2 class="text-sm font-bold uppercase tracking-wide {{ $theme['text'] }}">{{ $section['title'] }}</h2>
                </div>

                <div class="space-y-1 p-5">
                    @foreach ($section['items'] as $item)
                        <a href="{{ $item['href'] }}" class="group flex items-center justify-between rounded-lg px-2 py-3 text-sm font-semibold transition-colors {{ $item['disabled'] ?? false ? 'text-gray-500 hover:bg-gray-50' : 'text-gray-800 hover:bg-blue-50 hover:text-blue-700' }}" @if ($item['disabled'] ?? false) aria-disabled="true" @endif>
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="text-gray-400 transition-colors group-hover:text-current">
                                    @include('partials.coordenacao-icon', ['name' => $item['icon']])
                                </span>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-gray-300 transition-colors group-hover:text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                            </svg>
                        </a>
                    @endforeach
                </div>
            </article>
        @endforeach
    </section>
</div>
@endsection