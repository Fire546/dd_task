<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Throwable;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
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
}
