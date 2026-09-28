<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->string('label', 100)->nullable()->comment('Keterangan, mis. RS Mitra Keluarga Pamulang');
            $table->string('phone', 25);
            $table->string('phone_normalized', 25)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pindahkan pic_phone lama menjadi kontak pertama agar login tetap berjalan.
        $now = now();
        DB::table('hospitals')
            ->whereNotNull('pic_phone')
            ->where('pic_phone', '<>', '')
            ->orderBy('id')
            ->get()
            ->each(function ($hospital) use ($now) {
                $digits = preg_replace('/\D+/', '', (string) $hospital->pic_phone);

                if ($digits === '') {
                    return;
                }

                if (str_starts_with($digits, '0')) {
                    $digits = '62'.substr($digits, 1);
                } elseif (! str_starts_with($digits, '62')) {
                    $digits = '62'.$digits;
                }

                DB::table('hospital_contacts')->insert([
                    'hospital_id' => $hospital->id,
                    'label' => 'PIC',
                    'phone' => $hospital->pic_phone,
                    'phone_normalized' => $digits,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_contacts');
    }
};
