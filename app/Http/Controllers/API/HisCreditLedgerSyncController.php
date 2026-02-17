<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Sync\CreditLedgerService\ImportCreditLedgerFromHisService;
use Illuminate\Http\JsonResponse;
use Throwable;

class HisCreditLedgerSyncController extends Controller
{
    public function __construct(
        private ImportCreditLedgerFromHisService $service
    ) {}

    public function sync(): JsonResponse
    {
        try {

            $stats = $this->service->handle();

            return response()->json([
                'status'  => 'success',
                'message' => 'دریافت و ثبت ledger از HIS با موفقیت انجام شد',
                'data'    => $stats,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'خطا در سینک ledger از HIS',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
