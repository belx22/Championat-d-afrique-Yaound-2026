<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provisional_registrations', function (Blueprint $table) {
            $table->string('signed_document')
                  ->nullable()
                  ->after('status')
                  ->comment('Document signé par la fédération (PDF ou image)');
        });
    }

    public function down(): void
    {
        Schema::table('provisional_registrations', function (Blueprint $table) {
            $table->dropColumn('signed_document');
        });
    }
};
