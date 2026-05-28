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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('laboratory_id')->constrained('laboratories')->onDelete('cascade');
            $table->string('internal_employee_number')->unique(); 
            $table->string('job_title'); // نكتب فيه: manager, receptionist, assistant
            $table->string('specialization')->nullable(); // للمساعدين والفنيين فقط
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
