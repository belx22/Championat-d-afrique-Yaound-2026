<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provisional_registrations', function (Blueprint $table) {

            $table->id();

            /* ===============================
             * Liaison délégation (obligatoire)
             * =============================== */
            $table->foreignId('delegation_id')
                ->constrained('delegations')
                ->cascadeOnDelete();

            /* ===============================
             * Gymnasts (selon formulaire officiel)
             * =============================== */
            $table->unsignedInteger('mag_junior')->default(0);
            $table->unsignedInteger('mag_senior')->default(0);
            $table->unsignedInteger('wag_junior')->default(0);
            $table->unsignedInteger('wag_senior')->default(0);

            /* ===============================
             * Membres de la délégation
             * =============================== */
            $table->unsignedInteger('gymnast_team')->default(0);
            $table->unsignedInteger('gymnast_individuals')->default(0);
            $table->unsignedInteger('coach')->default(0);
            $table->unsignedInteger('judges_total')->default(0);
            $table->unsignedInteger('head_of_delegation')->default(0);
            $table->unsignedInteger('doctor_paramedics')->default(0);
            $table->unsignedInteger('team_manager')->default(0);

            /* ===============================
             * Workflow d’étape
             * =============================== */
            $table->enum('status', [ 'en_cours',
                                    'en_attente',
                                    'valide',
                                    'rejete'])
                ->default('en_cours')
                ->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provisional_registrations');
    }
};
