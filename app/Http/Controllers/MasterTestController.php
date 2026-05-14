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
}