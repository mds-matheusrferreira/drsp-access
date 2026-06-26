<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Manifestação - {{ $originalProtocolo }}</title>
    <style>
        @page { margin: 150px 38px 24px; }
        body { color: #000; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.22; }
        *, *:before, *:after { box-sizing: border-box; }

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
            width: 55px;
            height: 55px;
            display: block;
            margin: 0 auto 0px auto;
            padding: 0;
        }
        .header-text {
            font-size: 9px;
            font-weight: normal;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .min-cidadania { font-size: 10px; font-weight: bold; margin-top: 0px; }

        .row { clear: both; margin-bottom: 5px; width: 100%; }
        .row:after { clear: both; content: ''; display: table; }
        .col { float: left; min-height: 13px; }

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
        .w-55 { width: 55%; }
        .w-58 { width: 58%; }
        .w-62 { width: 62%; }
        .w-70 { width: 70%; }
        .w-75 { width: 75%; }
        .w-78 { width: 78%; }
        .w-82 { width: 82%; }

        .label { font-weight: bold; text-transform: uppercase; }
        .nowrap { white-space: nowrap; }
        .section-title { font-weight: bold; margin: 12px 0 6px; text-align: center; text-transform: uppercase; font-size: 10px; }
        .indent { padding-left: 16px; }
        .field-box { border-bottom: 1px solid #9a9a9a; min-height: 13px; padding: 0 3px 1px; }
        .textarea-box { border: 1px solid #b5b5b5; min-height: 80px; padding: 4px; text-align: justify; }

        table { border-collapse: collapse; width: 100%; margin-top: 5px; }
        th { font-size: 8px; font-weight: bold; text-align: center; text-transform: uppercase; border: none; padding-bottom: 3px; }
        td { border: 1px solid #b5b5b5; font-size: 8px; min-height: 12px; padding: 2px 3px; vertical-align: top; }
        .activities td { height: 13px; }

        .closing-block { page-break-inside: avoid; break-inside: avoid; margin-top: 15px; }
        .footer { margin-top: 0; }
        .link { color: #00f; text-decoration: underline; }
        .signatures { clear: both; margin-top: 36px; width: 100%; }
        .signature { float: left; text-align: center; width: 37%; }
        .signature.left { margin-left: 12%; margin-right: 8%; }
        .signature-line { border-top: 1px solid #000; margin-bottom: 3px; }
    </style>
</head>
<body>
@php
    $v = fn (string $field, string $fallback = '') => is_string($processo[$field] ?? null)
        ? str_replace('_x000D_', "\n", $processo[$field])
        : ($processo[$field] ?? $fallback);
    $date = fn (string $field) => preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $v($field))
        ? \Carbon\Carbon::parse($v($field))->format('d/m/Y')
        : $v($field);
    $certificacaoInicio = $date('dt_certificacao_anterior_inicio');
    $certificacaoFim = $date('dt_certificacao_anterior_fim');
    $certificacao = trim($certificacaoInicio && $certificacaoFim ? "$certificacaoInicio a $certificacaoFim" : "$certificacaoInicio $certificacaoFim");
    $certificacao = $certificacao !== '' ? $certificacao : $v('CERTIFICACAO', '');
    $list = fn (string $field) => implode('; ', array_filter(array_map('trim', preg_split('/\r\n|\r|\n|;/', (string) $v($field)))));
    $signatureName = fn (string $field, array $names) => $names[(string) (int) $v($field)] ?? $v($field);
    $signatoryNames = ['1' => 'Leandro de Oliveira Nardi', '3' => 'Edgilson Tavares de Araújo'];
    $cgcebSignature = $signatureName('cgceb_manifestacacao', $signatoryNames);
    $drspSignature = $signatureName('drsp_manifestacao', $signatoryNames);

    $orgaoEncaminhamento = trim($list('orgao_encaminhamento'));
    $bannerManifestacao = $orgaoEncaminhamento !== '' ? 'Solicitação de Manifestação ao ' . $orgaoEncaminhamento : '';

    $areaMap = ['MEC' => 'educação', 'MS' => 'saúde', 'DEPAD' => 'assistência social'];
    $areaOrgao = $areaMap[strtoupper($orgaoEncaminhamento)] ?? strtolower($orgaoEncaminhamento);
    $outrasOfertas = trim((string) $v('outras_ofertas'));
    $areaTexto = $outrasOfertas ?: $areaOrgao;
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
        <div class="col w-10 label">UF:</div>
        <div class="col w-40">{{ $v('uf') }}</div>
    </div>

    <div class="row">
        <div class="col w-25 label nowrap">Última Certificação:</div>
        <div class="col w-75">{{ $certificacao }}</div>
    </div>

    <!-- SOLICITAÇÃO DE MANIFESTAÇÃO -->
    @if ($bannerManifestacao !== '')
        <div class="row" style="margin-top: 8px; margin-bottom: 8px;">
            <div style="border: 1px solid #b5a882; padding: 4px 8px; text-align: center; font-weight: bold; background-color: #f5edd6;">
                {{ $bannerManifestacao }}
            </div>
        </div>
    @endif

    <!-- I) Atividades do Relatório -->
    <div class="row label" style="margin-top: 8px;">I) Atividades do relatório: art. 74 inciso III Decreto 11.791/2023</div>

    <table class="activities">
        <thead>
            <tr>
                <th style="width: 36%; text-align: left; padding-left: 6px; font-weight: normal; text-transform: none;">a) Atividades</th>
                <th style="width: 10%; font-weight: normal;">Nº de atendidos</th>
                <th style="width: 34%; font-weight: normal;">Usuário(s)</th>
                <th style="width: 20%; font-weight: normal;">Qualificação usuário</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($offerRomanNumerals as $roman)
                @php
                    $romanLower = strtolower($roman);
                    $qualificacaoField = "qualificacao_usuario_$romanLower";
                    $oferta = $v("oferta_$romanLower");
                @endphp
                @if (trim((string) $oferta) !== '')
                    <tr>
                        <td>{{ $oferta }}</td>
                        <td>{{ $v("vagas_$romanLower") }}</td>
                        <td>{{ $list("usuario_$romanLower") }}</td>
                        <td>{{ $list($qualificacaoField) }}</td>
                    </tr>
                @else
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="row" style="margin-top: 8px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="white-space: nowrap; font-size: 9px; padding: 0 4px 1px 0; vertical-align: bottom; border: none;">Outra(s) atividade(s) inicialmente identificadas:</td>
                <td style="border: none; border-bottom: 1px solid #9a9a9a; padding: 0 3px 1px; vertical-align: bottom; width: 100%;">{{ $v('outras_ofertas') }}</td>
            </tr>
        </table>
    </div>

    <div class="row" style="margin-top: 4px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="white-space: nowrap; font-size: 9px; padding: 0 4px 1px 0; vertical-align: bottom; border: none;">Outras atividades (saúde e/ou educação):</td>
                <td style="border: none; border-bottom: 1px solid #9a9a9a; padding: 0 3px 1px; vertical-align: bottom; width: 100%;">{{ $list('ofertas_outras_areas') }}</td>
            </tr>
        </table>
    </div>

    <!-- II) Demonstrativo Contábil -->
    <div class="row label" style="margin-top: 8px;">II) Demonstrativo Contábil (DRE e Nota Explicativa): Fi(s)</div>
    <div class="row">
        <div class="field-box">{{ $v('gratuidade_fls') }}</div>
    </div>

    <div class="row" style="margin-top: 4px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="white-space: nowrap; font-size: 9px; padding: 0 4px 1px 0; vertical-align: bottom; border: none;">A preponderância das despesas</td>
                <td style="border: none; border-bottom: 1px solid #9a9a9a; padding: 0 3px 1px; vertical-align: bottom; width: 100%;">{{ $v('outras_ofertas_i') }}</td>
            </tr>
        </table>
    </div>

    <!-- OBSERVAÇÃO -->
    <div class="row" style="margin-top: 8px;">
        <div class="label">Observação:</div>
        <div class="textarea-box" style="min-height: 60px;">{{ $v('obs_pedido_manifestacao') }}</div>
    </div>

    <div class="closing-block">
        <div class="row" style="margin-top: 16px; font-size: 10px;">
            Considerando que as ações também são desenvolvidas na(s) área(s) de
            <span style="text-decoration: underline; padding: 0 4px;">{{ $areaTexto }}</span>
            solicita-se ao(s) ministério(s) mencionado(s) acima manifestação para comprovação dos requisitos exigidos em sua(s) área(s),
            se for o caso, conforme determina o art. 7º §3º, inciso I do Decreto nº 11.791/2023.
        </div>

        <div class="footer row" style="margin-top: 16px;">
            <div class="col w-55 link">https://www.gov.br/cidadania/pt-br/acoes-e-programas/assistencia-social/entidades-de-assistencia-social/certificacao-de-entidades-beneficentes-de-assistencia-social-cebas</div>
            <div class="col w-20" style="text-align: right; padding-right: 5px;">Brasília-DF,</div>
            <div class="col w-25">{{ now()->format('d/m/Y') }}</div>
        </div>

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
