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
        Schema::create('master_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_category_id')->constrained('test_categories')->onDelete('cascade');
            $table->string('name'); 
            $table->string('short_name')->nullable(); 
            $table->string('sample_type')->nullable(); 
            $table->string('unit')->nullable(); 
            $table->text('normal_range')->nullable(); 
            $table->text('description')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tests');
    }
};
