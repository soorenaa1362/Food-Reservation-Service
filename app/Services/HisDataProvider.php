<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class HisDataProvider
{
    private const DATA_PATH = 'his-data';
    private const CACHE_TTL = 3600; // 1 ساعت cache

    // دریافت تمام کاربران از HIS
    public function getUsers(): Collection
    {
        return $this->loadAndCache('users.json', fn($data) => collect($data['users'] ?? []));
    }

    // پیدا کردن کاربر بر اساس کد ملی
    public function getUserByNationalCode(string $nationalCode): ?array
    {
        return $this->getUsers()->firstWhere('national_code', $nationalCode);
    }

    // دریافت تمام مراکز
    public function getCenters(): Collection
    {
        return $this->loadAndCache('centers.json', fn($data) => collect($data['centers'] ?? []));
    }

    // دریافت مرکز بر اساس ID
    public function getCenterById(int $centerId): ?array
    {
        return $this->getCenters()->firstWhere('id', $centerId);
    }

    // دریافت منوی روزانه (بر اساس تاریخ و مرکز)
    public function getDailyMenu(Carbon $date, int $centerId): ?array
    {
        $dateStr = $date->format('Y-m-d');
        $menus = $this->loadAndCache('menus.json', fn($data) => collect($data['daily_menus'] ?? []));
        
        return $menus->firstWhere('date', $dateStr, 'center_id', $centerId);
    }

    // شبیه‌سازی دریافت event از HIS (webhook)
    public function syncUserEvent(array $event): bool
    {
        // در آینده: این متد webhook رو هندل می‌کنه
        // الان: داده رو به فایل JSON append می‌کنه
        $users = $this->getUsers();
        $event['updated_at'] = now()->toDateTimeString();
        
        $index = $users->search(fn($user) => $user['national_code'] === $event['national_code']);
        
        if ($index !== false) {
            $users[$index] = array_merge($users[$index], $event); // update
        } else {
            $users->push($event); // create
        }
        
        return $this->saveJson('users.json', ['users' => $users->toArray()]);
    }

    // پاک کردن cache بعد از sync
    public function clearCache(): void
    {
        Cache::forget('his_data_users');
        Cache::forget('his_data_centers');
        Cache::forget('his_data_menus');
    }

    // متدهای خصوصی کمکی
    private function loadAndCache(string $filename, callable $processor): Collection
    {
        $cacheKey = "his_data_{$filename}";
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filename, $processor) {
            $path = self::DATA_PATH . '/' . $filename;
            
            if (!Storage::disk('local')->exists($path)) {
                \Log::warning("HIS data file not found: {$path}");
                return collect();
            }

            $content = Storage::disk('local')->get($path);
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error("JSON error in {$path}: " . json_last_error_msg());
                return collect();
            }

            return $processor($data);
        });
    }

    private function saveJson(string $filename, array $data): bool
    {
        $path = self::DATA_PATH . '/' . $filename;
        $result = Storage::disk('local')->put($path, json_encode($data, JSON_UNESCAPED_UNICODE));
        $this->clearCache(); // cache رو پاک کن
        return $result;
    }

    // public function getAllMenus(): Collection
    // {
    //     return $this->loadAndCache('menus.json', fn($data) => collect($data));
    // }

    public function getAllMenus(): Collection
    {
        return $this->loadAndCache('menus.json', fn($data) => collect($data['menus'] ?? []));
    }
}