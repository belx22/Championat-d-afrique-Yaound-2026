<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Delegation;
use App\Models\NominativeRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AccreditationsExport;
use Maatwebsite\Excel\Facades\Excel;


use App\Models\Accreditation;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class AccreditationController extends Controller
{
public function index(Request $request)
    {
        $delegations = Delegation::withCount('nominativeRegistrations')
            ->when($request->search, fn($q) =>
                $q->where('country', 'like', "%{$request->search}%")
                ->orWhere('federation_name', 'like', "%{$request->search}%")
            )
            ->when($request->country, fn($q) =>
                $q->where('country', $request->country)
            )
            ->orderBy('country')
            ->paginate(10);

        $countries = Delegation::orderBy('country')
            ->distinct()
            ->pluck('country');

        return view('admin.accreditations.index', compact(
            'delegations',
            'countries'
        ));
    }

    public function show(Delegation $delegation)
    {
        $members = $delegation->nominativeRegistrations()->with('accreditation')->get();
        return view('admin.accreditations.show', compact('delegation','members'));
    }

    public function generate(NominativeRegistration $member)
    {
        if ($member->accreditation) {
            return back();
        }

        $badgeNumber = 'CAG-2026-'.$member->id.'-'.rand(100,999);

        $qrPath = QrCode::format('svg')
            ->size(300)
            ->generate($badgeNumber, storage_path("app/public/qrcodes/{$badgeNumber}.png"));

        Accreditation::create([
            'delegation_id' => $member->delegation_id,
            'nominative_registration_id' => $member->id,
            'badge_number' => $badgeNumber,
            'qr_code_path' => "qrcodes/{$badgeNumber}.png",
            'access_zones' => ['competition','training','restaurant'],
        ]);

        return back()->with('success','Badge généré');
    }

    public function validateBadge(Accreditation $accreditation)
    {
        $accreditation->update(['status'=>'valide']);
        return back();
    }

    public function rejectBadge(Accreditation $accreditation)
    {
        $accreditation->update(['status'=>'rejete']);
        return back();
    }


    /**
     * Impression badge individuel (PDF)
     */
    public function printSingle(NominativeRegistration $member)
    {
        // Génération QR Code (SVG = stable, sans imagick)
        $qrCode = QrCode::format('svg')
            ->size(150);
            //->generate(route('accreditation.scan', $member->id));

        // Génération PDF badge
        $pdf = Pdf::loadView('admin.accreditations.badge_single', [
            'member' => $member,
            'qrCode' => $qrCode,
        ])->setPaper('A7', 'landscape');

        return $pdf->stream(
            'badge_'.$member->family_name.'.pdf'
        );
    }


public function scan(Request $request)
{
    $member = NominativeRegistration::where(
        'qr_token', $request->token
    )->firstOrFail();

    abort_unless(
        $member->canAccess($request->zone),
        403,
        'Accès refusé'
    );

    

    return response()->json([
        'status' => 'OK',
        'name' => $member->full_name,
        'role' => $member->function,
    ]);
}


public function showByDelegation(Delegation $delegation)
    {
        $members = $delegation->nominativeRegistrations()
            ->with('accreditation')
            ->get()
            ->groupBy('function');

        return view('admin.accreditations.delegation', compact(
            'delegation',
            'members'
        ));
    }

     public function exportExcel()
    {
        return Excel::download(
            new AccreditationsExport,
            'accreditations_'.now()->format('Ymd_His').'.xlsx'
        );
    }


public function badgesPdf(Delegation $delegation)
    {
        $members = $delegation->nominativeRegistrations()
            ->with('accreditation')
            ->orderBy('function')
            ->orderBy('family_name')
            ->get();

        $pdf = Pdf::loadView(
            'admin.accreditations.badges-pdf',
            compact('delegation','members')
        )->setPaper('a4', 'portrait');

        return $pdf->stream(
            'badges_'.$delegation->country.'.pdf'
        );
    }


}


