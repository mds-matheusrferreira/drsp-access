<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Acesso Interno');
        $response->assertSee('Usuário...');
        $response->assertSee('Senha...');
        $response->assertSee('Lembrar Senha');
    }

    public function test_users_can_authenticate_with_bcrypt_password_hash(): void
    {
        $user = User::factory()->create([
            'user' => 'usuario.teste',
            'password' => md5('legacy-password'),
            'password_hash' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'usuario' => 'usuario.teste',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_legacy_md5_password_is_upgraded_after_successful_login(): void
    {
        $user = User::factory()->create([
            'user' => 'usuario.legado',
            'password' => md5('password'),
            'password_hash' => null,
        ]);

        $response = $this->post('/login', [
            'usuario' => 'usuario.legado',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertTrue(Hash::check('password', $user->fresh()->password_hash));
        $this->assertSame(md5('password'), $user->fresh()->password);
    }

    public function test_legacy_md5_password_is_rejected_after_bcrypt_hash_exists(): void
    {
        $user = User::factory()->create([
            'user' => 'usuario.migrado',
            'password' => md5('old-password'),
            'password_hash' => Hash::make('new-password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'usuario' => 'usuario.migrado',
            'password' => 'old-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('usuario');
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password_hash));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'user' => 'usuario.teste',
            'password' => md5('password'),
            'password_hash' => null,
        ]);

        $response = $this->from('/login')->post('/login', [
            'usuario' => 'usuario.teste',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('usuario');
        $this->assertNull($user->fresh()->password_hash);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
