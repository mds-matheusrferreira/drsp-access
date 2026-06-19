@extends('layouts.app')

@section('title', 'Base externa - Editar Processo')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-700';
    $sectionStyles = [
        'Identificação' => ['icon' => 'document', 'iconBg' => 'bg-blue-600', 'header' => 'border-blue-200 bg-blue-50', 'subtitle' => 'Dados básicos do processo'],
        'Datas e recebimento' => ['icon' => 'calendar', 'iconBg' => 'bg-purple-600', 'header' => 'border-purple-200 bg-purple-50', 'subtitle' => 'Informações de recebimento e prazos'],
        'Encaminhamentos' => ['icon' => 'arrow', 'iconBg' => 'bg-green-600', 'header' => 'border-green-200 bg-green-50', 'subtitle' => 'Dados de envio, retorno e manifestação'],
        'Diligências' => ['icon' => 'mail', 'iconBg' => 'bg-orange-600', 'header' => 'border-orange-200 bg-orange-50', 'subtitle' => 'Ofícios, respostas e complementações'],
        'Parecer/decisão' => ['icon' => 'check', 'iconBg' => 'bg-green-600', 'header' => 'border-green-200 bg-green-50', 'subtitle' => 'Pareceres, decisões e portarias'],
        'Requisitos legais' => ['icon' => 'shield', 'iconBg' => 'bg-orange-600', 'header' => 'border-orange-200 bg-orange-50', 'subtitle' => 'Documentos e requisitos legais'],
        'Ofertas/usuários/vagas' => ['icon' => 'users', 'iconBg' => 'bg-purple-600', 'header' => 'border-purple-200 bg-purple-50', 'subtitle' => 'Ofertas, usuários e capacidade de atendimento'],
        'Campos adicionais' => ['icon' => 'dots', 'iconBg' => 'bg-blue-600', 'header' => 'border-blue-200 bg-blue-50', 'subtitle' => 'Demais campos da tabela processos_sei'],
    ];
@endphp

<div class="space-y-6">
    <div class="space-y-3">
        <a href="{{ route('base-externa.analise-processo.index', ['search' => $originalProtocolo]) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 transition-colors hover:text-blue-700">
            <span aria-hidden="true">←</span>
            Voltar para processos
        </a>
        <div class="space-y-2">
            <h1 class="text-3xl font-semibold text-gray-900">Editar Processo</h1>
            <p class="text-gray-600">Edite os dados do processo {{ $originalProtocolo }}.</p>
        </div>
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

    <form method="POST" action="{{ route('base-externa.analise-processo.update') }}" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="original_protocolo" value="{{ $originalProtocolo }}">

        @foreach ($sections as $section)
            @php
                $style = $sectionStyles[$section['title']] ?? $sectionStyles['Campos adicionais'];
            @endphp
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="section-{{ $loop->index }}-title">
                <div class="border-b px-6 py-4 {{ $style['header'] }}">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white {{ $style['iconBg'] }}">
                            @switch($style['icon'])
                                @case('calendar')
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25m10.5-2.25v2.25M3 18.75V7.5A2.25 2.25 0 0 1 5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    @break
                                @case('arrow')
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                    </svg>
                                    @break
                                @case('mail')
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0l-7.5-4.615A2.25 2.25 0 0 1 2.25 6.993V6.75" />
                                    </svg>
                                    @break
                                @case('check')
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                    @break
                                @case('shield')
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                    </svg>
                                    @break
                                @case('users')
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 9.094 9.094 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                    </svg>
                                    @break
                                @case('dots')
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                    </svg>
                                    @break
                                @default
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5m-7.5 3h10.5A2.25 2.25 0 0 0 18 18.75V9.75L10.5 2.25H5.25A2.25 2.25 0 0 0 3 4.5v14.25A2.25 2.25 0 0 0 5.25 21Z" />
                                    </svg>
                            @endswitch
                        </div>
                        <div>
                            <h2 id="section-{{ $loop->index }}-title" class="text-lg font-semibold text-gray-900">{{ $section['title'] }}</h2>
                            <p class="text-sm text-gray-600">{{ $style['subtitle'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid gap-5 lg:grid-cols-3">
                        @foreach ($section['fields'] as $field)
                            @php
                                $type = $repository->inputType($field, $columnTypes[$field] ?? null);
                                $value = old($field, $repository->formatValueForInput($processo[$field] ?? null, $type));
                            @endphp

                            <div class="{{ $type === 'textarea' ? 'lg:col-span-3' : '' }}">
                                <label for="field-{{ $loop->parent->index }}-{{ $loop->index }}" class="{{ $labelClass }}">{{ $repository->fieldLabel($field) }}</label>

                                @if ($type === 'textarea')
                                    <textarea id="field-{{ $loop->parent->index }}-{{ $loop->index }}" name="{{ $field }}" rows="3" class="{{ $inputClass }}">{{ $value }}</textarea>
                                @elseif ($type === 'boolean')
                                    <select id="field-{{ $loop->parent->index }}-{{ $loop->index }}" name="{{ $field }}" class="{{ $inputClass }} bg-white">
                                        <option value="" @selected($value === null || $value === '')>Não informado</option>
                                        <option value="1" @selected((string) $value === '1')>Sim</option>
                                        <option value="0" @selected((string) $value === '0')>Não</option>
                                    </select>
                                @else
                                    <input id="field-{{ $loop->parent->index }}-{{ $loop->index }}" name="{{ $field }}" type="{{ $type }}" value="{{ $value }}" class="{{ $inputClass }}" @if ($type === 'number') step="any" @endif>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach

        <div class="flex flex-col items-stretch justify-end gap-4 pt-4 sm:flex-row sm:items-center">
            <a href="{{ route('base-externa.analise-processo.index', ['search' => $originalProtocolo]) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-8 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 text-sm font-medium text-white shadow-md transition-colors hover:bg-blue-700 hover:shadow-lg">Salvar Alterações</button>
        </div>
    </form>
</div>
@endsection
