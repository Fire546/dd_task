<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
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
