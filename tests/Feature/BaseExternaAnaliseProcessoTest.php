<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BaseExternaAnaliseProcessoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_analysis_page_to_login(): void
    {
        $this->get('/base-externa/analise-processo')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_analysis_page(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/base-externa/analise-processo');

        $response->assertOk();
        $response->assertSee('Análise de Processo');
        $response->assertSee('Pesquisar processo externo');
    }

    public function test_analysis_search_finds_process_by_main_fields(): void
    {
        $this->insertAccessProcess([
            'protocolo' => 'ANALISE-001',
            'protocolo_sei' => 'SEI-001',
            'cnpj' => '12345678000190',
            'municipio' => 'BRASILIA',
            'uf' => 'DF',
        ]);

        $response = $this->actingAs(User::factory()->create())->get('/base-externa/analise-processo?search=SEI-001');

        $response->assertOk();
        $response->assertSee('ANALISE-001');
        $response->assertSee('SEI-001');
        $response->assertSee('Editar banco');
        $response->assertSee('Parecer Técnico');
    }

    public function test_authenticated_user_can_edit_process(): void
    {
        $this->insertAccessProcess([
            'protocolo' => 'ANALISE-EDIT-001',
            'cnpj' => '12345678000190',
            'municipio' => 'BRASILIA',
            'uf' => 'DF',
            'fase_processo' => 'AGUARDANDO ANÁLISE',
        ]);

        $response = $this->actingAs(User::factory()->create())->put('/base-externa/analise-processo', [
            'original_protocolo' => 'ANALISE-EDIT-001',
            'protocolo' => 'ANALISE-EDIT-001',
            'motivo_recebimento' => 'Competência para julgamento',
            'cnpj' => '12345678000190',
            'municipio' => 'BRASILIA',
            'uf' => 'DF',
            'fase_processo' => 'ANÁLISE TÉCNICA',
        ]);

        $response->assertRedirect(route('base-externa.analise-processo.edit', ['protocolo' => 'ANALISE-EDIT-001']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('access', [
            'protocolo' => 'ANALISE-EDIT-001',
            'motivo_recebimento' => 'Competência para julgamento',
            'fase_processo' => 'ANÁLISE TÉCNICA',
        ]);
    }

    public function test_edit_is_blocked_when_protocol_is_duplicated(): void
    {
        $this->insertAccessProcess(['protocolo' => 'DUPLICADO-001', 'protocolo_sei' => 'Primeira']);
        $this->insertAccessProcess(['protocolo' => 'DUPLICADO-001', 'protocolo_sei' => 'Segunda']);

        $response = $this->actingAs(User::factory()->create())->get('/base-externa/analise-processo/editar?protocolo=DUPLICADO-001');

        $response->assertRedirect(route('base-externa.analise-processo.index', ['search' => 'DUPLICADO-001']));
        $response->assertSessionHas('error', 'Edição bloqueada: este protocolo aparece em mais de um registro.');
    }

    public function test_update_is_blocked_when_original_protocol_is_not_unique(): void
    {
        $this->insertAccessProcess(['protocolo' => 'DUPLICADO-002', 'protocolo_sei' => 'Primeira']);
        $this->insertAccessProcess(['protocolo' => 'DUPLICADO-002', 'protocolo_sei' => 'Segunda']);

        $response = $this->actingAs(User::factory()->create())->put('/base-externa/analise-processo', [
            'original_protocolo' => 'DUPLICADO-002',
            'protocolo' => 'DUPLICADO-002',
            'motivo_recebimento' => 'Não deve atualizar',
        ]);

        $response->assertRedirect(route('base-externa.analise-processo.index', ['search' => 'DUPLICADO-002']));
        $response->assertSessionHas('error', 'Edição bloqueada: este protocolo aparece em mais de um registro.');

        $this->assertDatabaseMissing('access', [
            'protocolo' => 'DUPLICADO-002',
            'motivo_recebimento' => 'Não deve atualizar',
        ]);
    }

    public function test_update_is_blocked_when_new_protocol_is_empty(): void
    {
        $this->insertAccessProcess(['protocolo' => 'protocolo-VAZIO-001']);

        $response = $this->actingAs(User::factory()->create())->put('/base-externa/analise-processo', [
            'original_protocolo' => 'protocolo-VAZIO-001',
            'protocolo' => '',
            'motivo_recebimento' => 'Não deve atualizar',
        ]);

        $response->assertRedirect(route('base-externa.analise-processo.edit', ['protocolo' => 'protocolo-VAZIO-001']));
        $response->assertSessionHas('error', 'Edição bloqueada: o protocolo não pode ficar vazio.');

        $this->assertDatabaseHas('access', [
            'protocolo' => 'protocolo-VAZIO-001',
            'motivo_recebimento' => 'Manifestação',
        ]);
    }

    public function test_update_is_blocked_when_new_protocol_already_exists(): void
    {
        $this->insertAccessProcess(['protocolo' => 'protocolo-ORIGINAL-001']);
        $this->insertAccessProcess(['protocolo' => 'protocolo-EXISTENTE-001']);

        $response = $this->actingAs(User::factory()->create())->put('/base-externa/analise-processo', [
            'original_protocolo' => 'protocolo-ORIGINAL-001',
            'protocolo' => 'protocolo-EXISTENTE-001',
            'motivo_recebimento' => 'Não deve atualizar',
        ]);

        $response->assertRedirect(route('base-externa.analise-processo.edit', ['protocolo' => 'protocolo-ORIGINAL-001']));
        $response->assertSessionHas('error', 'Edição bloqueada: o novo protocolo informado já existe em outro registro.');

        $this->assertDatabaseHas('access', [
            'protocolo' => 'protocolo-ORIGINAL-001',
            'motivo_recebimento' => 'Manifestação',
        ]);
        $this->assertDatabaseHas('access', [
            'protocolo' => 'protocolo-EXISTENTE-001',
        ]);
        $this->assertDatabaseMissing('access', [
            'protocolo' => 'protocolo-EXISTENTE-001',
            'motivo_recebimento' => 'Não deve atualizar',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function insertAccessProcess(array $attributes): void
    {
        $data = array_merge([
            'tipo_processo' => 'Concessão',
            'protocolo' => 'protocolo-TESTE',
            'protocolo_sei' => 'SEI-TESTE',
            'cnpj' => '12345678000190',
            'uf' => 'DF',
            'municipio' => 'BRASILIA',
            'orgao_origem' => 'MS',
            'dt_protocolo' => '2026-06-01',
            'dt_recebimento_mds' => '2026-06-01',
            'motivo_recebimento' => 'Manifestação',
            'fase_processo' => 'ANÁLISE TÉCNICA',
            'situacao_cneas' => 'Regular',
        ], $attributes);

        DB::table('access')->insert(array_intersect_key($data, array_flip(Schema::getColumnListing('access'))));
    }
}
