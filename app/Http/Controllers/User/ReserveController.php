<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Menu;
use App\Models\User;
use App\Models\MealItem;
use App\Models\MealPlan;
use App\Models\CreditCard;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\ReservationItem;
use App\Services\Menu\MenuService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\CreditCard\CreditCardService;

class ReserveController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;
        $centerId = session('selected_center_id');
        
        // اگر مرکزی انتخاب نشده، پیام خطا نمایش دهید
        if (!$centerId) {
            return back()->with('error', 'لطفاً ابتدا یک مرکز انتخاب کنید.');
        }

        $items = ReservationItem::whereHas('reservation.user', function ($query) use ($userId) {
                $query->where('id', $userId);
            })
            ->whereHas('reservation.center', function ($query) use ($centerId) {
                $query->where('id', $centerId); // فیلتر بر اساس مرکز انتخابی
            })
            ->with('reservation.center')
            ->select('date', 'meal_type', 'food_name', 'quantity', 'reservation_id')
            ->get();

        $groupedItems = $items->groupBy('date')->map(function ($dayItems) {
            $meals = $dayItems->groupBy('meal_type');

            $centerName = $dayItems->first()->reservation?->center?->name ?? 'نامشخص';

            return [
                'date'      => $dayItems->first()->date,
                'center'    => $centerName,
                'breakfast' => $meals->get('breakfast', collect())->values(),
                'lunch'     => $meals->get('lunch', collect())->values(),
                'dinner'    => $meals->get('dinner', collect())->values(),
            ];
        })->sortKeysDesc();

        return view('user.reserves.index', compact('groupedItems'));
    }
}
