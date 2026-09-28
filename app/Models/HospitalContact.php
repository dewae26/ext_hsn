<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalContact extends Model
{
    protected $fillable = [
        'hospital_id',
        'label',
        'phone',
        'phone_normalized',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
