<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_logs', function (Blueprint $table) {
            $table->foreignId('hospital_contact_id')->nullable()->after('hospital_id')
                ->constrained('hospital_contacts')->nullOnDelete();
            $table->string('pic_phone', 25)->nullable()->after('hospital_contact_id')
                ->comment('Snapshot nomor PIC yang melakukan pengecekan');
            $table->string('pic_label', 100)->nullable()->after('pic_phone')
                ->comment('Snapshot keterangan PIC (mis. cabang/daerah)');
        });
    }

    public function down(): void
    {
        Schema::table('verification_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hospital_contact_id');
            $table->dropColumn(['pic_phone', 'pic_label']);
        });
    }
};
