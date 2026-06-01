@extends('layouts.app')

@section('title', 'Base externa - Editar Processo')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-700';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-2">
            <div class="text-sm font-semibold uppercase tracking-wide text-blue-600">Base Externa</div>
            <h1 class="text-3xl font-semibold text-gray-900">Editar Processo</h1>
            <p class="text-gray-600">Atualize os campos do processo {{ $originalProtocolo }}.</p>
        </div>
        <a href="{{ route('base-externa.analise-processo.index', ['search' => $originalProtocolo]) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50">
            Voltar para pesquisa
        </a>
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
        <input type="hidden" name="ORIGINAL_PROTOCOLO" value="{{ $originalProtocolo }}">

        @foreach ($sections as $section)
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="section-{{ $loop->index }}-title">
                <div class="border-b border-blue-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                    <h2 id="section-{{ $loop->index }}-title" class="text-lg font-semibold text-gray-900">{{ $section['title'] }}</h2>
                    <p class="text-sm text-gray-600">{{ count($section['fields']) }} campo(s)</p>
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
