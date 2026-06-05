<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل الميجريشن لإضافة الحقل
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // إضافة حقل كود العينة ويكون فريداً وقابلاً للقيمة الفارغة مؤقتاً لتجنب المشاكل مع البيانات القديمة
            $table->string('sample_code')->nullable()->unique()->after('status');
        });
    }

    /**
     * التراجع عن الميجريشن (حذف الحقل)
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('sample_code');
        });
    }
};