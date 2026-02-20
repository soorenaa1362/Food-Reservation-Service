<?php

namespace App\Services;

use App\Models\CenterMealDeadline;
use App\Models\CreditCard;
use App\Models\CreditLedger;
use App\Models\Meal;
use App\Models\MealItem;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Transaction;
use App\Services\Transaction\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;

class MenuService
{
    // ثابت نام‌های فارسی (برای نمایش در پیام‌ها)
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
     * دریافت لیست روزها از امروز تا آخر ماه شمسی
     */
    public function getMenusForCurrentMonth(int $centerId): array
    {
        $today = Carbon::today();
        $currentHour = Carbon::now()->hour;

        // --- دریافت ددلاین‌های فعال این مرکز ---
        $deadlines = CenterMealDeadline::where('center_id', $centerId)
            ->where('is_active', true)
            ->get()
            ->keyBy('meal_type'); // ایندکس کردن بر اساس نوع وعده

        // --- محاسبه بازه زمانی: امروز تا آخر ماه شمسی ---
        $jNow = \Morilog\Jalali\Jalalian::now();
        $year = $jNow->getYear();
        $month = $jNow->getMonth();

        $nextMonth = $month + 1;
        $nextYear = $year;

        if ($month > 11) { 
            $nextMonth = 1;
            $nextYear = $year + 1;
        }

        $firstOfNextMonthJalali = new \Morilog\Jalali\Jalalian($nextYear, $nextMonth, 1);
        $endOfMonth = $firstOfNextMonthJalali->subDays(1)->toCarbon();

        // دریافت منوها
        $meals = Meal::with(['items' => fn($q) => $q->orderBy('meal_type')->orderBy('id')])
            ->where('center_id', $centerId)
            ->whereBetween('date', [$today, $endOfMonth])
            ->orderBy('date')
            ->get();

        $mealsByKey = $meals->keyBy(function ($item) {
            return $item->date->format('Y-m-d');
        });

        $days = [];
        $currentDate = $today->copy();

        while ($currentDate->lte($endOfMonth)) {
            $dateString = $currentDate->format('Y-m-d');
            $isToday = $currentDate->isToday();

            if ($mealsByKey->has($dateString)) {
                $meal = $mealsByKey->get($dateString);
                
                // ارسال ددلاین مربوط به هر وعده به متد prepareMeal
                $days[] = [
                    'date'      => $dateString,
                    'is_today'  => $isToday,
                    'breakfast' => $this->prepareMeal($meal->breakfast ?? collect(), $isToday, $currentHour, 'breakfast', $deadlines->get('breakfast')),
                    'lunch'     => $this->prepareMeal($meal->lunch ?? collect(), $isToday, $currentHour, 'lunch', $deadlines->get('lunch')),
                    'dinner'    => $this->prepareMeal($meal->dinner ?? collect(), $isToday, $currentHour, 'dinner', $deadlines->get('dinner')),
                ];
            } else {
                $days[] = [
                    'date'      => $dateString,
                    'is_today'  => $isToday,
                    'breakfast' => [],
                    'lunch'     => [],
                    'dinner'    => [],
                ];
            }

            $currentDate->addDay();
        }

        return $days;
    }

    /**
     * آماده‌سازی آیتم‌های یک وعده (با چک ددلاین داینامیک)
     */
    private function prepareMeal(Collection $items, bool $isToday, int $currentHour, string $mealType, ?CenterMealDeadline $deadline): array
    {
        if ($items->isEmpty()) {
            return [];
        }

        // اگر ددلاین یافت نشد یا غیرفعال بود، به منزله بسته بودن رزرو است (ایمن‌ترین حالت)
        if (!$deadline || !$deadline->is_active) {
             return [[
                'deadline_passed' => true,
                'message'         => "امکان رزرو برای " . self::PERSIAN_NAMES[$mealType] . " فعلاً غیرفعال است.",
                'is_reservable'   => false,
            ]];
        }

        // چک کردن زمان: اگر امروز است و ساعت فعلی از ساعت پایان (reservation_to_hour) گذشته باشد
        if ($isToday && $currentHour > $deadline->reservation_to_hour) {
            return [[
                'deadline_passed' => true,
                'message'         => "مهلت رزرو " . self::PERSIAN_NAMES[$mealType] . " تا ساعت " . sprintf('%02d:00', $deadline->reservation_to_hour) . " بوده است.",
                'is_reservable'   => false,
            ]];
        }
        
        // چک کردن ساعت شروع (اختیاری): اگر می‌خواهید قبل از ساعت شروع هم نمایش داده نشود، خط زیر را فعال کنید
        // if ($isToday && $currentHour < $deadline->reservation_from_hour) { ... }

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
        
        // دریافت ددلاین‌های فعال برای اعتبارسنجی سمت سرور
        $deadlines = CenterMealDeadline::where('center_id', $centerId)
            ->where('is_active', true)
            ->get()
            ->keyBy('meal_type');

        if (empty($cartItems)) {
            return ['success' => false, 'message' => 'هیچ غذایی انتخاب نشده است.'];
        }

        $totalAmount = 0;
        $reservationItemsData = [];
        $mealItemsToIncrement = [];
        $now = Carbon::now();

        try {
            DB::beginTransaction();

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

                        // --- اعتبارسنجی ددلاین با استفاده از مدل ---
                        $deadline = $deadlines->get($mealType);

                        // 1. اگر ددلاین تعریف نشده یا غیرفعال باشد
                        if (!$deadline || !$deadline->is_active) {
                            throw new \Exception("رزرو " . self::PERSIAN_NAMES[$mealType] . " برای این مرکز غیرفعال است.");
                        }

                        // 2. چک کردن زمان برای رزرو همان روز
                        if ($date->isToday() && $now->hour > $deadline->reservation_to_hour) {
                            throw new \Exception(
                                "مهلت رزرو " . self::PERSIAN_NAMES[$mealType] . 
                                " تا ساعت " . sprintf('%02d:00', $deadline->reservation_to_hour) . " بوده و تمام شده است."
                            );
                        }
                        // --- پایان اعتبارسنجی ددلاین ---

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

            // مرحله 3: افزایش reserved_count
            foreach ($mealItemsToIncrement as $itemId => $qty) {
                MealItem::where('id', $itemId)->increment('reserved_count', $qty);
            }

            // مرحله 4: ایجاد رزرو
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

            // مرحله 6 و 7: تراکنش و لجر
            $transaction = $this->transactionService->createTransactionForFoodReserve($userId, $centerId, $totalAmount);

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