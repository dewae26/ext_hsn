<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Snapshot nama RS agar log tetap utuh walau data RS dihapus.
        Schema::table('verification_logs', function (Blueprint $table) {
            $table->string('hospital_name', 150)->nullable()->after('hospital_id');
        });

        Schema::table('hospital_login_logs', function (Blueprint $table) {
            $table->string('hospital_name', 150)->nullable()->after('hospital_id');
            $table->string('pic_label', 100)->nullable()->after('phone');
        });

        // Isi snapshot untuk data lama dari relasi yang masih ada.
        DB::table('hospitals')->select('id', 'name')->orderBy('id')->get()->each(function ($hospital) {
            DB::table('verification_logs')->where('hospital_id', $hospital->id)->whereNull('hospital_name')
                ->update(['hospital_name' => $hospital->name]);
            DB::table('hospital_login_logs')->where('hospital_id', $hospital->id)->whereNull('hospital_name')
                ->update(['hospital_name' => $hospital->name]);
        });
    }

    public function down(): void
    {
        Schema::table('verification_logs', function (Blueprint $table) {
            $table->dropColumn('hospital_name');
        });

        Schema::table('hospital_login_logs', function (Blueprint $table) {
            $table->dropColumn(['hospital_name', 'pic_label']);
        });
    }
};
