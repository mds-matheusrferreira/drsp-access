<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCoordenacaoPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $permission = (string) ($request->user()?->permission ?? '');

        abort_unless(in_array($permission, ['1', '2'], true), 403);

        return $next($request);
    }
}
