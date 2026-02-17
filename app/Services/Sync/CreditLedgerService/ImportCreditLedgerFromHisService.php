<?php

namespace App\Services\Sync\CreditLedgerService;

use App\Models\Center;
use App\Models\CreditLedger;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ImportCreditLedgerFromHisService
{
    public function handle(): array
    {
        $payload = $this->fetchFromJson();

        if (!$payload) {
            return [
                'processed' => 0,
                'message'   => 'no data found',
            ];
        }

        $stats = [
            'processed' => 0,
        ];

        DB::transaction(function () use ($payload, &$stats) {

            $user = $this->resolveUser($payload);
            $center = $this->resolveCenter($payload);

            if (!$user || !$center) {
                return;
            }

            // پیدا کردن کارت مرتبط با کاربر و مرکز
            $creditCard = $user->creditCards()->where('center_id', $center->id)->first();
            if (!$creditCard) {
                throw new Exception("CreditCard not found for user_id={$user->id} center_id={$center->id}");
            }

            $transaction = $this->resolveTransaction($payload, $user, $center);

            $amount = (int)($payload['transaction']['amount'] ?? 0);

            CreditLedger::createIdempotent([
                'transaction_id' => $transaction->id,
                'user_id'        => $user->id,
                'center_id'      => $center->id,
                'credit_card_id' => $creditCard->id,

                'amount'         => $amount,
                'balance_before' => 0, // TODO: محاسبه واقعی
                'balance_after'  => $amount,

                'type'        => CreditLedger::TYPE_INCREASE,
                'source_type' => CreditLedger::SOURCE_PAYMENT,
                'source_id'   => null,

                'origin'      => CreditLedger::ORIGIN_HIS,
                'external_id' => $payload['event_id'] ?? null,

                'received_from_his_at' => now(),
                'description'          => $payload['transaction']['description'] ?? null,
                'meta'                 => $payload['meta'] ?? [],

                'created_at' => now(),
            ]);

            // آپدیت balance در CreditCard
            $creditCard->balance += $amount;
            $creditCard->save();

            $stats['processed']++;
        });

        return $stats;
    }

    /* ======================================================
     | Resolve Methods
     * ====================================================== */

    private function resolveUser(array $payload): ?User
    {
        $nationalCode = $payload['user']['national_code'] ?? null;
        if (!$nationalCode) {
            Log::warning('national_code missing in HIS payload', $payload['user'] ?? []);
            return null;
        }

        $hashedCode = hash('sha256', $nationalCode);

        $user = User::where('national_code_hashed', $hashedCode)->first();

        if (!$user) {
            Log::warning('User not found during HIS ledger import', [
                'national_code' => $nationalCode,
            ]);
        }

        return $user;
    }

    private function resolveCenter(array $payload): ?Center
    {
        $hisCenterId = $payload['center']['his_center_id'] ?? null;

        $center = Center::where('his_center_id', $hisCenterId)->first();

        if (!$center) {
            Log::warning('Center not found during HIS ledger import', [
                'his_center_id' => $hisCenterId,
            ]);
        }

        return $center;
    }

    private function resolveTransaction(array $payload, User $user, Center $center): Transaction
    {
        return Transaction::firstOrCreate(
            [
                'external_id' => $payload['transaction']['his_transaction_id'] ?? null,
            ],
            [
                'user_id'     => $user->id,
                'center_id'   => $center->id,
                'amount'      => $payload['transaction']['amount'] ?? 0,
                'description' => $payload['transaction']['description'] ?? null,
            ]
        );
    }

    /* ======================================================
     | Data Source (Temporary JSON Adapter)
     * ====================================================== */

    private function fetchFromJson(): ?array
    {
        $path = storage_path('app/his-data/credit-ledgers.json');

        if (!file_exists($path)) {
            Log::warning('HIS credit-ledgers.json not found', ['path' => $path]);
            return null;
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid HIS credit-ledgers JSON', [
                'error' => json_last_error_msg(),
                'path'  => $path,
            ]);
            return null;
        }

        return $data;
    }
}
