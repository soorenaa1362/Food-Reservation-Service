<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\CreditCard;
use App\Services\Payment\GatewayService;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    protected GatewayService $gateway;

    public function __construct(GatewayService $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * شروع فرآیند پرداخت برای کارت اعتباری
     */
    public function initiatePayment(int $userId, int $centerId, int $amount, ?string $phone = null): string
    {
        // کارت کاربر را پیدا کن
        $card = CreditCard::firstOrCreate(
            ['user_id' => $userId, 'center_id' => $centerId],
            ['balance' => 0, 'usable_balance' => 0]
        );

        // ایجاد و درخواست توکن از درگاه سامان
        $result = $this->gateway->initiateCharge($card->id, $amount, $phone);

        if (!$result['success'] || empty($result['url'])) {
            throw new Exception($result['message'] ?? 'خطا در برقراری ارتباط با درگاه');
        }

        return $result['url'];
    }

    /**
     * هندل callback بانک
     */
    public function paymentCallback($request): array
    {
        return $this->gateway->handleCallback($request);
    }
}

