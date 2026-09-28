<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed admin awal (opsional) dari variabel .env.
     * Tambahkan admin lain melalui menu "Kelola Admin".
     */
    public function run(): void
    {
        $nrp = env('SEED_ADMIN_NRP');

        if (! $nrp) {
            $this->command?->info('SEED_ADMIN_NRP tidak diisi. Lewati seeding admin.');

            return;
        }

        User::updateOrCreate(
            ['employee_id' => $nrp],
            [
                'name' => env('SEED_ADMIN_NAME', 'Administrator'),
                'email' => env('SEED_ADMIN_EMAIL'),
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );

        $this->command?->info('Admin awal dibuat: '.$nrp);
    }
}
