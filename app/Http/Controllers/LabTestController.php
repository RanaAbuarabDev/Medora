<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLabTestRequest;
use App\Models\Laboratory;
use App\Models\User;
use App\Services\ApiResponseService;

$user = \Illuminate\Support\Facades\Auth::user();

class LabTestController extends Controller
{
    


    public function store(StoreLabTestRequest $request)
    {
        
        $user = auth()->user();
        $labId = $user->lab_id; 

        if (!$labId) {
            return ApiResponseService::error('هذا المستخدم غير مرتبط بأي مخبر', 403);
        }

        $lab = Laboratory::findOrFail($labId);

    
        $lab->masterTests()->syncWithoutDetaching([
            $request->master_test_id => [
                'price' => $request->price,
                'is_available' => true
            ]
        ]);

        return ApiResponseService::success([], 'تم إضافة التحليل أو تحديث سعره في مخبرك بنجاح');
    }

   
    public function index()
    {
        $labId = auth()->user()->lab_id;
        $lab = Laboratory::with('masterTests')->findOrFail($labId);

        return ApiResponseService::success($lab->masterTests, 'قائمة تحاليل المخبر');
    }


    public function destroy($masterTestId)
    {
        $user = auth()->user();
        $labId = $user->laboratory_id;

        $lab = Laboratory::findOrFail($labId);
        $lab->masterTests()->detach($masterTestId);

        return ApiResponseService::success([], 'تم حذف التحليل من قائمة مخبرك بنجاح');
    }
}