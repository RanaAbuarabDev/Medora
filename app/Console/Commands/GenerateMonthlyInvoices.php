<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Subscription;

class GenerateMonthlyInvoices extends Command
{
   
    protected $signature = 'invoices:generate';

    protected $description = 'إنشاء فواتير الاشتراك الشهرية للمخابر النشطة';

    public function handle()
    {
        $this->info('بدء عملية توليد الفواتير...');

        
        $labs = User::role('lab_manager')->where('status', 'active')->get();

        $count = 0;
        $currentMonth = now()->format('Y-m');

        foreach ($labs as $lab) {
            
            Subscription::firstOrCreate([
                'lab_id' => $lab->id,
                'billing_month' => $currentMonth,
            ], [
                'invoice_number' => 'INV-' . now()->format('Ym') . '-' . $lab->id,
                'amount' => 50.00,
                'status' => 'pending',
            ]);

            $count++;
        }

        $this->info("تم توليد $count فاتورة لشهر $currentMonth بنجاح!");
    }
}