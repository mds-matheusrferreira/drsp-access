@extends('layouts.app')

@section('title', 'Base externa - Nota Técnica')

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

    $rejectionReasonOptions = [
        'Não apresentou documento(s) obrigatório(s)',
        'Não demonstrou gratuidade nas ofertas',
        'Não atua no âmbito da assistência social',
        'Não demonstrou continuidade, planejamento e universalidade nas ofertas',
        'Não atendeu os requisitos de outra(s) área(s) da certificação',
        'Não atua preponderantemente no âmbito da assistência social',
        'Não está de acordo com a Política Nacional de Assistência Social - PNAS',
        'Estatuto Social não compatível com a legislação',
        'Estatuto Social não compatível com a LOAS',
        'Não demonstrou atuar preponderantemente no âmbito da Assistência Social',
        'A entidade permancece cumprindo os requisitos que ensejaram a Certificação',
        'Não demonstrou continuidade nas ofertas',
        'Não demonstrou planejamento nas ofertas',
        'Não demonstrou universalidade nas ofertas',
        'Não se aplica',
    ];

    $selectValues = [
        'DOCUMENTOS_OBRIGATORIOS' => ['Apresentou todos os documentos', 'Não apresentou todos os documentos', 'Não foram analisados os documentos por não autar na assistência social'],
        'DOCUMENTOS_PENDENTES' => ['Ata de eleição', 'Balanço patrimonial', 'Comprovante de cnpj', 'Declaração de gratuidade', 'Demonstração dos fluxos de caixa', 'Demonstração das mutações do patrimônio líquido', 'Demonstração de resultado de exercício (D.R.E.)', 'Estatuto', 'Inscrição no Conselho Local de Assistência Social', 'Nota explicativa', 'Parecer de auditoria independente', 'Relatório de atividades'],
        'COMPATIBILIDADE_ESTATUTO_LOAS' => ['Compatível com a legislação', 'Não está compatível com a legislação', 'Não apresentou o documento', 'Não foi analisado'],
        'DESTINO_PATRIMONIO_CASO_DISSOLUCAO' => ['Compatível com a legislação', 'Não está compatível com a legislação', 'Não apresentou o documento', 'Não foi analisado'],
        'GRATUIDADE_PARECER' => ['A participação do idoso supera o limite da lei', 'É possível aferir a gratuidade das ofertas', 'Há indícios de contraprestação do usuário', 'Não apresentou documento que demonstre gratuidade', 'Não é possível aferir a gratuidade das ofertas', 'Não se aplica'],
        'MOTIVO_ENCAMINHAMENTO' => [
            'Encaminhamento de processo ao MS',
            'Encaminhamento de processo ao MEC',
            'Encaminhamento de processo ao DEPAD',
            'Resposta de manifestação ao MS',
            'Resposta de manifestação ao MEC',
            'Resposta de manifestação ao DEPAD',
        ],
        'ORGAO_ENCAMINHAMENTO' => ['MEC', 'MS', 'DEPAD', 'Não se aplica'],
        'CONTINUIDADE' => ['Não', 'Sim'],
        'PLANEJAMENTO' => ['Não', 'Sim'],
        'UNIVERSALIDADE' => ['Não', 'Sim'],
        'DECISAO_PARECER' => ['DEFERIDO', 'INDEFERIDO', 'FAVORÁVEL', 'DESFAVORÁVEL', 'ENCAMINHAMENTO', 'MANUTENÇÃO DO CEBAS', 'APRESENTAR DEFESA', 'SUGERE-SE O CANCELAMENTO DA CERTIFICAÇÃO'],
        'MOTIVO_INDEFERIMENTO' => $rejectionReasonOptions,
        'CGCEB_PARECER' => [
            ['value' => '1', 'label' => 'Leandro de Oliveira Nardi'],
            ['value' => '3', 'label' => 'Edgilson Tavares de Araújo'],
        ],
        'DRSP_PARECER' => [
            ['value' => '1', 'label' => 'Leandro de Oliveira Nardi'],
            ['value' => '3', 'label' => 'Edgilson Tavares de Araújo'],
        ],
    ];

    $romanField = fn (string $prefix, string $roman) => $prefix.'_'.$roman;
    $qualificationField = fn (string $roman) => $roman === 'IV' ? 'QUALIFICACAO_USUARIO_Iv' : 'QUALIFICACAO_USUARIO_'.$roman;

    foreach (['I', 'II', 'III', 'IV', 'V', 'VI', 'VII'] as $roman) {
        $selectValues[$romanField('OFERTA', $roman)] = $offerOptions;
        $selectValues[$romanField('USUARIO', $roman)] = $userOptions;
        $selectValues[$qualificationField($roman)] = $userQualificationOptions;
    }
    $sectionStyles = [
        'Análise técnica' => ['iconBg' => 'bg-blue-600', 'header' => 'border-blue-200 bg-blue-50', 'subtitle' => 'Documentos obrigatórios e objetivos estatutários'],
        'Atividades do relatório' => ['iconBg' => 'bg-purple-600', 'header' => 'border-purple-200 bg-purple-50', 'subtitle' => 'Ofertas, vagas, usuários e qualificações'],
        'Gratuidade e manifestações' => ['iconBg' => 'bg-orange-600', 'header' => 'border-orange-200 bg-orange-50', 'subtitle' => 'Gratuidade e manifestações de outros órgãos'],
        'Princípios de Atendimento da Assistência Social' => ['iconBg' => 'bg-teal-600', 'header' => 'border-teal-200 bg-teal-50', 'subtitle' => 'Continuidade, planejamento e universalidade'],
        'Conclusão' => ['iconBg' => 'bg-green-600', 'header' => 'border-green-200 bg-green-50', 'subtitle' => 'Decisão e exposição de motivos'],
        'Assinaturas' => ['iconBg' => 'bg-gray-600', 'header' => 'border-gray-200 bg-gray-50', 'subtitle' => 'Responsáveis pela nota técnica'],
    ];
