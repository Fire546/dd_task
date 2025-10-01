<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'gender',
    ];

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }
}
