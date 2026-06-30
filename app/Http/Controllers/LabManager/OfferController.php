<?php

namespace App\Http\Controllers\LabManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabManager\StoreOfferRequest;
use App\Http\Requests\LabManager\UpdateOfferRequest;
use App\Services\LabManager\OfferService;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;

class OfferController extends Controller
{
    protected OfferService $offerService;
    protected ApiResponseService $apiResponse;

    public function __construct(OfferService $offerService, ApiResponseService $apiResponse)
    {
        $this->offerService = $offerService;
        $this->apiResponse = $apiResponse;
    }

    /**
     * جلب كل العروض لمدير المخبر الحالي (عبر الـ Auth)
     */
    public function indexForAdmin(): JsonResponse
    {
        $labId = auth()->user()->lab_id; // حماية مطلقة
        $offers = $this->offerService->getLabOffersForAdmin($labId);
        return $this->apiResponse->success($offers, 'تم جلب عروض مختبركم بنجاح.');
    }

    /**
     * جلب عروض مخبر معين للمريض (عبر الـ URL لأن المريض زائر ويتصفح)
     */
    public function indexForPatient(int $labId): JsonResponse
    {
        $offers = $this->offerService->getActiveOffersForPatient($labId);
        return $this->apiResponse->success($offers, 'تم جلب العروض الحالية للمختبر بنجاح.');
    }

    /**
     * إنشاء عرض جديد
     */
    public function store(StoreOfferRequest $request): JsonResponse
    {
        $labId = auth()->user()->lab_id;
        $offer = $this->offerService->createOffer($request->validated(), $labId);
        return $this->apiResponse->success($offer, 'تم إنشاء العرض وتفعيله بنجاح.', 201);
    }

    /**
     * تعديل عرض
     */
    public function update(UpdateOfferRequest $request, int $id): JsonResponse
    {
        $labId = auth()->user()->lab_id;
        $updatedOffer = $this->offerService->updateOffer($id, $request->validated(), $labId);
        return $this->apiResponse->success($updatedOffer, 'تم تحديث بيانات العرض بنجاح.');
    }

    /**
     * حذف عرض
     */
    public function destroy(int $id): JsonResponse
    {
        $labId = auth()->user()->lab_id;
        $this->offerService->deleteOffer($id, $labId);
        return $this->apiResponse->success(null, 'تم حذف العرض بنجاح.');
    }
}