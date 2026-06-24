<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <title>@yield('title', config('app.name', 'DRSP'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 font-sans antialiased text-gray-800">
    @php
        $user = auth()->user();
        $displayName = $user?->name ?: $user?->email ?: 'Usuário';
        $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
        $menuItems = [
            [
                'label' => 'Principal',
                'active' => request()->routeIs('dashboard') || request()->routeIs('principal.*'),
                'icon' => 'home',
                'href' => route('dashboard'),
            ],
            [
                'label' => 'Base externa',
                'active' => request()->routeIs('base-externa.*'),
                'icon' => 'building',
                'children' => [
                    [
                        'label' => 'Inserir Processo Externo',
                        'href' => route('base-externa.processos.create'),
                        'active' =>
                            request()->routeIs('base-externa.processos.create') ||
                            request()->routeIs('base-externa.processos.store'),
                    ],
                    [
                        'label' => 'Análise de Processo',
                        'href' => route('base-externa.analise-processo.index'),
                        'active' => request()->routeIs('base-externa.analise-processo.*'),
                    ],
                ],
            ],
            [
                'label' => 'Coordenação',
                'active' => request()->routeIs('coordenacao.*'),
                'icon' => 'folder',
                'href' => route('coordenacao.index'),
            ],
            [
                'label' => 'Emissor de Certificado',
                'active' => false,
                'icon' => 'file',
                'href' => 'https://aplicacoes.mds.gov.br/snas/redeprivada/eccebas/',
                'external' => true,
            ],
            [
                'label' => 'Plataformas',
                'active' => false,
                'icon' => 'layers',
                'children' => [
                    [
                        'label' => 'Lecom',
                        'href' => 'https://cidadania.servicos.gov.br/bpm/pesquisa_processo',
                        'external' => true,
                    ],
                    [
                        'label' => 'SEI',
                        'href' =>
                            'https://sei.cidadania.gov.br/sei/controlador.php?acao=procedimento_controlar&acao_origem=principal&acao_retorno=principal&inicializando=1&infra_sistema=100000100&infra_unidade_atual=110000221&infra_hash=8cb6c25c95791024313862d3229b5a82cd9b808c004770caeebd59464ba6f74b',
                        'external' => true,
                    ],
                    [
                        'label' => 'CNEAS',
                        'href' =>
                            'https://aplicacoes.mds.gov.br/saa-web/login.action?url=https://aplicacoes.mds.gov.br/cneas&mensagemSaa=Sess%E3o+encerrada.',
                        'external' => true,
                    ],
                    [
                        'label' => 'Protocolo Digital',
                        'href' =>
                            'https://app.anm.gov.br/SCA/Site/Login.aspx?ReturnUrl=https%3A%2F%2Fapp.anm.gov.br%2Fprotocolo',
                        'external' => true,
                    ],
                ],
            ],
        ];
    @endphp

    <div class="min-h-screen lg:flex">
        <aside
            class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-30 lg:flex lg:w-64 lg:flex-col bg-gradient-to-b from-blue-600 to-blue-800 text-white">
            <div class="flex h-24 items-center gap-3 border-b border-blue-700/60 px-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-lg font-semibold">
                    {{ $initial }}</div>
                <p class="truncate text-sm font-semibold uppercase">{{ $displayName }}</p>
            </div>

            <nav class="min-h-0 flex-1 overflow-y-auto py-4" aria-label="Menu principal">
                @foreach ($menuItems as $item)
                    @if (isset($item['children']))
                        <details
                            class="group border-l-4 {{ $item['active'] ? 'border-white bg-white/10' : 'border-transparent' }}"
                            @if ($item['active']) open @endif>
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-white/10 [&::-webkit-details-marker]:hidden">
                                <span class="flex min-w-0 items-center gap-3">
                                    @include('partials.menu-icon', ['name' => $item['icon']])
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 transition-transform group-open:rotate-90"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                </svg>
                            </summary>
                            <div
                                class="mx-4 mb-3 max-h-[calc(100vh-12rem)] overflow-y-auto rounded-lg bg-white py-2 text-gray-700 shadow-xl">
                                @foreach ($item['children'] as $child)
                                    @if (($child['type'] ?? null) === 'heading')
                                        <p
                                            class="px-4 pb-1 pt-3 text-xs font-semibold uppercase tracking-wide text-gray-400 first:pt-1">
                                            {{ $child['label'] }}</p>
                                    @elseif (($child['type'] ?? null) === 'divider')
                                        <hr class="my-2 border-gray-200">
                                    @else
                                        <a href="{{ $child['href'] }}"
                                            class="block px-4 py-2 text-sm font-medium transition-colors {{ $child['active'] ?? false ? 'bg-blue-50 text-blue-700' : ($child['disabled'] ?? false ? 'text-gray-400 hover:bg-gray-50' : 'hover:bg-blue-50 hover:text-blue-700') }}"
                                            @if ($child['disabled'] ?? false) aria-disabled="true" @endif
                                            @if ($child['external'] ?? false) target="_blank" rel="noopener noreferrer" @endif>
                                            {{ $child['label'] }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </details>
                    @else
                        <a href="{{ $item['href'] }}"
                            class="flex items-center justify-between border-l-4 px-6 py-3 text-sm font-semibold transition-colors {{ $item['active'] ? 'border-white bg-white/20 text-white' : 'border-transparent text-white hover:bg-white/10' }}"
                            @if ($item['href'] === '#') aria-disabled="true" @endif
                            @if ($item['external'] ?? false) target="_blank" rel="noopener noreferrer" @endif>
                            <span class="flex min-w-0 items-center gap-3">
                                @include('partials.menu-icon', ['name' => $item['icon']])
                                <span class="truncate">{{ $item['label'] }}</span>
                            </span>
                        </a>
                    @endif
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0 flex-1 lg:pl-64">
            <header class="sticky top-0 z-20 border-b border-gray-200 bg-white">
                <div
                    class="flex min-h-20 flex-col gap-4 px-4 py-4 sm:px-6 lg:h-24 lg:flex-row lg:items-center lg:justify-end lg:px-8 lg:py-0">
                    <div class="flex items-center justify-between gap-4 lg:justify-end">
                        <img src="{{ asset('images/logo-mds.png') }}"
                            alt="Ministério do Desenvolvimento e Assistência Social, Família e Combate à Fome e Governo Federal Brasil - União e Reconstrução"
                            class="h-auto w-full max-w-60 sm:max-w-72">

                        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                            @csrf
                            <button type="submit"
                                class="text-sm font-semibold text-blue-600 transition-colors hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Desconectar
                            </button>
                        </form>
                    </div>

                    <div class="lg:hidden">
                        <details
                            class="overflow-hidden rounded-lg bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-sm">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 [&::-webkit-details-marker]:hidden">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/20 text-sm font-semibold">
                                        {{ $initial }}</div>
                                    <p class="truncate text-sm font-semibold uppercase">{{ $displayName }}</p>
                                </div>
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-white/10"
                                    aria-label="Abrir menu">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                    </svg>
                                </span>
                            </summary>

                            <nav class="border-t border-white/15 p-2" aria-label="Menu principal mobile">
                                <div class="space-y-1">
                                    @foreach ($menuItems as $item)
                                        @if (isset($item['children']))
                                            <details class="rounded-md" @if ($item['active']) open @endif>
                                                <summary
                                                    class="flex cursor-pointer list-none items-center justify-between rounded-md px-3 py-2 text-sm font-semibold {{ $item['active'] ? 'bg-white/20' : 'hover:bg-white/10' }} [&::-webkit-details-marker]:hidden">
                                                    <span class="flex items-center gap-2">
                                                        @include('partials.menu-icon', [
                                                            'name' => $item['icon'],
                                                        ])
                                                        {{ $item['label'] }}
                                                    </span>
                                                    <span class="text-white/70">▾</span>
                                                </summary>
                                                <div class="mt-1 rounded-md bg-white py-1 text-gray-700 shadow-sm">
                                                    @foreach ($item['children'] as $child)
                                                        @if (($child['type'] ?? null) === 'heading')
                                                            <p
                                                                class="px-4 pb-1 pt-3 text-xs font-semibold uppercase tracking-wide text-gray-400 first:pt-1">
                                                                {{ $child['label'] }}</p>
                                                        @elseif (($child['type'] ?? null) === 'divider')
                                                            <hr class="my-2 border-gray-200">
                                                        @else
                                                            <a href="{{ $child['href'] }}"
                                                                class="block px-4 py-2 text-sm font-medium transition-colors {{ $child['active'] ?? false ? 'bg-blue-50 text-blue-700' : ($child['disabled'] ?? false ? 'text-gray-400 hover:bg-gray-50' : 'hover:bg-blue-50 hover:text-blue-700') }}"
                                                                @if ($child['disabled'] ?? false) aria-disabled="true" @endif
                                                                @if ($child['external'] ?? false) target="_blank" rel="noopener noreferrer" @endif>
                                                                {{ $child['label'] }}
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </details>
                                        @else
                                            <a href="{{ $item['href'] }}"
                                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold {{ $item['active'] ? 'bg-white/20' : 'hover:bg-white/10' }}"
                                                @if ($item['href'] === '#') aria-disabled="true" @endif
                                                @if ($item['external'] ?? false) target="_blank" rel="noopener noreferrer" @endif>
                                                @include('partials.menu-icon', ['name' => $item['icon']])
                                                {{ $item['label'] }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </nav>
                        </details>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
