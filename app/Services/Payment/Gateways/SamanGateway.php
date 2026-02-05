<?php

namespace App\Services\Payment\Gateways;

use SoapClient;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\GatewayService;

class SamanGateway implements GatewayService
{
    protected string $merchantId;
    protected string $redirectUrl;

    public function __construct()
    {
        $this->merchantId  = config('gateways.saman.merchant_id');
        $this->redirectUrl = route('user.credit-card.callback');
    }

    // public function initiateCharge(
    //     int $referenceId,
    //     int $amount,
    //     ?string $phone = null
    // ): array {
    //     try {
    //         $client = new SoapClient(
    //             'https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL'
    //         );

    //         $token = $client->RequestToken(
    //             $this->merchantId,
    //             (string) $referenceId,
    //             $amount,
    //             $this->redirectUrl,
    //             $phone ?? ''
    //         );

    //         if (!$token || strlen($token) < 10) {
    //             Log::error('Saman token failed', compact('token'));
    //             return ['success' => false, 'message' => 'خطا در دریافت توکن'];
    //         }

    //         return [
    //             'success' => true,
    //             'url' => 'https://sep.shaparak.ir/payment.aspx?Token=' . $token,
    //         ];

    //     } catch (Exception $e) {
    //         Log::error('Saman initiate error: ' . $e->getMessage());
    //         return ['success' => false, 'message' => 'خطای ارتباط با بانک'];
    //     }
    // }

    public function initiateCharge(
        int $referenceId,
        int $amount,
        ?string $phone = null
    ): array {
        try {
            $client = new SoapClient(
                'https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL',
                ['trace' => true]
            );

            $response = $client->RequestToken([
                'MerchantID'  => $this->merchantId,
                'Amount'      => $amount,
                'ResNum'      => (string) $referenceId,
                'RedirectURL' => $this->redirectUrl,
                'CellNumber'  => $phone ?? '',
            ]);

            $token = $response->RequestTokenResult ?? null;

            if (!$token || strlen($token) < 10) {
                Log::error('Saman token failed', [
                    'response' => $response,
                ]);

                return [
                    'success' => false,
                    'message' => 'خطا در دریافت توکن از بانک سامان',
                ];
            }

            return [
                'success' => true,
                'url' => 'https://sep.shaparak.ir/payment.aspx?Token=' . $token,
            ];

        } catch (Exception $e) {
            Log::error('Saman initiate error', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'خطای ارتباط با بانک سامان',
            ];
        }
    }


    public function handleCallback(Request $request): array
    {
        $token  = $request->input('Token');
        $status = $request->input('State');

        if ($status !== 'OK') {
            return ['success' => false, 'message' => 'پرداخت ناموفق بود'];
        }

        try {
            $client = new SoapClient(
                'https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL'
            );

            $result = $client->verifyTransaction($token, $this->merchantId);

            if ($result <= 0) {
                return ['success' => false, 'message' => 'تأیید تراکنش ناموفق'];
            }

            return [
                'success'        => true,
                'amount'         => $result,
                'transaction_id' => $token,
            ];

        } catch (Exception $e) {
            Log::error('Saman verify error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'خطا در تأیید پرداخت'];
        }
    }
}
