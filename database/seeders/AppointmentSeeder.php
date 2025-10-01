<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Support\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = ['Therapist','Cardiologist','Dermatologist','Dentist','Neurologist'];

        // Для каждого пациента создаем 1–3 приёма в будущем
        Patient::chunk(50, function ($patients) use ($specializations) {
            foreach ($patients as $patient) {
                $count = rand(1, 3);
                $slotsForPatient = []; // чтобы не пересекались у пациента

                for ($i=0; $i<$count; $i++) {
                    $doctor = 'Dr. ' . fake()->lastName();
                    $spec   = fake()->randomElement($specializations);

                    $dateTime = $this->pickFreeSlot($patient->id, $doctor, $slotsForPatient);

                    Appointment::create([
                        'patient_id'     => $patient->id,
                        'doctor_name'    => $doctor,
                        'specialization' => $spec,
                        'date_time'      => $dateTime,
                        'status'         => 'scheduled',
                    ]);

                    $slotsForPatient[] = $dateTime;
                }
            }
        });
    }

    private function pickFreeSlot(int $patientId, string $doctor, array $patientTakenSlots): string
    {
        // попытки найти свободный слот
        for ($try = 0; $try < 20; $try++) {
            // шаг по 30 минут в ближайшие 30 дней
            $dateTime = Carbon::now()->addDays(rand(1, 30))->setTime(rand(9, 17), [0,30][rand(0,1)]);

            $dtString = $dateTime->format('Y-m-d H:i:s');

            // не занят ли у пациента?
            if (in_array($dtString, $patientTakenSlots, true)) {
                continue;
            }

            // не занят ли у доктора?
            $doctorBusy = Appointment::where('doctor_name', $doctor)
                ->where('date_time', $dtString)
                ->exists();

            if ($doctorBusy) {
                continue;
            }

            return $dtString;
        }

        // если не нашли — сдвигаем на 1 день от текущего
        return Carbon::now()->addDay()->setTime(10, 0)->format('Y-m-d H:i:s');
    }
}
