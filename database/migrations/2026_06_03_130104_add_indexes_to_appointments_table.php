<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل الميجريشن لإضافة الفهارس
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            
            $table->index('status');
            $table->index(['appointment_date', 'start_time']);
        });
    }

    /**
     * التراجع عن الميجريشن (حذف الفهارس)
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // حذف الفهارس عند التراجع
            $table->dropIndex(['status']);
            $table->dropIndex(['appointment_date', 'start_time']);
        });
    }
};