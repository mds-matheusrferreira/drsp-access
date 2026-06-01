<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'usuario.required' => 'Informe o usuário.',
            'password.required' => 'Informe a senha.',
        ]);

        $user = User::query()
            ->where('user', $validated['usuario'])
            ->first();

        if (! $user || ! $this->passwordIsValid($user, $validated['password'])) {
            throw ValidationException::withMessages([
                'usuario' => 'As credenciais informadas não conferem.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function passwordIsValid(User $user, string $password): bool
    {
        return hash_equals((string) $user->password, md5($password));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
