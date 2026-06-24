@extends('layouts.app')

@section('title', 'Automação CNEAS')

@section('content')
<div class="space-y-6">
    <div class="space-y-2">
        <h1 class="text-3xl font-semibold text-gray-900">Automação CNEAS</h1>
        <p class="text-base text-gray-600">Gere e baixe o relatório CNEAS consolidado.</p>
    </div>

    @if (session('error'))
        <div class="rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-green-200 bg-green-100 px-6 py-5">
            <h2 class="text-xl font-semibold text-gray-900">Gerar relatório</h2>
            <p class="text-sm text-gray-600">Usa os arquivos da pasta docs/Automação_CNEAS/Origem.</p>
        </div>
        <div class="space-y-4 p-6">
            <form method="POST" action="{{ route('coordenacao.automacoes.cneas.generate') }}">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-4 text-base font-semibold text-white hover:bg-blue-700">
                    Gerar relatório CNEAS
                </button>
            </form>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-xl font-semibold text-gray-900">Último relatório gerado</h2>
        </div>
        <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                @if ($latest)
                    <p class="font-semibold text-gray-900">{{ $latest['name'] }}</p>
                    <p class="text-sm text-gray-600">Gerado em: {{ $latest['date'] }}</p>
                @else
                    <p class="text-sm text-gray-600">Nenhum relatório gerado.</p>
                @endif
            </div>
            @if ($latest)
                <a href="{{ route('coordenacao.automacoes.cneas.latest') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    Baixar último relatório
                </a>
            @endif
        </div>
    </section>
</div>
@endsection
