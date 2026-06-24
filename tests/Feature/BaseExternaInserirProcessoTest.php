<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseExternaInserirProcessoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_insert_process_form_to_login(): void
    {
        $this->get('/base-externa/inserir-processo')->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_when_submitting_insert_process_form(): void
    {
        $this->post('/base-externa/inserir-processo', [])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_insert_process_form(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/base-externa/inserir-processo');

        $response->assertOk();
        $response->assertSee('Base externa - Inserir Processo');
        $response->assertSee('Tipo do Processo');
        $response->assertSee('Protocolo');
        $response->assertSee('cnpj');
    }

    public function test_insert_process_validation_rejects_invalid_payload(): void
    {
        $response = $this->actingAs(User::factory()->create())->post('/base-externa/inserir-processo', [
            'cnpj' => '123',
            'uf' => 'XX',
        ]);

        $response->assertSessionHasErrors([
            'tipo_processo',
            'protocolo',
            'protocolo_sei',
            'cnpj',
            'uf',
            'municipio',
            'orgao_origem',
            'dt_protocolo',
            'dt_recebimento_mds',
            'motivo_recebimento',
            'fase_processo',
            'situacao_cneas',
        ]);
    }

    public function test_authenticated_user_can_insert_process(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs(User::factory()->create())->post('/base-externa/inserir-processo', $payload);

        $response->assertRedirect(route('base-externa.processos.create'));
        $response->assertSessionHas('success', 'Processo inserido com sucesso.');
        $this->assertDatabaseHas('access', [
            'tipo_processo' => 'Concessão',
            'protocolo' => '12345',
            'protocolo_sei' => 'SEI-12345',
            'cnpj' => '12345678000190',
            'uf' => 'DF',
            'municipio' => 'BRASILIA',
            'orgao_origem' => 'MS',
            'dt_protocolo' => '2026-05-20',
            'dt_recebimento_mds' => '2026-05-27',
            'motivo_recebimento' => 'Manifestação',
            'dt_certificacao_anterior_inicio' => '2025-01-01',
            'dt_certificacao_anterior_fim' => '2025-12-31',
            'dt_publicacao_certificacao_anterior_dou' => '2025-02-01',
            'tempestividade' => 'Tempestivo',
            'fase_processo' => 'ANÁLISE TÉCNICA',
            'situacao_cneas' => 'Regular',
        ]);
    }

    public function test_insert_process_normalizes_cnpj_uf_and_municipio(): void
    {
        $payload = array_merge($this->validPayload(), [
            'cnpj' => '12.345.678/0001-90',
            'uf' => 'df',
            'municipio' => 'São José do Rio Preto',
        ]);

        $this->actingAs(User::factory()->create())->post('/base-externa/inserir-processo', $payload);

        $this->assertDatabaseHas('access', [
            'protocolo' => '12345',
            'cnpj' => '12345678000190',
            'uf' => 'DF',
            'municipio' => 'SAO JOSE DO RIO PRETO',
        ]);
    }

    public function test_insert_process_allows_empty_tempestividade(): void
    {
        $payload = array_merge($this->validPayload(), [
            'protocolo' => '12346',
            'tempestividade' => '',
        ]);

        $this->actingAs(User::factory()->create())->post('/base-externa/inserir-processo', $payload);

        $this->assertDatabaseHas('access', [
            'protocolo' => '12346',
            'tempestividade' => null,
        ]);
    }

    public function test_insert_process_rejects_invalid_tempestividade(): void
    {
        $payload = array_merge($this->validPayload(), [
            'tempestividade' => 'Intempestivo',
        ]);

        $response = $this->actingAs(User::factory()->create())->post('/base-externa/inserir-processo', $payload);

        $response->assertSessionHasErrors('tempestividade');
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'tipo_processo' => 'Concessão',
            'protocolo' => '12345',
            'protocolo_sei' => 'SEI-12345',
            'cnpj' => '12345678000190',
            'uf' => 'DF',
            'municipio' => 'BRASILIA',
            'orgao_origem' => 'MS',
            'dt_protocolo' => '2026-05-20',
            'dt_recebimento_mds' => '2026-05-27',
            'motivo_recebimento' => 'Manifestação',
            'dt_certificacao_anterior_inicio' => '2025-01-01',
            'dt_certificacao_anterior_fim' => '2025-12-31',
            'dt_publicacao_certificacao_anterior_dou' => '2025-02-01',
            'tempestividade' => 'Tempestivo',
            'fase_processo' => 'ANÁLISE TÉCNICA',
            'situacao_cneas' => 'Regular',
        ];
    }
}
