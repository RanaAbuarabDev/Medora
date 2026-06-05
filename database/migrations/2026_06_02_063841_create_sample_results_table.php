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
        Schema::create('sample_results', function (Blueprint $table) {
            $table->id();

            // 1. ربط النتيجة بالموعد الحالي للمريض (موجود في جداولك)
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');

            // 2. ربط النتيجة بالفحص الفرعي الدقيق (مثل الهيموجلوبين) لمعرفة الـ Min والـ Max والوحدة
            $table->foreignId('parameter_id')->constrained('test_parameters')->onDelete('cascade');

            // 3. القيمة الطبية الفوقية التي يدخلها المساعد يدوياً في الواجهة (مثل 11.20)
            // استخدام decimal أدق رياضياً وطبياً من float للحسابات
            $table->decimal('result_value', 8, 2); 

            // 4. ربط النتيجة بالموظف المخبري اللي قام بالإدخال (رشا الأحمد) للتوثيق والمساءلة الطبية
            // يربط مع جدول employee_profiles الموجود عندك في السيستم
            $table->foreignId('employee_id')->constrained('employee_profiles')->onDelete('cascade');

            // 5. حالة النتيجة (مسودة لم تكتمل بعد، مكتملة ونهائية، أو تم تعديلها لاحقاً)
            $table->enum('status', ['draft', 'completed', 'modified'])->default('draft');

            // 6. حقل نصي اختياري للملاحظات المخبرية على هذا الفحص الفرعي إذا وجد شيء غير طبيعي
            $table->text('technician_notes')->nullable();

            $table->timestamps();

            // فكرة هندسية متقدمة (Index): لتسريع الاستعلامات (Queries) عند جلب نتائج موعد معين في واجهة النتائج
            $table->index(['appointment_id', 'parameter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_results');
    }
};
