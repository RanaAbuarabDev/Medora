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
        Schema::create('laboratory_patient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('laboratory_id')->constrained('laboratories')->onDelete('cascade');
            $table->string('internal_patient_number'); // الرقم الخاص بالمريض داخل هذا المخبر
            $table->unique(['user_id', 'laboratory_id']); // يمنع تكرار المريض بنفس المخبر
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratory_patient');
    }
};
