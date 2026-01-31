<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use App\Services\HisDataProvider;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class AuthService
{
    private const OTP_LENGTH = 5;
    private const OTP_EXPIRES_MINUTES = 5;
    private const MAX_OTP_ATTEMPTS = 5;
    private HisDataProvider $hisProvider;

    public function __construct(HisDataProvider $hisProvider)
    {
        $this->hisProvider = $hisProvider;
    }

    public function sendVerificationCode(string $nationalCode): array
    {
        // ۱- پیدا کردن کاربر در his.json
        $userData = $this->hisProvider->getUserByNationalCode($nationalCode);
        
        if (!$userData) {
            return ['success' => false];
        }

        // ۲- هش کردن کد ملی و موبایل
        $nationalCodeHashed = hash('sha256', $nationalCode);
        $mobileHashed = hash('sha256', $userData['phone_number'] ?? '');

        // ۳- ایجاد یا به‌روزرسانی کاربر محلی
        $user = User::updateOrCreate(
            ['national_code_hashed' => $nationalCodeHashed],
            [
                'mobile_hashed'         => $mobileHashed,
                'encrypted_first_name'  => Crypt::encryptString($userData['name'] ?? ''),
                'encrypted_last_name'   => Crypt::encryptString($userData['family'] ?? ''),
                'encrypted_full_name'   => Crypt::encryptString(trim(($userData['name'] ?? '') . ' ' . ($userData['family'] ?? ''))),
                'is_active'             => true,
            ]
        );

        // ۴- تولید و ذخیره OTP
        $otp = '12345'; // در پروداکشن: Str::random(self::OTP_LENGTH) یا عدد تصادفی واقعی

        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(self::OTP_EXPIRES_MINUTES);
        $user->otp_attempts = 0; // ریست تلاش‌ها
        $user->save();

        \Log::info("OTP generated and stored for national code: {$nationalCode}");

        return ['success' => true];
    }

    public function verifyOTPAndLogin(string $nationalCode, string $code): ?User
    {
        $nationalCodeHashed = hash('sha256', $nationalCode);

        $user = User::where('national_code_hashed', $nationalCodeHashed)
            ->where('otp_code', $code)
            ->where('otp_expires_at', '>', now())
            ->first();

        if (!$user) {
            // افزایش شمارنده تلاش ناموفق
            User::where('national_code_hashed', $nationalCodeHashed)->increment('otp_attempts');
            return null;
        }

        // پاک کردن OTP بعد از استفاده موفق
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->otp_attempts = 0;
        $user->save();

        return $user;
    }

    private function findUserInHisJson(string $nationalCode): ?array
    {
        if (!Storage::disk('local')->exists('his.json')) {
            \Log::error('his.json file not found');
            return null;
        }

        $json = Storage::disk('local')->get('his.json');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('JSON decode error in his.json: ' . json_last_error_msg());
            return null;
        }

        $users = $data['users'] ?? [];
        return collect($users)->firstWhere('national_code', $nationalCode);
    }
}