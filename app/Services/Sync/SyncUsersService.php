<?php

namespace App\Services\Sync;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Center;
use App\Models\CreditCard;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class SyncUsersService
{
    public function sync(): int
    {
        if (Center::count() === 0) {
            throw new RuntimeException(
                'Users sync requires centers to be synced first'
            );
        }

        $rawUsers = $this->fetchUsers();

        $count = 0;

        DB::transaction(function () use ($rawUsers, &$count) {

            foreach ($rawUsers as $data) {

                if (empty($data['national_code']) || empty($data['is_active'])) {
                    continue;
                }

                $user = User::updateOrCreate(
                    ['national_code_hashed' => hash('sha256', $data['national_code'])],
                    [
                        'mobile_hashed' => hash('sha256', $data['phone_number'] ?? ''),
                        'encrypted_first_name' => Crypt::encryptString($data['name'] ?? ''),
                        'encrypted_last_name'  => Crypt::encryptString($data['family'] ?? ''),
                        'encrypted_full_name'  => Crypt::encryptString(
                            trim(($data['name'] ?? '').' '.($data['family'] ?? ''))
                        ),
                        'is_active' => true,
                    ]
                );

                $centerIds = collect($data['centers'] ?? [])
                    ->map(fn ($id) => Center::where('his_center_id', $id)->value('id'))
                    ->filter()
                    ->toArray();

                $user->centers()->sync($centerIds);

                $this->syncCredits($user, $data['credits'] ?? []);

                $count++;
            }
        });

        return $count;
    }

    /* ======================================================
     |  Private Methods
     * ====================================================== */    
    private function syncUserCenters(User $user, array $hisCenterIds): void
    {
        $localCenterIds = collect($hisCenterIds)
            ->map(fn($hisId) => Center::where('his_center_id', (string) $hisId)->value('id'))
            ->filter()
            ->values()
            ->toArray();

        $user->centers()->sync($localCenterIds);
    }

    private function syncCredits(User $user, array $credits)
    {
        $credits = collect($credits);

        foreach ($user->centers as $center) {

            $credit = $credits->firstWhere(
                'center_id',
                $center->his_center_id
            );

            if (!$credit) continue;

            CreditCard::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'center_id' => $center->id,
                ],
                [
                    'balance' => $credit['balance'] ?? 0,
                    'membership_type' => $credit['membership_type'] ?? null,
                    'credit_expires_at' => $credit['credit_expires_at'] ?? null,
                ]
            );
        }
    }
   
    private function fetchUsers(): array
    {
        $path = storage_path('app/his-data/users.json');

        if (!file_exists($path)) {
            Log::warning("فایل users.json پیدا نشد", ['path' => $path]);
            return [];
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("خطا در پارس JSON کاربران", [
                'error' => json_last_error_msg(),
                'path'  => $path,
            ]);
            return [];
        }

        return $data['users'] ?? [];
    }
}