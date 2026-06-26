@extends('layouts.app')

@section('title', 'Base externa - Manifestação')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-700';

    $offerOptions = [
        'abordagem social',
        'acesso ao mundo do trabalho',
        'acolhimento da PSE de alta complexidade',
        'acolhimento em abrigo',
        'acolhimento em república',
        'ampliação de acesso a direitos estabelecidos',
        'apoio/proteção em emergência e calamidade pública',
        'articulação com órgãos públicos de defesa de direitos',
        'assessoramento político, técnico, administrativo e financeiro',
        'casa de apoio',
        'construção de novos direitos',
        'convivência e Fortalecimento de Vínculos',
        'enfrentamento das desigualdades sociais',
        'estudos e pesquisas sobre direitos',
        'família acolhedora',
        'formação e capacitação de lideranças comunitárias',
        'formação político-cidadã de grupos populares',
        'fortalecimento de mov. sociais e org. de usuários',
        'habilitação e reabilitação',
        'medidas socioeducativas de LA',
        'medidas socioeducativas de PSC',
        'monitoramento e controle popular sobre a Política de AS',
        'Não se aplica',
        'político, técnico, administrativo e financeiro',
        'projeto de geração de renda',
        'projetos de defesa/efetivação de direitos socioassistenciais',
        'promoção da cidadania',
        'proteção social básica no domícilio',
        'serviço de proteção social especial',
        'socioaprendizagem',
    ];

    $userOptions = [
        'adolescentes',
        'adultos',
        'comunidade',
        'conselheiros',
        'crianças',
        'entidades de Assistência Social',
        'famílias',
        'idosos',
        'jovens',
        'lideranças comunitárias',
        'movimentos sociais',
        'mulheres',
        'organização de usuários',
        'pessoas com direitos violados',
        'pessoa com deficiência',
        'pessoas em situação de rua',
        'pessoas em situação de violência',
        'Não se aplica',
    ];

    $userQualificationOptions = [
        'comunidades tradicionais',
        'povos indígenas',
        'população rural',
        'população negra',
        'LGBT',
        'catadores de materiais recicláveis',
        'migrantes',
        'situação carcerária',
        'Não se aplica',
    ];

    $selectValues = [
        'ORGAO_ENCAMINHAMENTO' => ['MEC', 'MS', 'DEPAD', 'Não se aplica'],
        'OUTRAS_OFERTAS' => ['Educação', 'Saúde'],
        'OFERTAS_OUTRAS_AREAS' => [
            'Creche',
            'Ensino básico',
            'Ensino superior',
            'Educação para jovens e adultos',
            'Educação à distância',
            'Comunidade terapêutica',
            'Atendimento médico',
            'Atendimento odontológico',
            'Promoção à saúde',
            'Ensino para pessoas com deficiência',
            'Cursos profissionalizantes',
            'Residência Terapêutica',
            'Não se aplica',
        ],
        'OUTRAS_OFERTAS_I' => [
            'está na área da assistência social',
            'está na área da educação',
            'está na área da saúde',
            'está em área não certificável',
            'não apresentou documento contábil segregado',
            'educação',
            'saúde',
            'Não se aplica',
        ],
        'CGCEB_MANIFESTACACAO' => [
            ['value' => '1', 'label' => 'Leandro de Oliveira Nardi'],
            ['value' => '3', 'label' => 'Edgilson Tavares de Araújo'],
        ],
        'DRSP_MANIFESTACAO' => [
            ['value' => '1', 'label' => 'Leandro de Oliveira Nardi'],
            ['value' => '3', 'label' => 'Edgilson Tavares de Araújo'],
        ],
    ];

    $romanField = fn (string $prefix, string $roman) => $prefix . '_' . $roman;
    $qualificationField = fn (string $roman) => $roman === 'IV' ? 'QUALIFICACAO_USUARIO_Iv' : 'QUALIFICACAO_USUARIO_' . $roman;

    foreach (['I', 'II', 'III', 'IV', 'V', 'VI', 'VII'] as $roman) {
        $selectValues[$romanField('OFERTA', $roman)] = $offerOptions;
        $selectValues[$romanField('USUARIO', $roman)] = $userOptions;
        $selectValues[$qualificationField($roman)] = $userQualificationOptions;
    }

    $sectionStyles = [
        'Atividades do relatório' => ['iconBg' => 'bg-purple-600', 'header' => 'border-purple-200 bg-purple-50', 'subtitle' => 'Ofertas, vagas, usuários e qualificações'],
        'Documento'               => ['iconBg' => 'bg-blue-600',   'header' => 'border-blue-200 bg-blue-50',     'subtitle' => 'CNAE e demonstrativo contábil'],
        'Observação'              => ['iconBg' => 'bg-orange-600', 'header' => 'border-orange-200 bg-orange-50', 'subtitle' => 'Texto da solicitação de manifestação'],
        'Assinaturas'             => ['iconBg' => 'bg-gray-600',   'header' => 'border-gray-200 bg-gray-50',     'subtitle' => 'Responsáveis pela manifestação'],
    ];
