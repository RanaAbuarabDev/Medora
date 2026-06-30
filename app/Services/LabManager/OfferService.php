<?php

namespace App\Services\LabManager;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Collection;

class OfferService
{
    /**
     * جلب عروض المختبر بالكامل للوحة المدير
     */
    public function getLabOffersForAdmin(int $labId): Collection
    {
        return Offer::where('lab_id', $labId)->latest()->get();
    }

    /**
     * جلب العروض النشطة اليوم فقط للمريض
     */
    public function getActiveOffersForPatient(int $labId): Collection
    {
        return Offer::where('lab_id', $labId)->activeToday()->get();
    }

    /**
     * إنشاء العرض وحقن الـ lab_id المأخوذ من الـ Auth بأمان
     */
    public function createOffer(array $data, int $labId): Offer
    {
        $data['lab_id'] = $labId;
        return Offer::create($data);
    }

    /**
     * تحديث العرض مع التحقق الأمني من الـ lab_id
     */
    public function updateOffer(int $id, array $data, int $labId): Offer
    {
        $offer = Offer::where('id', $id)->where('lab_id', $labId)->firstOrFail();
        $offer->update($data);
        return $offer;
    }

    /**
     * حذف العرض مع التحقق الأمني من الـ lab_id
     */
    public function deleteOffer(int $id, int $labId): bool
    {
        $offer = Offer::where('id', $id)->where('lab_id', $labId)->firstOrFail();
        return $offer->delete();
    }
}