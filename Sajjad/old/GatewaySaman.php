<?php

namespace App\Clients\Gateways;

use SoapClient;
use Exception;
use Illuminate\Support\Facades\Log;

class GatewaySaman
{
    protected $merchantId;
    protected $password;
    protected $redirectUrl;

    public function __construct()
    {
        // این مقادیر رو بعداً از دیتابیس یا کانفیگ بگیر (از مدل PaymentGateway)
        $this->merchantId = config('gateways.saman.merchant_id'); // یا از gateway model بگیر
        $this->password   = config('gateways.saman.password');
        $this->redirectUrl = route('user.credit-card.callback'); // آدرس بازگشت
    }

    /**
     * درخواست توکن از سامان برای رفتن به صفحه پرداخت
     */
    public function requestToken(int $reserveId, int $amount, string $mobile = null): ?string
    {
        try {
            $client = new SoapClient('https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL');

            $params = [
                'MID'         => $this->merchantId,
                'ResNum'      => (string)$reserveId, // شماره رزرو یا تراکنش شما
                'Amount'      => $amount,            // به ریال (سامان به ریال کار می‌کند!)
                'RedirectURL' => $this->redirectUrl,
                'MobileNo'    => $mobile ?? '',
            ];

            $result = $client->RequestToken(
                $params['MID'],
                $params['ResNum'],
                $params['Amount'],
                $params['RedirectURL'],
                $params['MobileNo']
            );

            // نتیجه معمولاً یک توکن مثل "ABC123..." یا عدد منفی در صورت خطا
            if ($result && strlen($result) > 10) {
                return $result;
            }

            Log::error('Saman Token Error', ['result' => $result, 'params' => $params]);
            return null;

        } catch (Exception $e) {
            Log::error('Saman SOAP Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * وریفای تراکنش پس از بازگشت از بانک
     */
    public function verifyTransaction(string $token, int $amount)
    {
        try {
            $client = new SoapClient('https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL');

            $result = $client->verifyTransaction($token, $this->merchantId);

            // نتیجه موفق معمولاً برابر با مبلغ پرداختی است
            if ($result == $amount) {
                return [
                    'success' => true,
                    'amount'  => $result,
                    'RefNum'  => $client->RefNum ?? null, // شماره ارجاع سامان
                ];
            }

            Log::warning('Saman Verify Failed', ['token' => $token, 'result' => $result, 'expected' => $amount]);
            return ['success' => false, 'message' => 'مبلغ واریزی مطابقت ندارد'];

        } catch (Exception $e) {
            Log::error('Saman Verify Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ریورس تراکنش (در صورت نیاز)
     */
    public function reverseTransaction(string $token)
    {
        try {
            $client = new SoapClient('https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL');

            $result = $client->reverseTransaction($token, $this->merchantId);

            return $result > 0; // نتیجه مثبت یعنی موفقیت

        } catch (Exception $e) {
            Log::error('Saman Reverse Exception: ' . $e->getMessage());
            return false;
        }
    }
}