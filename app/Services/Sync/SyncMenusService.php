<?php

namespace App\Services\Sync;

use App\Models\Center;
use App\Models\Meal;
use App\Models\MealItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SyncMenusService
{
    public function sync(): int
    {
        if (Center::count() === 0) {
            throw new RuntimeException(
                'Menus sync requires centers to be synced first'
            );
        }

        $menus = $this->fetchMenus();

        $items = 0;

        DB::transaction(function () use ($menus, &$items) {

            foreach ($menus as $menu) {

                $center = Center::where(
                    'his_center_id',
                    $menu['center_id']
                )->first();

                if (!$center) continue;

                $meal = Meal::firstOrNew([
                    'center_id' => $center->id,
                    'date' => Carbon::parse($menu['date'])->toDateString(),
                ]);

                $meal->save();

                foreach (['breakfast','lunch','dinner'] as $type) {

                    foreach ($menu['meals'][$type] ?? [] as $item) {

                        MealItem::updateOrCreate(
                            [
                                'meal_id' => $meal->id,
                                'meal_type' => $type,
                                'food_name' => $item['food_name'],
                            ],
                            [
                                'portions' => $item['portions'],
                                'price' => $item['price'],
                                'reserved_count' => 0,
                            ]
                        );

                        $items++;
                    }
                }
            }
        });

        return $items;
    }

    /* ======================================================
     |  Private Methods
     * ====================================================== */
    private function fetchMenus(): array
    {
        $path = storage_path('app/his-data/menus.json');

        if (!file_exists($path)) {
            Log::warning("فایل menus.json پیدا نشد", ['path' => $path]);
            return [];
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("خطا در پارس JSON منوی غذا", [
                'error' => json_last_error_msg(),
                'path'  => $path,
            ]);
            return [];
        }

        return $data['menus'] ?? [];
    }
}