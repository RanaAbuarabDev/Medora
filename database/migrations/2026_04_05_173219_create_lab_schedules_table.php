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
        Schema::create('lab_schedules', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('lab_id')
                  ->constrained('laboratories')
                  ->cascadeOnDelete();
                  
            $table->tinyInteger('day_of_week'); 
            
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            $table->boolean('is_day_off')->default(false);
            
            $table->timestamps();

            $table->unique(['lab_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_schedules');
    }
};
