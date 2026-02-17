<?php

namespace App\Services\Sync;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Center;

class SyncCentersService
{
    // public function sync(): int
    // {
    //     $rawCenters = $this->fetchCenters();

    //     if (empty($rawCenters)) {
    //         Log::warning('هیچ مرکزی برای سینک دریافت نشد');
    //         return 0;
    //     }

    //     $count = 0;

    //     DB::transaction(function () use ($rawCenters, &$count) {
    //         foreach ($rawCenters as $data) {
    //             // اعتبارسنجی حداقل
    //             if (empty($data['id'])) {
    //                 Log::warning('مرکز بدون id نادیده گرفته شد', ['data' => $data]);
    //                 continue;
    //             }

    //             $hisCenterId = (string) $data['id'];

    //             Center::updateOrCreate(
    //                 ['his_center_id' => $hisCenterId],
    //                 [
    //                     'name'    => $data['name']    ?? 'مرکز بدون نام',
    //                     'type'    => $data['type']    ?? null,
    //                     'address' => $data['address'] ?? null,
    //                     // اگر فیلدهای بیشتری در آینده اضافه شد، اینجا اضافه می‌کنی
    //                 ]
    //             );

    //             $count++;
    //         }
    //     });

    //     Log::info('سینک مراکز تمام شد', ['تعداد سینک‌شده' => $count]);

    //     return $count;
    // }

    
    public function sync(): int
    {
        $rawCenters = $this->fetchCenters();

        $count = 0;

        DB::transaction(function () use ($rawCenters, &$count) {

            foreach ($rawCenters as $data) {

                if (empty($data['id'])) continue;

                Center::updateOrCreate(
                    ['his_center_id' => (string)$data['id']],
                    [
                        'name' => $data['name'] ?? '',
                        'type' => $data['type'] ?? null,
                        'address' => $data['address'] ?? null,
                    ]
                );

                $count++;
            }
        });

        return $count;
    }

    /* ======================================================
     |  Private Methods
     * ====================================================== */
    private function fetchCenters(): array
    {
        $path = storage_path('app/his-data/centers.json');

        if (!file_exists($path)) {
            Log::warning("فایل centers.json پیدا نشد", ['path' => $path]);
            return [];
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("خطا در پارس JSON مراکز", [
                'error' => json_last_error_msg(),
                'path'  => $path,
            ]);
            return [];
        }

        return $data['centers'] ?? [];
    }
}