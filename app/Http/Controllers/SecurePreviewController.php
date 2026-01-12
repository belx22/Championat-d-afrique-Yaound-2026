<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ProvisionalRegistration;
use App\Models\DefinitiveRegistration;
use App\Models\NominativeRegistration;
use App\Models\DelegationInfo;

class SecurePreviewController extends Controller
{
    public function preview(string $context, int $id, string $field)
    {
        // ✅ AUTH CORRECT (championat)
        $user = auth('championat')->user();

        if (!$user) {
            abort(403);
        }

        // 🔎 Résolution dynamique du modèle
        $model = match ($context) {
            'provisional' => ProvisionalRegistration::findOrFail($id),
            'definitive'  => DefinitiveRegistration::findOrFail($id),
            'nominative'  => NominativeRegistration::findOrFail($id),
            'delegation'  => DelegationInfo::findOrFail($id),
            default       => abort(404),
        };

        // 🛡️ Récupération delegation_id
        $delegationId = $model->delegation_id ?? null;

        if (!$delegationId) {
            abort(403);
        }

        /**
         * 🔐 LOGIQUE D’AUTORISATION
         * - super-admin / admin-local : accès total
         * - admin-federation : accès uniquement à sa délégation
         */
        if ($user->role === 'admin-federation') {

            $delegation = \App\Models\Delegation::where('user_id', $user->id)->first();

            if (!$delegation || $delegation->id !== $delegationId) {
                abort(403);
            }
        }

        // 📂 Champ fichier
        if (!isset($model->{$field})) {
            abort(404);
        }

        $path = $model->{$field};

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        // 🧠 MIME TYPE
        $mime = Storage::disk('public')->mimeType($path);

        // 🚫 HEADERS ANTI-TÉLÉCHARGEMENT
        return response(
            Storage::disk('public')->get($path),
            Response::HTTP_OK,
            [
                'Content-Type'        => $mime,
                'Content-Disposition' => 'inline',
                'X-Frame-Options'     => 'SAMEORIGIN',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}