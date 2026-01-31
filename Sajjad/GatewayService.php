<?php

namespace App\Services;

use App\Clients\Gateways\GatewaySaman;
use App\Models\Payment;
use App\Models\CreditCard; // مدل کیف پول شما
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GatewayService
{
    protected $gatewaySaman;

    public function __construct(GatewaySaman $gatewaySaman)
    {
        $this->gatewaySaman = $gatewaySaman;
    }

    /**
     * ایجاد درخواست پرداخت برای افزایش اعتبار کیف پول
     */
    public function initiateCharge(int $cardId, int $amount, ?string $phone = null)
    {
        // مبلغ باید به ریال باشه برای سامان
        $amountInRial = $amount * 10;

        // ایجاد رکورد پرداخت (یا تراکنش موقت)
        $payment = Payment::create([
            'user_id'     => auth()->id(),
            'card_id'     => $cardId,
            'amount'      => $amount, // تومان ذخیره کن
            'amount_rial' => $amountInRial,
            'gateway'     => 'saman',
            'status'      => 'pending',
            'ref_id'      => 'charge_' . time() . '_' . auth()->id(),
        ]);

        $token = $this->gatewaySaman->requestToken(
            $payment->id, // به عنوان ResNum استفاده می‌شه
            $amountInRial,
            $phone
        );

        if (!$token) {
            $payment->update(['status' => 'failed']);
            return ['success' => false, 'message' => 'خطا در ارتباط با درگاه'];
        }

        $payment->update(['token' => $token]);

        return [
            'success' => true,
            'token'   => $token,
            'url'     => 'https://sep.shaparak.ir/payment.aspx?Token=' . $token,
        ];
    }

    /**
     * هندل کردن بازگشت از بانک (callback)
     */
    public function handleCallback($request)
    {
        $token = $request->Token;
        $status = $request->State ?? $request->status;

        if ($status !== 'OK') {
            return ['success' => false, 'message' => 'پرداخت ناموفق بود'];
        }

        $payment = Payment::where('token', $token)->firstOrFail();

        // جلوگیری از وریفای تکراری
        if ($payment->status === 'completed') {
            return ['success' => true, 'message' => 'پرداخت قبلاً تأیید شده'];
        }

        $verify = $this->gatewaySaman->verifyTransaction($token, $payment->amount_rial);

        if (!$verify['success']) {
            $payment->update(['status' => 'failed']);
            return ['success' => false, 'message' => 'تراکنش تأیید نشد'];
        }

        DB::transaction(function () use ($payment, $verify) {
            // افزایش موجودی کیف پول
            $card = CreditCard::findOrFail($payment->card_id);
            $card->balance += $payment->amount;
            $card->usable_balance += $payment->amount;
            $card->save();

            // به‌روزرسانی پرداخت
            $payment->update([
                'status'     => 'completed',
                'ref_num'    => $verify['RefNum'] ?? null,
                'verified_at'=> now(),
            ]);
        });

        return ['success' => true, 'message' => 'پرداخت با موفقیت انجام شد'];
    }
}