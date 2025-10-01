<?php
namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

class ApiSchemas
{
    /**
     * @OA\Schema(
     *   schema="Patient",
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="first_name", type="string", example="John"),
     *   @OA\Property(property="last_name", type="string", example="Doe"),
     *   @OA\Property(property="birth_date", type="string", format="date", example="1990-05-10"),
     *   @OA\Property(property="gender", type="string", enum={"male","female"}, example="male"),
     *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-01T03:42:32Z"),
     *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-01T03:42:32Z")
     * )
     *
     * @OA\Schema(
     *   schema="Appointment",
     *   @OA\Property(property="id", type="integer", example=10),
     *   @OA\Property(property="patient_id", type="integer", example=1),
     *   @OA\Property(property="date_time", type="string", format="date-time", example="2025-10-05T09:30:00Z"),
     *   @OA\Property(property="status", type="string", enum={"scheduled","completed","cancelled"}, example="scheduled")
     * )
     *
     * @OA\Schema(
     *   schema="Meta",
     *   @OA\Property(property="current_page", type="integer", example=1),
     *   @OA\Property(property="per_page", type="integer", example=10),
     *   @OA\Property(property="total", type="integer", example=37),
     *   @OA\Property(property="last_page", type="integer", example=4),
     *   @OA\Property(property="next", type="string", nullable=true, example="http://127.0.0.1:8000/api/patients?page=2"),
     *   @OA\Property(property="prev", type="string", nullable=true, example=null)
     * )
     *
     * @OA\Schema(
     *   schema="SuccessListResponsePatient",
     *   @OA\Property(property="status", type="string", example="success"),
     *   @OA\Property(property="message", type="string", example="patients list"),
     *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Patient")),
     *   @OA\Property(property="meta", ref="#/components/schemas/Meta")
     * )
     *
     * @OA\Schema(
     *   schema="SuccessItemResponsePatient",
     *   @OA\Property(property="status", type="string", example="success"),
     *   @OA\Property(property="message", type="string", example="patient created"),
     *   @OA\Property(property="data", ref="#/components/schemas/Patient")
     * )
     *
     * @OA\Schema(
     *   schema="ErrorResponse",
     *   @OA\Property(property="status", type="string", example="error"),
     *   @OA\Property(property="message", type="string", example="patient not found")
     * )
     */
}
