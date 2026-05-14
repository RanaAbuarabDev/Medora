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
        Schema::create('lab_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lab_id')->constrained('laboratories')->onDelete('cascade');
            $table->foreignId('lab_test_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned(); 
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['appointment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_ratings');
    }
};
