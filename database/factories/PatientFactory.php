<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);
        return [
            'first_name' => $gender === 'male' ? fake()->firstNameMale() : fake()->firstNameFemale(),
            'last_name'  => fake()->lastName(),
            'birth_date' => fake()->date(max: '2005-12-31'), // 18+ лет условно
            'gender'     => $gender,
        ];
    }
}
