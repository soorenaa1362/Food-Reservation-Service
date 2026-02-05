<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService 
{
    public function getAllCenterTransactions(int $userId, int $centerId)
    {
        try {
            return Transaction::where('user_id', $userId)
                ->where('center_id', $centerId)->get();
        } catch (\Exception $e) {
            Log::error("Error getting transactions for center_id: $centerId" . $e->getMessage());
            return null;
        }
    }


    public function createTransactionForFoodReserve(int $userId, int $centerId, int $totalAmount, 
        string $description = 'رزرو غذا'
    ): Transaction
    {
        return Transaction::create([
            'user_id'     => $userId,
            'center_id'   => $centerId,
            'type'        => 2, // نوع تراکنش رزرو غذا
            'amount'      => $totalAmount,
            'status'      => 1, // موفق
            'description' => $description,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }


    public function createTransactionForIncreaseBalance(int $userId, int $centerId, int $amount,
        string $description = 'شارژ اعتبار (حالت تست)'
    ): Transaction
    {
        return Transaction::create([
            'user_id' => $userId,
            'center_id' => $centerId,
            'type'        => 1, // نوع تراکنش افزایش اعتبار
            'amount' => $amount,
            'status' => 1, // success
            'description' => $description,
            'meta' => json_encode([
                'test' => true,
                'timestamp' => now()->toDateTimeString(),
                'ip' => request()->ip()
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }


    public function showTransaction($transactionId)
    {
        // فقط تمام ledger های این تراکنش دریافت بشه 
        // $transaction = Transaction::with('ledgers')
        //     ->findOrFail($transactionId);

        // دریافت و به ترتیب بیاد
        $transaction = Transaction::with([
            'ledgers' => fn($q) => $q->latest()
            ])->findOrFail($transactionId);


        return $transaction;
    }
}