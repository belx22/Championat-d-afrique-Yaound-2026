<?php

namespace App\Http\Controllers;

use App\Models\UserChampionat;
use App\Models\Delegation;
use App\Models\NominativeRegistration;
use App\Models\ProvisionalRegistration;
use App\Models\DefinitiveRegistration;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        // ======================
        // UTILISATEURS
        // ======================
        $totalUsers = UserChampionat::count();

        $totalAdminFederation = UserChampionat::where(
            'role',
            'admin-federation'
        )->count();

        // ======================
        // DÉLÉGATIONS
        // ======================
        $totalDelegations = Delegation::count();

        $delegationsValidated = Delegation::whereHas(
            'definitiveRegistration',
            fn ($q) => $q->where('status', 'valide')
        )->count();

        $delegationsInProgress = Delegation::whereHas(
            'provisionalRegistration',
            fn ($q) => $q->whereIn('status', ['en_cours', 'en_attente'])
        )->count();

        $delegationsNotStarted = Delegation::whereDoesntHave(
            'provisionalRegistration'
        )->count();

        // ======================
        // MEMBRES
        // ======================
        $totalMembers = NominativeRegistration::count();

        $totalGymnasts = NominativeRegistration::where(
            'function',
            'gymnast'
        )->count();

        // ======================
        // PROVISIONAL / DEFINITIVE
        // ======================
        $provisionalGymnasts = ProvisionalRegistration::sum(
            \DB::raw('mag_junior + mag_senior + wag_junior + wag_senior')
        );

        $definitiveGymnasts = DefinitiveRegistration::sum(
            \DB::raw('mag_junior + mag_senior + wag_junior + wag_senior')
        );

        return view('dashboard.super_admin', compact(
            'totalUsers',
            'totalAdminFederation',
            'totalDelegations',
            'delegationsValidated',
            'delegationsInProgress',
            'delegationsNotStarted',
            'totalMembers',
            'totalGymnasts',
            'provisionalGymnasts',
            'definitiveGymnasts'
        ));
    }
}
