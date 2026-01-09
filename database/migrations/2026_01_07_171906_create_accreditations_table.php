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
        Schema::create('accreditations', function (Blueprint $table) {
    $table->id();

    $table->foreignId('delegation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('nominative_registration_id')->constrained()->cascadeOnDelete();

    $table->string('badge_number')->unique();
    $table->string('qr_code_path');

    $table->json('access_zones')->nullable();

    $table->enum('status', ['en_attente','valide','rejete'])->default('en_attente');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accreditations');
    }
};
