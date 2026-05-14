<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LabSchedule;
use App\Models\Laboratory;

class LabScheduleSeeder extends Seeder
{
    public function run(): void
    {
       
        $laboratories = Laboratory::all();

        if ($laboratories->isEmpty()) {
            $this->command->warn("لا يوجد مخابر حالياً. يرجى إضافة مخابر أولاً ثم تشغيل السييدر.");
            return;
        }

        
        foreach ($laboratories as $lab) {
            
            for ($i = 0; $i < 7; $i++) {
                LabSchedule::updateOrCreate(
                    [
                        'lab_id'      => $lab->id,
                        'day_of_week' => $i,
                    ],
                    [
                        'start_time' => '08:00:00',
                        'end_time'   => '16:00:00',
                        'is_day_off' => ($i == 5) ? true : false, 
                    ]
                );
            }
        }

        $this->command->info("تم إنشاء جداول دوام افتراضية لجميع المخابر بنجاح!");
    }
}