<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalLoginLog extends Model
{
    protected $fillable = [
        'hospital_id',
        'hospital_name',
        'phone',
        'pic_label',
        'status',
        'reason',
        'ip',
        'user_agent',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
