<?php

namespace App\Http\Controllers;

use App\Models\Delegation;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Http\Request;

class AdminRegistrationController extends Controller
{

public function index(Request $request)
{
    $query = Delegation::query()
        ->with([
            'provisionalRegistration',
            'definitiveRegistration',
            'nominativeRegistrations',
            'delegationInfo'
        ]);

    /* =========================
     * 🔍 RECHERCHE (Pays / Fédération)
     * ========================= */
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('country', 'LIKE', "%$search%")
              ->orWhere('federation_name', 'LIKE', "%$search%");
        });
    }

    /* =========================
     * 🔎 FILTRE PAR STATUT
     * ========================= */
    if ($request->filled('status')) {
        $status = $request->status;

        $query->where(function ($q) use ($status) {
            $q->whereHas('provisionalRegistration', fn ($s) => $s->where('status', $status))
              ->orWhereHas('definitiveRegistration', fn ($s) => $s->where('status', $status));
        });
    }

    /* =========================
     * 📄 PAGINATION
     * ========================= */
    $delegations = $query
        ->orderBy('country')
        ->paginate(10)
        ->withQueryString();

    /* =========================
     * 📊 TRANSFORMATION POUR LA VUE
     * ========================= */
    $rows = $delegations->getCollection()->map(function ($delegation) {

        $steps = [
            'provisional' => $delegation->provisionalRegistration,
            'definitive'  => $delegation->definitiveRegistration,
            'nominative'  => $delegation->nominativeRegistrations,
        ];

        // ===== COMPLETION (%)
        $completed = 0;
        if ($steps['provisional']) $completed += 33;
        if ($steps['definitive'])  $completed += 33;
        if ($steps['nominative']->count()) $completed += 34;

        // ===== MISSING FILES
        $missing = [];

        if (!$delegation->delegationInfo) {
            $missing[] = 'Delegation info';
        }

        if ($steps['provisional'] && !$steps['provisional']->signed_document) {
            $missing[] = 'Provisional signed doc';
        }

        if ($steps['definitive'] && !$steps['definitive']->signed_document) {
            $missing[] = 'Definitive signed doc';
        }

        return [
            'delegation'    => $delegation,
            'steps'         => $steps,
            'completion'    => $completed,
            'missing_files' => $missing,
        ];
    });

    // 🔁 on remet la collection transformée dans le paginator
    $delegations->setCollection($rows);

    return view('admin.registrations.index', [
        'rows' => $delegations
    ]);
}


 public function show(Delegation $delegation)
{
    $delegation->load([
        'delegationInfo',
        'provisionalRegistration',
        'definitiveRegistration',
        'nominativeRegistrations'
    ]);

    return view('admin.registrations.show', compact('delegation'));
}

 
public function validateStep(Delegation $delegation, string $step)
{
    $model = match ($step) {
        'provisional' => $delegation->provisionalRegistration,
        'definitive'  => $delegation->definitiveRegistration,
        default       => null
    };

    abort_if(!$model, 404);

    $model->update(['status' => 'valide']);

    return back()->with('success', ucfirst($step).' registration validée.');
}

/**
 * Rejet d’une étape
 */
public function rejectStep(Delegation $delegation, string $step)
{
    $model = match ($step) {
        'provisional' => $delegation->provisionalRegistration,
        'definitive'  => $delegation->definitiveRegistration,
        default       => null
    };

    abort_if(!$model, 404);

    $model->update(['status' => 'rejete']);

    return back()->with('error', ucfirst($step).' registration rejetée.');
}

/**
 * ZIP COMPLET D’UNE DÉLÉGATION
 */
public function downloadDelegation(Delegation $delegation)
{
    $delegation->load([
        'delegationInfo',
        'provisionalRegistration',
        'definitiveRegistration',
        'nominativeRegistrations'
    ]);

    $zip = new ZipArchive;
    $fileName = 'delegation_'.$delegation->country.'_'.now()->format('Ymd_His').'.zip';
    $zipPath = storage_path('app/tmp/'.$fileName);

    if (!file_exists(storage_path('app/tmp'))) {
        mkdir(storage_path('app/tmp'), 0775, true);
    }

    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    

    // === Delegation Info
    if ($delegation->delegationInfo) {
        $this->addIfExists($zip, $delegation->delegationInfo->flag_image, 'delegation/flag');
        $this->addIfExists($zip, $delegation->delegationInfo->national_anthem, 'delegation/anthem');
    }

    // === Provisional
    $this->addIfExists(
        $zip,
        optional($delegation->provisionalRegistration)->signed_document,
        'provisional/signed_document'
    );

    // === Definitive
    $this->addIfExists(
        $zip,
        optional($delegation->definitiveRegistration)->signed_document,
        'definitive/signed_document'
    );

    // === Members
    foreach ($delegation->nominativeRegistrations as $m) {
        $base = 'members/'.$m->family_name.'_'.$m->given_name;

        $this->addIfExists($zip, $m->passport_scan, $base.'/passport');
        $this->addIfExists($zip, $m->photo_4x4, $base.'/photo');

        if ($m->music_file) {
            $this->addIfExists($zip, $m->music_file, $base.'/music');
        }
    }

    $zip->close();

    return response()->download($zipPath)->deleteFileAfterSend();
}

/**
 * Helper ZIP
 */
private function addIfExists(ZipArchive $zip, ?string $path, string $zipName)
{
    if ($path && Storage::disk('public')->exists($path)) {
        $zip->addFile(
            storage_path('app/public/'.$path),
            $zipName.'.'.pathinfo($path, PATHINFO_EXTENSION)
        );
    }
}

}
