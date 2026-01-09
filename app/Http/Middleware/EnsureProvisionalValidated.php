<?php

namespace App\Http\Middleware;

use App\Models\ProvisionalRegistration;
use Closure;
use Illuminate\Http\Request;

class EnsureProvisionalValidated
{
    public function handle(Request $request, Closure $next)
    {
        /**
         * MODE TEST (statique)
         * Remplacer par auth plus tard
         */
        $delegationId = 2;

        // PRODUCTION (quand prêt)
        // $delegationId = auth('championat')->user()->delegation->id;

        $provisional = ProvisionalRegistration::where(
            'delegation_id',
            $delegationId
        )->first();

        if (
            !$provisional ||
            $provisional->status !== ProvisionalRegistration::STATUS_VALIDE
        ) {
            return redirect()
                ->route('provisional.index')
                ->with('error',
                    'La Provisional Registration doit être VALIDÉE par l’admin local avant de continuer.'
                );
        }

        return $next($request);
    }
}
