<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('nominative_registrations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('delegation_id')->constrained()->cascadeOnDelete();

    // Identité (document officiel)
    $table->string('family_name');
    $table->string('given_name');
    $table->enum('gender', ['M','F']);
    $table->date('date_of_birth');
    $table->string('nationality');

    // Passeport
    $table->string('passport_number');
    $table->date('passport_expiry_date');
    $table->string('passport_scan');

    // Fonction
    $table->enum('function', [
        'gymnast',
        'coach',
        'judge',
        'doctor',
        'manager',
        'head'
    ]);

    // Gymnaste uniquement
    $table->enum('discipline', ['GAM','GAF'])->nullable();
    $table->enum('category', ['junior','senior'])->nullable();
    $table->string('fig_id')->nullable();

    // Fichiers
    $table->string('photo_4x4');
    $table->string('music_file')->nullable(); // GAF uniquement

    $table->timestamps();
});



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominative_registrations');
    }
};
