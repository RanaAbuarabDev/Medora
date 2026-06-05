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
        Schema::create('test_parameters', function (Blueprint $table) {
            $table->id();
            
            // التعديل الجوهري: الربط مع التحليل المخصص للمختبر الحالي
            $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade'); 
            
            $table->string('name_en');      
            $table->string('name_ar');   
            $table->string('unit');         
            $table->decimal('min_value', 8, 2); 
            $table->decimal('max_value', 8, 2); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_parameters');
    }
};
