<?php

namespace App\Services\Sync;

use App\Models\Meal;
use App\Models\User;
use App\Models\Center;
use App\Models\MealItem;
use App\Models\CreditCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Services\Sync\SyncMenusService;
use App\Services\Sync\SyncUsersService;
use App\Services\Sync\SyncCentersService;

class SyncAllService
{
    private SyncCentersService $centersService;

    public function __construct(
        SyncCentersService $centersService,
        SyncUsersService $usersService,    
        SyncMenusService $menusService,
    )
    {
        $this->centersService = $centersService;
        $this->usersService = $usersService;
        $this->menusService = $menusService;
    }

    public function syncAllData(): array
    {
        $stats = [
            'centers_synced'     => 0,
            'users_synced'       => 0,
            'menu_items_synced'  => 0,
            'duration_seconds'   => 0,
            'success'            => true,
        ];

        $start = microtime(true);

        try {
            DB::transaction(function () use (&$stats) {
                $stats['centers_synced']    = $this->centersService->sync();
                $stats['users_synced']      = $this->usersService->sync();
                $stats['menu_items_synced'] = $this->menusService->sync();
            });
        } catch (\Exception $e) {
            $stats['success'] = false;
            $stats['error']   = $e->getMessage();
            Log::error('سینک کامل شکست خورد', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        $stats['duration_seconds'] = round(microtime(true) - $start, 2);

        if ($stats['success']) {
            Log::info('سینک کامل موفق', $stats);
        }

        return $stats;
    }
}