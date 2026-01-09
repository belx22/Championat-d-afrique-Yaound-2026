<?php

namespace App\Http\Controllers;

use App\Models\ProvisionalRegistration;
use Illuminate\Http\Request;

class AdminLocalDashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.admin_local', [
               'stats' => [
            'en_attente' => \App\Models\ProvisionalRegistration::where('status','en_attente')->count(),
            'valide'     => \App\Models\ProvisionalRegistration::where('status','valide')->count(),
            'rejete'     => \App\Models\ProvisionalRegistration::where('status','rejete')->count(),
               ],
            'pendingProvisional' =>
                ProvisionalRegistration::where('status', 'en_attente')->count(),
            'validatedProvisional' =>
                ProvisionalRegistration::where('status', 'valide')->count(),
        ]);
    }
}
