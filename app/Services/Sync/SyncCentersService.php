<?php

namespace App\Services\Sync;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Center;

class SyncCentersService
{
    public function sync(): int
    {
        $rawCenters = $this->fetchCenters();
        $count = 0;

        DB::transaction(function () use ($rawCenters, &$count) {
            foreach ($rawCenters as $data) {
                if (empty($data['id'])) {
                    continue;
                }

                $center = Center::updateOrCreate(
                    ['his_center_id' => (string)$data['id']],
                    [
                        'name'    => $data['name']    ?? '',
                        'type'    => $data['type']    ?? null,
                        'address' => $data['address'] ?? null,
                    ]
                );

                $count++;

                // مدیریت meal_deadlines - overwrite کامل
                $deadlinesData = $data['meal_deadlines'] ?? [];

                // حذف تمام ددلاین‌های قبلی این مرکز
                $center->mealDeadlines()->delete();

                foreach ($deadlinesData as $item) {
                    if (empty($item['meal_type']) || !in_array($item['meal_type'], ['breakfast', 'lunch', 'dinner'])) {
                        Log::warning("ددلاین نامعتبر برای مرکز {$center->his_center_id}", ['item' => $item]);
                        continue;
                    }

                    $center->mealDeadlines()->create([
                        'meal_type'             => $item['meal_type'],
                        'reservation_to_hour'   => (int)($item['to_hour']   ?? 23),
                        'is_active'             => $item['is_active'] ?? true,
                    ]);
                }
            }
        });

        Log::info("سینک مراکز به پایان رسید", ['processed' => $count]);

        return $count;
    }

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