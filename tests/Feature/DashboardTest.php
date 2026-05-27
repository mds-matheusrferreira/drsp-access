<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_principal_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Matheus Berreira',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Painel - Principal');
        $response->assertSee('Confira o Mapa');
        $response->assertSee('Desconectar');
        $response->assertSee('Baixar Tabela Completa');
        $response->assertSee('Matheus Berreira');
    }
}
