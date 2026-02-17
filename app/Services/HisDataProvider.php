<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Center;
use App\Models\CreditCard;
use App\Models\Transaction;
use App\Services\CreditLedger;

class HisDataProvider
{
    private const DATA_PATH = 'his-data';
    private const CACHE_TTL = 3600; // 1 hour

    /* ======================================================
     |  Public HIS Data Access
     * ====================================================== */

    public function getUsers(): Collection
    {
        return $this->loadAndCache('users.json', fn ($data) => collect($data['users'] ?? []));
    }

    public function getUserByNationalCode(string $nationalCode): ?array
    {
        return $this->getUsers()->firstWhere('national_code', $nationalCode);
    }

    public function getCenters(): Collection
    {
        return $this->loadAndCache('centers.json', fn ($data) => collect($data['centers'] ?? []));
    }

    public function getCenterById(int $centerId): ?array
    {
        return $this->getCenters()->firstWhere('id', $centerId);
    }

    public function getAllMenus(): Collection
    {
        return $this->loadAndCache('menus.json', fn ($data) => collect($data['menus'] ?? []));
    }

    public function getDailyMenu(Carbon $date, int $centerId): ?array
    {
        $dateStr = $date->format('Y-m-d');

        $menus = $this->loadAndCache(
            'menus.json',
            fn ($data) => collect($data['daily_menus'] ?? [])
        );

        return $menus->first(function ($menu) use ($dateStr, $centerId) {
            return ($menu['date'] ?? null) === $dateStr
                && (int) ($menu['center_id'] ?? 0) === $centerId;
        });
    }

    /* ======================================================
     |  User Sync (Mock HIS Webhook)
     * ====================================================== */

    public function syncUserEvent(array $event): bool
    {
        if (empty($event['national_code'])) {
            Log::warning('User sync missing national_code', $event);
            return false;
        }

        $users = $this->getUsers();
        $event['updated_at'] = now()->toDateTimeString();

        $index = $users->search(
            fn ($user) => ($user['national_code'] ?? null) === $event['national_code']
        );

        if ($index !== false) {
            $users[$index] = array_merge($users[$index], $event);
        } else {
            $users->push($event);
        }

        return $this->saveJson('users.json', ['users' => $users->values()->toArray()]);
    }

    /* ======================================================
     |  Credit Charge Sync (HIS → Local Ledger)
     * ====================================================== */

    public function syncCreditChargeEvent(array $event): bool
    {
        $required = ['event_id', 'user', 'center', 'transaction'];

        foreach ($required as $key) {
            if (empty($event[$key])) {
                Log::warning("Credit charge missing {$key}", $event);
                return false;
            }
        }

        $nationalCode = $event['user']['national_code'] ?? null;
        $hisCenterId = (int) ($event['center']['his_center_id'] ?? 0);

        $tx = $event['transaction'];
        $amount = (int) ($tx['amount'] ?? 0);
        $hisTxId = $tx['his_transaction_id'] ?? null;

        if (!$nationalCode || !$hisCenterId || !$hisTxId || $amount <= 0) {
            Log::warning('Invalid credit charge payload', $event);
            return false;
        }

        // Idempotency — prevent duplicate credit
        $duplicate = Transaction::where('meta->his_transaction_id', $hisTxId)->exists();
        if ($duplicate) {
            Log::info('Duplicate HIS transaction ignored', ['his_tx' => $hisTxId]);
            return true;
        }

        DB::beginTransaction();

        try {
            /* ---------- Resolve user ---------- */

            $hashed = hash('sha256', $nationalCode);
            $user = User::where('national_code_hashed', $hashed)->first();

            if (!$user) {
                throw new \RuntimeException("User not found: {$nationalCode}");
            }

            /* ---------- Resolve center ---------- */

            $center = Center::where('his_center_id', $hisCenterId)->first();

            if (!$center) {
                throw new \RuntimeException("Center not found: {$hisCenterId}");
            }

            /* ---------- Credit card ---------- */

            $creditCard = CreditCard::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'center_id' => $center->id,
                ],
                [
                    'balance' => 0,
                    'membership_type' => $event['credit_policy']['membership_type'] ?? 'default',
                    'credit_expires_at' => $event['credit_policy']['expires_at'] ?? null,
                ]
            );

            /* ---------- Transaction record ---------- */

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'center_id' => $center->id,
                'type' => Transaction::TYPE_CREDIT_CHARGE,
                'amount' => $amount,
                'status' => Transaction::STATUS_SUCCESS,
                'description' => $tx['description'] ?? 'HIS credit charge',
                'meta' => array_merge(
                    $event['meta'] ?? [],
                    ['his_transaction_id' => $hisTxId]
                ),
            ]);

            /* ---------- Ledger increase ---------- */

            CreditLedger::increase(
                $creditCard,
                $amount,
                CreditLedger::SOURCE_PAYMENT,
                $transaction->id,
                $tx['description'] ?? 'HIS credit charge',
                [
                    'event_id' => $event['event_id'],
                    'charged_at' => $event['occurred_at'] ?? now()->toDateTimeString(),
                ]
            );

            DB::commit();

            Log::info('Credit synced', [
                'user' => $user->id,
                'center' => $center->id,
                'amount' => $amount,
            ]);

            return true;

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Credit sync failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /* ======================================================
     |  Mock Ledger Storage (optional)
     * ====================================================== */

    public function appendToCreditHistory(array $event): void
    {
        $file = self::DATA_PATH . '/credit-ladgers.json';

        $existing = Storage::disk('local')->exists($file)
            ? json_decode(Storage::disk('local')->get($file), true)
            : [];

        $records = $existing['charges'] ?? [];

        $event['synced_at'] = now()->toDateTimeString();
        $records[] = $event;

        Storage::disk('local')->put(
            $file,
            json_encode(['charges' => $records], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    /* ======================================================
     |  Cache + Storage Helpers
     * ====================================================== */

    public function clearCache(): void
    {
        foreach (['users.json', 'centers.json', 'menus.json'] as $file) {
            Cache::forget($this->cacheKey($file));
        }
    }

    private function cacheKey(string $filename): string
    {
        return "his_data_{$filename}";
    }

    private function loadAndCache(string $filename, callable $processor): Collection
    {
        return Cache::remember(
            $this->cacheKey($filename),
            self::CACHE_TTL,
            function () use ($filename, $processor) {
                $path = self::DATA_PATH . '/' . $filename;

                if (!Storage::disk('local')->exists($path)) {
                    Log::warning("HIS file missing: {$path}");
                    return collect();
                }

                $json = Storage::disk('local')->get($path);
                $data = json_decode($json, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error("JSON error in {$path}: " . json_last_error_msg());
                    return collect();
                }

                return $processor($data);
            }
        );
    }

    private function saveJson(string $filename, array $data): bool
    {
        $path = self::DATA_PATH . '/' . $filename;

        $result = Storage::disk('local')->put(
            $path,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        $this->clearCache();

        return $result;
    }
}
