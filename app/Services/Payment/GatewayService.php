<?php

namespace App\Services\Payment;

use Illuminate\Http\Request;

interface GatewayService
{
    /**
     * شروع پرداخت و گرفتن آدرس هدایت به درگاه
     *
     * @param int         $referenceId   شناسه مرجع داخلی (مثلاً payment_order_id)
     * @param int         $amount        مبلغ به ریال
     * @param string|null $phone         شماره موبایل (اختیاری)
     *
     * @return array{
     *     success: bool,
     *     url?: string,
     *     message?: string
     * }
     */
    public function initiateCharge(
        int $referenceId,
        int $amount,
        ?string $phone = null
    ): array;

    /**
     * پردازش callback بانک بعد از پرداخت
     *
     * @param Request $request
     *
     * @return array{
     *     success: bool,
     *     reference_id?: int,
     *     transaction_id?: string,
     *     amount?: int,
     *     message?: string
     * }
     */
    public function handleCallback(Request $request): array;
}
