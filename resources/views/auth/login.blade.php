@extends('layouts.guest')

@section('title', 'Acesso Interno')

@section('content')
<main class="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 px-4 py-8 sm:px-6 sm:py-10">
    <section class="w-full max-w-2xl rounded-lg bg-white px-5 py-8 shadow-2xl sm:px-12 sm:py-12" aria-labelledby="login-title">
        <div class="mb-9 text-center">
            <h1 id="login-title" class="text-2xl font-medium text-gray-800 sm:text-3xl">Acesso Interno</h1>

            <div class="mt-7 flex justify-center">
                <img
                    src="{{ asset('images/logo-mds.png') }}"
                    alt="Ministério do Desenvolvimento e Assistência Social, Família e Combate à Fome e Governo Federal Brasil - União e Reconstrução"
                    class="h-auto w-full max-w-72 sm:max-w-96"
                >
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
            @csrf

            <div>
                <label for="usuario" class="sr-only">Usuário</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                    </svg>
                    <input
                        id="usuario"
                        name="usuario"
                        type="text"
                        value="{{ old('usuario') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Usuário..."
                        class="w-full rounded-full border border-gray-200 py-3.5 pl-12 pr-4 text-gray-700 placeholder:text-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 @error('usuario') border-red-500 @enderror"
                    >
                </div>
                @error('usuario')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="sr-only">Senha</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 0 0-9 0V10.5m-.75 0h10.5A1.5 1.5 0 0 1 18.75 12v6.75a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z" />
                    </svg>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="Senha..."
                        class="w-full rounded-full border border-gray-200 py-3.5 pl-12 pr-4 text-gray-700 placeholder:text-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                    >
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <label for="remember" class="flex w-fit items-center gap-2 text-sm font-medium text-gray-600">
                <input id="remember" name="remember" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" @checked(old('remember'))>
                <span>Lembrar Senha</span>
            </label>

            <button type="submit" class="w-full rounded-full bg-blue-600 py-3.5 text-sm font-medium text-white shadow-lg transition-colors hover:bg-blue-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Entrar
            </button>
        </form>
    </section>
</main>
@endsection
