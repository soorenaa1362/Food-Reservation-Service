<?php

namespace App\Services\Auth;

use App\Repositories\User\UserRepositoryInterface;

class OtpService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function sendOtp(string $nationalCode, ?string $mobile, string $otp): bool
    {
        \Log::info("Storing OTP $otp for $nationalCode");
        $this->userRepository->updateOtp($nationalCode, $otp, now()->addMinutes(5));
        return true; // همیشه true، چون پیامک واقعی نداریم
    }
}