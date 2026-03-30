<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el usuario autenticado tenga el permiso indicado
     * (definido como Gate dinámico en AppServiceProvider).
     *
     * Uso en rutas: ->middleware('check.permission:users.create')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  Nombre del permiso a verificar
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            abort(403, 'No autenticado.');
        }

        if (!Gate::allows($permission)) {
            abort(403, "No tienes permiso para realizar esta acción. Se requiere el permiso: {$permission}.");
        }

        return $next($request);
    }
}
