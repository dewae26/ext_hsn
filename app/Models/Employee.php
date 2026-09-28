<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model READ-ONLY untuk tabel `employees` milik database MHCIS.
 *
 * PENTING:
 * - Koneksi `mhcis` hanya untuk membaca. Aplikasi ini tidak menulis,
 *   mengubah, atau memigrasi apa pun pada database MHCIS.
 * - Mass assignment diblokir ($guarded = ['*']) sebagai pengaman tambahan.
 */
class Employee extends Model
{
    use SoftDeletes;

    protected $connection = 'mhcis';

    protected $table = 'employees';

    protected $guarded = ['*'];

    protected $hidden = [
        // tidak perlu menyembunyikan apa pun; kolom yang dipakai hanya field verifikasi
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Status aktif berdasarkan deleted_at.
     * Aktif = deleted_at kosong; Tidak Aktif = deleted_at terisi.
     */
    public function isActive(): bool
    {
        return $this->deleted_at === null;
    }
}
