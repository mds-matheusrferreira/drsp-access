<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CneasAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_cneas_automation_routes(): void
    {
        $this->get('/coordenacao/automacoes/cneas')->assertRedirect(route('login'));
        $this->post('/coordenacao/automacoes/cneas/gerar')->assertRedirect(route('login'));
        $this->get('/coordenacao/automacoes/cneas/ultimo')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_cneas_automation_page(): void
    {
        Storage::deleteDirectory('cneas/relatorios');

        $this->actingAs(User::factory()->create())
            ->get('/coordenacao/automacoes/cneas')
            ->assertOk()
            ->assertSee('Automação CNEAS')
            ->assertSee('Gerar relatório CNEAS')
            ->assertSee('Nenhum relatório gerado.');
    }

    public function test_download_latest_report(): void
    {
        Storage::put('cneas/relatorios/Relatório_CNEAS gerado em 12.06.2026 10h00.xlsx', 'xlsx');

        $response = $this->actingAs(User::factory()->create())
            ->get('/coordenacao/automacoes/cneas/ultimo');

        $response->assertOk();
        $this->assertStringContainsString('Relatorio_CNEAS gerado em 12.06.2026 10h00.xlsx', $response->headers->get('content-disposition'));
    }
}
