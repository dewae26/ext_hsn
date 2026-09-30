<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationLog extends Model
{
    protected $fillable = [
        'hospital_id',
        'hospital_name',
        'hospital_contact_id',
        'pic_phone',
        'pic_label',
        'nrp',
        'employee_name',
        'group_company',
        'company_name',
        'ktp',
        'room_rate',
        'result',
        'feedback',
        'ip',
        'user_agent',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(HospitalContact::class, 'hospital_contact_id');
    }
}
