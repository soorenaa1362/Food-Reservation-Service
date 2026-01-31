<?php

namespace App\Http\Controllers\API;

use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Models\ReservationItem;
use App\Http\Controllers\Controller;

class HisReservationController extends Controller
{
    /**
     * پاسخ به HIS با رزروهای مرکز و بازه مشخص
     * پارامترهای query:
     *  - center_id (اختیاری)
     *  - date (اختیاری) YYYY-MM-DD
     */
    public function index(Request $request)
    {
        $centerId = $request->query('center_id');
        $date = $request->query('date');
        
        // اضافه کردن center به with
        $query = Reservation::with(['user', 'items', 'center']);

        if ($centerId) {
            $query->where('center_id', $centerId);
        }

        if ($date) {
            $query->whereDate('reservation_date', $date);
        }

        $reservations = $query->get();

        $result = $reservations->map(function ($reservation) {
            $nationalCode = $reservation->user->national_code_hashed;
            $centerName = $reservation->center->name ?? null; // دریافت نام مرکز
            
            return $reservation->items->map(function ($item) use ($reservation, $nationalCode, $centerName) {
                return [
                    'national_code' => $nationalCode,
                    'center_id'     => $reservation->center_id,
                    'center_name'   => $centerName, // اضافه کردن نام مرکز
                    'date'          => $item->date->format('Y-m-d'),
                    'meal_type'     => $item->meal_type,
                    'food_name'     => $item->food_name,
                    'quantity'      => $item->quantity,
                    'price'         => $item->price,
                    'total'         => $item->total,
                    'reserved_at'   => $reservation->reserved_at->toIso8601String(),
                ];
            });
        })->flatten(1);

        return response()->json([
            'reservations' => $result
        ]);
    }
}
