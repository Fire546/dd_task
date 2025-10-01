<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_name',
        'specialization',
        'date_time',
    ];

    public function patient() {
        return $this->belongsTo(Patient::class);
    }
}
