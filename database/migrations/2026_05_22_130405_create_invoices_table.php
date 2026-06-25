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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->onDelete('cascade');
            $table->foreignId('lab_id')->index()->constrained('laboratories')->onDelete('cascade');
            $table->foreignId('patient_id')->index()->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 12, 2); 
            $table->decimal('amount_paid', 12, 2)->default(0.00); 
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid')->index(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
