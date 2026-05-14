<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLabRatingRequest;
use App\Models\LabRating;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabRatingController extends Controller
{
    public function store(StoreLabRatingRequest $request)
    {
        
        $data = $request->validated();

        
        $appointment = \App\Models\Appointment::where('id', $data['appointment_id'])
            ->where('user_id', Auth::id())
            ->where('lab_id', $data['lab_id'])
            ->where('status', 'completed') 
            ->first();

        if (!$appointment) {
            return ApiResponseService::error('لا يمكنك تقييم هذا الحجز. قد يكون غير مكتمل أو لا يخصك.', 403);
        }

       
        $rating = LabRating::create([
            'user_id'        => Auth::id(),
            'lab_id'         => $data['lab_id'],
            'appointment_id' => $data['appointment_id'],
            'rating'         => $data['rating'],
            'comment'        => $data['comment'] ?? null,
        ]);

        return ApiResponseService::success($rating, 'شكراً لتقييمك، رأيك يهمنا!');
    }


    
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            return ApiResponseService::error('غير مسموح لك بالوصول لهذه البيانات', 403);
        }
        $ratings = LabRating::with(['user:id,name', 'laboratory:id,name'])->latest()->get();
        return ApiResponseService::success($ratings, 'All ratings retrieved successfully');
    }

    
    public function getLabRatings($labId)
    {
        $ratings = LabRating::where('lab_id', $labId)
            ->with('user:id,name')
            ->latest()
            ->paginate(10); 

        return ApiResponseService::success($ratings, 'Lab ratings retrieved successfully');
    }
}
