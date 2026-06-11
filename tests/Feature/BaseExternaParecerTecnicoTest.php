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
            'original_protocolo' => 'PARECER-001',
        ])->assertRedirect(route('login'));
        $this->get('/base-externa/analise-processo/parecer-tecnico/pdf?protocolo=PARECER-001')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_parecer_page(): void
    {
        $this->insertAccessProcess([
            'protocolo' => 'PARECER-VIEW-001',
            'entidade' => 'FUNDAÇÃO TESTE',
            'documentos_obrigatorios' => 'Não foram analisados os documentos',
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
        $this->insertAccessProcess(['protocolo' => 'PARECER-LINK-001']);

        $response = $this->actingAs(User::factory()->create())
            ->get('/base-externa/analise-processo?search=PARECER-LINK-001');

        $response->assertOk();
        $response->assertSee('Parecer Técnico');
        $response->assertSee(route('base-externa.analise-processo.parecer.edit', ['protocolo' => 'PARECER-LINK-001']), false);
    }

    public function test_parecer_update_saves_only_parecer_fields(): void
    {
        $this->insertAccessProcess([
            'protocolo' => 'PARECER-UPDATE-001',
            'documentos_obrigatorios' => 'Valor antigo',
            'decisao_parecer' => 'ANTIGO',
            'vagas_i' => 10,
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->put('/base-externa/analise-processo/parecer-tecnico', [
                'original_protocolo' => 'PARECER-UPDATE-001',
                'protocolo' => 'NAO-DEVE-ALTERAR',
                'documentos_obrigatorios' => 'Novo documento analisado',
                'decisao_parecer' => 'DEFERIDO',
                'justificativa_indeferimento' => 'Motivo atualizado',
                'vagas_i' => '25',
            ]);

        $response->assertRedirect(route('base-externa.analise-processo.parecer.edit', ['protocolo' => 'PARECER-UPDATE-001']));
        $response->assertSessionHas('success', 'Parecer técnico atualizado com sucesso.');

        $this->assertDatabaseHas('access', [
            'protocolo' => 'PARECER-UPDATE-001',
            'documentos_obrigatorios' => 'Novo documento analisado',
            'decisao_parecer' => 'DEFERIDO',
            'justificativa_indeferimento' => 'Motivo atualizado',
            'vagas_i' => 25,
        ]);
        $this->assertDatabaseMissing('access', ['protocolo' => 'NAO-DEVE-ALTERAR']);
    }

    public function test_parecer_update_is_blocked_when_protocol_is_duplicated(): void
    {
        $this->insertAccessProcess(['protocolo' => 'PARECER-DUPLICADO-001', 'documentos_obrigatorios' => 'Primeiro']);
        $this->insertAccessProcess(['protocolo' => 'PARECER-DUPLICADO-001', 'documentos_obrigatorios' => 'Segundo']);

        $response = $this->actingAs(User::factory()->create())
            ->put('/base-externa/analise-processo/parecer-tecnico', [
                'original_protocolo' => 'PARECER-DUPLICADO-001',
                'documentos_obrigatorios' => 'Não deve salvar',
            ]);

        $response->assertRedirect(route('base-externa.analise-processo.index', ['search' => 'PARECER-DUPLICADO-001']));
        $response->assertSessionHas('error', 'Parecer técnico bloqueado: este protocolo aparece em mais de um registro.');
        $this->assertDatabaseMissing('access', ['documentos_obrigatorios' => 'Não deve salvar']);
    }

    public function test_pdf_route_returns_pdf_download(): void
    {
        $this->insertAccessProcess([
            'protocolo' => 'PARECER-PDF-001',
            'entidade' => 'entidade PDF',
            'decisao_parecer' => 'DEFERIDO',
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
            'tipo_processo' => 'Supervisão Extraordinária',
            'protocolo' => 'PARECER-TESTE',
            'protocolo_sei' => 'SEI-PARECER-TESTE',
            'cnpj' => '17233032000130',
            'entidade' => 'FUNDAÇÃO OBRAS SOCIAIS NOSSA SENHORA DA BOA VIAGEM',
            'uf' => 'MG',
            'municipio' => 'BELO HORIZONTE',
            'orgao_origem' => 'MS',
            'dt_protocolo' => '2018-04-04',
            'dt_recebimento_mds' => '2018-04-04',
            'motivo_recebimento' => 'Manifestação',
            'fase_processo' => 'ANÁLISE TÉCNICA',
            'situacao_cneas' => 'Regular',
            'documentos_obrigatorios' => 'Não se aplica',
            'compatibilidade_estatuto_loas' => 'Não está compatível com a legislação',
            'destino_patrimonio_caso_dissolucao' => 'Não apresentou o documento',
            'oferta_i' => 'fortalecimento de mov. sociais e org. de usuários',
            'vagas_i' => 2005,
            'usuario_i' => 'comunidade; crianças; famílias',
            'qualificacao_usuario_i' => 'Não se aplica',
            'decisao_parecer' => 'INDEFERIDO',
            'justificativa_indeferimento' => 'Exposição inicial',
        ], $attributes);

        DB::table('access')->insert(array_intersect_key($data, array_flip(Schema::getColumnListing('access'))));
    }
}
