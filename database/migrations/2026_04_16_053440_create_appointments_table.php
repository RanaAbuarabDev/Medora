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
            
            
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('lab_id')->constrained('laboratories')->onDelete('cascade');  
            
            
            $table->date('appointment_date'); 
            $table->time('start_time');       
            $table->time('end_time');         
            
            
            $table->enum('status', [
                'pending',
                'waiting',
                'in_progress',               
                'confirmed',             
                'cancelled_by_patient',  
                'cancelled_by_lab',      
                'completed'              
            ])->default('pending');

            
            $table->text('cancel_reason')->nullable();
            
            $table->timestamps();
            
            
            $table->index(['lab_id', 'appointment_date']);
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
