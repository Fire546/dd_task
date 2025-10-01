<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        // 20 тестовых пациентов
        Patient::factory()->count(20)->create();

        // Несколько фиксированных для удобства тестов
        Patient::updateOrCreate(
            ['first_name' => 'John', 'last_name' => 'Doe'],
            ['birth_date' => '1990-05-10', 'gender' => 'male']
        );
        Patient::updateOrCreate(
            ['first_name' => 'Jane', 'last_name' => 'Smith'],
            ['birth_date' => '1988-03-22', 'gender' => 'female']
        );
    }
}
