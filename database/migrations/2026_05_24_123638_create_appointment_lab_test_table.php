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
        Schema::create('appointment_lab_test', function (Blueprint $table) {
            $table->id();
            
           
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade');
            
            
            $table->string('result_value')->nullable(); 
            $table->string('status')->default('pending'); 
                
            $table->timestamps();
            
            
            $table->unique(['appointment_id', 'lab_test_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_lab_test');
    }
};
