<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TestCategory;
use App\Models\MasterTest;

class MasterTestSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'دمويات (Hematology)' => [
                ['name' => 'تعداد الدم الكامل', 'short_name' => 'CBC', 'sample_type' => 'Whole Blood', 'unit' => 'Mixed', 'normal_range' => 'Hb: 12-16, WBC: 4k-11k'],
                ['name' => 'زمن البروترومبين', 'short_name' => 'PT', 'sample_type' => 'Plasma', 'unit' => 'Seconds', 'normal_range' => '11-13.5 sec'],
                ['name' => 'سرعة التثفل', 'short_name' => 'ESR', 'sample_type' => 'Whole Blood', 'unit' => 'mm/hr', 'normal_range' => '0-20'],
                ['name' => 'مؤشرات الكريات الحمر', 'short_name' => 'MCV/MCH/MCHC', 'sample_type' => 'Whole Blood', 'unit' => 'fL/pg/g/dL', 'normal_range' => 'MCV: 80-100'],
                ['name' => 'عرض توزيع الكريات الحمراء', 'short_name' => 'RDW', 'sample_type' => 'Whole Blood', 'unit' => '%', 'normal_range' => '11.5-14.5%'],
                ['name' => 'تعداد الصفيحات', 'short_name' => 'PLT', 'sample_type' => 'Whole Blood', 'unit' => '10^3/µL', 'normal_range' => '150-450k'],
                ['name' => 'تعداد الشبكية', 'short_name' => 'Reticulocyte', 'sample_type' => 'Whole Blood', 'unit' => '%', 'normal_range' => '0.5-2.5%'],
                ['name' => 'لطخة دم محيطية', 'short_name' => 'Blood Smear', 'sample_type' => 'Whole Blood', 'unit' => 'N/A', 'normal_range' => 'Normal Morphology'],
                ['name' => 'زمن النزف', 'short_name' => 'BT', 'sample_type' => 'Capillary Blood', 'unit' => 'Minutes', 'normal_range' => '2-7 min'],
                ['name' => 'زمن التخثر', 'short_name' => 'CT', 'sample_type' => 'Whole Blood', 'unit' => 'Minutes', 'normal_range' => '5-15 min'],
                ['name' => 'النسبة المعيارية الدولية', 'short_name' => 'INR', 'sample_type' => 'Plasma', 'unit' => 'Ratio', 'normal_range' => '0.8-1.2'],
            ],
            'كيمياء حيوية (Biochemistry)' => [
                ['name' => 'سكر الدم العشوائي', 'short_name' => 'RBS', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '< 140'],
                ['name' => 'الخضاب الغلوكوزي (التراكمي)', 'short_name' => 'HbA1c', 'sample_type' => 'Whole Blood', 'unit' => '%', 'normal_range' => '4-5.6%'],
                ['name' => 'اليوريا', 'short_name' => 'BUN', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '7-20'],
                ['name' => 'أنزيم الكبد ALT', 'short_name' => 'ALT (SGPT)', 'sample_type' => 'Serum', 'unit' => 'U/L', 'normal_range' => '7-55'],
                ['name' => 'أنزيم الكبد AST', 'short_name' => 'AST (SGOT)', 'sample_type' => 'Serum', 'unit' => 'U/L', 'normal_range' => '8-48'],
                ['name' => 'الفوسفاتاز القلوية', 'short_name' => 'ALP', 'sample_type' => 'Serum', 'unit' => 'U/L', 'normal_range' => '40-129'],
                ['name' => 'البروتين الكلي', 'short_name' => 'Total Protein', 'sample_type' => 'Serum', 'unit' => 'g/dL', 'normal_range' => '6.3-7.9'],
                ['name' => 'الألبومين', 'short_name' => 'Albumin', 'sample_type' => 'Serum', 'unit' => 'g/dL', 'normal_range' => '3.5-5.0'],
                ['name' => 'البيليروبين (الكلي والمباشر)', 'short_name' => 'Bilirubin T/D', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => 'Total < 1.2'],
                ['name' => 'الشحوم الثلاثية', 'short_name' => 'Triglycerides', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '< 150'],
                ['name' => 'الكوليسترول عالي الكثافة', 'short_name' => 'HDL', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '> 40'],
                ['name' => 'الكوليسترول منخفض الكثافة', 'short_name' => 'LDL', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '< 100'],
            ],
            'هرمونات (Hormones)' => [
                ['name' => 'هرمون الدرق الحر T3', 'short_name' => 'Free T3', 'sample_type' => 'Serum', 'unit' => 'pg/mL', 'normal_range' => '2.3-4.2'],
                ['name' => 'هرمون الدرق الحر T4', 'short_name' => 'Free T4', 'sample_type' => 'Serum', 'unit' => 'ng/dL', 'normal_range' => '0.8-1.8'],
                ['name' => 'الهرمون اللوتيني', 'short_name' => 'LH', 'sample_type' => 'Serum', 'unit' => 'mIU/mL', 'normal_range' => 'Variable'],
                ['name' => 'الهرمون المنبه للجريب', 'short_name' => 'FSH', 'sample_type' => 'Serum', 'unit' => 'mIU/mL', 'normal_range' => 'Variable'],
                ['name' => 'تستوستيرون', 'short_name' => 'Testosterone', 'sample_type' => 'Serum', 'unit' => 'ng/dL', 'normal_range' => '300-1000 (Male)'],
                ['name' => 'إستروجين', 'short_name' => 'Estrogen (E2)', 'sample_type' => 'Serum', 'unit' => 'pg/mL', 'normal_range' => 'Variable'],
                ['name' => 'بروجسترون', 'short_name' => 'Progesterone', 'sample_type' => 'Serum', 'unit' => 'ng/mL', 'normal_range' => 'Variable'],
                ['name' => 'الأنسولين', 'short_name' => 'Insulin', 'sample_type' => 'Serum', 'unit' => 'µU/mL', 'normal_range' => '2.6-24.9'],
            ],
            'أحياء دقيقة (Microbiology)' => [
                ['name' => 'زرع دم', 'short_name' => 'Blood Culture', 'sample_type' => 'Blood', 'unit' => 'N/A', 'normal_range' => 'No Growth'],
                ['name' => 'زرع قشع', 'short_name' => 'Sputum Culture', 'sample_type' => 'Sputum', 'unit' => 'N/A', 'normal_range' => 'Normal Flora'],
                ['name' => 'تلوين غرام', 'short_name' => 'Gram Stain', 'sample_type' => 'Swab/Sputum', 'unit' => 'N/A', 'normal_range' => 'N/A'],
                ['name' => 'تلوين عصيات السل', 'short_name' => 'ZN Stain', 'sample_type' => 'Sputum', 'unit' => 'N/A', 'normal_range' => 'Negative'],
            ],
            'فيروسات / مناعة (Virology/Immunology)' => [
                ['name' => 'فيروس كورونا PCR', 'short_name' => 'COVID-19 PCR', 'sample_type' => 'Swab', 'unit' => 'N/A', 'normal_range' => 'Negative'],
                ['name' => 'حمى الضنك', 'short_name' => 'Dengue NS1', 'sample_type' => 'Serum', 'unit' => 'Index', 'normal_range' => 'Negative'],
                ['name' => 'فيروس إبشتاين بار', 'short_name' => 'EBV', 'sample_type' => 'Serum', 'unit' => 'Index', 'normal_range' => 'Negative'],
            ],
            'تحاليل البراز (Stool Analysis)' => [
                ['name' => 'جرثومة المعدة في البراز', 'short_name' => 'H. pylori Ag', 'sample_type' => 'Stool', 'unit' => 'N/A', 'normal_range' => 'Negative'],
                ['name' => 'فحص كالبكتين البراز', 'short_name' => 'Calprotectin', 'sample_type' => 'Stool', 'unit' => 'µg/g', 'normal_range' => '< 50'],
            ],
            'تحاليل مناعية (Serology/Immunology)' => [
                ['name' => 'الأضداد النووية', 'short_name' => 'ANA', 'sample_type' => 'Serum', 'unit' => 'Titer', 'normal_range' => 'Negative'],
                ['name' => 'عامل ASO', 'short_name' => 'ASO Titer', 'sample_type' => 'Serum', 'unit' => 'IU/mL', 'normal_range' => '< 200'],
                ['name' => 'المتممة C3 / C4', 'short_name' => 'C3 / C4', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => 'C3: 90-180'],
            ],
            'تحاليل جينية (Genetic Tests)' => [
                ['name' => 'تحري الثلاسيميا', 'short_name' => 'Thalassemia Sc', 'sample_type' => 'Whole Blood', 'unit' => 'N/A', 'normal_range' => 'Normal'],
                ['name' => 'اختبار التمنجل', 'short_name' => 'Sickle Cell', 'sample_type' => 'Whole Blood', 'unit' => 'N/A', 'normal_range' => 'Negative'],
            ],
            'تحاليل المعادن والعناصر النزرة (Minerals & Trace Elements)' => [
                ['name' => 'مغنيزيوم المصل', 'short_name' => 'Magnesium', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '1.7-2.2'],
                ['name' => 'الفوسفور', 'short_name' => 'Phosphorus', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '2.5-4.5'],
                ['name' => 'فيريتين (مخزون الحديد)', 'short_name' => 'Ferritin', 'sample_type' => 'Serum', 'unit' => 'ng/mL', 'normal_range' => '30-400'],
            ],
            'تحاليل وظائف القلب (Cardiac Markers)' => [
                ['name' => 'بروتين BNP للقلب', 'short_name' => 'BNP', 'sample_type' => 'Plasma', 'unit' => 'pg/mL', 'normal_range' => '< 100'],
                ['name' => 'نازعة هيدروجين اللاكتات', 'short_name' => 'LDH', 'sample_type' => 'Serum', 'unit' => 'U/L', 'normal_range' => '140-280'],
            ],
            'تحاليل الحمل (Pregnancy Tests)' => [
                ['name' => 'اختبار حمل منزلي (بول)', 'short_name' => 'Urine HCG', 'sample_type' => 'Urine', 'unit' => 'N/A', 'normal_range' => 'Negative'],
            ],
            'تحاليل الحساسية (Allergy Tests)' => [
                ['name' => 'لوحة حساسية الأطعمة', 'short_name' => 'Food Allergy', 'sample_type' => 'Serum', 'unit' => 'Class', 'normal_range' => 'Class 0'],
                ['name' => 'لوحة الحساسية التنفسية', 'short_name' => 'Respiratory Al', 'sample_type' => 'Serum', 'unit' => 'Class', 'normal_range' => 'Class 0'],
            ],
        ];

        foreach ($data as $categoryName => $tests) {
            $category = TestCategory::where('name', $categoryName)->first();

            if ($category) {
                foreach ($tests as $testData) {
                    MasterTest::updateOrCreate(
                        ['name' => $testData['name'], 'test_category_id' => $category->id],
                        [
                            'short_name' => $testData['short_name'],
                            'sample_type' => $testData['sample_type'],
                            'unit' => $testData['unit'],
                            'normal_range' => $testData['normal_range'],
                        ]
                    );
                }
            }
        }
    }
}