@endphp

<div class="space-y-6">
    <div class="space-y-3">
        <a href="{{ route('base-externa.analise-processo.index', ['search' => $originalProtocolo]) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 transition-colors hover:text-blue-700">
            <span aria-hidden="true">←</span>
            Voltar para processos
        </a>
        <div class="space-y-2">
            <h1 class="text-3xl font-semibold text-gray-900">Manifestação</h1>
            <p class="text-gray-600">Edite os dados da manifestação do processo {{ $originalProtocolo }}.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Confira os dados informados.</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
        <img src="{{ asset('images/brasao.png') }}" alt="Brasão da República Federativa do Brasil" class="mx-auto h-11 w-auto object-contain">
        <div class="mt-4 space-y-1 text-sm text-gray-900">
            <p class="text-base font-bold uppercase">Ministério do Desenvolvimento e Assistência Social, Família e Combate à Fome</p>
            <p>Secretaria Nacional de Assistência Social</p>
            <p>Departamento da Rede Socioassistencial Privada do SUAS</p>
            <p class="font-semibold">Coordenação Geral de Certificação das Entidades Beneficentes de Assistência Social</p>
        </div>
    </section>

    <form method="POST" action="{{ route('base-externa.analise-processo.manifestacao.update') }}" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="original_protocolo" value="{{ $originalProtocolo }}">

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="informacoes-title">
            <div class="border-b border-blue-200 bg-blue-50 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-white">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5m-7.5 3h10.5A2.25 2.25 0 0 0 18 18.75V9.75L10.5 2.25H5.25A2.25 2.25 0 0 0 3 4.5v14.25A2.25 2.25 0 0 0 5.25 21Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="informacoes-title" class="text-lg font-semibold text-gray-900">Informações do Processo</h2>
                        <p class="text-sm text-gray-600">Dados básicos da manifestação</p>
                    </div>
                </div>
            </div>
            <div class="grid gap-5 p-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($headerColumns as $field)
                    @php
                        $headerValue = $processo[$field] ?? '-';

                        if (str_starts_with($field, 'DT_') && is_string($headerValue) && preg_match('/^\d{4}-\d{2}-\d{2}/', $headerValue)) {
                            $headerValue = \Carbon\Carbon::parse($headerValue)->format('d/m/Y');
                        }
                    @endphp
                    <div class="{{ $field === 'ENTIDADE' ? 'md:col-span-2 xl:col-span-3' : '' }}">
                        <label class="{{ $labelClass }}">{{ $repository->parecerTecnicoLabel($field) }}</label>
                        <div class="min-h-11 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700">{{ $headerValue }}</div>
                    </div>
                @endforeach
            </div>
        </section>

