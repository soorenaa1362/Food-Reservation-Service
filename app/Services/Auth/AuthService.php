<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthService
{
    private const OTP_LENGTH = 5;
    private const OTP_EXPIRES_MINUTES = 5;
    private const MAX_OTP_ATTEMPTS = 5;
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function sendVerificationCode(string $nationalCode): array
    {
        $nationalCodeHashed = hash('sha256', $nationalCode);

        $user = User::where('national_code_hashed', $nationalCodeHashed)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'کاربر یافت نشد'
            ];
        }

        if (empty($user->mobile_encrypted)) {
            \Log::warning("No mobile_encrypted for user ID: {$user->id}");
            return [
                'success' => false,
                'message' => 'شماره موبایل ثبت نشده'
            ];
        }

        try {
            $mobile = Crypt::decryptString($user->mobile_encrypted);
        } catch (\Exception $e) {
            \Log::error('Decrypt mobile failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'خطا در بازیابی شماره موبایل'
            ];
        }

        if (!preg_match('/^09[0-9]{9}$/', $mobile)) {
            \Log::warning("Invalid mobile format", ['user_id' => $user->id]);
            return [
                'success' => false,
                'message' => 'شماره موبایل نامعتبر'
            ];
        }

        // تولید OTP (برای تست می‌تونی ثابت بذاری، بعداً عوض کن)
        $otp = random_int(10000, 99999);
        // $otp = 123456;   // ← برای تست سریع

        // ذخیره OTP
        $user->otp_code       = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->otp_attempts   = 0;
        $user->save();

        // ارسال پیامک با سرویس SOAP
        $smsResponse = $this->otpService->sendOtp($mobile, $otp);

        if ($smsResponse === false) {
            Log::error('SMS sending failed via SOAP', [
                'user_id' => $user->id,
                'mobile'  => $mobile // فقط در لاگ error
            ]);

            return [
                'success' => false,
                'message' => 'خطا در ارسال کد تأیید، لطفاً دوباره تلاش کنید'
            ];
        }

        Log::info('OTP sent successfully', [
            'user_id'       => $user->id,
            'national_code' => substr($nationalCode, 0, 3) . '*******' . substr($nationalCode, -1)
        ]);

        return [
            'success' => true,
            'message' => 'کد تأیید با موفقیت ارسال شد'
        ];
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