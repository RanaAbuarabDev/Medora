<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $lab_id
 * @property int $master_test_id
 * @property numeric $price
 * @property int $is_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereLabId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereMasterTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LabTest extends Model
{
    public function parameters()
    {
        return $this->hasMany(TestParameter::class, 'lab_test_id');
    }

    // 2. علاقة LabTest مع التحليل العام الأب (BelongsTo)
    public function masterTest()
    {
        return $this->belongsTo(MasterTest::class, 'master_test_id');
    }

    // 3. علاقة LabTest مع المختبر نفسه (BelongsTo)
    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    

    public function categoryOffers()
    {
        return $this->hasMany(Offer::class, 'category_id', 'category_id')
                    ->whereNull('lab_test_id'); // لضمان عدم التداخل
    }


    public function directOffers()
    {
        return $this->hasMany(Offer::class, 'lab_test_id');
    }


    public function getPriceDetailsAttribute()
    {
        $originalPrice = $this->price;
        $discountPercentage = 0;
        $hasOffer = false;
        $offerName = null;

        // 1. البحث عن عرض مباشر على التحليل نفسه نشط اليوم
        $activeOffer = $this->directOffers()->activeToday()->first();

        // 2. إذا لم يجد، يبحث عن عرض مطبق على فئة هذا التحليل (عبر الماستر تيست)
        if (!$activeOffer && $this->masterTest) {
            $categoryId = $this->masterTest->category_id; 
            if ($categoryId) {
                $activeOffer = Offer::where('category_id', $categoryId)
                                    ->whereNull('lab_test_id')
                                    ->activeToday()
                                    ->where('lab_id', $this->lab_id)
                                    ->first();
            }
        }

        // 3. إذا وُجد العرض، نأخذ النسبة والاسم
        if ($activeOffer) {
            $discountPercentage = $activeOffer->discount_percentage;
            $hasOffer = true;
            $offerName = $activeOffer->name;
        }

        // الحسبة الرياضية البرمجية الدقيقة للسعر بعد الخصم
        $finalPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));

        return [
            'original_price'      => (float) $originalPrice,
            'final_price'         => (float) $finalPrice,
            'has_offer'           => $hasOffer,
            'offer_name'          => $offerName,
            'discount_percentage' => (float) $discountPercentage
        ];
    }
}
