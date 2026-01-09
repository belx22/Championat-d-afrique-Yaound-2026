<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NominativeRegistration;
use App\Models\DelegationInfo;


class NominativeRegistrationController extends Controller
{
    public function index()
    {
        $delegationId = 2; // MODE TEST

        return view('registrations.nominative_registration', [
            'members' => NominativeRegistration::where('delegation_id',$delegationId)->get(),
            'info'    => DelegationInfo::where('delegation_id',$delegationId)->first(),
        ]);
    }

    /**
     * Infos délégation (UNE SEULE FOIS)
     */
    public function storeDelegationInfo(Request $request)
    {
        $delegationId = 2;

        $data = $request->validate([
            'arrival_date' => 'required|date',
            'departure_date' => 'required|date|after:arrival_date',
            'flag_image' => 'required|image|max:4096',
            'national_anthem' => 'required|file|mimes:mp3,wav,ogg|max:15360',
        ]);

        $data['flag_image'] =
            $request->file('flag_image')->store("delegations/$delegationId/flag",'public');

        $data['national_anthem'] =
            $request->file('national_anthem')->store("delegations/$delegationId/anthem",'public');

        DelegationInfo::updateOrCreate(
            ['delegation_id' => $delegationId],
            $data
        );

        return back()->with('success','Delegation information saved.');
    }

    /**
     * Ajout membre nominatif
     */
    public function store(Request $request)
    {
        //dd('STORE HIT',$request->all());
        $delegationId = 2;

        $rules = [
            'family_name' => 'required',
            'given_name' => 'required',
            'gender' => 'required|in:M,F',
            'date_of_birth' => 'required|date',
            'nationality' => 'required',
            'passport_number' => 'required',
            'passport_expiry_date' => 'required|date',
            'passport_scan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'photo_4x4' => 'required|image|max:10096',
            'function'  => 'required|in:gymnast,coach,judge,doctor,manager,head',
        ];

   if (in_array($request->function,['gymnast','coach','judge','doctor'])) {
            $rules['fig_id'] = 'required|string|max:50';
        }

        if ($request->function === 'gymnast') {
            $rules['discipline'] = 'required|in:GAM,GAF';
            $rules['category']   = 'required|in:junior,senior';
        }

        if ($request->function === 'gymnast' && $request->discipline === 'GAF') {
            $rules['music_file'] = 'required|file|mimes:mp3,mp4,wav|max:15360';
        }

        $data = $request->validate($rules);

        // 🔒 BLOQUER MUSIQUE SI PAS GAF
       

        // 📂 Uploads
        $data['passport_scan'] =
            $request->file('passport_scan')->store("delegations/$delegationId/passports",'public');

        $data['photo_4x4'] =
            $request->file('photo_4x4')->store("delegations/$delegationId/photos",'public');

        if ($request->hasFile('music_file')) {
            $data['music_file'] =
                $request->file('music_file')->store("delegations/$delegationId/music",'public');
        }

        $data['delegation_id'] = $delegationId;

        NominativeRegistration::create($data);

        return back()->with('success','Member added.');
    }

    /* ================================================= */
    /* UPDATE */
    /* ================================================= */
    public function update(Request $request, NominativeRegistration $member)
{
    $rules = [
        'family_name' => 'required',
        'given_name' => 'required',
        'gender' => 'required|in:M,F',
        'date_of_birth' => 'required|date',
        'nationality' => 'required',
        'passport_number' => 'required',
        'passport_expiry_date' => 'required|date',
        'function' => 'required',
    ];

    if ($request->function === 'gymnast') {
        $rules['discipline'] = 'required|in:GAM,GAF';
        $rules['category'] = 'required|in:junior,senior';
        $rules['fig_id'] = 'required';
    }

    $data = $request->validate($rules);

    // 📂 fichiers optionnels
    if ($request->hasFile('passport_scan')) {
        $data['passport_scan'] =
            $request->file('passport_scan')
            ->store("delegations/{$member->delegation_id}/passports", 'public');
    }

    if ($request->hasFile('photo_4x4')) {
        $data['photo_4x4'] =
            $request->file('photo_4x4')
            ->store("delegations/{$member->delegation_id}/photos", 'public');
    }

    if ($request->hasFile('music_file')) {
        $data['music_file'] =
            $request->file('music_file')
            ->store("delegations/{$member->delegation_id}/music", 'public');
    }

    $member->update($data);

    return back()->with('success', 'Member updated successfully.');
}


    /* ================================================= */
    /* DELETE */
    /* ================================================= */
    public function destroy(NominativeRegistration $member)
    {
        Storage::disk('public')->delete([
            $member->passport_scan,
            $member->photo_4x4,
            $member->music_file,
        ]);

        $member->delete();

        return back()->with('success','Member removed.');
    }

   
}