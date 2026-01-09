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
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_type', ['partial_50', 'final_100'])->default('partial_50')->after('status');
            $table->date('payment_deadline_50')->nullable()->after('payment_type');
            $table->date('payment_deadline_100')->nullable()->after('payment_deadline_50');
            $table->dateTime('payment_made_at')->nullable()->after('payment_deadline_100');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'payment_deadline_50', 'payment_deadline_100', 'payment_made_at']);
        });
    }
};
