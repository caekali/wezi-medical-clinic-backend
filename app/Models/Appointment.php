<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{

    protected $fillable = [
        'phone_number',
        'patient_name',
        'doctor_id',
        'service_id',
        'appointment_date',
        'appointment_time',
        'status'
    ];


    /**
     * Assigned doctor
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Related service
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
