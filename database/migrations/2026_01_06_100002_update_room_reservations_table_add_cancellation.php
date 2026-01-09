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
            $table->boolean('is_cancelled')->default(false)->after('status');
            $table->dateTime('cancelled_at')->nullable()->after('is_cancelled');
            $table->string('cancellation_reason')->nullable()->after('cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_reservations', function (Blueprint $table) {
            $table->dropColumn(['is_cancelled', 'cancelled_at', 'cancellation_reason']);
        });
    }
};
