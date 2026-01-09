<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // MODE TEST (statique)
        // À retirer quand auth est prête
        // --------------------------------
        // Simuler un rôle pour test :
        // $currentRole = 'admin-federation';

        // MODE PRODUCTION
        $user = auth('championat')->user();

        if (!$user) {
            abort(401, 'Non authentifié.');
        }

        $currentRole = $user->role;

        if (!in_array($currentRole, $roles)) {
            abort(403, 'Accès refusé : permissions insuffisantes.');
        }

        return $next($request);
    }
}
