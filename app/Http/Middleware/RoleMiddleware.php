<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Usage: ->middleware('role:1,2') artinya hanya role 1 dan 2 yang boleh akses
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check() || !in_array((int) auth()->user()->idRole, array_map('intval', $roles))) {
            abort(403, 'Akses ditolak');
        }

        return $next($request);
    }
}
