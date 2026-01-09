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
        Schema::create('room_reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('delegation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('room_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('rooms_reserved');
    $table->string('status')->default('en_attente'); // en_attente | valide | rejete
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_reservations');
    }
};
