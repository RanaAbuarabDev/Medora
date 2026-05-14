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
                ['name' => 'تعداد الدم الكامل', 'short_name' => 'CBC', 'sample_type' => 'Whole Blood', 'unit' => 'Mixed', 'normal_range' => 'Hb: 12-16, WBC: 4k-11k', 'description' => 'فحص شامل لخلايا الدم الحمراء والبيضاء والصفائح الدموية.'],
                ['name' => 'زمن البروترومبين', 'short_name' => 'PT', 'sample_type' => 'Plasma', 'unit' => 'Seconds', 'normal_range' => '11-13.5 sec', 'description' => 'قياس سرعة تجلط الدم وتقييم عوامل التخثر.'],
                ['name' => 'سرعة التثفل', 'short_name' => 'ESR', 'sample_type' => 'Whole Blood', 'unit' => 'mm/hr', 'normal_range' => '0-20', 'description' => 'مؤشر غير نوعي للكشف عن وجود التهابات أو عدوى في الجسم.'],
                ['name' => 'مؤشرات الكريات الحمر', 'short_name' => 'MCV/MCH/MCHC', 'sample_type' => 'Whole Blood', 'unit' => 'fL/pg/g/dL', 'normal_range' => 'MCV: 80-100', 'description' => 'تحديد حجم وخصائص كريات الدم الحمراء لتشخيص أنواع فقر الدم.'],
                ['name' => 'عرض توزيع الكريات الحمراء', 'short_name' => 'RDW', 'sample_type' => 'Whole Blood', 'unit' => '%', 'normal_range' => '11.5-14.5%', 'description' => 'قياس مدى التباين في حجم خلايا الدم الحمراء.'],
                ['name' => 'تعداد الصفيحات', 'short_name' => 'PLT', 'sample_type' => 'Whole Blood', 'unit' => '10^3/µL', 'normal_range' => '150-450k', 'description' => 'حساب عدد الصفائح المسؤول عن وقف النزيف وتجلط الدم.'],
                ['name' => 'تعداد الشبكية', 'short_name' => 'Reticulocyte', 'sample_type' => 'Whole Blood', 'unit' => '%', 'normal_range' => '0.5-2.5%', 'description' => 'تقييم قدرة نخاع العظم على إنتاج خلايا دم حمراء جديدة.'],
                ['name' => 'لطخة دم محيطية', 'short_name' => 'Blood Smear', 'sample_type' => 'Whole Blood', 'unit' => 'N/A', 'normal_range' => 'Normal Morphology', 'description' => 'فحص مجهري لشكل خلايا الدم للكشف عن الأمراض الدموية.'],
                ['name' => 'زمن النزف', 'short_name' => 'BT', 'sample_type' => 'Capillary Blood', 'unit' => 'Minutes', 'normal_range' => '2-7 min', 'description' => 'اختبار يقيس الوقت الذي يستغرقه الجرح الصغير للتوقف عن النزيف.'],
                ['name' => 'زمن التخثر', 'short_name' => 'CT', 'sample_type' => 'Whole Blood', 'unit' => 'Minutes', 'normal_range' => '5-15 min', 'description' => 'قياس الوقت اللازم لتكون الجلطة الدموية خارج الجسم.'],
                ['name' => 'النسبة المعيارية الدولية', 'short_name' => 'INR', 'sample_type' => 'Plasma', 'unit' => 'Ratio', 'normal_range' => '0.8-1.2', 'description' => 'معيار عالمي لمتابعة فعالية أدوية ميوعة الدم وتجلطه.'],
            ],
            'كيمياء حيوية (Biochemistry)' => [
                ['name' => 'سكر الدم العشوائي', 'short_name' => 'RBS', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '< 140', 'description' => 'قياس مستوى الغلوكوز في الدم في أي وقت خلال اليوم.'],
                ['name' => 'الخضاب الغلوكوزي (التراكمي)', 'short_name' => 'HbA1c', 'sample_type' => 'Whole Blood', 'unit' => '%', 'normal_range' => '4-5.6%', 'description' => 'معدل سكر الدم خلال الثلاثة أشهر الماضية.'],
                ['name' => 'اليوريا', 'short_name' => 'BUN', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '7-20', 'description' => 'قياس فضلات النيتروجين في الدم لتقييم وظائف الكلى.'],
                ['name' => 'أنزيم الكبد ALT', 'short_name' => 'ALT (SGPT)', 'sample_type' => 'Serum', 'unit' => 'U/L', 'normal_range' => '7-55', 'description' => 'أنزيم حساس يستخدم للكشف عن تلف خلايا الكبد.'],
                ['name' => 'أنزيم الكبد AST', 'short_name' => 'AST (SGOT)', 'sample_type' => 'Serum', 'unit' => 'U/L', 'normal_range' => '8-48', 'description' => 'أنزيم موجود في الكبد والقلب، يشير ارتفاعه إلى إصابة نسيجية.'],
                ['name' => 'الفوسفاتاز القلوية', 'short_name' => 'ALP', 'sample_type' => 'Serum', 'unit' => 'U/L', 'normal_range' => '40-129', 'description' => 'أنزيم مرتبط بوظائف الكبد والمرارة وصحة العظام.'],
                ['name' => 'البروتين الكلي', 'short_name' => 'Total Protein', 'sample_type' => 'Serum', 'unit' => 'g/dL', 'normal_range' => '6.3-7.9', 'description' => 'قياس مجموع الألبومين والغلوبولين في الدم.'],
                ['name' => 'الألبومين', 'short_name' => 'Albumin', 'sample_type' => 'Serum', 'unit' => 'g/dL', 'normal_range' => '3.5-5.0', 'description' => 'البروتين الرئيسي المصنع في الكبد ويحافظ على ضغط الدم.'],
                ['name' => 'البيليروبين (الكلي والمباشر)', 'short_name' => 'Bilirubin T/D', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => 'Total < 1.2', 'description' => 'قياس الصباغ الصفراوي لتشخيص اليرقان وأمراض الكبد.'],
                ['name' => 'الشحوم الثلاثية', 'short_name' => 'Triglycerides', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '< 150', 'description' => 'قياس الدهون المخزنة في الجسم لاستخدامها كطاقة.'],
                ['name' => 'الكوليسترول عالي الكثافة', 'short_name' => 'HDL', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '> 40', 'description' => 'الكوليسترول "الجيد" الذي يساعد في حماية القلب من التصلب.'],
                ['name' => 'الكوليسترول منخفض الكثافة', 'short_name' => 'LDL', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '< 100', 'description' => 'الكوليسترول "الضار" الذي يرتبط بزيادة خطر أمراض الشرايين.'],
            ],
            'هرمونات (Hormones)' => [
                ['name' => 'هرمون الدرق الحر T3', 'short_name' => 'Free T3', 'sample_type' => 'Serum', 'unit' => 'pg/mL', 'normal_range' => '2.3-4.2', 'description' => 'قياس الشكل النشط لهرمون الترييودوثيرونين لتقييم نشاط الغدة.'],
                ['name' => 'هرمون الدرق الحر T4', 'short_name' => 'Free T4', 'sample_type' => 'Serum', 'unit' => 'ng/dL', 'normal_range' => '0.8-1.8', 'description' => 'الهرمون الأساسي الذي تفرزه الغدة الدرقية لتنظيم الاستقلاب.'],
                ['name' => 'الهرمون اللوتيني', 'short_name' => 'LH', 'sample_type' => 'Serum', 'unit' => 'mIU/mL', 'normal_range' => 'Variable', 'description' => 'هرمون ينظم الدورة الشهرية والتبويض عند النساء والخصوبة عند الرجال.'],
                ['name' => 'الهرمون المنبه للجريب', 'short_name' => 'FSH', 'sample_type' => 'Serum', 'unit' => 'mIU/mL', 'normal_range' => 'Variable', 'description' => 'المسؤول عن نمو البويضات في المبيضين وإنتاج النطاف في الخصيتين.'],
                ['name' => 'تستوستيرون', 'short_name' => 'Testosterone', 'sample_type' => 'Serum', 'unit' => 'ng/dL', 'normal_range' => '300-1000 (Male)', 'description' => 'هرمون الذكورة الرئيسي المسؤول عن الخصائص الجنسية.'],
                ['name' => 'إستروجين', 'short_name' => 'Estrogen (E2)', 'sample_type' => 'Serum', 'unit' => 'pg/mL', 'normal_range' => 'Variable', 'description' => 'هرمون الأنوثة الأساسي لتنظيم الجهاز التناسلي والدورة الشهرية.'],
                ['name' => 'بروجسترون', 'short_name' => 'Progesterone', 'sample_type' => 'Serum', 'unit' => 'ng/mL', 'normal_range' => 'Variable', 'description' => 'هرمون مهم لتهيئة الرحم للحمل والمحافظة عليه.'],
                ['name' => 'الأنسولين', 'short_name' => 'Insulin', 'sample_type' => 'Serum', 'unit' => 'µU/mL', 'normal_range' => '2.6-24.9', 'description' => 'الهرمون المنظم لمستويات السكر في الدم.'],
            ],
            'أحياء دقيقة (Microbiology)' => [
                ['name' => 'زرع دم', 'short_name' => 'Blood Culture', 'sample_type' => 'Blood', 'unit' => 'N/A', 'normal_range' => 'No Growth', 'description' => 'الكشف عن وجود بكتيريا أو فطريات في تيار الدم.'],
                ['name' => 'زرع قشع', 'short_name' => 'Sputum Culture', 'sample_type' => 'Sputum', 'unit' => 'N/A', 'normal_range' => 'Normal Flora', 'description' => 'تحديد مسببات العدوى في الجهاز التنفسي السفلي.'],
                ['name' => 'تلوين غرام', 'short_name' => 'Gram Stain', 'sample_type' => 'Swab/Sputum', 'unit' => 'N/A', 'normal_range' => 'N/A', 'description' => 'طريقة سريعة لتصنيف البكتيريا ومعرفة نوعها الأولي.'],
                ['name' => 'تلوين عصيات السل', 'short_name' => 'ZN Stain', 'sample_type' => 'Sputum', 'unit' => 'N/A', 'normal_range' => 'Negative', 'description' => 'صبغة خاصة للكشف عن بكتيريا المسببة لمرض السل.'],
            ],
            'فيروسات / مناعة (Virology/Immunology)' => [
                ['name' => 'فيروس كورونا PCR', 'short_name' => 'COVID-19 PCR', 'sample_type' => 'Swab', 'unit' => 'N/A', 'normal_range' => 'Negative', 'description' => 'الكشف النوعي عن المادة الوراثية لفيروس سارس-كوف-2.'],
                ['name' => 'حمى الضنك', 'short_name' => 'Dengue NS1', 'sample_type' => 'Serum', 'unit' => 'Index', 'normal_range' => 'Negative', 'description' => 'الكشف المبكر عن بروتين فيروس حمى الضنك في الدم.'],
                ['name' => 'فيروس إبشتاين بار', 'short_name' => 'EBV', 'sample_type' => 'Serum', 'unit' => 'Index', 'normal_range' => 'Negative', 'description' => 'تشخيص العدوى بفيروس EBV المسبب لداء الوحيدات الخمجية.'],
            ],
            'تحاليل البراز (Stool Analysis)' => [
                ['name' => 'جرثومة المعدة في البراز', 'short_name' => 'H. pylori Ag', 'sample_type' => 'Stool', 'unit' => 'N/A', 'normal_range' => 'Negative', 'description' => 'تحري وجود مستضدات بكتيريا الملوية البوابية في البراز.'],
                ['name' => 'فحص كالبكتين البراز', 'short_name' => 'Calprotectin', 'sample_type' => 'Stool', 'unit' => 'µg/g', 'normal_range' => '< 50', 'description' => 'مؤشر لالتهاب الأمعاء والتمييز بين القولون العصبي والالتهابي.'],
            ],
            'تحاليل مناعية (Serology/Immunology)' => [
                ['name' => 'الأضداد النووية', 'short_name' => 'ANA', 'sample_type' => 'Serum', 'unit' => 'Titer', 'normal_range' => 'Negative', 'description' => 'فحص أولي للكشف عن أمراض المناعة الذاتية مثل الذئبة الحمامية.'],
                ['name' => 'عامل ASO', 'short_name' => 'ASO Titer', 'sample_type' => 'Serum', 'unit' => 'IU/mL', 'normal_range' => '< 200', 'description' => 'قياس الأجسام المضادة الناتجة عن الإصابة بالمكورات العقدية.'],
                ['name' => 'المتممة C3 / C4', 'short_name' => 'C3 / C4', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => 'C3: 90-180', 'description' => 'تقييم كفاءة البروتينات المناعية في حالات الالتهاب والمناعة الذاتية.'],
            ],
            'تحاليل جينية (Genetic Tests)' => [
                ['name' => 'تحري الثلاسيميا', 'short_name' => 'Thalassemia Sc', 'sample_type' => 'Whole Blood', 'unit' => 'N/A', 'normal_range' => 'Normal', 'description' => 'مسح جيني للكشف عن فقر دم البحر المتوسط الوراثي.'],
                ['name' => 'اختبار التمنجل', 'short_name' => 'Sickle Cell', 'sample_type' => 'Whole Blood', 'unit' => 'N/A', 'normal_range' => 'Negative', 'description' => 'تحري وجود خضاب الدم المنجلي (HbS) المسبب لفقر الدم المنجلي.'],
            ],
            'تحاليل المعادن والعناصر النزرة (Minerals & Trace Elements)' => [
                ['name' => 'مغنيزيوم المصل', 'short_name' => 'Magnesium', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '1.7-2.2', 'description' => 'قياس مستوى المغنيزيوم الضروري للأعصاب والعضلات.'],
                ['name' => 'الفوسفور', 'short_name' => 'Phosphorus', 'sample_type' => 'Serum', 'unit' => 'mg/dL', 'normal_range' => '2.5-4.5', 'description' => 'معدن أساسي لصحة العظام والأسنان وتوازن الطاقة.'],
                ['name' => 'فيريتين (مخزون الحديد)', 'short_name' => 'Ferritin', 'sample_type' => 'Serum', 'unit' => 'ng/mL', 'normal_range' => '30-400', 'description' => 'قياس كمية الحديد المخزنة في الجسم.'],
            ],
            'تحاليل وظائف القلب (Cardiac Markers)' => [
                ['name' => 'بروتين BNP للقلب', 'short_name' => 'BNP', 'sample_type' => 'Plasma', 'unit' => 'pg/mL', 'normal_range' => '< 100', 'description' => 'مؤشر حيوي للكشف عن فشل القلب الاحتقاني.'],
                ['name' => 'نازعة هيدروجين اللاكتات', 'short_name' => 'LDH', 'sample_type' => 'Serum', 'unit' => 'U/L', 'normal_range' => '140-280', 'description' => 'أنزيم يرتفع عند حدوث تلف في أنسجة القلب أو الرئة أو الدم.'],
            ],
            'تحاليل الحمل (Pregnancy Tests)' => [
                ['name' => 'اختبار حمل منزلي (بول)', 'short_name' => 'Urine HCG', 'sample_type' => 'Urine', 'unit' => 'N/A', 'normal_range' => 'Negative', 'description' => 'كشف سريع عن هرمون الحمل في البول.'],
            ],
            'تحاليل الحساسية (Allergy Tests)' => [
                ['name' => 'لوحة حساسية الأطعمة', 'short_name' => 'Food Allergy', 'sample_type' => 'Serum', 'unit' => 'Class', 'normal_range' => 'Class 0', 'description' => 'تحري التحسس تجاه مجموعة متنوعة من الأغذية الشائعة.'],
                ['name' => 'لوحة الحساسية التنفسية', 'short_name' => 'Respiratory Al', 'sample_type' => 'Serum', 'unit' => 'Class', 'normal_range' => 'Class 0', 'description' => 'تحري التحسس تجاه مسببات الحساسية المنقولة بالهواء كالغبار والطلع.'],
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
                            'description' => $testData['description'], // إضافة الحقل هنا
                        ]
                    );
                }
            }
        }
    }
}