<?php

namespace App\Services\LabManager;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryService
{
    /**
     * 1. جلب بيانات لوحة تحكم المخزن كاملة (الكروت + الجدول مفلتر)
     */
    public function getInventoryDashboard(int $labId, array $filters)
    {
        return [
            'cards' => $this->getInventorySummaryCards($labId),
            'items' => $this->getFilteredInventoryItems($labId, $filters)
        ];
    }

    /**
     * 2. حساب بيانات الكروت العلوية الثلاثة بدقة متناهية
     */
    private function getInventorySummaryCards(int $labId): array
    {
        // الكرت 1: إجمالي المواد المسجلة في مخزن المختبر الحالي
        $totalItems = DB::table('lab_inventories')->where('lab_id', $labId)->count();

        // الكرت 2: تنبيهات نقص المخزون (تتم مقارنة الكمية الحالية بحد التنبيه لكل مادة على حدة)
        $lowStockCount = DB::table('lab_inventories')
            ->where('lab_id', $labId)
            ->whereRaw('current_quantity <= alert_level')
            ->count();

        // الكرت 3: تاريخ ووقت آخر عملية تحديث جرت في مخزن المختبر
        $lastUpdateItem = DB::table('lab_inventories')
            ->where('lab_id', $labId)
            ->orderBy('updated_at', 'desc')
            ->first();

        $lastUpdateText = 'لا يوجد تحديثات بعد';
        if ($lastUpdateItem) {
            $lastUpdateText = Carbon::parse($lastUpdateItem->updated_at)->translatedFormat('اليوم، h:i أ');
        }

        return [
            'total_registered_items' => $totalItems, // إجمالي المواد المسجلة
            'low_stock_alerts'       => $lowStockCount, // تنبيهات نقص المخزون
            'last_updated_time'      => $lastUpdateText // آخر تحديث للمخزن
        ];
    }

    /**
     * 3. جلب جدول المستلزمات الطبية مفلتراً ومقسماً لصفحات (Pagination)
     */
    private function getFilteredInventoryItems(int $labId, array $filters)
    {
        $perPage = $filters['per_page'] ?? 10;

        $query = DB::table('lab_inventories')
            ->join('master_items', 'lab_inventories.master_item_id', '=', 'master_items.id')
            ->where('lab_inventories.lab_id', $labId)
            ->select(
                'lab_inventories.id',
                'master_items.name_ar',
                'master_items.name_en',
                'lab_inventories.current_quantity',
                'lab_inventories.alert_level'
            );

        // فلترة بالبحث المرن (عربي أو إنجليزي علمي)
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('master_items.name_ar', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('master_items.name_en', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('master_items.name_ar', 'asc')
            ->paginate($perPage)
            ->through(fn($item) => [
                'id'             => $item->id,
                'item_name'      => $item->name_ar . ' (' . $item->name_en . ')', // اسم المادة المدمج للأناقة
                'current_stock'  => $item->current_quantity, // الكمية الحالية
                'alert_level'    => $item->alert_level, // حد التنبيه الأدنى
                // حساب حالة المخزون لحظياً بناءً على القواعد الهندسية
                'status'         => $item->current_quantity <= $item->alert_level ? 'منخفض' : 'متوفر'
            ]);
    }

    /**
     * 4. ربط وإضافة مادة جديدة من القائمة العامة إلى مخزن المختبر
     */
    public function addItemToInventory(int $labId, array $data)
    {
        return DB::table('lab_inventories')->insert([
            'lab_id'           => $labId,
            'master_item_id'   => $data['master_item_id'],
            'current_quantity' => $data['current_quantity'],
            'alert_level'      => $data['alert_level'],
            'created_at'       => now(),
            'updated_at'       => now()
        ]);
    }

    /**
     * 5. تحديث وتعديل كميات مادة موجودة بالفعل داخل المخزن
     */
    public function updateInventoryItem(int $labId, int $itemId, array $data)
    {
        return DB::table('lab_inventories')
            ->where('lab_id', $labId)
            ->where('id', $itemId)
            ->update([
                'current_quantity' => $data['current_quantity'],
                'alert_level'      => $data['alert_level'],
                'updated_at'       => now()
            ]);
    }

    /**
     * 6. حذف مادة وإلغاء ارتباطها بمخزن المختبر الحالي
     */
    public function deleteInventoryItem(int $labId, int $itemId)
    {
        return DB::table('lab_inventories')
            ->where('lab_id', $labId)
            ->where('id', $itemId)
            ->delete();
    }
}