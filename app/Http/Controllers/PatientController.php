<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Throwable;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="Patient and Appointments API", version="1.0.0")
 * @OA\Server(url="http://127.0.0.1:8000")
 */

class PatientController extends Controller
{
        /**
     * @OA\Get(
     *   path="/api/patients",
     *   summary="List patients",
     *   description="Список пациентов с поиском и пагинацией",
     *   tags={"Patients"},
     *   @OA\Parameter(name="search", in="query", description="Поиск по имени/фамилии", @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=100), example=10),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1), example=1),
     *   @OA\Parameter(name="order", in="query", @OA\Schema(type="string", enum={"asc","desc"}), example="desc"),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="patients list"),
     *       @OA\Property(property="data", type="array", @OA\Items(
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="first_name", type="string", example="John"),
     *         @OA\Property(property="last_name", type="string", example="Doe"),
     *         @OA\Property(property="birth_date", type="string", format="date", example="1990-05-10"),
     *         @OA\Property(property="gender", type="string", enum={"male","female"}, example="male"),
     *         @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-01T03:42:32Z"),
     *         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-01T03:42:32Z")
     *       )),
     *       @OA\Property(property="meta", type="object",
     *         @OA\Property(property="current_page", type="integer", example=1),
     *         @OA\Property(property="per_page", type="integer", example=10),
     *         @OA\Property(property="total", type="integer", example=37),
     *         @OA\Property(property="last_page", type="integer", example=4),
     *         @OA\Property(property="next", type="string", nullable=true, example="http://127.0.0.1:8000/api/patients?page=2"),
     *         @OA\Property(property="prev", type="string", nullable=true, example=null)
     *       )
     *     )
     *   )
     * )
     */
    public function index(Request $request)
    {
        //
        try {
            $perPage = min((int)$request->query('per_page', 10), 100);

            $q = Patient::query();
            
            if ($s = $request->query('search')) {
                $q->where('first_name','like',"%$s%")
                ->orWhere("last_name","like","%$s%");
            }

            $p = $q->latest('id')->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'message' => 'patients list',
                'data'=> $p->items(),
                'meta'=> [
                    'current_page' => $p->currentPage(),
                    'per_page' => $p->perPage(),
                    'total' => $p->total(),
                    'last_page' => $p->lastPage(),
                    'next' => $p->nextPageUrl(),
                    'prev' => $p->previousPageUrl(),
                ],
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'failed to get patients',
            ], 500);
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
     *   path="/api/patients",
     *   summary="Create patient",
     *   tags={"Patients"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"first_name","last_name","birth_date","gender"},
     *       @OA\Property(property="first_name", type="string", maxLength=25, example="John"),
     *       @OA\Property(property="last_name", type="string", maxLength=25, example="Doe"),
     *       @OA\Property(property="birth_date", type="string", format="date", example="1990-05-10"),
     *       @OA\Property(property="gender", type="string", enum={"male","female"}, example="male")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="patient created"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="id", type="integer", example=7),
     *         @OA\Property(property="first_name", type="string", example="John"),
     *         @OA\Property(property="last_name", type="string", example="Doe"),
     *         @OA\Property(property="birth_date", type="string", format="date", example="1990-05-10"),
     *         @OA\Property(property="gender", type="string", enum={"male","female"}, example="male"),
     *         @OA\Property(property="created_at", type="string", format="date-time"),
     *         @OA\Property(property="updated_at", type="string", format="date-time")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=422, description="Validation error",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="failed to create patient")
     *     )
     *   )
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'first_name' => ['required','string','max:25'],
                'last_name'  => ['required','string','max:25'],
                'birth_date' => ['required','date','before:today'],
                'gender'     => ['required','in:male,female'],
            ]);

            $patient = Patient::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'patient created',
                'data' => $patient,
            ], 201);
        } catch (Throwable $e) {
            $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 400;
            return response()->json([
                'status' => 'error',
                'message' => 'failed to create patient',
            ], $code);
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *   path="/api/patients/{patient}",
     *   summary="Get patient by id",
     *   tags={"Patients"},
     *   @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="integer"), example=5),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="patient details"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="id", type="integer", example=5),
     *         @OA\Property(property="first_name", type="string", example="Jane"),
     *         @OA\Property(property="last_name", type="string", example="Smith"),
     *         @OA\Property(property="birth_date", type="string", format="date", example="1985-03-22"),
     *         @OA\Property(property="gender", type="string", enum={"male","female"}, example="female"),
     *         @OA\Property(property="created_at", type="string", format="date-time"),
     *         @OA\Property(property="updated_at", type="string", format="date-time")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=404, description="Not found",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="patient not found")
     *     )
     *   )
     * )
     */
    public function show($id)
    {
        try {
            $patient = Patient::findOrFail($id);
    
            return response()->json([
                'status'  => 'success',
                'message' => 'patient details',
                'data'    => $patient,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'patient not found',
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * @OA\Patch(
     *   path="/api/patients/{patient}",
     *   summary="Update patient",
     *   tags={"Patients"},
     *   @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="integer"), example=5),
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       @OA\Property(property="first_name", type="string", maxLength=25, example="Johnny"),
     *       @OA\Property(property="last_name", type="string", maxLength=25, example="Doe"),
     *       @OA\Property(property="birth_date", type="string", format="date", example="1991-01-01"),
     *       @OA\Property(property="gender", type="string", enum={"male","female"}, example="male")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="patient updated"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="id", type="integer", example=5),
     *         @OA\Property(property="first_name", type="string", example="Johnny"),
     *         @OA\Property(property="last_name", type="string", example="Doe"),
     *         @OA\Property(property="birth_date", type="string", format="date", example="1991-01-01"),
     *         @OA\Property(property="gender", type="string", enum={"male","female"}, example="male"),
     *         @OA\Property(property="created_at", type="string", format="date-time"),
     *         @OA\Property(property="updated_at", type="string", format="date-time")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=404, description="Not found",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="patient not found")
     *     )
     *   ),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */

    public function update(Request $request, Patient $patient)
    {
        try {
            $data = $request->validate([
                'first_name' => ['sometimes','required','string','max:25'],
                'last_name'  => ['sometimes','required','string','max:25'],
                'birth_date' => ['sometimes','required','date','before:today'],
                'gender'     => ['sometimes','required','in:male,female'],
            ]);

            $patient->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'patient updated',
                'data' => $patient,
            ]);
        } catch (Throwable $e) {
            $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 400;
            return response()->json([
                'status' => 'error',
                'message' => 'failed to update patient',
            ], $code);
        }
    }

    /**
     * Remove the specified resource from storage.
     */

     /**
     * @OA\Delete(
     *   path="/api/patients/{patient}",
     *   summary="Delete patient",
     *   tags={"Patients"},
     *   @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="integer"), example=5),
     *   @OA\Response(
     *     response=200,
     *     description="Deleted",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="patient deleted")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Not found",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="patient not found")
     *     )
     *   )
     * )
     */

    public function destroy(Patient $patient)
    {
        try {
            $patient->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'patient deleted',
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'failed to delete patient',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/patients/{patient}/appointments",
     *   summary="List appointments by patient",
     *   tags={"Appointments"},
     *   @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="integer"), example=5),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=100), example=10),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1), example=1),
     *   @OA\Parameter(name="order", in="query", @OA\Schema(type="string", enum={"asc","desc"}), example="asc"),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", example="patient appointments"),
     *       @OA\Property(property="data", type="array", @OA\Items(
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=12),
     *         @OA\Property(property="patient_id", type="integer", example=5),
     *         @OA\Property(property="date_time", type="string", format="date-time", example="2025-10-05T09:30:00Z"),
     *         @OA\Property(property="status", type="string", enum={"scheduled","completed","cancelled"}, example="scheduled")
     *       )),
     *       @OA\Property(property="meta", type="object",
     *         @OA\Property(property="current_page", type="integer", example=1),
     *         @OA\Property(property="per_page", type="integer", example=10),
     *         @OA\Property(property="total", type="integer", example=3),
     *         @OA\Property(property="last_page", type="integer", example=1),
     *         @OA\Property(property="next", type="string", nullable=true, example=null),
     *         @OA\Property(property="prev", type="string", nullable=true, example=null)
     *       )
     *     )
     *   ),
     *   @OA\Response(response=404, description="Patient not found",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="message", type="string", example="patient not found")
     *     )
     *   )
     * )
     */

    public function byPatient(string $patient, Request $request)
    {
        if (!ctype_digit((string)$patient)) {
            return response()->json(['status'=>'error','message'=>'invalid patient id'], 400);
        }

        $patientModel = Patient::find($patient);
        if ($patientModel === null) {
            return response()->json(['status'=>'error','message'=>'patient not found'], 404);
        }

        $perPage = min((int)$request->query('per_page', 10), 100);
        $order   = $request->query('order', 'asc') === 'desc' ? 'desc' : 'asc';

        $p = Appointment::where('patient_id', $patient)
            ->orderBy('date_time', $order)
            ->paginate($perPage);

        return response()->json([
            'status'  => 'success',
            'message' => 'patient appointments',
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
    }
}
