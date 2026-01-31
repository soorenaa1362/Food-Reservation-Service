<?php

namespace App\Repositories\CreditCard;

use App\Models\CreditCard;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Models\ReservationItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CreditCardRepository implements CreditCardRepositoryInterface
{
    /**
     * یافتن کارت اعتباری کاربر برای یک مرکز خاص
     */
    public function findCreditCard(int $userId, int $centerId): ?CreditCard
    {
        try {
            return CreditCard::where('user_id', $userId)
                ->where('center_id', $centerId)
                ->first();
        } catch (\Exception $e) {
            Log::error("Error finding credit card for user_id: $userId, center_id: $centerId - " . $e->getMessage());
            return null;
        }
    }

    /**
     * بررسی موجودی و کسر مبلغ از کارت
     */
    public function checkAndDeductCredit(CreditCard $card, float $totalAmount): bool
    {
        try {
            // بررسی موجودی قابل استفاده
            if ($card->usable_balance < $totalAmount) {
                Log::warning("Insufficient balance for card_id: {$card->id}, required: $totalAmount, available: {$card->usable_balance}");
                return false;
            }

            // کسر مبلغ از balance و به‌روزرسانی available_balance
            $card->balance -= $totalAmount;
            $card->available_balance = $card->balance - $card->reserved_amount;
            $card->last_transaction_at = now();
            $card->save();

            Log::info("Credit deducted successfully for card_id: {$card->id}, amount: $totalAmount");
            return true;
        } catch (\Exception $e) {
            Log::error("Error deducting credit for card_id: {$card->id} - " . $e->getMessage());
            return false;
        }
    }

    /**
     * ثبت رزرو و آیتم‌های آن
     */
    public function storeReservation(Request $request, int $userId, int $centerId, float $totalAmount): Reservation
    {
        try {
            // ثبت رزرو
            $reservation = Reservation::create([
                'user_id' => $userId,
                'center_id' => $centerId,
                'total_amount' => $totalAmount,
                'reservation_date' => now()->toDateString(),
                'status' => 'confirmed',
            ]);

            // ثبت آیتم‌های رزرو
            $cartItems = $request->input('cart_items', []);
            foreach ($cartItems as $dayIndex => $meals) {
                foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                    if (isset($meals[$mealType])) {
                        foreach ($meals[$mealType] as $foodIndex => $item) {
                            $price = (int) ($item['price'] ?? 0);
                            $quantity = (int) ($item['quantity'] ?? 0);
                            if ($quantity > 0) {
                                ReservationItem::create([
                                    'reservation_id' => $reservation->id,
                                    'food_name' => $item['food_name'] ?? '',
                                    'meal_type' => $mealType,
                                    'quantity' => $quantity,
                                    'price' => $price,
                                    'total' => $price * $quantity,
                                    'date' => $item['date'] ?? now()->toDateString(),
                                ]);
                            }
                        }
                    }
                }
            }

            Log::info("Reservation created successfully for user_id: $userId, reservation_id: {$reservation->id}");
            return $reservation;
        } catch (\Exception $e) {
            Log::error("Error storing reservation for user_id: $userId - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ذخیره رزرو در فایل JSON برای مرکز
     */
    public function saveReservationToJson(int $centerId, Reservation $reservation): bool
    {
        try {
            $filePath = "reservations/center_{$centerId}_reservations.json";

            // دریافت آیتم‌های رزرو
            $reservationItems = $reservation->items->map(function ($item) {
                return [
                    'food_name' => $item->food_name,
                    'meal_type' => $item->meal_type,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                    'date' => $item->date,
                ];
            })->toArray();

            // ساختار داده برای ذخیره در JSON
            $reservationData = [
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'center_id' => $reservation->center_id,
                'total_amount' => $reservation->total_amount,
                'reservation_date' => $reservation->reservation_date,
                'status' => $reservation->status,
                'items' => $reservationItems,
                'created_at' => $reservation->created_at->toDateTimeString(),
            ];

            // بارگذاری داده‌های قبلی (اگر فایل وجود داشته باشد)
            $existingData = [];
            if (Storage::disk('local')->exists($filePath)) {
                $json = Storage::disk('local')->get($filePath);
                $existingData = json_decode($json, true) ?: [];
                if (!is_array($existingData)) {
                    $existingData = [];
                }
            }

            // اضافه کردن رزرو جدید به داده‌های قبلی
            $existingData[] = $reservationData;

            // ذخیره در فایل JSON
            Storage::disk('local')->put($filePath, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Log::info("Reservation saved to JSON for center_id: $centerId, reservation_id: {$reservation->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error saving reservation to JSON for center_id: $centerId - " . $e->getMessage());
            return false;
        }
    }
}