<?php

namespace App\Http\Controllers\API;

use App\Models\Reservation;
use App\Models\ReservationItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class HisReservationController extends Controller
{
    /**
     * دریافت لیست رزروهای ارسال‌نشده برای HIS
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'center_id'   => ['nullable', 'integer', 'exists:centers,id'],
            'date'        => ['nullable', 'date_format:Y-m-d'],
            'from_date'   => ['nullable', 'date_format:Y-m-d', 'required_with:to_date'],
            'to_date'     => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'user_id'     => ['nullable', 'integer', 'exists:users,id'],
            'user_name'   => ['nullable', 'string'],
            'page'        => ['nullable', 'integer', 'min:1'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $perPage = $request->input('per_page', 50);
        $page    = $request->input('page', 1);

        // کوئری با شرط فقط رزروهایی که حداقل یک آیتم ارسال‌نشده دارند
        $query = Reservation::with(['user', 'items', 'center'])
            ->whereHas('items', function ($q) {
                $q->whereNull('sent_to_his');
            });

        // فیلتر مرکز
        if ($centerId = $request->input('center_id')) {
            $query->where('center_id', $centerId);
        }

        // فیلتر تاریخ یا بازه
        if ($date = $request->input('date')) {
            $query->whereDate('reservation_date', $date);
        } elseif ($request->filled(['from_date', 'to_date'])) {
            $query->whereBetween('reservation_date', [
                $request->input('from_date'),
                $request->input('to_date')
            ]);
        }

        // فیلتر کاربر
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        } elseif ($userName = $request->input('user_name')) {
            $query->whereHas('user', function ($q) use ($userName) {
                $q->where('name', 'like', "%{$userName}%");
            });
        }

        // صفحه‌بندی
        $reservations = $query->paginate($perPage, ['*'], 'page', $page);

        // اگر هیچ رکوردی نبود
        if ($reservations->isEmpty()) {
            return response()->json([
                'status'       => 'success',
                'reservations' => [],
                'pagination'   => [
                    'current_page' => 1,
                    'per_page'     => (int) $perPage,
                    'total'        => 0,
                    'last_page'    => 1,
                    'from'         => null,
                    'to'           => null,
                ]
            ]);
        }

        $sentItemIds = [];

        // ساخت آرایه خروجی فقط از آیتم‌های ارسال‌نشده
        $result = $reservations->getCollection()->map(function ($reservation) use (&$sentItemIds) {
            $nationalCode = $reservation->user?->national_code_hashed ?? null;
            $centerName   = $reservation->center?->name ?? null;

            return $reservation->items
                ->whereNull('sent_to_his')
                ->map(function ($item) use ($reservation, $nationalCode, $centerName, &$sentItemIds) {
                    $sentItemIds[] = $item->id;

                    return [
                        'national_code' => $nationalCode,
                        'center_id'     => $reservation->center_id,
                        'center_name'   => $centerName,
                        'date'          => $item->date?->format('Y-m-d') ?? null,
                        'meal_type'     => $item->meal_type,
                        'food_name'     => $item->food_name,
                        'quantity'      => (int) $item->quantity,
                        'price'         => (int) $item->price,
                        'total'         => (int) $item->total,
                        'reserved_at'   => $reservation->reserved_at?->toIso8601String(),
                    ];
                });
        })->flatten(1)->values();

        // آپدیت وضعیت ارسال (اتمی)
        if (!empty($sentItemIds)) {
            DB::transaction(function () use ($sentItemIds) {
                ReservationItem::whereIn('id', array_unique($sentItemIds))
                    ->update(['sent_to_his' => now()]);
            });
        }

        return response()->json([
            'status'       => 'success',
            'reservations' => $result,
            'pagination'   => [
                'current_page' => $reservations->currentPage(),
                'per_page'     => $reservations->perPage(),
                'total'        => $reservations->total(),
                'last_page'    => $reservations->lastPage(),
                'from'         => $reservations->firstItem(),
                'to'           => $reservations->lastItem(),
            ]
        ]);
    }
}