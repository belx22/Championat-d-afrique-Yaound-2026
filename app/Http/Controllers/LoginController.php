<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Page login
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Traitement login classique
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('championat')->attempt($credentials)) {

            $user = Auth::guard('championat')->user();

            // Vérification statut
            if ($user->status !== 'actif') {
                Auth::guard('championat')->logout();

                return back()->withErrors([
                    'email' => 'Compte désactivé'
                ]);
            }

            $request->session()->regenerate();

            // Redirection par rôle
            return match ($user->role) {
                'super-admin' => redirect('/dashboard/super-admin'),
                'admin-local' => redirect('/dashboard/admin_local'),
                'admin-federation' => redirect('/dashboard/admin_federation'),
                default       => redirect('/login'),
            };
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects'
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::guard('championat')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
