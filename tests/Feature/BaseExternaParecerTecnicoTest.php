<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BaseExternaParecerTecnicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_parecer_routes_to_login(): void
    {
        $this->get('/base-externa/analise-processo/parecer-tecnico?protocolo=PARECER-001')->assertRedirect(route('login'));
        $this->put('/base-externa/analise-processo/parecer-tecnico', [
            'ORIGINAL_PROTOCOLO' => 'PARECER-001',
        ])->assertRedirect(route('login'));
        $this->get('/base-externa/analise-processo/parecer-tecnico/pdf?protocolo=PARECER-001')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_parecer_page(): void
    {
        $this->insertAccessProcess([
            'PROTOCOLO' => 'PARECER-VIEW-001',
            'ENTIDADE' => 'FUNDAÇÃO TESTE',
            'DOCUMENTOS_OBRIGATORIOS' => 'Não foram analisados os documentos',
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get('/base-externa/analise-processo/parecer-tecnico?protocolo=PARECER-VIEW-001');

        $response->assertOk();
        $response->assertSee('Parecer Técnico');
        $response->assertSee('Informações do Processo');
        $response->assertSee('FUNDAÇÃO TESTE');
        $response->assertSee('Documentos Obrigatórios');
        $response->assertSee('Gerar PDF');
    }

    public function test_analysis_action_links_to_parecer_page(): void
    {
        $this->insertAccessProcess(['PROTOCOLO' => 'PARECER-LINK-001']);

        $response = $this->actingAs(User::factory()->create())
            ->get('/base-externa/analise-processo?search=PARECER-LINK-001');

        $response->assertOk();
        $response->assertSee('Parecer Técnico');
        $response->assertSee(route('base-externa.analise-processo.parecer.edit', ['protocolo' => 'PARECER-LINK-001']), false);
    }

    public function test_parecer_update_saves_only_parecer_fields(): void
    {
        $this->insertAccessProcess([
            'PROTOCOLO' => 'PARECER-UPDATE-001',
            'DOCUMENTOS_OBRIGATORIOS' => 'Valor antigo',
            'DECISAO_PARECER' => 'ANTIGO',
            'VAGAS_I' => 10,
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->put('/base-externa/analise-processo/parecer-tecnico', [
                'ORIGINAL_PROTOCOLO' => 'PARECER-UPDATE-001',
                'PROTOCOLO' => 'NAO-DEVE-ALTERAR',
                'DOCUMENTOS_OBRIGATORIOS' => 'Novo documento analisado',
                'DECISAO_PARECER' => 'DEFERIDO',
                'JUSTIFICATIVA_INDEFERIMENTO' => 'Motivo atualizado',
                'VAGAS_I' => '25',
            ]);

        $response->assertRedirect(route('base-externa.analise-processo.parecer.edit', ['protocolo' => 'PARECER-UPDATE-001']));
        $response->assertSessionHas('success', 'Parecer técnico atualizado com sucesso.');

        $this->assertDatabaseHas('access', [
            'PROTOCOLO' => 'PARECER-UPDATE-001',
            'DOCUMENTOS_OBRIGATORIOS' => 'Novo documento analisado',
            'DECISAO_PARECER' => 'DEFERIDO',
            'JUSTIFICATIVA_INDEFERIMENTO' => 'Motivo atualizado',
            'VAGAS_I' => 25,
        ]);
        $this->assertDatabaseMissing('access', ['PROTOCOLO' => 'NAO-DEVE-ALTERAR']);
    }

    public function test_parecer_update_is_blocked_when_protocol_is_duplicated(): void
    {
        $this->insertAccessProcess(['PROTOCOLO' => 'PARECER-DUPLICADO-001', 'DOCUMENTOS_OBRIGATORIOS' => 'Primeiro']);
        $this->insertAccessProcess(['PROTOCOLO' => 'PARECER-DUPLICADO-001', 'DOCUMENTOS_OBRIGATORIOS' => 'Segundo']);

        $response = $this->actingAs(User::factory()->create())
            ->put('/base-externa/analise-processo/parecer-tecnico', [
                'ORIGINAL_PROTOCOLO' => 'PARECER-DUPLICADO-001',
                'DOCUMENTOS_OBRIGATORIOS' => 'Não deve salvar',
            ]);

        $response->assertRedirect(route('base-externa.analise-processo.index', ['search' => 'PARECER-DUPLICADO-001']));
        $response->assertSessionHas('error', 'Parecer técnico bloqueado: este protocolo aparece em mais de um registro.');
        $this->assertDatabaseMissing('access', ['DOCUMENTOS_OBRIGATORIOS' => 'Não deve salvar']);
    }

    public function test_pdf_route_returns_pdf_download(): void
    {
        $this->insertAccessProcess([
            'PROTOCOLO' => 'PARECER-PDF-001',
            'ENTIDADE' => 'ENTIDADE PDF',
            'DECISAO_PARECER' => 'DEFERIDO',
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get('/base-externa/analise-processo/parecer-tecnico/pdf?protocolo=PARECER-PDF-001');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename=parecer-tecnico-parecer-pdf-001.pdf');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function insertAccessProcess(array $attributes): void
    {
        $data = array_merge([
            'TIPO_PROCESSO' => 'Supervisão Extraordinária',
            'PROTOCOLO' => 'PARECER-TESTE',
            'PROTOCOLO_SEI' => 'SEI-PARECER-TESTE',
            'CNPJ' => '17233032000130',
            'ENTIDADE' => 'FUNDAÇÃO OBRAS SOCIAIS NOSSA SENHORA DA BOA VIAGEM',
            'UF' => 'MG',
            'MUNICIPIO' => 'BELO HORIZONTE',
            'ORGAO_ORIGEM' => 'MS',
            'DT_PROTOCOLO' => '2018-04-04',
            'DT_RECEBIMENTO_MDS' => '2018-04-04',
            'MOTIVO_RECEBIMENTO' => 'Manifestação',
            'FASE_PROCESSO' => 'ANÁLISE TÉCNICA',
            'SITUAÇÃO_CNEAS' => 'Regular',
            'DOCUMENTOS_OBRIGATORIOS' => 'Não se aplica',
            'COMPATIBILIDADE_ESTATUTO_LOAS' => 'Não está compatível com a legislação',
            'DESTINO_PATRIMONIO_CASO_DISSOLUCAO' => 'Não apresentou o documento',
            'OFERTA_I' => 'fortalecimento de mov. sociais e org. de usuários',
            'VAGAS_I' => 2005,
            'USUARIO_I' => 'comunidade; crianças; famílias',
            'QUALIFICACAO_USUARIO_I' => 'Não se aplica',
            'DECISAO_PARECER' => 'INDEFERIDO',
            'JUSTIFICATIVA_INDEFERIMENTO' => 'Exposição inicial',
        ], $attributes);

        DB::table('access')->insert(array_intersect_key($data, array_flip(Schema::getColumnListing('access'))));
    }
}
