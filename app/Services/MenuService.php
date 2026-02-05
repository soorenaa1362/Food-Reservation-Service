<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Meal;
use App\Models\MealItem;
use App\Models\CreditCard;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\CreditLedger;
use Illuminate\Http\Request;
use App\Models\ReservationItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Transaction\TransactionService;

class MenuService
{
    // ددلاین‌ها برای هر وعده (ساعت به صورت 24 ساعته)
    private const DEADLINES = [
        'breakfast' => 9,   // تا ساعت 09:00
        'lunch'     => 16,  // تا ساعت 16:00
        'dinner'    => 21,  // تا ساعت 21:00
    ];

    private const PERSIAN_NAMES = [
        'breakfast' => 'صبحانه',
        'lunch'     => 'ناهار',
        'dinner'    => 'شام',
    ];

    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * دریافت منوهای از امروز تا آخر ماه جاری
     */
    public function getMenusForCurrentMonth(int $centerId): array
    {
        $today = Carbon::today();
        $endOfMonth = Carbon::now()->endOfMonth();

        $meals = Meal::with(['items' => fn($q) => $q->orderBy('meal_type')->orderBy('id')])
            ->where('center_id', $centerId)
            ->whereBetween('date', [$today, $endOfMonth])
            ->orderBy('date')
            ->get();

        if ($meals->isEmpty()) {
            return [];
        }

        $now = Carbon::now();
        $currentHour = $now->hour;

        $days = $meals->map(function (Meal $meal) use ($now, $currentHour) {
            $isToday = $meal->date->isToday();

            return [
                'date'      => $meal->date->format('Y-m-d'),
                'is_today'  => $isToday,
                'breakfast' => $this->prepareMeal($meal->breakfast, $isToday, $currentHour, 'breakfast'),
                'lunch'     => $this->prepareMeal($meal->lunch, $isToday, $currentHour, 'lunch'),
                'dinner'    => $this->prepareMeal($meal->dinner, $isToday, $currentHour, 'dinner'),
            ];
        })->values()->all();

        return $days;
    }

    /**
     * آماده‌سازی آیتم‌های یک وعده (با چک ددلاین)
     */
    private function prepareMeal(Collection $items, bool $isToday, int $currentHour, string $mealType): array
    {
        if ($items->isEmpty()) {
            return [];
        }

        $deadlineHour = self::DEADLINES[$mealType];
        $persianName = self::PERSIAN_NAMES[$mealType];

        if (!$isToday) {
            return $this->formatNormalItems($items);
        }

        if ($currentHour >= $deadlineHour) {
            return [[
                'deadline_passed' => true,
                'message'         => "مهلت رزرو {$persianName} تا ساعت " . sprintf('%02d:00', $deadlineHour) . " است.",
                'is_reservable'   => false,
            ]];
        }

        return $this->formatNormalItems($items);
    }

    /**
     * فرمت آیتم‌های عادی (قابل رزرو)
     */
    private function formatNormalItems(Collection $items): array
    {
        return $items->map(function ($item) {
            return [
                'id'                 => $item->id,
                'food_name'          => $item->food_name,
                'price'              => $item->price,
                'portions'           => $item->portions,
                'reserved_count'     => $item->reserved_count,
                'available_portions' => $item->available_portions,
                'is_reservable'      => $item->is_reservable,
                'deadline_passed'    => false,
            ];
        })->values()->all();
    }

