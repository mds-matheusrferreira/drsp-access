@extends('layouts.app')

@section('title', 'CNEAS')

@section('content')
<div class="space-y-6">
    <div class="space-y-2">
        <h1 class="text-3xl font-semibold text-gray-900">Planilha CNEAS - Upload e download de Dados</h1>
        <p class="text-base text-gray-600">Sistema de importação de planilha CNEAS</p>
    </div>

    <section class="rounded-lg bg-blue-50 px-5 py-4 text-blue-700" aria-labelledby="informacoes-cneas-title">
        <div class="flex gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
            <div>
                <h2 id="informacoes-cneas-title" class="font-semibold">Informações Importantes</h2>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    <li>Formato aceito: Excel (.xlsx, .xls)</li>
                    <li>Tamanho máximo: 10MB</li>
                    <li>Certifique-se de que a planilha segue o modelo padrão</li>
                </ul>
            </div>
        </div>
    </section>

    <div>
        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0 4.5-4.5M12 16.5V3" />
            </svg>
            Baixar Modelo de Planilha
        </button>
    </div>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="importar-cneas-title">
        <div class="border-b border-green-200 bg-green-100 px-6 py-5">
            <div class="flex items-center gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-600 text-white">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-.989-2.386l-4.5-4.5a3.375 3.375 0 0 0-2.386-.989H8.25A2.25 2.25 0 0 0 6 6v12a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 18v-3.75M12 8.25V3.75M9.75 14.25h4.5m-4.5 3h4.5" />
                    </svg>
                </div>
                <div>
                    <h2 id="importar-cneas-title" class="text-xl font-semibold text-gray-900">Importar Planilha CNEAS</h2>
                    <p class="text-sm text-gray-600">Selecione o arquivo Excel para importar os dados</p>
                </div>
            </div>
        </div>

        <div class="space-y-6 p-6">
            <label for="cneas-file" class="flex min-h-64 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 px-6 py-12 text-center transition-colors hover:border-blue-400 hover:bg-blue-50/40">
                <span class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                    <svg class="h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 7.5 12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                </span>
                <span class="mt-6 text-lg font-semibold text-gray-900">Clique para selecionar ou arraste o arquivo</span>
                <span id="cneas-file-label" class="mt-2 text-sm font-medium text-gray-600">Arquivos Excel (.xlsx, .xls) - Máximo 10MB</span>
                <input id="cneas-file" type="file" class="sr-only" accept=".xlsx,.xls">
            </label>

            <button id="cneas-submit" type="button" disabled class="inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg bg-gray-300 px-4 py-4 text-base font-semibold text-white transition-colors">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 7.5 12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
                Enviar Dados CNEAS
            </button>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="uploads-recentes-title">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 id="uploads-recentes-title" class="text-xl font-semibold text-gray-900">Uploads Recentes</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach ([
                ['name' => 'cneas_nacional_2026_05.xlsx', 'date' => '29/05/2026 09:20', 'records' => '1.240 registros'],
                ['name' => 'cneas_nacional_2026_04.xlsx', 'date' => '28/04/2026 11:10', 'records' => '1.150 registros'],
                ['name' => 'cneas_nacional_2026_03.xlsx', 'date' => '25/03/2026 14:05', 'records' => '980 registros'],
            ] as $upload)
                <article class="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-.989-2.386l-4.5-4.5a3.375 3.375 0 0 0-2.386-.989H8.25A2.25 2.25 0 0 0 6 6v12a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 18v-3.75M12 8.25V3.75M9.75 14.25h4.5m-4.5 3h4.5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $upload['name'] }}</h3>
                            <p class="text-sm text-gray-600">{{ $upload['date'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-4 sm:justify-end">
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">{{ $upload['records'] }}</p>
                            <p class="text-sm text-green-600">Importado com sucesso</p>
                        </div>
                        <svg class="h-6 w-6 shrink-0 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('cneas-file');
        const fileLabel = document.getElementById('cneas-file-label');
        const submitButton = document.getElementById('cneas-submit');

        fileInput?.addEventListener('change', () => {
            const file = fileInput.files?.[0];

            if (!file) {
                fileLabel.textContent = 'Arquivos Excel (.xlsx, .xls) - Máximo 10MB';
                submitButton.disabled = true;
                submitButton.classList.add('cursor-not-allowed', 'bg-gray-300');
                submitButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                return;
            }

            fileLabel.textContent = file.name;
            submitButton.disabled = false;
            submitButton.classList.remove('cursor-not-allowed', 'bg-gray-300');
            submitButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
        });
    });
</script>
@endsection