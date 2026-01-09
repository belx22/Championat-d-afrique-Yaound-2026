<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DefinitiveRegistration;

class definitiveController extends Controller
{
    //
      public function index()
    {
        // MODE TEST
        $delegationId = 2;

        $registration = DefinitiveRegistration::firstOrCreate(
            ['delegation_id' => $delegationId]
        );

        return view(
            'definitive',
            compact('registration')
        );
    }

    public function store(Request $request)
    {

        dd('STORE HIT',$request->all());
        $delegationId = 2;

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
        ]);

        DefinitiveRegistration::updateOrCreate(
            ['delegation_id' => $delegationId],
            array_merge($validated, [
                'status' => DefinitiveRegistration::STATUS_EN_ATTENTE
            ])
        );

        return back()->with('success', 'Definitive Registration soumise.');
    }

    public function validateStep()
    {
        $delegationId = 2;

        DefinitiveRegistration::where(
            'delegation_id',
            $delegationId
        )->update(['status' => DefinitiveRegistration::STATUS_VALIDE]);

        return back()->with('success', 'Definitive Registration validée.');
    }
}
