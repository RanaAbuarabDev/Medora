<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name_ar' => 'إبر سحب الدم', 'name_en' => 'Needles'],
            ['name_ar' => 'محاقن يدوية', 'name_en' => 'Syringes'],
            ['name_ar' => 'أنابيب مفرغة غطاء بنفسجي', 'name_en' => 'Vacutainer Tubes - CBC'],
            ['name_ar' => 'أنابيب مفرغة غطاء أحمر', 'name_en' => 'Vacutainer Tubes - Serum'],
            ['name_ar' => 'أنابيب مفرغة غطاء أزرق', 'name_en' => 'Vacutainer Tubes - Coagulation'],
            ['name_ar' => 'حامل الأنابيب البلاستيكي', 'name_en' => 'Tube Holder'],
            ['name_ar' => 'العاصبة المطاطية', 'name_en' => 'Tourniquet'],
            ['name_ar' => 'مسحات كحولية معقمة', 'name_en' => 'Alcohol Swabs'],
            ['name_ar' => 'قطن طبي', 'name_en' => 'Cotton Balls'],
            ['name_ar' => 'شاش معقم', 'name_en' => 'Gauze'],
            ['name_ar' => 'قفازات طبية', 'name_en' => 'Medical Gloves'],
            ['name_ar' => 'كمامات طبية', 'name_en' => 'Face Masks'],
            ['name_ar' => 'معاطف مخبرية', 'name_en' => 'Lab Coats'],
            ['name_ar' => 'شرائح زجاجية مجهرية', 'name_en' => 'Glass Slides'],
            ['name_ar' => 'أغطية شرائح زجاجية', 'name_en' => 'Cover Slips'],
            ['name_ar' => 'ماصات بلاستيكية', 'name_en' => 'Pasteur Pipettes'],
            ['name_ar' => 'رؤوس ماصات آلية زرقاء', 'name_en' => 'Pipette Tips - Blue'],
            ['name_ar' => 'رؤوس ماصات آلية صفراء', 'name_en' => 'Pipette Tips - Yellow'],
            ['name_ar' => 'محلول فحص السكر', 'name_en' => 'Glucose Reagent'],
            ['name_ar' => 'محلول فحص اليوريا', 'name_en' => 'Urea Reagent'],
            ['name_ar' => 'محلول فحص الكوليسترول', 'name_en' => 'Cholesterol Reagent'],
            ['name_ar' => 'محلول التخفيف الملحي', 'name_en' => 'Normal Saline 0.9%'],
            ['name_ar' => 'ماء مقطر طبي', 'name_en' => 'Distilled Water'],
            ['name_ar' => 'ملون غيمسا للفحص المجهري', 'name_en' => 'Giemsa Stain'],
            ['name_ar' => 'أنابيب طرد مركزي', 'name_en' => 'Centrifuge Tubes'],
            ['name_ar' => 'علب جمع عينات البول المعقمة', 'name_en' => 'Urine Containers'],
            ['name_ar' => 'علب جمع عينات البراز', 'name_en' => 'Stool Containers'],
            ['name_ar' => 'حاوية التخلص من الأدوات الحادة', 'name_en' => 'Sharps Container'],
        ];

        foreach ($items as $item) {
            DB::table('master_items')->updateOrInsert(
                ['name_en' => $item['name_en']], 
                [
                    'name_ar'    => $item['name_ar'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}