    /**
     * پردازش نهایی رزرو غذا
     */
    public function processReservation(Request $request, int $userId, int $centerId): array
    {
        $cartItems = $request->input('cart_items', []);

        if (empty($cartItems)) {
            return [
                'success' => false,
                'message' => 'هیچ غذایی انتخاب نشده است.'
            ];
        }

        $totalAmount = 0;
        $reservationItemsData = [];
        $mealItemsToIncrement = [];
        $now = Carbon::now();

        try {
            DB::beginTransaction();

            // مرحله 1: اعتبارسنجی ددلاین و محاسبه مبلغ کل
            foreach ($cartItems as $dayData) {
                foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                    if (!isset($dayData[$mealType])) continue;

                    foreach ($dayData[$mealType] as $item) {
                        $quantity = (int)($item['quantity'] ?? 0);
                        if ($quantity <= 0) continue;

                        $price = (int)($item['price'] ?? 0);
                        $dateStr = $item['date'] ?? null;
                        $foodName = $item['food_name'] ?? null;

                        if (!$dateStr || !$foodName) {
                            throw new \Exception('اطلاعات آیتم رزرو ناقص است.');
                        }

                        $date = Carbon::parse($dateStr);

                        // چک ددلاین برای رزرو همان روز
                        if ($date->isToday() && $now->hour >= self::DEADLINES[$mealType]) {
                            throw new \Exception(
                                "مهلت رزرو " . self::PERSIAN_NAMES[$mealType] . 
                                " تا ساعت " . sprintf('%02d:00', self::DEADLINES[$mealType]) . " است."
                            );
                        }

                        // پیدا کردن MealItem با lock
                        $mealItem = MealItem::whereHas('meal', function ($q) use ($centerId, $date) {
                            $q->where('center_id', $centerId)->where('date', $date);
                        })
                        ->where('meal_type', $mealType)
                        ->where('food_name', $foodName)
                        ->lockForUpdate()
                        ->first();

                        if (!$mealItem) {
                            throw new \Exception("غذای «{$foodName}» در تاریخ {$dateStr} یافت نشد.");
                        }

                        if ($quantity > $mealItem->available_portions) {
                            throw new \Exception(
                                "سهم کافی برای «{$foodName}» موجود نیست (درخواست: {$quantity}، موجود: {$mealItem->available_portions})."
                            );
                        }

                        $totalAmount += $price * $quantity;

                        $reservationItemsData[] = [
                            'meal_item_id' => $mealItem->id,
                            'quantity'     => $quantity,
                            'unit_price'   => $price,
                            'food_name'    => $foodName,
                            'meal_type'    => $mealType,
                            'date'         => $dateStr,
                        ];

                        $mealItemsToIncrement[$mealItem->id] = ($mealItemsToIncrement[$mealItem->id] ?? 0) + $quantity;
                    }
                }
            }

            if ($totalAmount === 0) {
                throw new \Exception('هیچ موردی برای رزرو انتخاب نشده است.');
            }

            // مرحله 2: بررسی و کسر موجودی کارت اعتباری
            $creditCard = CreditCard::where('user_id', $userId)
                ->where('center_id', $centerId)
                ->lockForUpdate()
                ->first();

            if (!$creditCard || $creditCard->balance < $totalAmount) {
                return [
                    'success' => false,
                    'error_type' => 'insufficient_balance',
                    'message' => 'موجودی کارت اعتباری کافی نیست.'
                ];
            }

            $balanceBefore = $creditCard->balance;
            $creditCard->decrement('balance', $totalAmount);
            $balanceAfter = $creditCard->balance;

            // مرحله 3: افزایش reserved_count برای MealItemها
            foreach ($mealItemsToIncrement as $itemId => $qty) {
                MealItem::where('id', $itemId)->increment('reserved_count', $qty);
            }

            // مرحله 4: ایجاد رزرو اصلی
            $reservation = Reservation::create([
                'user_id' => $userId,
                'center_id' => $centerId,
                'total_amount' => $totalAmount,
                'reservation_date' => now()->toDateString(),
                'status' => 'confirmed',
                'reserved_at' => now(),
            ]);

            // مرحله 5: ایجاد آیتم‌های رزرو
            foreach ($reservationItemsData as $data) {
                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'meal_item_id' => $data['meal_item_id'],
                    'food_name' => $data['food_name'],
                    'meal_type' => $data['meal_type'],
                    'quantity' => $data['quantity'],
                    'price' => $data['unit_price'],
                    'total' => $data['unit_price'] * $data['quantity'],
                    'date' => $data['date'],
                ]);
            }

            // مرحله 6: ثبت تراکنش رزرو غذا
            $transaction = $this->transactionService->createTransactionForFoodReserve(
                $userId, $centerId, $totalAmount
            );

            // مرحله 7: ثبت CreditLedger
            CreditLedger::create([
                'transaction_id'    =>  $transaction->id,
                'user_id'           =>  $userId,
                'center_id'         =>  $centerId,
                'credit_card_id'    =>  $creditCard->id,
                'amount'            =>  -$totalAmount,
                'balance_before'    =>  $balanceBefore,
                'balance_after'     =>  $balanceAfter,
                'type'              =>  CreditLedger::TYPE_DECREASE,
                'source_type'       =>  CreditLedger::SOURCE_PAYMENT,
                'source_id'         =>  $transaction->id,
                'description'       =>  "کسر اعتبار برای رزرو غذا (رزرو شماره {$reservation->id})",
            ]);

            DB::commit();

            return ['success' => true];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Food reservation failed', [
                'user_id' => $userId,
                'center_id' => $centerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage() ?: 'خطایی در ثبت رزرو رخ داد.'
            ];
        }
    }

}