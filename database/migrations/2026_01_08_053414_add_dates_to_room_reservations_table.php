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
        Schema::table('room_reservations', function (Blueprint $table) {
            $table->date('check_in_date')->nullable()->after('status');
            $table->date('check_out_date')->nullable()->after('check_in_date');
            $table->integer('number_of_nights')->nullable()->after('check_out_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_reservations', function (Blueprint $table) {
            $table->dropColumn(['check_in_date', 'check_out_date', 'number_of_nights']);
        });
    }
};
