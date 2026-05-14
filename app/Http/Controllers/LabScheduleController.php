<?php

namespace App\Http\Controllers; // تم تعديل الـ A لتصبح كبيرة

use App\Http\Requests\LabScheduleRequest;
use App\Http\Resources\LabScheduleResource;
use App\Services\LabScheduleService; // تم تعديل الـ A لتصبح كبيرة
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LabScheduleController extends Controller
{
    public function __construct(
        protected LabScheduleService $service
    ) {}

    public function index()
    {
        try {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            
            // تحقق ما إذا كان المستخدم يملك مختبراً لتفادي الـ Crash
            if (!$user->laboratory) {
                return response()->json(['message' => 'هذا المستخدم غير مرتبط بمختبر'], 404);
            }

            $labId = $user->laboratory->id;
            $schedules = $this->service->getSchedule($labId);

            return LabScheduleResource::collection($schedules);

        } catch (\Exception $e) {
            Log::error('LabSchedule index error: ' . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ ما'], 500);
        }
    }

    
    public function update(LabScheduleRequest $request, int $day_of_week)
{
    DB::beginTransaction();
    try {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->laboratory) {
            return response()->json(['message' => 'هذا المستخدم غير مرتبط بمختبر'], 404);
        }

        $labId = $user->laboratory->id; 

        $schedule = $this->service->updateSchedule(
            $labId,
            $day_of_week,
            $request->validated()
        );

        DB::commit();
        return new LabScheduleResource($schedule);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('LabSchedule update error: ' . $e->getMessage());
        return response()->json(['message' => 'حدث خطأ ما','error_detail' => $e->getMessage()], 500);
    }
}
}