<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Parecer Técnico - {{ $originalProtocolo }}</title>
    <style>
        @page { margin: 150px 38px 24px; }
        body { color: #000; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.22; }
        *, *:before, *:after { box-sizing: border-box; }

        /* Configuração do Cabeçalho Oficial */
        .header-container {
        position: fixed;
        top: -130px;
        left: 0;
        right: 0;
        text-align: center;
        margin-bottom: 0;
        width: 100%;
        }
        .header-container img { 
        width: 55px;          /* Mudamos para width fixa (em vez de height) para encorpar o SVG */
        height: 55px;         /* Força a proporção quadrada típica do brasão */
        display: block; 
        margin: 0 auto 0px auto; 
        padding: 0;
        }
        .header-text { 
        font-size: 9px; 
        font-weight: normal; 
        text-transform: uppercase; 
        margin-top: 2px;      /* Distância mínima controlada milimetricamente */
        }       
        .min-cidadania { font-size: 10px; font-weight: bold; margin-top: 0px; } /* Apenas o Ministério da Cidadania em negrito */

        /* Estrutura de Grid por pontos flutuantes (Compatível com Dompdf) */
        .row { clear: both; margin-bottom: 5px; width: 100%; }
        .row:after { clear: both; content: ''; display: table; }
        .col { float: left; min-height: 13px; }
        
        /* Definições de Largura das Colunas */
        .w-10 { width: 10%; }
        .w-15 { width: 15%; }
        .w-18 { width: 18%; }
        .w-20 { width: 20%; }
        .w-22 { width: 22%; }
        .w-25 { width: 25%; }
        .w-32 { width: 32%; }
        .w-35 { width: 35%; }
        .w-38 { width: 38%; }
        .w-40 { width: 40%; }
        .w-42 { width: 42%; }
        .w-45 { width: 45%; }
        .w-50 { width: 50%; }
        .w-58 { width: 58%; }
        .w-62 { width: 62%; }
        .w-75 { width: 75%; }
        .w-78 { width: 78%; }
        .w-82 { width: 82%; }
        
        /* Estilização de Rótulos e Seções */
        .label { font-weight: bold; text-transform: uppercase; }
        .nowrap { white-space: nowrap; }
        .section-title { font-weight: bold; margin: 12px 0 6px; text-align: center; text-transform: uppercase; font-size: 10px; }
        .indent { padding-left: 16px; }
        .field-box { border-bottom: 1px solid #9a9a9a; min-height: 13px; padding: 0 3px 1px; }
        .textarea-box { border: 1px solid #b5b5b5; min-height: 38px; padding: 4px; }
        .exposition-label { text-align: right; padding-right: 8px; width: 17%; }
        .exposition-box { width: 80%; }
        
        /* Tabela de Atividades */
        table { border-collapse: collapse; width: 100%; margin-top: 5px; }
        th { font-size: 8px; font-weight: bold; text-align: center; text-transform: uppercase; border: none; padding-bottom: 3px; }
        td { border: 1px solid #b5b5b5; font-size: 8px; min-height: 12px; padding: 2px 3px; vertical-align: top; }
        .activities td { height: 13px; }
        
        /* Rodapé e Assinaturas */
        .closing-block { page-break-inside: avoid; break-inside: avoid; margin-top: 15px; }
        .footer { margin-top: 0; }
        .link { color: #00f; text-decoration: underline; }
        .signatures { clear: both; margin-top: 36px; width: 100%; }
        .signature { float: left; text-align: center; width: 37%; }
        .signature.middle { margin: 0; }
        .signature.left { margin-left: 12%; margin-right: 8%; }
        .signature-line { border-top: 1px solid #000; margin-bottom: 3px; }
        .decision-text { break-inside: avoid; page-break-inside: avoid; text-align: left; }
        .analysis-block { break-inside: avoid; page-break-inside: avoid; }
        .analysis-title { font-weight: bold; margin: 16px 0 2px; }
    </style>
</head>
<body>
@php
    $v = fn (string $field, string $fallback = '') => $processo[$field] ?? $fallback;
    $date = fn (string $field) => preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $v($field))
        ? \Carbon\Carbon::parse($v($field))->format('d/m/Y')
        : $v($field);
    $certificacao = trim(($date('dt_certificacao_anterior_inicio') ?: '').' '.($date('dt_certificacao_anterior_fim') ?: ''));
    $certificacao = $certificacao !== '' ? $certificacao : $v('CERTIFICACAO', '');
    $signatureName = fn (string $field, array $names) => $names[(string) (int) $v($field)] ?? $v($field);
    $manifestacaoOutroMinisterioOptions = [
        '1' => 'MEC: não atua para fins de CEBAS; e parecer favorável do MS',
        '2' => 'MS: não atua para fins de CEBAS; e parecer desfavorável do MEC',
        '3' => 'Não atua para fins de CEBAS',
        '4' => 'Não se aplica',
        '5' => 'Parecer desfavorável',
        '6' => 'Parecer favorável',
        '7' => 'Parecer favorável no MEC e desfavorável no MS',
        '8' => 'Parecer favorável no MS e desfavorável no MEC',
        '9' => 'Pareceres desfavoráveis em ambos os ministérios',
        '10' => 'Pareceres favoráveis em ambos os ministérios',
    ];
    $manifestacaoOutroMinisterio = $manifestacaoOutroMinisterioOptions[(string) $v('manifestacao_outro_ministerio')] ?? $v('manifestacao_outro_ministerio');
    $list = fn (string $field) => implode('; ', array_filter(array_map('trim', preg_split('/\r\n|\r|\n|;/', (string) $v($field)))));
    $pendingDocuments = $list('documentos_pendentes');
    $cgcebSignature = $signatureName('cgceb_parecer', ['1' => 'Leandro de Oliveira Nardi']);
    $drspSignature = $signatureName('drsp_parecer', ['3' => 'Edgilson Tavares de Araújo']);
    $isLei187 = $v('legislacao_parecer') === 'Lei Complementar 187/2021';
    $documentosObrigatoriosLabel = $isLei187 ? 'Art. 31º I, II, III, IV - LC 187/2021' : 'Art. 3º, II, III, IV, VIII e Art. 39, I e II do Decreto 8.242/2014';
    $compatibilidadeLoasLabel = $isLei187 ? 'art. 73º, I Dec.111791/2023' : 'art. 34, I, Dec. 7.237/10 ou art. 39, I, Dec. 8.242/14';
    $destinoPatrimonioLabel = $isLei187 ? 'art. 3º, VIII, Lei 187/2021' : 'art. 3º, II, Lei 12.101/09';
    $gratuidadeLabel = $isLei187 ? 'Art. 3º, IV, Lei 187/2021 - Art.5º I, C, Decreto 11.791/2023.' : 'Art. 18 da Lei 12.101/09 e Art. 57 do Decreto 8.242/14';
    $cpuLabel = $isLei187 ? 'ART. 31º, §§ 1º, 2º -Lei 187/2021:' : 'Art. 18 da Lei 12.101/2009';
    $certificacaoLegislacao = $isLei187 ? 'Lei Complementar nº 187/2021 e Decreto nº 11.791/2023' : 'Lei nº 12.101/2009 e Decreto nº 8.242/2014';
    $deferidoIntroText = $isLei187
        ? 'A partir da documentação apresentada, a entidade demonstrou atuar na área da assistência social em conformidade com a Lei Orgânica da Assistência Social (Lei nº 8.742/93) e a legislação pertinente à certificação.'
        : 'A partir da documentação apresentada, a entidade demonstrou atuar na área da assistência social em conformidade com a Lei Orgânica da Assistência Social (Lei nº 8.742/93) e a legislação pertinente à certificação (Lei nº 12.101/2009 e Decreto nº 8.242/2014).';
    $supervisaoText = $isLei187
        ? 'Insta informar, por fim, que a entidade poderá ser objeto de supervisão a qualquer tempo, por este Ministério, conforme determina o Decreto nº 11.791/2023 e, caso a entidade descumpra quaisquer requisitos estabelecidos na Lei nº 187/2021, poderá ter a sua Certificação cancelada.'
        : 'Insta informar, por fim, que a entidade poderá ser objeto de supervisão a qualquer tempo por este Ministério e, caso a entidade descumpra quaisquer requisitos estabelecidos na legislação pertinente à certificação (Lei nº 12.101/2009 e Decreto nº 8.242/2014), poderá ter a sua Certificação cancelada.';
@endphp

    <!-- Cabeçalho Oficial do Ministério -->
    <div class="header-container">
        <img src="{{ public_path('images/brasao.png') }}" alt="Brasão da República">
        <div class="header-text">
            <div class="min-cidadania">Ministério do Desenvolvimento e Assistência Social, Família e Combate à Fome</div>
            <div>Secretaria Nacional de Assistência Social</div>
            <div>Departamento da Rede Socioassistencial Privada do SUAS</div>
            <div>Coordenação Geral de Certificação das Entidades Beneficentes de Assistência Social</div>
        </div>
    </div>

    <!-- Informações do Processo -->
    <div class="row">
        <div class="col w-58">&nbsp;</div>
        <div class="col w-20 label">Situação CNEAS:</div>
        <div class="col w-22">{{ $v('situacao_cneas', $v('situacao_cneas')) }}</div>
    </div>

    <div class="row">
        <div class="col w-18 label">Protocolo:</div>
        <div class="col w-40">{{ $v('protocolo') }}</div>
        <div class="col w-20 label">Tipo de Processo:</div>
        <div class="col w-22">{{ $v('tipo_processo') }}</div>
    </div>

    <div class="row">
        <div class="col w-18 label">C.N.P.J:</div>
        <div class="col w-40">{{ $v('cnpj') }}</div>
        <div class="col w-20 label">Data de Protocolo:</div>
        <div class="col w-22">{{ $date('dt_protocolo') }}</div>
    </div>

    <div class="row">
        <div class="col w-18 label">Entidade:</div>
        <div class="col w-82">{{ $v('entidade') }}</div>
    </div>

    <div class="row">
        <div class="col w-18 label">Município:</div>
        <div class="col w-32">{{ $v('municipio') }}</div>
        <div class="col w-10 label">uf:</div>
        <div class="col w-40">{{ $v('uf') }}</div>
    </div>

    <div class="row">
        <div class="col w-25 label nowrap">Última Certificação:</div>
        <div class="col w-75">{{ $certificacao }}</div>
    </div>

    <!-- I) Documentos Obrigatórios -->
    <div class="section-title">Análise Técnica</div>

    <div class="row">
        <div class="label">I) DOCUMENTOS OBRIGATÓRIOS: {{ $documentosObrigatoriosLabel }}</div>
        <div class="field-box">{{ $v('documentos_obrigatorios') }}</div>
    </div>
    <div class="row indent">
        <div class="col w-25 label">(Documentos pendentes)</div>
        <div class="col field-box" style="width: 72%;">{{ $pendingDocuments }}</div>
    </div>

    <!-- II) Finalidades Objetivos -->
    <div class="row">
        <div class="label">II) Finalidades ou objetivos do estatuto social:</div>
    </div>
    <div class="row">
        <div class="col w-50">
            <div class="indent">
                <div>a) Compatibilidade do estatuto com LOAS: {{ $compatibilidadeLoasLabel }}</div>
                <div class="field-box">{{ $v('compatibilidade_estatuto_loas') }}</div>
            </div>
        </div>
        <div class="col w-50">
            <div class="indent">
                <div>b) Destino do patrimônio em caso de dissolução: {{ $destinoPatrimonioLabel }}</div>
                <div class="field-box">{{ $v('destino_patrimonio_caso_dissolucao') }}</div>
            </div>
        </div>
    </div>

    <!-- III) Tabela de Atividades do Relatório -->
    <div class="row label" style="margin-top: 8px;">III) Atividades do relatório:</div>
    <table class="activities">
        <thead>
            <tr>
                <th style="width: 36%; text-align: left; padding-left: 15px; font-weight: normal; text-transform: none;">a) Atividades</th>
                <th style="width: 9%; font-weight: normal;">Nº de atendidos</th>
                <th style="width: 35%; font-weight: normal;">Usuários(s)</th>
                <th style="width: 20%; font-weight: normal;">Qualificação usuário</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($offerRomanNumerals as $roman)
                @php
                    $romanLower = strtolower($roman);
                    $qualificacaoField = "qualificacao_usuario_$romanLower";
                @endphp
                <tr>
                    <td>{{ $v("oferta_$romanLower") }}</td>
                    <td>{{ $v("vagas_$romanLower") }}</td>
                    <td>{{ $list("usuario_$romanLower") }}</td>
                    <td>{{ $list($qualificacaoField) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row" style="margin-top: 8px;">
        <div style="padding-left: 15px; font-size: 8px; text-transform: none;">b) Atividades de outras áreas não certificáveis:</div>
        <div class="field-box">{{ $v('outras_atividades') }}</div>
    </div>

    <!-- IV) Gratuidade -->
    <div class="row" style="margin-top: 8px;">
        <div class="label">IV) Gratuidade (a partir dos documentos apresentados): {{ $gratuidadeLabel }}</div>
        <div class="field-box">{{ $v('gratuidade_parecer') }}</div>
    </div>

    <!-- V) Manifestações de Outros Órgãos -->
    <div class="row" style="margin-top: 8px;">
        <div class="col w-32 label">V) Manifestação de outro órgão:</div>
        <div class="col" style="width: 68%;">
            <div class="field-box">{{ $list('orgao_encaminhamento') }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col w-32">Número(s):</div>
        <div class="col" style="width: 68%;">
            <div class="field-box">{{ $v('nota_tecnica_outro_orgao') }}</div>
        </div>
    </div>
    @if (trim((string) $manifestacaoOutroMinisterio) !== '')
        <div class="row indent">
            <div class="field-box">{{ $manifestacaoOutroMinisterio }}</div>
        </div>
    @endif
    <div class="row">
        <div class="col w-32">Outras atividades (saúde e/ou educação):</div>
        <div class="col" style="width: 68%;">
            <div class="field-box">{{ $v('ofertas_outras_areas') }}</div>
        </div>
    </div>

    <!-- VI) Artigo 18 -->
    <div class="row" style="margin-top: 8px;">
        <span class="label">VI) {{ $cpuLabel }}</span>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <span class="label">Continuidade</span> {{ $v('continuidade') }}
        &nbsp;&nbsp;&nbsp;&nbsp;
        <span class="label">Planejamento</span> {{ $v('planejamento') }}
        &nbsp;&nbsp;&nbsp;&nbsp;
        <span class="label">Universalidade</span> {{ $v('universalidade') }}
    </div>

    <!-- VII) Conclusão do Parecer -->
    <div class="row" style="margin-top: 8px;">
        <div class="col w-25 label">VII) Conclusão do parecer:</div>
        <div class="col w-75">
            <div class="field-box">{{ $v('decisao_parecer') }}</div>
        </div>
    </div>
    @if ($v('decisao_parecer') === 'INDEFERIDO')
        <div class="row">
            <div class="col exposition-label">Exposição de<br>motivos:</div>
            <div class="col textarea-box exposition-box">{{ $list('motivo_indeferimento') ?: 'Não se aplica' }}</div>
        </div>
    @endif

    @if ($v('decisao_parecer') === 'DEFERIDO')
        <div class="row decision-text" style="margin-top: 18px;">
            {{ $deferidoIntroText }}
        </div>
        <div class="row decision-text">
            O conjunto de atividades apresentadas no item III.a do presente parecer expressam as ofertas socioassistenciais em conformidade com a Tipificação Nacional dos Serviços Socioassistenciais e as Resoluções CNAS nº 27, 33 e 34/2011. As atividades descritas no item III.b não se caracterizam como ofertas socioassistenciais. No entanto, não impedem o deferimento do pedido de certificação, pois não correspondem à atuação preponderante da entidade.
        </div>

        <div class="row decision-text" style="margin-top: 18px;">
            O Cadastro Nacional de Entidades de Assistência Social (CNEAS) é um instrumento de reconhecimento e de monitoramento das ofertas socioassistenciais prestadas por organizações da sociedade civil. Seu preenchimento é responsabilidade dos órgãos gestores municipais. Para consultar a situação da sua entidade, acesse http://aplicacoes.mds.gov.br/cneas/consultacneas. Caso não a encontre, procure pelo órgão gestor da assistência social e solicite o cadastramento no CNEAS.
        </div>

        <div class="row decision-text" style="margin-top: 18px;">
            {{ $supervisaoText }}
        </div>
    @elseif ($v('decisao_parecer') === 'INDEFERIDO')
        <div class="analysis-block">
            <div class="analysis-title">Análise:</div>
            <div class="textarea-box decision-text">{{ trim((string) $v('justificativa_indeferimento')) !== '' ? $v('justificativa_indeferimento') : 'Não se aplica' }}</div>
        </div>

        <div class="row decision-text" style="margin-top: 18px;">
            A análise das atividades descritas no referido processo foi fundamentada na Lei Orgânica da Assistência Social (Lei nº 8.742/1993) e na legislação pertinente à certificação ({{ $certificacaoLegislacao }}), bem como na Tipificação Nacional dos Serviços Socioassistenciais (Resolução CNAS nº 109/2009) e nas Resoluções CNAS nº 27, 33 e 34/2011.
        </div>

        @if (! $isLei187)
            <div class="row decision-text" style="margin-top: 10px;">
                A entidade poderá recorrer da decisão em até trinta (30) dias a partir da publicação no Diário Oficial da União (D.O.U.). Ressalta-se que o recurso não tem efeito suspensivo, ou seja, a partir da publicação do indeferimento a entidade perde o direito à isenção prevista na legislação pertinente à certificação ({{ $certificacaoLegislacao }}). Caso o fundamento do indeferimento seja a não apresentação de documentação obrigatória, a entidade poderá apresentar em sede de recurso a documentação faltante indicada acima.
            </div>
        @endif

        <div class="row decision-text" style="margin-top: 10px;">
            “O Cadastro Nacional de Entidades de Assistência Social (CNEAS) é um instrumento de reconhecimento e de monitoramento das ofertas socioassistenciais prestadas por organizações da sociedade civil. Seu preenchimento é responsabilidade dos órgãos gestores municipais. Para consultar a situação da sua entidade, acesse http://aplicacoes.mds.gov.br/cneas/consultacneas. Caso não a encontre, procure pelo órgão gestor da assistência social e solicite o cadastramento no CNEAS.”
        </div>
    @endif

    <div class="closing-block">
        <!-- Rodapé de Localidade -->
        <div class="footer row">
            <div class="col w-45 link">https://www.gov.br/cidadania/pt-br/acoes-e-programas/assistencia-social/entidades-de-assistencia-social/certificacao-de-entidades-beneficentes-de-assistencia-social-cebas</div>
            <div class="col w-30" style="text-align: right; padding-right: 5px;">Brasília, DF</div>
            <div class="col w-25">{{ now()->format('d/m/Y') }}</div>
        </div>

        <!-- Bloco de Assinaturas Colunadas -->
        <div class="signatures">
            <div class="signature left">
                <div class="signature-line"></div>
                {{ $cgcebSignature }}<br>CGCEB/DRSP/SNAS/MDS<br>Coordenador Geral
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                {{ $drspSignature }}<br>DRSP/SNAS/MDS<br>Diretor
            </div>
        </div>
    </div>
</body>
</html>