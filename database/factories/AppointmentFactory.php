<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Patient;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $specializations = ['Therapist','Cardiologist','Dermatologist','Dentist','Neurologist'];
        $doctor = 'Dr. ' . fake()->lastName();

        return [
            'patient_id'     => Patient::factory(), // по умолчанию создаст пациента
            'doctor_name'    => $doctor,
            'specialization' => fake()->randomElement($specializations),
            // В будущем время (требуется твоей валидацией after:now)
            'date_time'      => fake()->dateTimeBetween('+1 day', '+30 days'),
            'status'         => 'scheduled',
        ];
    }
}
