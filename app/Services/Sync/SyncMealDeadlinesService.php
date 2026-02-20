<?php

namespace App\Services\Sync;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Center;

class SyncMealDeadlinesService
{
    public function sync(): int
    {
        $rawDeadlines = $this->fetchDeadlines();
        $count = 0;

        DB::transaction(function () use ($rawDeadlines, &$count) {
            foreach ($rawDeadlines as $item) {
                $centerId = $item['center_id'] ?? null;
                if (!$centerId) continue;

                $center = Center::where('his_center_id', (string)$centerId)->first();
                if (!$center) {
                    Log::warning("مرکز با his_center_id {$centerId} یافت نشد", ['data' => $item]);
                    continue;
                }

                // حذف ددلاین‌های قبلی این مرکز
                $center->mealDeadlines()->delete();

                $deadlinesData = $item['meal_deadlines'] ?? [];

                foreach ($deadlinesData as $dl) {
                    if (empty($dl['meal_type'])) continue;

                    $center->mealDeadlines()->create([
                        'meal_type'           => $dl['meal_type'],
                        'reservation_to_hour' => (int)($dl['to_hour'] ?? 23),
                        'is_active'           => $dl['is_active'] ?? true,
                    ]);
                }

                $count++;
            }
        });

        return $count;
    }

    private function fetchDeadlines(): array
    {
        $path = storage_path('app/his-data/center-meal-deadlines.json');

        if (!file_exists($path)) {
            Log::warning("فایل center-meal-deadlines.json یافت نشد", ['path' => $path]);
            return [];
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("خطا در پارس فایل center-meal-deadlines.json", [
                'error' => json_last_error_msg(),
                'path'  => $path,
            ]);
            return [];
        }

        return $data['deadlines'] ?? [];
    }
}