<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLab;
use App\Models\Laboratory;
use App\Services\ApiResponseService;
use App\Services\AppointmentService;
use App\Services\labService;
use Illuminate\Http\Request;

class LaboratoryController extends Controller
{

    protected $labService;
    protected $appointmentService;
    public function __construct(labService $labService,AppointmentService $appointmentService)
    {
        $this->labService = $labService;
        $this->appointmentService = $appointmentService;
    }


    public function store(CreateLab $request) {
        
        $data = $this->labService->createNewLabWithManager($request->validated());
        
        return ApiResponseService::success([$data], 'Lab and Manager created successfully');
    }


    public function getSlots(Request $request, $labId)
    {
        
        $date = $request->query('date', now()->format('Y-m-d'));

        try {
            $slots = $this->appointmentService->getAvailableSlots($labId, $date);
            
            return response()->json([
                'status' => 'success',
                'date' => $date,
                'data' => $slots
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }


    public function show($id)
    {
        $lab = Laboratory::findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $lab]);
    }

    // 2. قبول المختبر
    public function approve($id)
    {
        $lab = Laboratory::findOrFail($id);
        $lab->update(['status' => 'active']);
        return response()->json(['message' => 'تم تفعيل المختبر بنجاح']);
    }

    // 3. رفض المختبر
    public function reject($id)
    {
        $lab = Laboratory::findOrFail($id);
        $lab->update(['status' => 'rejected']);
        return response()->json(['message' => 'تم رفض طلب الانضمام']);
    }

    // 4. حظر أو تعطيل المختبر (أيقونة المنع الحمراء)
    public function block($id)
    {
        $lab = Laboratory::findOrFail($id);
        $lab->update(['status' => 'blocked']);
        return response()->json(['message' => 'تم حظر المختبر مؤقتاً']);
    }
}
