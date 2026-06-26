<?php

namespace App\Services\LabAssistant;

use Illuminate\Support\Facades\DB;
use App\Notifications\LabManager\LowStockNotification;
use App\Models\User;
use Exception;

class LabInventoryUsageService
{
    /**
     * دالة (1): جلب جميع مستلزمات المختبر الحالي لتعرض ككروت بقيمة استهلاك تبدأ من 0
     */
    public function getLabConsumablesCards(int $labId)
    {
        return DB::table('lab_inventories')
            ->join('master_items', 'lab_inventories.master_item_id', '=', 'master_items.id')
            ->where('lab_inventories.lab_id', $labId)
            ->select('lab_inventories.id as lab_inventory_id', 'master_items.name_ar')
            ->get()
            ->map(fn($item) => [
                'lab_inventory_id' => $item->lab_inventory_id,
                'item_name'        => $item->name_ar,
                'quantity_used'    => 0 // القيمة الافتراضية للاستهلاك في الواجهة هي صفر
            ]);
    }

    /**
     * دالة (2): خصم الكميات المستهلكة وفحص حد التنبيه لإرسال الإشعار
     */
    public function consumeSupplies(int $labId, array $consumablesData): bool
    {
        DB::beginTransaction();

        try {
            foreach ($consumablesData as $consumable) {
                
                // تخطي المادة إذا أرسلها الفرونت إند بقيمة 0 (أي لم يتم استخدامها)
                if (($consumable['quantity_used'] ?? 0) <= 0) {
                    continue;
                }

                // جلب بيانات المادة من المخزن الحالي للمختبر
                $inventoryItem = DB::table('lab_inventories')
                    ->join('master_items', 'lab_inventories.master_item_id', '=', 'master_items.id')
                    ->where('lab_inventories.lab_id', $labId)
                    ->where('lab_inventories.id', $consumable['lab_inventory_id'])
                    ->select('lab_inventories.*', 'master_items.name_ar')
                    ->lockForUpdate()
                    ->first();

                if (!$inventoryItem) {
                    throw new Exception("المستلزم الطبي غير متوفر في مخزن هذا المختبر.");
                }

                // حساب الكمية الجديدة بعد الخصم
                $newQuantity = $inventoryItem->current_quantity - $consumable['quantity_used'];
                if ($newQuantity < 0) $newQuantity = 0;

                // تحديث قاعدة البيانات بالكمية الجديدة
                DB::table('lab_inventories')
                    ->where('id', $inventoryItem->id)
                    ->update([
                        'current_quantity' => $newQuantity,
                        'updated_at'       => now()
                    ]);

                // فحص هل نزلت الكمية عن حد التنبيه الأدنى؟
                if ($newQuantity <= $inventoryItem->alert_level) {
                    // جلب مدير المختبر الحالي
                    $manager = User::where('lab_id', $labId)
                        ->whereHas('roles', function($q) {
                            $q->where('name', 'lab_manager');
                        })->first();

                    if ($manager) {
                        // إرسال الإشعار فوراً للمدير
                        $manager->notify(new LowStockNotification($inventoryItem->name_ar, $newQuantity));
                    }
                }
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('فشل في تحديث استهلاك المخزن: ' . $e->getMessage());
        }
    }
}