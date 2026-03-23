<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_id');
            $table->unsignedBigInteger('master_test_id');
            $table->decimal('price', 10, 2);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            // الربط اليدوي الصريح
            $table->foreign('lab_id')->references('id')->on('laboratories')->onDelete('cascade');
            $table->foreign('master_test_id')->references('id')->on('master_tests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};