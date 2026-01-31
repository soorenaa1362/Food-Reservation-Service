<?php

namespace App\Services\CreditCard;

use App\Repositories\CreditCard\CreditCardRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\CreditCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditCardService
{
    protected $creditCardRepository;

    public function __construct(CreditCardRepositoryInterface $creditCardRepository)
    {
        $this->creditCardRepository = $creditCardRepository;
    }


    public function processReservation(Request $request, int $userId, int $centerId): bool
    {
        $request->validate([
            'cart_items' => 'required|array',
            'cart_items.*.*.*.food_name' => 'required|string',
            'cart_items.*.*.*.price' => 'required|numeric|min:0',
            'cart_items.*.*.*.quantity' => 'required|integer|min:0',
            'cart_items.*.*.*.date' => 'required|date',
        ]);

        // محاسبه مجموع مبلغ پرداختی
        $totalAmount = 0;
        $cartItems = $request->input('cart_items', []);

        foreach ($cartItems as $dayIndex => $meals) {
            foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                if (isset($meals[$mealType])) {
                    foreach ($meals[$mealType] as $foodIndex => $item) {
                        $price = (int) ($item['price'] ?? 0);
                        $quantity = (int) ($item['quantity'] ?? 0);
                        $totalAmount += $price * $quantity;
                    }
                }
            }
        }

        // شروع تراکنش
        DB::beginTransaction();
        try {
            // یافتن کارت اعتباری
            $card = $this->creditCardRepository->findCreditCard($userId, $centerId);
            if (!$card) {
                Log::error("No credit card found for user_id: $userId, center_id: $centerId");
                return false;
            }

            // بررسی و کسر موجودی
            if (!$this->creditCardRepository->checkAndDeductCredit($card, $totalAmount)) {
                DB::rollBack();
                Log::warning("Insufficient balance for user_id: $userId, required: $totalAmount");
                return false;
            }

            // ثبت رزرو
            $reservation = $this->creditCardRepository->storeReservation($request, $userId, $centerId, $totalAmount);

            // ذخیره رزرو در فایل JSON
            if (!$this->creditCardRepository->saveReservationToJson($centerId, $reservation)) {
                DB::rollBack();
                Log::error("Failed to save reservation to JSON for center_id: $centerId, reservation_id: {$reservation->id}");
                return false;
            }

            // تأیید تراکنش
            DB::commit();
            Log::info("Reservation processed successfully for user_id: $userId, reservation_id: {$reservation->id}");
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing reservation for user_id: $userId - " . $e->getMessage());
            return false;
        }
    }


    public function getUserCreditCard(int $userId, int $centerId): ?CreditCard
    {
        return $this->creditCardRepository->findCreditCard($userId, $centerId);
    }


    public function getCenterCreditCard(int $userId, int $centerId): ?CreditCard
    {
        try {
            return CreditCard::where('user_id', $userId)
                ->where('center_id', $centerId)->first();
        } catch (\Exception $e) {
            Log::error("Error finding credit card for center_id: $centerId" . $e->getMessage());
            return null;
        }
    }
    

    // public function increaseBalance(int $userId, int $centerId, int $amount): array
    // {
    //     return DB::transaction(function () use ($userId, $centerId, $amount) {
    //         $creditCard = CreditCard::where('user_id', $userId)
    //             ->where('center_id', $centerId)
    //             ->lockForUpdate()    // حیاتی: جلوگیری از race condition
    //             ->first();

    //         if (!$creditCard) {
    //             // بهتره اینجا یه Exception پرت کنیم یا false برگردونیم
    //             return [
    //                 'success' => false,
    //                 'message' => 'کارت اعتباری یافت نشد.'
    //             ];
    //         }

    //         // افزایش همزمان دو فیلد به صورت اتمیک و امن
    //         $creditCard->increment('balance', $amount);
    //         $creditCard->increment('available_balance', $amount);

    //         // اختیاری: ثبت لاگ تراکنش
    //         // $creditCard->transactions()->create([
    //         //     'amount'      => $amount,
    //         //     'type'        => 'deposit',
    //         //     'status'      => 'success',
    //         //     'description' => 'شارژ آفلاین (تست)',
    //         // ]);

    //         return ['success' => true];
    //     });
    // }

    public function increaseBalance(int $userId, int $centerId, int $amount): array
    {
        return DB::transaction(function () use ($userId, $centerId, $amount) {
            // پیدا کردن کارت اعتباری کاربر در مرکز انتخاب شده
            $creditCard = CreditCard::where('user_id', $userId)
                ->where('center_id', $centerId)
                ->lockForUpdate() // جلوگیری از race condition
                ->first();

            if (!$creditCard) {
                // اگر کارت اعتباری وجود نداشت، ایجاد می‌کنیم
                $creditCard = CreditCard::create([
                    'user_id' => $userId,
                    'center_id' => $centerId,
                    'balance' => $amount,
                    'initial_credit' => 0,
                    'membership_type' => 'regular',
                    'credit_expires_at' => now()->addYear(),
                ]);
                
                return [
                    'success' => true,
                    'message' => 'کارت اعتباری جدید ایجاد و شارژ شد.'
                ];
            }

            // افزایش موجودی (فقط فیلد balance)
            $creditCard->increment('balance', $amount);

            return ['success' => true];
        });
    }
}