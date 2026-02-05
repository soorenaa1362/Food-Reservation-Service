<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Meal;
use App\Models\User;
use App\Models\Center;
use App\Models\MealItem;
use App\Models\CreditCard;
use Illuminate\Console\Command;
use App\Services\HisDataProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class SyncHisData extends Command
{
    /**
     * نام و توضیح دستور
     */
    protected $signature = 'his:sync 
                            {type? : نوع داده برای sync - users, centers, menus یا all}
                            {--force : بدون توجه به cache، از فایل بخوان}';

    protected $description = 'همگام‌سازی داده‌های HIS (کاربران، مراکز، منوها) از فایل‌های JSON به دیتابیس محلی سامانه رزرو غذا';

    public function handle(HisDataProvider $hisProvider)
    {
        $type = $this->argument('type') ?? 'all';

        // اگر --force باشه، cache رو پاک کن
        if ($this->option('force')) {
            $hisProvider->clearCache();
            $this->info('Cache پاک شد (force mode).');
        }

        $this->info("شروع همگام‌سازی نوع: {$type}");

        DB::transaction(function () use ($hisProvider, $type) {
            if (in_array($type, ['all', 'centers'])) {
                $this->syncCenters($hisProvider);
            }

            if (in_array($type, ['all', 'users'])) {
                $this->syncUsers($hisProvider);  // بعد از centers
            }

            if (in_array($type, ['all', 'menus'])) {
                $this->syncMenus($hisProvider);
            }
        });

        $this->newLine();
        $this->info('✅ همگام‌سازی داده‌های HIS با موفقیت انجام شد!');
    }

    private function syncCenters(HisDataProvider $hisProvider)
    {
        $centers = $hisProvider->getCenters();

        $count = 0;
        foreach ($centers as $data) {
            Center::updateOrCreate(
                ['his_center_id' => (string)$data['id']], // چون در JSON رشته است
                [
                    'name'    => $data['name'],
                    'type'    => $data['type'],
                    'address' => $data['address'] ?? null,
                ]
            );
            $count++;
        }

        $this->info("{$count} مرکز همگام‌سازی شد.");
    }

    // private function syncUsers(HisDataProvider $hisProvider)
    // {
    //     $users = $hisProvider->getUsers();

    //     $count = 0;
    //     foreach ($users as $data) {
    //         if (empty($data['is_active'])) {
    //             continue;
    //         }

    //         $nationalCodeHashed = hash('sha256', $data['national_code']);
    //         $mobileHashed = hash('sha256', $data['phone_number'] ?? '');

    //         $user = User::updateOrCreate(
    //             ['national_code_hashed' => $nationalCodeHashed],
    //             [
    //                 'mobile_hashed'        => $mobileHashed,
    //                 'encrypted_first_name' => Crypt::encryptString($data['name'] ?? ''),
    //                 'encrypted_last_name'  => Crypt::encryptString($data['family'] ?? ''),
    //                 'encrypted_full_name'  => Crypt::encryptString(trim(($data['name'] ?? '') . ' ' . ($data['family'] ?? ''))),
    //                 'is_active'            => true,
    //             ]
    //         );

    //         // sync مراکز مجاز کاربر (توسعه‌پذیر: اگر centers خالی باشه، هیچی sync نکن)
    //         $centersIds = collect($data['centers'] ?? [])->map(function ($hisId) {
    //             $center = Center::where('his_center_id', (string)$hisId)->first();
    //             if (!$center) {
    //                 $this->warn("مرکز با HIS ID {$hisId} برای کاربر {$data['national_code']} پیدا نشد.");
    //                 return null;
    //             }
    //             return $center->id;
    //         })->filter()->toArray();

    //         $user->centers()->sync($centersIds);

    //         $count++;
    //     }

    //     $this->info("{$count} کاربر همگام‌سازی شد (به همراه مراکز مجاز).");
    // }

    private function syncUsers(HisDataProvider $hisProvider)
    {
        $users = $hisProvider->getUsers();
        $count = 0;
        foreach ($users as $data) {
            if (empty($data['is_active'])) {
                continue;
            }
            $nationalCodeHashed = hash('sha256', $data['national_code']);
            $mobileHashed = hash('sha256', $data['phone_number'] ?? '');
            $user = User::updateOrCreate(
                ['national_code_hashed' => $nationalCodeHashed],
                [
                    'mobile_hashed'        => $mobileHashed,
                    'encrypted_first_name' => Crypt::encryptString($data['name'] ?? ''),
                    'encrypted_last_name'  => Crypt::encryptString($data['family'] ?? ''),
                    'encrypted_full_name'  => Crypt::encryptString(trim(($data['name'] ?? '') . ' ' . ($data['family'] ?? ''))),
                    'is_active'            => true,
                ]
            );

            // sync مراکز مجاز کاربر
            $centersIds = collect($data['centers'] ?? [])->map(function ($hisId) {
                $center = Center::where('his_center_id', (string)$hisId)->first();
                if (!$center) {
                    $this->warn("مرکز با HIS ID {$hisId} برای کاربر {$data['national_code']} پیدا نشد.");
                    return null;
                }
                return $center->id;
            })->filter()->toArray();
            $user->centers()->sync($centersIds);

            // sync اعتبارها (per-center)
            $credits = collect($data['credits'] ?? []);
            foreach ($centersIds as $localCenterId) {  // فقط برای مراکز مجاز کاربر
                $center = Center::find($localCenterId);
                $hisCenterId = $center->his_center_id;

                $creditData = $credits->firstWhere('center_id', $hisCenterId);
                if (!$creditData) {
                    $this->warn("اعتبار برای مرکز {$hisCenterId} کاربر {$data['national_code']} پیدا نشد.");
                    continue;
                }

                CreditCard::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'center_id' => $localCenterId,
                    ],
                    [
                        'balance'           => $creditData['balance'] ?? 0,
                        'membership_type'   => $creditData['membership_type'] ?? null,
                        'credit_expires_at' => $creditData['credit_expires_at'] ?? null,
                    ]
                );
            }

            $count++;
        }
        $this->info("{$count} کاربر همگام‌سازی شد (به همراه مراکز مجاز و اعتبارها).");
    }

    private function syncMenus(HisDataProvider $hisProvider)
    {
        // اول متد getMenus رو به HisDataProvider اضافه کن (اگر نداری)
        $menusData = $hisProvider->getAllMenus(); // متد جدید

        $itemCount = 0;
        foreach ($menusData as $menuData) {
            $center = Center::where('his_center_id', (string)$menuData['center_id'])->first();
            if (!$center) {
                $this->warn("مرکز با ID {$menuData['center_id']} پیدا نشد — منو نادیده گرفته شد.");
                continue;
            }

            $meal = Meal::updateOrCreate(
                [
                    'center_id' => $center->id,
                    'date'      => $menuData['date'],
                ],
                [
                    'center_id' => $center->id,
                    'date'      => $menuData['date'],
                ]
            );

            foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                if (!isset($menuData['meals'][$mealType])) continue;

                foreach ($menuData['meals'][$mealType] as $item) {
                    MealItem::updateOrCreate(
                        [
                            'meal_id'   => $meal->id,
                            'meal_type' => $mealType,
                            'food_name' => $item['food_name'],
                        ],
                        [
                            'portions'       => $item['portions'],
                            'price'          => $item['price'],
                            'reserved_count' => 0,
                        ]
                    );
                    $itemCount++;
                }
            }
        }

        $this->info("{$itemCount} آیتم غذایی همگام‌سازی شد.");
    }
}