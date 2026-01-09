<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_championat', function (Blueprint $table) {

            $table->id();

            // Authentification
            $table->string('email')->unique();
            $table->string('password');

            // Rôle métier
            $table->enum('role', [
                'super-admin',
                'admin-local',
                'admin-federation'
            ])->index();

            // Statut du compte
            $table->enum('status', [
                'actif',
                'desactiver'
            ])->default('actif')->index();

            // Liaison logique avec délégation (uniquement admin-federation)
            $table->foreignId('delegation_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->timestamps(); // created_at = date de création
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_championat');
    }
};
