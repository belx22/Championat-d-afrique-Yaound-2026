<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProvisionalRegistration;
use App\Models\DefinitiveRegistration;
use App\Models\NominativeRegistration;

class AdminFederationDashboardController extends Controller
{
    public function index()
    {
        $user = auth('championat')->user(); 


        $delegation = \App\Models\Delegation::where('user_id',$user->id)->first();
        
        // MODE TEST

        if(!$delegation){
            abort(403,'Délegation non  trouvé');

        }

        $delegationId = $delegation->id;

    $provisional = ProvisionalRegistration::where('delegation_id',$delegationId)->first();
    $definitive  = DefinitiveRegistration::where('delegation_id',$delegationId)->first();

    $members = NominativeRegistration::where('delegation_id',$delegationId)->get();

    return view('dashboard.admin_federation', [
        'prov'        => $provisional,
        'provisional'        => $provisional,
        'definitive'         => $definitive,
        'provisionalStatus'  => $provisional?->status ?? 'bloque',
        'definitiveStatus'   => $definitive?->status ?? 'bloque',

        // KPIs
        'totalMembers' => $members->count(),
        'totalGymnasts'=> $members->where('function','gymnast')->count(),

        'magJunior' => $members->where('discipline','GAM')->where('category','junior')->count(),
        'magSenior' => $members->where('discipline','GAM')->where('category','senior')->count(),
        'gafJunior' => $members->where('discipline','GAF')->where('category','junior')->count(),
        'gafSenior' => $members->where('discipline','GAF')->where('category','senior')->count(),

        'progress' => collect([
            $provisional?->status === 'valide',
            $definitive?->status === 'valide',
        ])->filter()->count() * 50,
    ]);
    }
}
