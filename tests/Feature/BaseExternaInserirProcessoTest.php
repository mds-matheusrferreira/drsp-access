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
        $response->assertSee('CNPJ');
    }

    public function test_insert_process_validation_rejects_invalid_payload(): void
    {
        $response = $this->actingAs(User::factory()->create())->post('/base-externa/inserir-processo', [
            'CNPJ' => '123',
            'UF' => 'XX',
        ]);

        $response->assertSessionHasErrors([
            'TIPO_PROCESSO',
            'PROTOCOLO',
            'PROTOCOLO_SEI',
            'CNPJ',
            'UF',
            'MUNICIPIO',
            'ORGAO_ORIGEM',
            'DT_PROTOCOLO',
            'DT_RECEBIMENTO_MDS',
            'MOTIVO_RECEBIMENTO',
            'FASE_PROCESSO',
            'SITUACAO_CNEAS',
        ]);
    }

    public function test_authenticated_user_can_insert_process(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs(User::factory()->create())->post('/base-externa/inserir-processo', $payload);

        $response->assertRedirect(route('base-externa.processos.create'));
        $response->assertSessionHas('success', 'Processo inserido com sucesso.');
        $this->assertDatabaseHas('access', [
            'TIPO_PROCESSO' => 'Concessão',
            'PROTOCOLO' => '12345',
            'PROTOCOLO_SEI' => 'SEI-12345',
            'CNPJ' => '12345678000190',
            'UF' => 'DF',
            'MUNICIPIO' => 'BRASILIA',
            'ORGAO_ORIGEM' => 'MS',
            'DT_PROTOCOLO' => '2026-05-20',
            'DT_RECEBIMENTO_MDS' => '2026-05-27',
            'MOTIVO_RECEBIMENTO' => 'Manifestação',
            'DT_CERTIFICACAO_ANTERIOR_INICIO' => '2025-01-01',
            'DT_CERTIFICACAO_ANTERIOR_FIM' => '2025-12-31',
            'DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU' => '2025-02-01',
            'TEMPESTIVIDADE' => 'Tempestivo',
            'FASE_PROCESSO' => 'ANÁLISE TÉCNICA',
            'SITUAÇÃO_CNEAS' => 'Regular',
        ]);
    }

    public function test_insert_process_normalizes_cnpj_uf_and_municipio(): void
    {
        $payload = array_merge($this->validPayload(), [
            'CNPJ' => '12.345.678/0001-90',
            'UF' => 'df',
            'MUNICIPIO' => 'São José do Rio Preto',
        ]);

        $this->actingAs(User::factory()->create())->post('/base-externa/inserir-processo', $payload);

        $this->assertDatabaseHas('access', [
            'PROTOCOLO' => '12345',
            'CNPJ' => '12345678000190',
            'UF' => 'DF',
            'MUNICIPIO' => 'SAO JOSE DO RIO PRETO',
        ]);
    }

    public function test_insert_process_allows_empty_tempestividade(): void
    {
        $payload = array_merge($this->validPayload(), [
            'PROTOCOLO' => '12346',
            'TEMPESTIVIDADE' => '',
        ]);

        $this->actingAs(User::factory()->create())->post('/base-externa/inserir-processo', $payload);

        $this->assertDatabaseHas('access', [
            'PROTOCOLO' => '12346',
            'TEMPESTIVIDADE' => null,
        ]);
    }

    public function test_insert_process_rejects_invalid_tempestividade(): void
    {
        $payload = array_merge($this->validPayload(), [
            'TEMPESTIVIDADE' => 'Intempestivo',
        ]);

        $response = $this->actingAs(User::factory()->create())->post('/base-externa/inserir-processo', $payload);

        $response->assertSessionHasErrors('TEMPESTIVIDADE');
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'TIPO_PROCESSO' => 'Concessão',
            'PROTOCOLO' => '12345',
            'PROTOCOLO_SEI' => 'SEI-12345',
            'CNPJ' => '12345678000190',
            'UF' => 'DF',
            'MUNICIPIO' => 'BRASILIA',
            'ORGAO_ORIGEM' => 'MS',
            'DT_PROTOCOLO' => '2026-05-20',
            'DT_RECEBIMENTO_MDS' => '2026-05-27',
            'MOTIVO_RECEBIMENTO' => 'Manifestação',
            'DT_CERTIFICACAO_ANTERIOR_INICIO' => '2025-01-01',
            'DT_CERTIFICACAO_ANTERIOR_FIM' => '2025-12-31',
            'DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU' => '2025-02-01',
            'TEMPESTIVIDADE' => 'Tempestivo',
            'FASE_PROCESSO' => 'ANÁLISE TÉCNICA',
            'SITUACAO_CNEAS' => 'Regular',
        ];
    }
}
