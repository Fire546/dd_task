<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = min((int)$request->query('per_page', 10), 100);
            $order   = $request->query('order', 'asc') === 'desc' ? 'desc' : 'asc';

            $q = Appointment::query()->with('patient:id,first_name,last_name');

            if ($doctor = $request->query('doctor_name')) {
                $q->where('doctor_name', 'like', "%$doctor%");
            }
            if ($spec = $request->query('specialization')) {
                $q->where('specialization', 'like', "%$spec%");
            }

            $p = $q->orderBy('date_time', $order)->paginate($perPage);

            return response()->json([
                'status'  => 'success',
                'message' => 'appointments list',
                'data'    => $p->items(),
                'meta'    => [
                    'current_page' => $p->currentPage(),
                    'per_page'     => $p->perPage(),
                    'total'        => $p->total(),
                    'last_page'    => $p->lastPage(),
                    'next'         => $p->nextPageUrl(),
                    'prev'         => $p->previousPageUrl(),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json(['status'=>'error','message'=>'failed to get appointments'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'patient_id'     => ['required','integer','exists:patients,id'],
                'doctor_name'    => ['required','string','max:25'],
                'specialization' => ['required','string','max:25'],
                'date_time'      => ['required','date','after:now'],
            ]);

            
            // 1) Пациент не может иметь 2 записи в одно время
            $existsForPatient = Appointment::where('patient_id', $data['patient_id'])
                ->where('date_time', $data['date_time'])
                ->exists();

            if ($existsForPatient) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'conflict: patient already has appointment at this time',
                ], 409);
            }

            // 2) Врач не может принимать нескольких пациентов в одно время
            $existsForDoctor = Appointment::where('doctor_name', $data['doctor_name'])
                ->where('date_time', $data['date_time'])
                ->exists();

            if ($existsForDoctor) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'conflict: doctor already booked at this time',
                ], 409);
            }

            $appointment = Appointment::create([
                'patient_id'     => $data['patient_id'],
                'doctor_name'    => $data['doctor_name'],
                'specialization' => $data['specialization'],
                'date_time'      => $data['date_time'],
                'status'         => 'scheduled', // по умолчанию
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'appointment created',
                'data'    => $appointment,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json(['status'=>'error','message'=>'failed to create appointment'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appointment = Appointment::with('patient:id,first_name,last_name')->find($id);

        if ($appointment === null) {
            return response()->json(['status'=>'error','message'=>'appointment not found'], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'appointment details',
            'data'    => $appointment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $appointment = Appointment::find($id);
            if ($appointment === null) {
                return response()->json(['status'=>'error','message'=>'appointment not found'], 404);
            }

            $data = $request->validate([
                'doctor_name'    => ['sometimes','required','string','max:25'],
                'specialization' => ['sometimes','required','string','max:25'],
                'date_time'      => ['sometimes','required','date','after:now'],
                'status'         => ['sometimes','required','in:scheduled,cancelled,completed'],
            ]);

            
            $newDate  = $data['date_time']     ?? $appointment->date_time;
            $newDoc   = $data['doctor_name']    ?? $appointment->doctor_name;
            $patientId= $appointment->patient_id;

            if (isset($data['date_time']) || isset($data['doctor_name'])) {
                $conflictPatient = Appointment::where('patient_id', $patientId)
                    ->where('date_time', $newDate)
                    ->where('id', '!=', $appointment->id)
                    ->exists();
                if ($conflictPatient) {
                    return response()->json([
                        'status'=>'error',
                        'message'=>'conflict: patient already has appointment at this time',
                    ], 409);
                }

                $conflictDoctor = Appointment::where('doctor_name', $newDoc)
                    ->where('date_time', $newDate)
                    ->where('id', '!=', $appointment->id)
                    ->exists();
                if ($conflictDoctor) {
                    return response()->json([
                        'status'=>'error',
                        'message'=>'conflict: doctor already booked at this time',
                    ], 409);
                }
            }

            $appointment->update($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'appointment updated',
                'data'    => $appointment,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status'=>'error','message'=>'validation failed','errors'=>$e->errors()
            ], 422);
        } catch (Throwable $e) {
            return response()->json(['status'=>'error','message'=>'failed to update appointment'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $appointment = Appointment::find($id);
            if ($appointment === null) {
                return response()->json(['status'=>'error','message'=>'appointment not found'], 404);
            }

            $appointment->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'appointment deleted',
            ], 200);
        } catch (Throwable $e) {
            return response()->json(['status'=>'error','message'=>'failed to delete appointment'], 500);
        }
    }

    public function cancel(string $appointment)
    {
        $model = Appointment::find($appointment);
        if ($model === null) {
            return response()->json(['status'=>'error','message'=>'appointment not found'], 404);
        }

        if ($model->status === 'cancelled') {
            return response()->json(['status'=>'error','message'=>'already cancelled'], 409);
        }

        $model->status = 'cancelled';
        $model->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'appointment cancelled',
            'data'    => $model,
        ]);
    }
}
