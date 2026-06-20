<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterTest;
use App\Http\Requests\StoreMasterTestRequest;
use App\Http\Requests\UpdateMasterTestRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class MasterTestController extends Controller
{
    
    public function index()
    {

        $tests = MasterTest::with('category:id,name')->get();
        return ApiResponseService::success($tests, 'تم جلب قائمة التحاليل بنجاح');
    }

 
    public function store(StoreMasterTestRequest $request)
    {
        $test = MasterTest::create($request->validated());
        return ApiResponseService::success($test, 'تم إضافة التحليل الجديد بنجاح', 201);
    }

   
    public function show($id)
    {
        $test = MasterTest::with('category')->findOrFail($id);
        return ApiResponseService::success($test, 'تم جلب تفاصيل التحليل');
    }

    public function update(UpdateMasterTestRequest $request, $id)
    {
        $test = MasterTest::findOrFail($id);
        $test->update($request->validated());
        return ApiResponseService::success($test, 'تم تحديث بيانات التحليل بنجاح');
    }

   
    public function destroy($id)
    {
        $test = MasterTest::findOrFail($id);
        $test->delete();
        return ApiResponseService::success([], 'تم حذف التحليل من المنصة بنجاح');
    }


    public function getByCategory($categoryId)
    {
        $tests = MasterTest::where('test_category_id', $categoryId)->get();
        return ApiResponseService::success($tests, 'تم جلب تحاليل الفئة بنجاح');
    }


    public function searchTest(Request $request, $testId)
    {
        
        $sortBy = $request->query('sort_by', 'all'); 

        $test = MasterTest::with(['laboratories' => function($query) use ($sortBy) {
            $query->where('lab_tests.is_available', true)
                ->withAvg('ratings', 'rating')
                ->withCount('ratings')
                ->select('laboratories.*', 'lab_tests.price', 'lab_tests.estimated_time_hours');

            if ($sortBy === 'cheapest') {
                $query->orderBy('lab_tests.price', 'asc')->limit(5);
            } 
            elseif ($sortBy === 'top_rated') {
               
                $query->orderBy(
                    \App\Models\LabRating::selectRaw('avg(rating)')
                        ->whereColumn('lab_id', 'laboratories.id'), 
                    'desc'
                )->limit(5);
            }
            else {
             
                $query->latest();
            }

        }])->findOrFail($testId);

        // 3. إرجاع النتيجة كـ JSON
        return ApiResponseService::success($test, 'تم جلب المخابر وترتيبها بنجاح');
    }



    
    public function searchMultipleTests(Request $request)
    {
        // 1. الفاليديشن
        $request->validate([
            'test_ids'   => 'required|array|min:1',
            'test_ids.*' => 'integer|exists:master_tests,id',
            'sort_by'    => 'nullable|string|in:all,cheapest,top_rated',
        ]);

        $testIds = $request->input('test_ids');
        $sortBy  = $request->query('sort_by', 'all');
        $totalRequestedTests = count($testIds);

        // 2. الاستعلام الرئيسي
        $laboratoriesQuery = \App\Models\Laboratory::query()
            ->where('status', 'Active')
            
            // استخدام الجدول الوسيط مباشرة بداخل الـ whereHas للتحقق من التقاطع بدقة
            ->whereHas('masterTests', function($query) use ($testIds) {
                $query->whereIn('master_tests.id', $testIds)
                    ->where('lab_tests.is_available', true);
            }, '=', $totalRequestedTests)
            
            // حساب التقييمات
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            
            // شحن العلاقة مع تحديد حقول الجدول الوسيط (pivot) بدقة
            ->with(['masterTests' => function($query) use ($testIds) {
                $query->whereIn('master_tests.id', $testIds)
                    ->withPivot('price', 'estimated_time_hours'); // 👈 تأكيد شحن حقول الجدول الوسيط
            }]);

        // 3. الترتيب الذكي
        if ($sortBy === 'cheapest') {
            // ترتيب مستقر وآمن عبر الـ Subquery للجدول الوسيط lab_tests
            $laboratoriesQuery->withSum(['masterTests as total_package_price' => function($query) use ($testIds) {
                $query->whereIn('master_tests.id', $testIds);
            }], 'lab_tests.price') // لارافل سيقترح اسم العمود الافتراضي: total_package_price
            ->orderBy('total_package_price', 'asc');
            
        } elseif ($sortBy === 'top_rated') {
            $laboratoriesQuery->orderBy('ratings_avg_rating', 'desc');
        } else {
            $laboratoriesQuery->latest();
        }

        $laboratories = $laboratoriesQuery->get();

        // 4. بناء الـ Response المتوافق بالملّي مع البيانات الحقيقية لجدولكِ
        $result = $laboratories->map(function($lab) {
            return [
                'id'             => $lab->id,
                'name'           => $lab->name,
                'address'        => $lab->address,
                'avatar'         => $lab->avatar,
                'average_rating' => $lab->ratings_avg_rating ? round($lab->ratings_avg_rating, 1) : 0,
                'ratings_count'  => $lab->ratings_count,
                
                // حساب المجموع الإجمالي الفعلي من حقول الـ pivot المشحونة
                'total_price'    => $lab->masterTests->sum(function($test) {
                    return $test->pivot->price;
                }),
                
                'tests'          => $lab->masterTests->map(function($test) {
                    return [
                        'id'                   => $test->id,
                        'name'                 => $test->name,
                        'price'                => $test->pivot->price, // استخراج السعر من جدول الـ lab_tests المظهر في صورتكِ
                        'estimated_time_hours' => $test->pivot->estimated_time_hours,
                    ];
                }),
            ];
        });

        return ApiResponseService::success($result, 'تم جلب المخابر المشتركة بنجاح.');
    }
}