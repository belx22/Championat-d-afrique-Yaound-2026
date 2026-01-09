<?php

namespace App\Http\Controllers;

use App\Models\UserChampionat;
use App\Models\Delegation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserChampionatController extends Controller
{
    /**
     * Liste des utilisateurs championnat
     */
    public function index()
    {
        $users = UserChampionat::with('delegation')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('role_inscription', compact('users'));
    }

    /**
     * Création d’un utilisateur championnat
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:user_championat,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in([
                'super-admin',
                'admin-local',
                'admin-federation',
            ])],
    
            'status' => ['required', Rule::in(['actif', 'desactiver'])],
        ]);

        // Cohérence rôle / délégation
      
        UserChampionat::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'],
            
        ]);

        return redirect()
            ->route('role_inscription')
            ->with('success', 'Utilisateur championnat créé avec succès.');
    }

    /**
     * Mise à jour d’un utilisateur championnat
     */
    public function update(Request $request, UserChampionat $userChampionat)
    {
        // Empêcher modification critique de soi-même
        if (
            auth('championat')->id() === $userChampionat->id &&
            $request->has('role')
        ) {
            abort(403, 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('user_championat', 'email')->ignore($userChampionat->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in([
                'super-admin',
                'admin-local',
                'admin-federation',
            ])],
           
            'status' => ['required', Rule::in(['actif', 'desactiver'])],
        ]);


        $userChampionat->email = $validated['email'];
        $userChampionat->role = $validated['role'];
        $userChampionat->status = $validated['status'];
        

        if (!empty($validated['password'])) {
            $userChampionat->password = Hash::make($validated['password']);
        }

        $userChampionat->save();

        return redirect()
            ->route('role_inscription')
            ->with('success', 'Utilisateur mis à jour.');
    }

    /**
     * Activation / désactivation rapide
     */
    public function toggleStatus(UserChampionat $userChampionat)
    {
        // Empêcher auto-désactivation
        if (auth('championat')->id() === $userChampionat->id) {
            abort(403, 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $userChampionat->status =
            $userChampionat->status === 'actif' ? 'desactiver' : 'actif';

        $userChampionat->save();

        return back()->with('success', 'Statut utilisateur mis à jour.');
    }

    /**
     * Suppression d’un utilisateur championnat
     */
    public function destroy(UserChampionat $userChampionat)
    {
        // Empêcher auto-suppression
        if (auth('championat')->id() === $userChampionat->id) {
            abort(403, 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Empêcher suppression du dernier super admin
        if (
            $userChampionat->role === 'super-admin' &&
            UserChampionat::where('role', 'super-admin')->count() <= 1
        ) {
            abort(403, 'Impossible de supprimer le dernier Super Admin.');
        }

        $userChampionat->delete();

        return redirect()
            ->route('role_inscription')
            ->with('success', 'Utilisateur supprimé.');
    }
 
}
