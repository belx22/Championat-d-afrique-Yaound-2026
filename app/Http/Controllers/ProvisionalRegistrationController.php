<?php

namespace App\Http\Controllers;

use App\Models\ProvisionalRegistration;
use Illuminate\Http\Request;

class ProvisionalRegistrationController extends Controller
{
    /**
     * PAGE PROVISIONAL REGISTRATION (MODE TEST)
     */
    public function index()
    {
        // ============================
        // MODE TEST (ID FIXE)
        // ============================
        $delegationId = 2;

        $registration = ProvisionalRegistration::firstOrCreate(
            ['delegation_id' => $delegationId]
        );

        return view(
            'registrations.provisional_registration',
            compact('registration')
        );
    }

    /**
     * ENREGISTREMENT DES DONNÉES
     */
public function store(Request $request)
{
    $delegationId = 2; // MODE TEST

    $validated = $request->validate([
        'mag_junior'          => 'required|integer|min:0',
        'mag_senior'          => 'required|integer|min:0',
        'wag_junior'          => 'required|integer|min:0',
        'wag_senior'          => 'required|integer|min:0',
        'gymnast_team'        => 'required|integer|min:0',
        'gymnast_individuals' => 'required|integer|min:0',
        'coach'               => 'required|integer|min:0',
        'judges_total'        => 'required|integer|min:0',
        'head_of_delegation'  => 'required|integer|min:0',
        'doctor_paramedics'   => 'required|integer|min:0',
        'team_manager'        => 'required|integer|min:0',

        // 📎 document signé
        'signed_document' => [
        'nullable',
        'file',
        'mimes:pdf,jpg,jpeg,png',
        'max:10240' // 10 MB (au lieu de 5 MB)
],
    ]);


    if ($request->hasFile('signed_document')) {

        $validated['signed_document'] =
            $request->file('signed_document')->store(
                "delegations/{$delegationId}/provisional",
                'public' // ⚠️ IMPORTANT
            );
    }


    ProvisionalRegistration::updateOrCreate(
        ['delegation_id' => $delegationId],
        $validated
    );

    return back()->with('success', 'Provisional registration enregistrée.');
}


    /**
     * VALIDATION DE L’ÉTAPE
     */
   
    public function validateStep()
    {
        $delegationId = 2;

        $registration = ProvisionalRegistration::where(
            'delegation_id',
            $delegationId
        )->firstOrFail();

        if (!$registration->signed_document) {
            return back()->withErrors([
                'signed_document' =>
                'Le document signé par la fédération est obligatoire.'
            ]);
        }

        $registration->update(['status' => 'valide']);

        return back()->with('success', 'Provisional registration validée.');
    }
}