@endphp

<div class="space-y-6">
    <div class="space-y-3">
        <a href="{{ route('base-externa.analise-processo.index', ['search' => $originalProtocolo]) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 transition-colors hover:text-blue-700">
            <span aria-hidden="true">←</span>
            Voltar para processos
        </a>
        <div class="space-y-2">
            <h1 class="text-3xl font-semibold text-gray-900">Nota Técnica</h1>
            <p class="text-gray-600">Edite os dados da nota técnica do processo {{ $originalProtocolo }}.</p>
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

    <form method="POST" action="{{ route('base-externa.analise-processo.nota-tecnica.update') }}" class="space-y-6">
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
                        <p class="text-sm text-gray-600">Dados básicos da nota técnica</p>
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

        @foreach ($sections as $section)
            @php
                $sectionTitle = $section['title'];
                $style = $sectionStyles[$section['title']] ?? ['iconBg' => 'bg-blue-600', 'header' => 'border-blue-200 bg-blue-50', 'subtitle' => 'Campos da nota técnica'];
            @endphp
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="section-{{ $loop->index }}-title">
                <div class="border-b px-6 py-4 {{ $style['header'] }}">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white {{ $style['iconBg'] }}">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 id="section-{{ $loop->index }}-title" class="text-lg font-semibold text-gray-900">{{ $sectionTitle }}</h2>
                            <p class="text-sm text-gray-600">{{ $style['subtitle'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    @if ($section['title'] === 'Atividades do relatório')
                        <div class="space-y-4" data-activities-wrapper>
                            @php
                                $activityFields = collect($offerRomanNumerals)
                                    ->flatMap(fn ($roman) => [$romanField('oferta', $roman), $romanField('vagas', $roman), $romanField('usuario', $roman), $qualificationField($roman)])
                                    ->all();
                                $visibleActivities = 0;
                            @endphp

                            @foreach ($offerRomanNumerals as $roman)
                                @php
                                    $fields = [$romanField('oferta', $roman), $romanField('vagas', $roman), $romanField('usuario', $roman), $qualificationField($roman)];
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
                                                        str_starts_with($field, 'usuario_'), str_starts_with($field, 'qualificacao_usuario_') => 'md:col-span-2',
                                                        default => '',
                                                    };
                                                @endphp
                                                <div class="{{ $fieldClass }}">
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
                                    <label for="{{ $field }}" class="{{ $labelClass }}">{{ $repository->parecerTecnicoLabel($field) }}</label>
                                    @include('base-externa.analise-processo.nota-tecnica._field', ['field' => $field])
                                </div>
                            @endforeach
                        </div>
                    @else
                        @php
                            $sectionFields = $section['fields'];
                            $isGratuidadeSection = $section['title'] === 'Gratuidade e manifestações';

                            if ($isGratuidadeSection && ! in_array('NOTA_TECNICA_OUTRO_ORGAO', $sectionFields, true)) {
                                $insertAfter = array_search('ORGAO_ENCAMINHAMENTO', $sectionFields, true);
                                array_splice($sectionFields, $insertAfter === false ? count($sectionFields) : $insertAfter + 1, 0, ['NOTA_TECNICA_OUTRO_ORGAO']);
                            }

                            if ($isGratuidadeSection) {
                                $notaTecnicaIndex = array_search('NOTA_TECNICA_OUTRO_ORGAO', $sectionFields, true);
                                $manifestacaoIndex = array_search('MANIFESTACAO_OUTRO_MINISTERIO', $sectionFields, true);

                                if ($notaTecnicaIndex !== false && $manifestacaoIndex !== false && $notaTecnicaIndex > $manifestacaoIndex) {
                                    array_splice($sectionFields, $notaTecnicaIndex, 1);
                                    array_splice($sectionFields, $manifestacaoIndex, 0, ['NOTA_TECNICA_OUTRO_ORGAO']);
                                }
                            }
                        @endphp
                        <div class="grid gap-5 md:grid-cols-2 {{ $isGratuidadeSection ? '' : 'xl:grid-cols-3' }}">
                            @foreach ($sectionFields as $field)
                                @php
                                    $hidePendingDocuments = $field === 'DOCUMENTOS_PENDENTES'
                                        && old('DOCUMENTOS_OBRIGATORIOS', $processo['DOCUMENTOS_OBRIGATORIOS'] ?? '') !== 'Não apresentou todos os documentos';
                                    $hideRejectionReasons = $field === 'MOTIVO_INDEFERIMENTO'
                                        && old('DECISAO_PARECER', $processo['DECISAO_PARECER'] ?? '') !== 'INDEFERIDO';
                                    $wideFields = $isGratuidadeSection
                                        ? ['GRATUIDADE_PARECER', 'MOTIVO_ENCAMINHAMENTO', 'ORGAO_ENCAMINHAMENTO', 'NOTA_TECNICA_OUTRO_ORGAO']
                                        : ['DOCUMENTOS_PENDENTES', 'MOTIVO_INDEFERIMENTO', 'JUSTIFICATIVA_INDEFERIMENTO_NT'];
                                    $wideClass = in_array($field, $wideFields, true)
                                        ? ($isGratuidadeSection ? 'md:col-span-2' : 'md:col-span-2 xl:col-span-3')
                                        : '';
                                    $fieldLabel = match ($field) {
                                        'NOTA_TECNICA_OUTRO_ORGAO' => 'Nota técnica de outro ministério',
                                        'MANIFESTACAO_OUTRO_MINISTERIO' => 'Número(s)',
                                        default => $repository->parecerTecnicoLabel($field),
                                    };
                                @endphp
                                <div class="{{ $wideClass }} {{ $hidePendingDocuments || $hideRejectionReasons ? 'hidden' : '' }}" @if($field === 'DOCUMENTOS_PENDENTES') data-pending-documents-wrapper @endif @if($field === 'MOTIVO_INDEFERIMENTO') data-rejection-reasons-wrapper @endif>
                                    <label for="{{ $field }}" class="{{ $labelClass }}">{{ $fieldLabel }}</label>
                                    @include('base-externa.analise-processo.nota-tecnica._field', ['field' => $field])
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endforeach

        @php
            $logUsers = collect($notaTecnicaLogs)->pluck('user')->filter()->unique()->values();
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
            <a href="{{ route('base-externa.analise-processo.index', ['search' => $originalProtocolo]) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-8 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">Cancelar</a>
            <button type="submit" name="_action" value="save" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 text-sm font-medium text-white shadow-md transition-colors hover:bg-blue-700 hover:shadow-lg">Salvar alterações</button>
            <button type="submit" name="_action" value="save_pdf" class="inline-flex items-center justify-center rounded-lg bg-green-600 px-8 py-3 text-sm font-medium text-white shadow-md transition-colors hover:bg-green-700 hover:shadow-lg">Gerar PDF</button>
        </div>
    </form>

    @if (! empty($notaTecnicaLogs))
    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" data-log-modal>
        <div class="flex w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" style="max-height: 60vh;">

            <div class="flex shrink-0 items-center justify-between bg-blue-600 px-6 py-4 text-white">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h2 class="text-lg font-bold text-white">Histórico de alterações da nota técnica</h2>
                </div>
                <button type="button" class="cursor-pointer text-xl leading-none text-blue-100 hover:text-white" data-close-log-modal>&times;</button>
            </div>

            <div class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-white p-4">
                @foreach ($notaTecnicaLogs as $log)
                    <div class="rounded-xl border border-gray-200 p-4 text-sm shadow-xs">
                        <div class="flex items-center justify-between gap-4 mb-2">
                            <div class="font-bold text-gray-800">{{ $log['date_created'] }} — {{ $log['user'] }}</div>
                            <div class="text-xs font-semibold text-blue-600 shrink-0">{{ $log['area'] === 'parecer_tecnico' ? 'Salvou parecer' : 'Salvou nota técnica' }}</div>
                        </div>
                        <div class="text-gray-500 leading-relaxed">
                            <strong class="font-semibold text-gray-700">Campos alterados:</strong> {{ collect($log['campos'])->map(fn ($field) => $repository->parecerTecnicoLabel($field))->join('; ') ?: 'Não informado' }}
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-autoresize]').forEach((textarea) => {
            const resize = () => {
                textarea.style.height = 'auto';
                textarea.style.height = `${textarea.scrollHeight}px`;
            };

            textarea.addEventListener('input', resize);
            resize();
        });

        const requiredDocuments = document.getElementById('DOCUMENTOS_OBRIGATORIOS');
        const pendingDocumentsWrapper = document.querySelector('[data-pending-documents-wrapper]');
        const pendingDocuments = document.getElementById('DOCUMENTOS_PENDENTES');

        const togglePendingDocuments = () => {
            const shouldShow = requiredDocuments?.value === 'Não apresentou todos os documentos';
            pendingDocumentsWrapper?.classList.toggle('hidden', !shouldShow);

            if (!shouldShow && pendingDocuments) {
                pendingDocuments.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                    checkbox.checked = false;
                });
            }
        };

        requiredDocuments?.addEventListener('change', togglePendingDocuments);
        togglePendingDocuments();

        const decision = document.getElementById('DECISAO_PARECER');
        const rejectionReasonsWrapper = document.querySelector('[data-rejection-reasons-wrapper]');
        const rejectionReasons = document.getElementById('MOTIVO_INDEFERIMENTO');

        const toggleRejectionReasons = () => {
            const shouldShow = decision?.value === 'INDEFERIDO';
            rejectionReasonsWrapper?.classList.toggle('hidden', !shouldShow);

            if (!shouldShow) {
                rejectionReasons?.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                    checkbox.checked = false;
                });
            }
        };

        decision?.addEventListener('change', () => toggleRejectionReasons());
        toggleRejectionReasons();

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

            button?.addEventListener('click', () => {
                const nextBlock = blocks.find((block) => block.classList.contains('hidden'));

                if (!nextBlock) {
                    buttonWrapper?.classList.add('hidden');
                    return;
                }

                nextBlock.classList.remove('hidden');

                if (!blocks.some((block) => block.classList.contains('hidden'))) {
                    buttonWrapper?.classList.add('hidden');
                }
            });
        });
    });
</script>
@endsection
