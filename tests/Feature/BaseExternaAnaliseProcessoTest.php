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
            'PROTOCOLO' => 'ANALISE-001',
            'PROTOCOLO_SEI' => 'SEI-001',
            'CNPJ' => '12345678000190',
            'MUNICIPIO' => 'BRASILIA',
            'UF' => 'DF',
        ]);

        $response = $this->actingAs(User::factory()->create())->get('/base-externa/analise-processo?search=SEI-001');

        $response->assertOk();
        $response->assertSee('ANALISE-001');
        $response->assertSee('SEI-001');
        $response->assertSee('Editar');
    }

    public function test_authenticated_user_can_edit_process(): void
    {
        $this->insertAccessProcess([
            'PROTOCOLO' => 'ANALISE-EDIT-001',
            'CNPJ' => '12345678000190',
            'MUNICIPIO' => 'BRASILIA',
            'UF' => 'DF',
            'FASE_PROCESSO' => 'AGUARDANDO ANÁLISE',
        ]);

        $response = $this->actingAs(User::factory()->create())->put('/base-externa/analise-processo', [
            'ORIGINAL_PROTOCOLO' => 'ANALISE-EDIT-001',
            'PROTOCOLO' => 'ANALISE-EDIT-001',
            'MOTIVO_RECEBIMENTO' => 'Competência para julgamento',
            'CNPJ' => '12345678000190',
            'MUNICIPIO' => 'BRASILIA',
            'UF' => 'DF',
            'FASE_PROCESSO' => 'ANÁLISE TÉCNICA',
        ]);

        $response->assertRedirect(route('base-externa.analise-processo.edit', ['protocolo' => 'ANALISE-EDIT-001']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('access', [
            'PROTOCOLO' => 'ANALISE-EDIT-001',
            'MOTIVO_RECEBIMENTO' => 'Competência para julgamento',
            'FASE_PROCESSO' => 'ANÁLISE TÉCNICA',
        ]);
    }

    public function test_edit_is_blocked_when_protocol_is_duplicated(): void
    {
        $this->insertAccessProcess(['PROTOCOLO' => 'DUPLICADO-001', 'PROTOCOLO_SEI' => 'Primeira']);
        $this->insertAccessProcess(['PROTOCOLO' => 'DUPLICADO-001', 'PROTOCOLO_SEI' => 'Segunda']);

        $response = $this->actingAs(User::factory()->create())->get('/base-externa/analise-processo/editar?protocolo=DUPLICADO-001');

        $response->assertRedirect(route('base-externa.analise-processo.index', ['search' => 'DUPLICADO-001']));
        $response->assertSessionHas('error', 'Edição bloqueada: este protocolo aparece em mais de um registro.');
    }

    public function test_update_is_blocked_when_original_protocol_is_not_unique(): void
    {
        $this->insertAccessProcess(['PROTOCOLO' => 'DUPLICADO-002', 'PROTOCOLO_SEI' => 'Primeira']);
        $this->insertAccessProcess(['PROTOCOLO' => 'DUPLICADO-002', 'PROTOCOLO_SEI' => 'Segunda']);

        $response = $this->actingAs(User::factory()->create())->put('/base-externa/analise-processo', [
            'ORIGINAL_PROTOCOLO' => 'DUPLICADO-002',
            'PROTOCOLO' => 'DUPLICADO-002',
            'MOTIVO_RECEBIMENTO' => 'Não deve atualizar',
        ]);

        $response->assertRedirect(route('base-externa.analise-processo.index', ['search' => 'DUPLICADO-002']));
        $response->assertSessionHas('error', 'Edição bloqueada: este protocolo aparece em mais de um registro.');

        $this->assertDatabaseMissing('access', [
            'PROTOCOLO' => 'DUPLICADO-002',
            'MOTIVO_RECEBIMENTO' => 'Não deve atualizar',
        ]);
    }

    public function test_update_is_blocked_when_new_protocol_is_empty(): void
    {
        $this->insertAccessProcess(['PROTOCOLO' => 'PROTOCOLO-VAZIO-001']);

        $response = $this->actingAs(User::factory()->create())->put('/base-externa/analise-processo', [
            'ORIGINAL_PROTOCOLO' => 'PROTOCOLO-VAZIO-001',
            'PROTOCOLO' => '',
            'MOTIVO_RECEBIMENTO' => 'Não deve atualizar',
        ]);

        $response->assertRedirect(route('base-externa.analise-processo.edit', ['protocolo' => 'PROTOCOLO-VAZIO-001']));
        $response->assertSessionHas('error', 'Edição bloqueada: o protocolo não pode ficar vazio.');

        $this->assertDatabaseHas('access', [
            'PROTOCOLO' => 'PROTOCOLO-VAZIO-001',
            'MOTIVO_RECEBIMENTO' => 'Manifestação',
        ]);
    }

    public function test_update_is_blocked_when_new_protocol_already_exists(): void
    {
        $this->insertAccessProcess(['PROTOCOLO' => 'PROTOCOLO-ORIGINAL-001']);
        $this->insertAccessProcess(['PROTOCOLO' => 'PROTOCOLO-EXISTENTE-001']);

        $response = $this->actingAs(User::factory()->create())->put('/base-externa/analise-processo', [
            'ORIGINAL_PROTOCOLO' => 'PROTOCOLO-ORIGINAL-001',
            'PROTOCOLO' => 'PROTOCOLO-EXISTENTE-001',
            'MOTIVO_RECEBIMENTO' => 'Não deve atualizar',
        ]);

        $response->assertRedirect(route('base-externa.analise-processo.edit', ['protocolo' => 'PROTOCOLO-ORIGINAL-001']));
        $response->assertSessionHas('error', 'Edição bloqueada: o novo protocolo informado já existe em outro registro.');

        $this->assertDatabaseHas('access', [
            'PROTOCOLO' => 'PROTOCOLO-ORIGINAL-001',
            'MOTIVO_RECEBIMENTO' => 'Manifestação',
        ]);
        $this->assertDatabaseHas('access', [
            'PROTOCOLO' => 'PROTOCOLO-EXISTENTE-001',
        ]);
        $this->assertDatabaseMissing('access', [
            'PROTOCOLO' => 'PROTOCOLO-EXISTENTE-001',
            'MOTIVO_RECEBIMENTO' => 'Não deve atualizar',
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function insertAccessProcess(array $attributes): void
    {
        $data = array_merge([
            'TIPO_PROCESSO' => 'Concessão',
            'PROTOCOLO' => 'PROTOCOLO-TESTE',
            'PROTOCOLO_SEI' => 'SEI-TESTE',
            'CNPJ' => '12345678000190',
            'UF' => 'DF',
            'MUNICIPIO' => 'BRASILIA',
            'ORGAO_ORIGEM' => 'MS',
            'DT_PROTOCOLO' => '2026-06-01',
            'DT_RECEBIMENTO_MDS' => '2026-06-01',
            'MOTIVO_RECEBIMENTO' => 'Manifestação',
            'FASE_PROCESSO' => 'ANÁLISE TÉCNICA',
            'SITUAÇÃO_CNEAS' => 'Regular',
        ], $attributes);

        DB::table('access')->insert(array_intersect_key($data, array_flip(Schema::getColumnListing('access'))));
    }
}
