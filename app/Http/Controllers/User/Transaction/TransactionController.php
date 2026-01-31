<?php

namespace App\Http\Controllers\User\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Center;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment; // اگر از shetabit/payment استفاده می‌کنی
// یا use Shetabit\Multipay\Facades\Payment; — بسته به نسخه

class TransactionController extends Controller
{
    public function startPayment(Request $request)
    {
        dd($request->all());
        $user = Auth::user();

        // 1. اعتبارسنجی
        $validated = $request->validate([
            'amount'     => 'required|integer|min:10000|max:50000000', // تومان
            'center_id'  => 'required|integer|exists:centers,id',
        ]);

        $amountInToman = $validated['amount'];
        $amountInRial  = $amountInToman * 10; // تبدیل به ریال (درگاه‌ها به ریال کار می‌کنن)
        $centerId      = $validated['center_id'];

        // چک کنیم کاربر به این مرکز دسترسی داره
        $center = Center::findOrFail($centerId);
        if (!$user->centers()->where('center_id', $centerId)->exists()) {
            return back()->with('error', 'مرکز انتخاب شده معتبر نیست.');
        }

        // 2. ایجاد تراکنش pending
        $transaction = Transaction::create([
            'user_id'    => $user->id,
            'center_id'  => $centerId,
            'amount'     => $amountInRial, // ذخیره به ریال
            'gateway'    => 'zarinpal',    // یا هر درگاهی که استفاده می‌کنی
            'status'     => Transaction::STATUS_PENDING,
            'description'=> "شارژ کیف پول - مبلغ {$amountInToman} تومان",
            'meta'       => [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'from'       => 'web-increase-credit',
            ],
        ]);

        // 3. آماده‌سازی فاکتور برای زرین‌پال
        $invoice = new Invoice();
        $invoice->amount($amountInRial); // حتماً به ریال
        $invoice->detail([
            'description' => "شارژ کیف پول - کاربر {$user->id} - مرکز {$center->name}",
            'mobile'      => $user->national_code ?? null, // اختیاری
            'email'       => null,
        ]);

        // 4. ارسال به درگاه + دریافت authority
        try {
            $payment = Payment::callbackUrl(route('user.transactions.payment-callback'))
                ->purchase($invoice, function ($driver, $transactionId) use ($transaction) {
                    // این کلوجر وقتی اجرا میشه که درگاه authority بده
                    $transaction->update([
                        'authority' => $transactionId
                    ]);
                });

            // ریدایرکت خودکار به درگاه
            return $payment->redirect();

        } catch (\Exception $e) {
            // اگر خطا داد (مثلاً درگاه در دسترس نبود)
            $transaction->update([
                'status' => Transaction::STATUS_FAILED,
                'meta'   => array_merge($transaction->meta ?? [], ['error' => $e->getMessage()])
            ]);

            return back()->with('error', 'خطا در اتصال به درگاه پرداخت. لطفاً دوباره تلاش کنید.');
        }
    }
}
