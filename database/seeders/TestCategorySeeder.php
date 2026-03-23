<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'دمويات (Hematology)', 
                'icon' => 'fa-solid fa-droplet',
                'description' => 'تشمل دراسة خلايا الدم، التخثر، وفصائل الدم ومشاكل فقر الدم.'
            ],
            [
                'name' => 'كيمياء حيوية (Biochemistry)', 
                'icon' => 'fa-solid fa-flask-vial',
                'description' => 'تحليل السوائل الحيوية لقياس السكر، وظائف الكلى، الكبد، والشحوم.'
            ],
            [
                'name' => 'هرمونات (Hormones)', 
                'icon' => 'fa-solid fa-dna',
                'description' => 'قياس مستوى الهرمونات المنظمة للجسم مثل الغدة الدرقية وهرمونات النمو.'
            ],
            [
                'name' => 'أحياء دقيقة (Microbiology)', 
                'icon' => 'fa-solid fa-microscope',
                'description' => 'دراسة الجراثيم، الفطريات، وعمليات الزرع الجرثومي واختبار الحساسية.'
            ],
            [
                'name' => 'فيروسات / مناعة (Virology/Immunology)', 
                'icon' => 'fa-solid fa-virus',
                'description' => 'الكشف عن الإصابات الفيروسية مثل الكبد الوبائي واستجابة الجهاز المناعي.'
            ],
            [
                'name' => 'تحاليل البول (Urine Analysis)', 
                'icon' => 'fa-solid fa-vial',
                'description' => 'فحص البول الروتيني للكشف عن الالتهابات، الأملاح، ومشاكل الجهاز البولي.'
            ],
            [
                'name' => 'تحاليل البراز (Stool Analysis)', 
                'icon' => 'fa-solid fa-vial-circle-check', 
                'description' => 'الكشف عن الطفيليات، الديدان، ومشاكل الجهاز الهضمي والامتصاص.'
            ],
            [
                'name' => 'تحاليل مناعية (Serology/Immunology)', 
                'icon' => 'fa-solid fa-shield-virus',
                'description' => 'اختبارات المصل للكشف عن الأمراض المناعية والروماتيزمية.'
            ],
            [
                'name' => 'تحاليل جينية (Genetic Tests)', 
                'icon' => 'fa-solid fa-fingerprint',
                'description' => 'دراسة الوراثة، الكروموسومات، والأمراض الجينية المنقولة.'
            ],
            [
                'name' => 'تحاليل المعادن والعناصر النزرة (Minerals & Trace Elements)', 
                'icon' => 'fa-solid fa-gem',
                'description' => 'قياس مستويات المعادن الأساسية مثل الحديد، الكالسيوم، والزنك.'
            ],
            [
                'name' => 'تحاليل وظائف القلب (Cardiac Markers)', 
                'icon' => 'fa-solid fa-heart-pulse',
                'description' => 'مؤشرات حيوية للكشف عن صحة عضلة القلب واحتمالات الجلطات.'
            ],
            [
                'name' => 'تحاليل الحمل (Pregnancy Tests)', 
                'icon' => 'fa-solid fa-baby',
                'description' => 'تشمل اختبارات كشف الحمل وتتبع صحة الجنين خلال الفترات المختلفة.'
            ],
            [
                'name' => 'تحاليل الحساسية (Allergy Tests)', 
                'icon' => 'fa-solid fa-hand-dots',
                'description' => 'تحديد مسببات الحساسية تجاه الأطعمة، الأدوية، أو العوامل البيئية.'
            ]
        ];

        foreach ($categories as $category) {
            DB::table('test_categories')->updateOrInsert(
                ['name' => $category['name']], 
                [
                    'icon' => $category['icon'],
                    'description' => $category['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}