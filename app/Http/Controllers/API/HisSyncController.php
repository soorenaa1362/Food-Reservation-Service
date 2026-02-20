<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Sync\SyncAllService;
use App\Services\Sync\SyncCentersService;
use App\Services\Sync\SyncMenusService;
use App\Services\Sync\SyncUsersService;
use Illuminate\Http\JsonResponse;
use Throwable;

class HisSyncController extends Controller
{
    private SyncAllService $syncAllService;
    private SyncCentersService $syncCentersService;
    private SyncUsersService $syncUsersService;
    private SyncMenusService $syncMenusService;

    public function __construct(
        SyncAllService $syncAllService, 
        SyncCentersService $centersService,
        SyncUsersService $usersService,
        SyncMenusService $menusService,
    ) {
        $this->allService = $syncAllService;
        $this->centersService = $centersService;
        $this->usersService = $usersService;
        $this->menusService = $menusService;
    }

    public function syncAll(): JsonResponse
    {
        try {
            $stats = $this->allService->syncAllData();

            return response()->json([
                'status'  => 'success',
                'message' => 'سینک کامل با موفقیت انجام شد',
                'data'    => $stats,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطا در سینک کامل',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function syncCenters(): JsonResponse
    {
        try {
            $count = $this->centersService->sync();   

            return response()->json([
                'status'  => 'success',
                'message' => 'سینک مراکز با موفقیت انجام شد',
                'count'   => $count,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطا در سینک مراکز: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function syncUsers(): JsonResponse
    {
        try {
            $count = $this->usersService->sync();   

            return response()->json([
                'status'  => 'success',
                'message' => 'سینک کاربران با موفقیت انجام شد',
                'count'   => $count,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطا در سینک کاربران: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function syncMenus(): JsonResponse
    {
        try {
            $count = $this->menusService->sync();   

            return response()->json([
                'status'  => 'success',
                'message' => 'سینک منوی غذا با موفقیت انجام شد',
                'count'   => $count,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطا در سینک منوی غذا: ' . $e->getMessage(),
            ], 500);
        }
    }
}
