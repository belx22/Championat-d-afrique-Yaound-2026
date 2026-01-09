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
      Schema::create('delegation_infos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('delegation_id')->constrained()->cascadeOnDelete();

    $table->date('arrival_date');
    $table->date('departure_date');

    $table->string('flag_image');       // drapeau
    $table->string('national_anthem');  // hymne (mp3)

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegation_infos');
    }
};
