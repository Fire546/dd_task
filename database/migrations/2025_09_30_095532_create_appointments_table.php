<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('doctor_name');
            $table->string('specialization');
            $table->dateTime('date_time');
            $table->enum('status', ['scheduled','cancelled', 'completed'])->default('scheduled');
            $table->timestamps();

            $table->unique(['patient_id','date_time']);
            $table->unique(['doctor_name','date_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
