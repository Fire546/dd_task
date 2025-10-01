<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;
use OpenApi\Annotations as OA;


class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *   path="/api/appointments",
     *   summary="List appointments",
     *   description="Список приёмов с фильтрами и пагинацией",
     *   tags={"Appointments"},
     *   @OA\Parameter(name="doctor_name", in="query", description="Поиск по имени врача", @OA\Schema(type="string")),
     *   @OA\Parameter(name="specialization", in="query", description="Поиск по специализации", @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=100), example=10),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1), example=1),
     *   @OA\Parameter(name="order", in="query", @OA\Schema(type="string", enum={"asc","desc"}), example="asc"),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="appointments list"),
     *       @OA\Property(property="data", type="array", @OA\Items(
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=12),
     *         @OA\Property(property="patient_id", type="integer", example=5),
     *         @OA\Property(property="doctor_name", type="string", example="Dr. House"),
     *         @OA\Property(property="specialization", type="string", example="Therapist"),
     *         @OA\Property(property="date_time", type="string", format="date-time", example="2025-10-05T09:30:00Z"),
     *         @OA\Property(property="status", type="string", enum={"scheduled","cancelled","completed"}, example="scheduled")
     *       )),
     *       @OA\Property(property="meta", type="object",
     *         @OA\Property(property="current_page", type="integer", example=1),
     *         @OA\Property(property="per_page", type="integer", example=10),
     *         @OA\Property(property="total", type="integer", example=37),
     *         @OA\Property(property="last_page", type="integer", example=4),
     *         @OA\Property(property="next", type="string", nullable=true, example=null),
     *         @OA\Property(property="prev", type="string", nullable=true, example=null)
     *       )
     *     )
     *   )
     * )
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

     /**
     * @OA\Post(
     *   path="/api/appointments",
     *   summary="Create appointment",
     *   tags={"Appointments"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"patient_id","doctor_name","specialization","date_time"},
     *       @OA\Property(property="patient_id", type="integer", example=5),
     *       @OA\Property(property="doctor_name", type="string", maxLength=25, example="Dr. House"),
     *       @OA\Property(property="specialization", type="string", maxLength=25, example="Therapist"),
     *       @OA\Property(property="date_time", type="string", format="date-time", example="2025-10-05T09:30:00Z")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="appointment created"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="id", type="integer", example=21),
     *         @OA\Property(property="patient_id", type="integer", example=5),
     *         @OA\Property(property="doctor_name", type="string", example="Dr. House"),
     *         @OA\Property(property="specialization", type="string", example="Therapist"),
     *         @OA\Property(property="date_time", type="string", format="date-time"),
     *         @OA\Property(property="status", type="string", example="scheduled")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=409,
     *     description="Conflict (time already booked)",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="conflict: doctor already booked at this time")
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="validation failed"),
     *       @OA\Property(property="errors", type="object")
     *     )
     *   )
     * )
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

     /**
     * @OA\Get(
     *   path="/api/appointments/{appointment}",
     *   summary="Get appointment by id",
     *   tags={"Appointments"},
     *   @OA\Parameter(name="appointment", in="path", required=true, @OA\Schema(type="integer"), example=21),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="appointment details"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="id", type="integer", example=21),
     *         @OA\Property(property="patient_id", type="integer", example=5),
     *         @OA\Property(property="doctor_name", type="string", example="Dr. House"),
     *         @OA\Property(property="specialization", type="string", example="Therapist"),
     *         @OA\Property(property="date_time", type="string", format="date-time"),
     *         @OA\Property(property="status", type="string", enum={"scheduled","cancelled","completed"})
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not found",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="appointment not found")
     *     )
     *   )
     * )
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

     /**
     * @OA\Patch(
     *   path="/api/appointments/{appointment}",
     *   summary="Update appointment",
     *   tags={"Appointments"},
     *   @OA\Parameter(name="appointment", in="path", required=true, @OA\Schema(type="integer"), example=21),
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       @OA\Property(property="doctor_name", type="string", maxLength=25, example="Dr. Wilson"),
     *       @OA\Property(property="specialization", type="string", maxLength=25, example="Cardiologist"),
     *       @OA\Property(property="date_time", type="string", format="date-time", example="2025-10-06T14:00:00Z"),
     *       @OA\Property(property="status", type="string", enum={"scheduled","cancelled","completed"}, example="scheduled")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="appointment updated"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="id", type="integer", example=21),
     *         @OA\Property(property="doctor_name", type="string", example="Dr. Wilson"),
     *         @OA\Property(property="specialization", type="string", example="Cardiologist"),
     *         @OA\Property(property="date_time", type="string", format="date-time"),
     *         @OA\Property(property="status", type="string", enum={"scheduled","cancelled","completed"})
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=409,
     *     description="Conflict (time already booked)",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="conflict: patient already has appointment at this time")
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not found",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="appointment not found")
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="validation failed"),
     *       @OA\Property(property="errors", type="object")
     *     )
     *   )
     * )
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

     /**
     * @OA\Delete(
     *   path="/api/appointments/{appointment}",
     *   summary="Delete appointment",
     *   tags={"Appointments"},
     *   @OA\Parameter(name="appointment", in="path", required=true, @OA\Schema(type="integer"), example=21),
     *   @OA\Response(
     *     response=200,
     *     description="Deleted",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="appointment deleted")
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not found",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="appointment not found")
     *     )
     *   )
     * )
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

    /**
     * @OA\Post(
     *   path="/api/appointments/{appointment}/cancel",
     *   summary="Cancel appointment",
     *   tags={"Appointments"},
     *   @OA\Parameter(name="appointment", in="path", required=true, @OA\Schema(type="integer"), example=21),
     *   @OA\Response(
     *     response=200,
     *     description="Cancelled",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="appointment cancelled"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="id", type="integer", example=21),
     *         @OA\Property(property="status", type="string", example="cancelled")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not found",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="appointment not found")
     *     )
     *   ),
     *   @OA\Response(
     *     response=409,
     *     description="Already cancelled",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="already cancelled")
     *     )
     *   )
     * )
     */

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
