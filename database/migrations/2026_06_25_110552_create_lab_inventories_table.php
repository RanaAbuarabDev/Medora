<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_inventories', function (Blueprint $table) {
            $table->id();
            
            
            $table->foreignId('lab_id')->constrained('laboratories')->onDelete('cascade');
            $table->foreignId('master_item_id')->constrained('master_items')->onDelete('cascade');
            $table->integer('current_quantity')->default(0); 
            $table->integer('alert_level')->default(10); 
            $table->timestamps();
            $table->unique(['lab_id', 'master_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_inventories');
    }
};