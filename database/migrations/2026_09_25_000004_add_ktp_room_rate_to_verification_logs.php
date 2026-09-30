<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_logs', function (Blueprint $table) {
            $table->string('ktp', 32)->nullable()->after('company_name');
            $table->unsignedInteger('room_rate')->nullable()->after('ktp')
                ->comment('Nominal kamar per malam berdasarkan job_level');
        });
    }

    public function down(): void
    {
        Schema::table('verification_logs', function (Blueprint $table) {
            $table->dropColumn(['ktp', 'room_rate']);
        });
    }
};
