<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_id'); // لضمان الـ Multi-Tenancy الآمن
            $table->unsignedBigInteger('lab_test_id')->nullable(); // nullable إذا كان العرض لفئة كاملة
            $table->unsignedBigInteger('category_id')->nullable(); // nullable إذا كان العرض لتحليل محدد
            $table->string('name'); 
            $table->decimal('discount_percentage', 5, 2); // نسبة الخصم (مثال: 15.00)
            $table->date('start_date'); 
            $table->date('end_date');   
            $table->boolean('is_active')->default(true); 
            $table->timestamps();

            // القيود الخارجية (Foreign Keys)
            $table->foreign('lab_id')->references('id')->on('laboratories')->onDelete('cascade');
            $table->foreign('lab_test_id')->references('id')->on('lab_tests')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('test_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};