<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;

Route::apiResource("patients", PatientController::class);
Route::apiResource("appointments", AppointmentController::class);

Route::get('patients/{patient}/appointments', [AppointmentController::class,'byPatient']);
Route::post('appointments/{appointment}/cancel', [AppointmentController::class,'cancel']);