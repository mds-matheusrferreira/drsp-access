<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrincipalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_principal_endpoints_to_login(): void
    {
        $this->get('/principal/updated-at')->assertRedirect(route('login'));
        $this->get('/principal/search?search=teste')->assertRedirect(route('login'));
        $this->get('/principal/state-totals')->assertRedirect(route('login'));
        $this->get('/principal/states/RS')->assertRedirect(route('login'));
        $this->get('/principal/download')->assertRedirect(route('login'));
    }

    public function test_updated_at_returns_reference_date_from_cebas_suas(): void
    {
        $this->createCebasSuasTable();
        $this->insertCebasSuas('RS', '111', 'Entidade RS', 'Porto Alegre', '2026-05-13');
        $this->insertCebasSuas('SP', '222', 'Entidade SP', 'São Paulo', '2026-05-29');

        $response = $this->actingAs(User::factory()->create())->getJson('/principal/updated-at');

        $response->assertOk()->assertJson([
            'updated_at' => '29/05/2026',
        ]);
    }

    public function test_search_returns_matching_cebas_records(): void
    {
        $this->createCebasTable();
        $this->insertCebas('111', 'Entidade Encontrada', 'Base A', '12345');
        $this->insertCebas('222', 'Outra Entidade', 'Base B', '67890');

        $response = $this->actingAs(User::factory()->create())->getJson('/principal/search?search=Encontrada');

        $response->assertOk()
            ->assertJsonPath('count_total', 1)
            ->assertJsonPath('data.0.ENTIDADE', 'Entidade Encontrada')
            ->assertJsonPath('columns.0', 'CNPJ');
    }

    public function test_state_totals_and_paginated_records_return_cebas_suas_data(): void
    {
        $this->createCebasSuasTable();
        $this->insertCebasSuas('RS', '111', 'Entidade RS', 'Porto Alegre', '2026-05-13');
        $this->insertCebasSuas('RS', '222', 'Entidade RS 2', 'Caxias do Sul', '2026-05-13');
        $this->insertCebasSuas('SP', '333', 'Entidade SP', 'São Paulo', '2026-05-13');

        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/principal/state-totals')
            ->assertOk()
            ->assertJsonFragment(['uf' => 'RS', 'total' => 2])
            ->assertJsonFragment(['uf' => 'SP', 'total' => 1]);

        $this->actingAs($user)->getJson('/principal/states/RS?page=1')
            ->assertOk()
            ->assertJsonPath('uf', 'RS')
            ->assertJsonPath('total_uf', 2)
            ->assertJsonPath('limit', 100)
            ->assertJsonCount(2, 'cebas');
    }

    public function test_downloads_return_excel_content(): void
    {
        $this->createCebasSuasTable();
        $this->insertCebasSuas('RS', '111', 'Entidade RS', 'Porto Alegre', '2026-05-13');
        $this->insertCebasSuas('SP', '222', 'Entidade SP', 'São Paulo', '2026-05-13');

        $user = User::factory()->create();

        $all = $this->actingAs($user)->get('/principal/download');
        $all->assertOk()->assertDownload('cebas-completo.xls');
        $this->assertSame('application/vnd.ms-excel; charset=UTF-8', $all->headers->get('content-type'));
        $this->assertStringContainsString('<table border="1">', $all->streamedContent());
        $this->assertStringContainsString('Entidade RS', $all->streamedContent());
        $this->assertStringContainsString('Entidade SP', $all->streamedContent());

        $state = $this->actingAs($user)->get('/principal/states/RS/download');
        $state->assertOk()->assertDownload('cebas-RS.xls');
        $this->assertSame('application/vnd.ms-excel; charset=UTF-8', $state->headers->get('content-type'));
        $this->assertStringContainsString('Entidade RS', $state->streamedContent());
        $this->assertStringNotContainsString('Entidade SP', $state->streamedContent());
    }

    private function createCebasTable(): void
    {
        Schema::create('cebas', function (Blueprint $table) {
            $table->string('CNPJ')->nullable();
            $table->string('PROCESSO')->nullable();
            $table->string('PROTOCOLO')->nullable();
            $table->string('BASE')->nullable();
            $table->string('ENTIDADE')->nullable();
        });
    }

    private function createCebasSuasTable(): void
    {
        Schema::create('cebas_suas', function (Blueprint $table) {
            $table->string('UF')->nullable();
            $table->string('CNPJ')->nullable();
            $table->string('ENTIDADE')->nullable();
            $table->string('MUNICIPIO')->nullable();
            $table->date('dt_referencia')->nullable();
        });
    }

    private function insertCebas(string $cnpj, string $entidade, string $base, string $processo): void
    {
        $this->app['db']->table('cebas')->insert([
            'CNPJ' => $cnpj,
            'PROCESSO' => $processo,
            'PROTOCOLO' => 'PROTOCOLO-' . $processo,
            'BASE' => $base,
            'ENTIDADE' => $entidade,
        ]);
    }

    private function insertCebasSuas(string $uf, string $cnpj, string $entidade, string $municipio, string $date): void
    {
        $this->app['db']->table('cebas_suas')->insert([
            'UF' => $uf,
            'CNPJ' => $cnpj,
            'ENTIDADE' => $entidade,
            'MUNICIPIO' => $municipio,
            'dt_referencia' => $date,
        ]);
    }
}
