<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delegations', function (Blueprint $table) {

            $table->id();

            // Informations délégation / fédération
            $table->string('country');
            $table->string('federation_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone');

            // Liaison avec l’utilisateur (admin fédération)
            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            // Contraintes
            $table->foreign('user_id')
                  ->references('id')
                  ->on('user_championat')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegations');
    }
};