{{-- Seções editáveis --}}
        @foreach ($sections as $section)
            @php
                $style = $sectionStyles[$section['title']] ?? ['iconBg' => 'bg-gray-600', 'header' => 'border-gray-200 bg-gray-50', 'subtitle' => ''];
                $isActivitySection = $section['title'] === 'Atividades do relatório';
            @endphp
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b {{ $style['header'] }} px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $style['iconBg'] }} text-white">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">{{ $section['title'] }}</h2>
                            <p class="text-sm text-gray-500">{{ $style['subtitle'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    @if ($isActivitySection)
                        <div class="space-y-4" data-activities-wrapper>
                            @php
                                $activityFields = collect($offerRomanNumerals)
                                    ->flatMap(fn ($roman) => [$romanField('OFERTA', $roman), $romanField('VAGAS', $roman), $romanField('USUARIO', $roman), $qualificationField($roman)])
                                    ->all();
                                $visibleActivities = 0;
                            @endphp

                            @foreach ($offerRomanNumerals as $roman)
                                @php
                                    $fields = [$romanField('OFERTA', $roman), $romanField('VAGAS', $roman), $romanField('USUARIO', $roman), $qualificationField($roman)];
                                    $presentFields = array_values(array_intersect($fields, $section['fields']));
                                    $hasContent = collect($presentFields)->contains(fn ($field) => filled(old($field, $processo[$field] ?? null)));
                                    $isVisible = $loop->first || $hasContent;
                                    $visibleActivities += $isVisible ? 1 : 0;
                                @endphp
                                @if ($presentFields !== [])
                                    <div class="rounded-lg border border-gray-200 p-4 {{ $isVisible ? '' : 'hidden' }}" data-activity-block>
                                        <h3 class="mb-6 text-base font-semibold text-gray-900">Atividade {{ $roman }}</h3>
                                        <div class="grid gap-6 md:grid-cols-2">
                                            @foreach ($presentFields as $field)
                                                @php
                                                    $fieldClass = match (true) {
                                                        str_starts_with(strtolower($field), 'usuario_'), str_starts_with(strtolower($field), 'qualificacao_usuario_') => 'md:col-span-2',
                                                        default => '',
                                                    };
                                                    $fieldData = match (true) {
                                                        str_starts_with(strtolower($field), 'oferta_') => 'data-oferta-field',
                                                        str_starts_with(strtolower($field), 'usuario_') => 'data-usuario-field',
                                                        str_starts_with(strtolower($field), 'qualificacao_usuario_') => 'data-qualificacao-field',
                                                        default => '',
                                                    };
                                                @endphp
                                                <div class="{{ $fieldClass }}" {!! $fieldData !!}>
                                                    <label for="{{ $field }}" class="{{ $labelClass }}">{{ $repository->parecerTecnicoLabel($field) }}</label>
                                                    @include('base-externa.analise-processo.nota-tecnica._field', ['field' => $field])
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            <div class="flex items-center gap-3 {{ $visibleActivities >= count($offerRomanNumerals) ? 'hidden' : '' }}" data-add-activity-wrapper>
                                <button type="button"
                                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        data-add-activity>
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Adicionar atividade
                                </button>
                            </div>

                            @foreach (array_diff($section['fields'], $activityFields) as $field)
                                <div>
                                    <label for="{{ $field }}" class="{{ $labelClass }}">{{ $repository->manifestacaoLabel($field) }}</label>
                                    @include('base-externa.analise-processo.nota-tecnica._field', ['field' => $field])
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="grid gap-5 {{ $section['title'] === 'Observação' ? 'grid-cols-1' : 'md:grid-cols-2' }}">
                            @foreach ($section['fields'] as $field)
                                <div>
                                    <label for="{{ $field }}" class="{{ $labelClass }}">{{ $repository->manifestacaoLabel($field) }}</label>
                                    @include('base-externa.analise-processo.nota-tecnica._field', ['field' => $field])
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endforeach

        @php
            $logUsers = collect($manifestacaoLogs)->pluck('user')->filter()->unique()->values();
        @endphp
        @if ($logUsers->isNotEmpty())
            <div class="pt-2 text-sm text-gray-600">
                Atualizada por:
                <button type="button" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline" data-open-log-modal>
                    {{ $logUsers->join(', ') }}
                </button>
            </div>
        @endif

        <div class="flex flex-col items-stretch justify-end gap-4 pt-4 sm:flex-row sm:items-center">
            <a href="{{ route('base-externa.analise-processo.index', ['search' => $originalProtocolo]) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-8 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" name="_action" value="save"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 text-sm font-medium text-white shadow-md transition-colors hover:bg-blue-700 hover:shadow-lg">
                Salvar alterações
            </button>
            <button type="submit" name="_action" value="save_pdf"
                    class="inline-flex items-center justify-center rounded-lg bg-green-600 px-8 py-3 text-sm font-medium text-white shadow-md transition-colors hover:bg-green-700 hover:shadow-lg">
                Gerar PDF
            </button>
        </div>
    </form>
</div>

@if (! empty($manifestacaoLogs))
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" data-log-modal>
    <div class="flex w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" style="max-height: 60vh;">

        <div class="flex shrink-0 items-center justify-between bg-blue-600 px-6 py-4 text-white">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h2 class="text-lg font-bold text-white">Histórico de alterações da manifestação</h2>
            </div>
            <button type="button" class="cursor-pointer text-xl leading-none text-blue-100 hover:text-white" data-close-log-modal>&times;</button>
        </div>

        <div class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-white p-4">
            @foreach ($manifestacaoLogs as $log)
                <div class="rounded-xl border border-gray-200 p-4 text-sm shadow-xs">
                    <div class="flex items-center justify-between gap-4 mb-2">
                        <div class="font-bold text-gray-800">{{ $log['date_created'] }} — {{ $log['user'] }}</div>
                        <div class="text-xs font-semibold text-blue-600 shrink-0">Salvou manifestação</div>
                    </div>
                    <div class="text-gray-500 leading-relaxed">
                        <strong class="font-semibold text-gray-700">Campos alterados:</strong> {{ collect($log['campos'])->map(fn ($field) => $repository->manifestacaoLabel($field))->join('; ') ?: 'Não informado' }}
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('textarea[data-autoresize]').forEach((textarea) => {
            const resize = () => {
                textarea.style.height = 'auto';
                textarea.style.height = `${textarea.scrollHeight}px`;
            };
            textarea.addEventListener('input', resize);
            resize();
        });

        const logModal = document.querySelector('[data-log-modal]');
        document.querySelector('[data-open-log-modal]')?.addEventListener('click', () => {
            logModal?.classList.remove('hidden');
            logModal?.classList.add('flex');
        });
        document.querySelector('[data-close-log-modal]')?.addEventListener('click', () => {
            logModal?.classList.add('hidden');
            logModal?.classList.remove('flex');
        });
        logModal?.addEventListener('click', (event) => {
            if (event.target === logModal) {
                logModal.classList.add('hidden');
                logModal.classList.remove('flex');
            }
        });

        document.querySelectorAll('[data-activities-wrapper]').forEach((wrapper) => {
            const buttonWrapper = wrapper.querySelector('[data-add-activity-wrapper]');
            const button = wrapper.querySelector('[data-add-activity]');
            const blocks = Array.from(wrapper.querySelectorAll('[data-activity-block]'));

            function syncOfertaDependents(block) {
                const ofertaSelect = block.querySelector('[data-oferta-field] select');
                const hasOferta = ofertaSelect && ofertaSelect.value !== '';
                block.querySelector('[data-usuario-field]')?.classList.toggle('hidden', !hasOferta);
                block.querySelector('[data-qualificacao-field]')?.classList.toggle('hidden', !hasOferta);
            }

            blocks.forEach((block) => {
                syncOfertaDependents(block);
                block.querySelector('[data-oferta-field] select')?.addEventListener('change', () => syncOfertaDependents(block));
            });

            button?.addEventListener('click', () => {
                const nextBlock = blocks.find((block) => block.classList.contains('hidden'));

                if (!nextBlock) {
                    buttonWrapper?.classList.add('hidden');
                    return;
                }

                nextBlock.classList.remove('hidden');
                syncOfertaDependents(nextBlock);

                if (!blocks.some((block) => block.classList.contains('hidden'))) {
                    buttonWrapper?.classList.add('hidden');
                }
            });
        });
    });
</script>
@endsection
