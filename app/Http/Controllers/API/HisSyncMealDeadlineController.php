<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Sync\SyncMealDeadlinesService;
use Illuminate\Http\JsonResponse;

class HisSyncMealDeadlineController extends Controller
{
    private SyncMealDeadlinesService $service;

    public function __construct(SyncMealDeadlinesService $service)
    {
        $this->service = $service;
    }

    public function sync(): JsonResponse
    {
        try {
            $count = $this->service->sync();

            return response()->json([
                'status'  => 'success',
                'message' => 'سینک ددلاین‌های مراکز با موفقیت انجام شد',
                'count'   => $count,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطا در سینک ددلاین‌ها: ' . $e->getMessage(),
            ], 500);
        }
    }
}