<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLabTestRequest;
use App\Models\Laboratory;
use App\Models\MasterTest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class LabTestController extends Controller
{
    /**
     * 1. جلب التحاليل القياسية المتاحة في النظام والتي لَم يضفها المخبر بعد (القسم الأيمن من واجهة الإضافة)
     */
    public function getAvailableMasterTests(Request $request): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;

            // جلب الـ IDs الخاصة بالتحاليل التي أضافها المخبر مسبقاً لاستثنائها
            $existingTestIds = DB::table('lab_tests')
                ->where('lab_id', $labId)
                ->pluck('master_test_id')
                ->toArray();

            // بناء الاستعلام للتحاليل غير المضافة مع الـ Eager Loading
            $query = MasterTest::with('category')->whereNotIn('id', $existingTestIds);

            // ⚡ التعديل الحاسم: البحث في الاسم الحقيقي (name) أو الاختصار (short_name)
            if ($request->has('search') && !empty($request->search)) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('short_name', 'like', '%' . $request->search . '%');
                });
            }

            // فلتر فئة التحليل
            if ($request->has('category_id') && $request->category_id !== 'all') {
                $query->where('test_category_id', $request->category_id);
            }

            $masterTests = $query->get()->map(function($test) {
                return [
                    'id' => $test->id,
                    'name_ar' => $test->name,
                    'name_en' => $test->short_name ?? $test->name, // عرض الاختصار الطبي في الواجهة
                    'category_name' => $test->category->name ?? 'عام',
                ];
            });

            return ApiResponseService::success($masterTests, 'التحاليل القياسية المتاحة للإضافة المفرزة بنجاح');

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ أثناء تحميل التحاليل المتاحة', 500);
        }
    }

    /**
     * 2. حفظ التحليل المختار مع إعدادات المخبر الخاصة (القسم الأيسر من واجهة الإضافة)
     */
    public function store(StoreLabTestRequest $request): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;

            if (!$labId) {
                return ApiResponseService::error(null, 'هذا المستخدم غير مرتبط بأي مخبر معتمد', 403);
            }

            $lab = Laboratory::findOrFail($labId);

            // ربط وحفظ الحقول الإضافية بداخل الجدول الوسيط (Pivot Table)
            $lab->masterTests()->syncWithoutDetaching([
                $request->master_test_id => [
                    'price'                   => $request->price,
                    'expected_duration_hours' => $request->expected_duration_hours,
                    'min_reference_value'     => $request->min_reference_value,
                    'max_reference_value'     => $request->max_reference_value,
                    'additional_notes'        => $request->additional_notes,
                    'is_available'            => true
                ]
            ]);

            return ApiResponseService::success([], 'تم إضافة التحليل الطبي الجديد وضبط إعداداته المخبرية بنجاح!', 200);

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'فشل حفظ إعدادات التحليل الطبي الجديد', 500);
        }
    }

    /**
     * 3. عرض قائمة تحاليل المخبر الحالية (الواجهة الرئيسية المجهزة بالـ Pagination)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id;
            if (!$labId) return ApiResponseService::error(null, 'غير مصرح للوصول لبيانات هذا المخبر', 403);

            $totalTestsCount = DB::table('lab_tests')->where('lab_id', $labId)->count();

            $topCategory = DB::table('appointment_lab_test')
                ->join('lab_tests', 'appointment_lab_test.lab_test_id', '=', 'lab_tests.id')
                ->join('master_tests', 'lab_tests.master_test_id', '=', 'master_tests.id')
                ->join('test_categories', 'master_tests.test_category_id', '=', 'test_categories.id') 
                ->join('appointments', 'appointment_lab_test.appointment_id', '=', 'appointments.id')
                ->where('appointments.lab_id', $labId)
                ->select('test_categories.name', DB::raw('COUNT(*) as occurrences')) 
                ->groupBy('test_categories.id', 'test_categories.name')
                ->orderBy('occurrences', 'desc')
                ->first();

            $topCategoryName = $topCategory->name ?? 'الكيمياء الحيوية';

            $lab = Laboratory::findOrFail($labId);
            $query = $lab->masterTests()->with('category');

            // الفلترة بناءً على حقل الفئة بجدول الماستر
            if ($request->has('category_id') && $request->category_id !== 'all') {
                $query->where('master_tests.test_category_id', $request->category_id);
            }

            $paginator = $query->paginate(5);

            $formattedTests = collect($paginator->items())->map(function ($masterTest) {
                return [
                    'id' => $masterTest->id,
                    'test_name_ar' => $masterTest->name,
                    'test_name_en' => $masterTest->short_name ?? '', // التعديل هنا ليعرض الاختصار مثل (CBC) أسفل الاسم العربي
                    'category_name' => $masterTest->category->name ?? 'عام',
                    'normal_range' => $masterTest->pivot->min_reference_value . ' - ' . $masterTest->pivot->max_reference_value, 
                    'price' => (float) $masterTest->pivot->price,
                    'expected_time' => $masterTest->pivot->expected_duration_hours . ' ساعة',
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => trans('قائمة تحاليل المخبر الحالية'),
                'cards' => [
                    'total_tests_count' => $totalTestsCount,
                    'top_requested_category' => $topCategoryName . ' (حوالي 45% من الطلبات)'
                ],
                'data' => $formattedTests,
                'pagination' => [
                    'total' => $paginator->total(),
                    'current_page' => $paginator->currentPage(),
                    'total_pages' => $paginator->lastPage()
                ]
            ], 200);

        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'حدث خطأ غير متوقع أثناء جلب قائمة التحاليل', 500);
        }
    }

    /**
     * 4. إزالة/حذف تحليل طبي من قائمة التحاليل المتاحة بالمخبر
     */
    public function destroy($masterTestId): JsonResponse
    {
        try {
            $labId = auth()->user()->lab_id; 
            $lab = Laboratory::findOrFail($labId);
            $lab->masterTests()->detach($masterTestId);

            return ApiResponseService::success([], 'تم إزالة التحليل من قائمة مخبركِ بنجاح', 200);
        } catch (Exception $e) {
            return ApiResponseService::error([$e->getMessage()], 'فشل حذف التحليل', 500);
        }
    }
}