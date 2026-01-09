<?php

namespace App\Http\Controllers;

use App\Models\Delegation;
use App\Models\UserChampionat;
use Illuminate\Http\Request;

class DelegationsController extends Controller
{
    /**
     * PAGE UNIQUE : listing + modals CRUD
     * 
     **/
public function index(Request $request)
{
    $query = Delegation::with('user');

    // 🔍 RECHERCHE
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('country', 'like', "%{$search}%")
              ->orWhere('federation_name', 'like', "%{$search}%")
              ->orWhereHas('user', function ($u) use ($search) {
                  $u->where('email', 'like', "%{$search}%");
              });
        });
    }

    // 📄 PAGINATION
    $delegations = $query
        ->orderBy('country')
        ->paginate(10)
        ->withQueryString();

    // 👤 ADMINS FÉDÉRATION ACTIFS (pour formulaires / selects)
    $federationAdmins = UserChampionat::where('role', 'admin-federation')
        ->where('status', 'actif')
        ->orderBy('email')
        ->get();

    return view('delegations', compact(
        'delegations',
        'federationAdmins'
    ));
}
    /**
     * CREATE
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'country'         => 'required|string|max:255',
            'federation_name' => 'required|string|max:255',
            'contact_person'  => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'phone'           => 'required|string|max:50',
            'user_id'         => 'required|exists:user_championat,id',
        ]);

        Delegation::create($validated);

        return redirect()
            ->route('delegations')
            ->with('success', 'Délégation créée et liée à un administrateur.');
    }

    /**
     * UPDATE
     */
    public function update(Request $request, Delegation $delegation)
    {
        $validated = $request->validate([
            'country'         => 'required|string|max:255',
            'federation_name' => 'required|string|max:255',
            'contact_person'  => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'phone'           => 'required|string|max:50',
            'user_id'         => 'required|exists:user_championat,id',
        ]);

        $delegation->update($validated);

        return redirect()
            ->route('delegations')
            ->with('success', 'Délégation mise à jour.');
    }

    /**
     * DELETE
     */
    public function destroy(Delegation $delegation)
    {
        
        $delegation->delete();

        return redirect()
            ->route('delegations')
            ->with('success', 'Délégation supprimée.');
    }
}
