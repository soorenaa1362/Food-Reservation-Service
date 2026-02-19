<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Log;

class OtpService
{
    public function sendOtp($phone, $otpCode)
    {
        $options = [
            'trace' => 1,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ]
            ])
        ];

        try {
            $wsdlUrl = env('SMS_WSDL_URL');
            $client = new \SoapClient($wsdlUrl, $options);

            $message = "کد تأیید شما: \n $otpCode";


            $response = $client->__soapCall('send', [
                'username' => env('SMS_USERNAME'),
                'password' => env('SMS_PASSWORD'),
                'to'       => $phone,
                'from'     => env('SMS_FROM'),
                'message'  => $message,
            ]);


            return $response;

        } catch (\Exception $e) {
            Log::error('OTP SMS failed: ' . $e->getMessage());
            return false;
        }
    }
}