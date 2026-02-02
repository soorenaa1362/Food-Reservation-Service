<?php

namespace App\Http\Controllers\User\Dashboard;

use App\Models\ReservationItem;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\CreditCard\CreditCardService;
use App\Services\Transaction\TransactionService;

class DashboardController extends Controller
{
    protected CreditCardService $creditCardService;

    public function __construct(
        CreditCardService $creditCardService,
        TransactionService $transactionService
    )
    {
        $this->creditCardService = $creditCardService;
        $this->transactionService = $transactionService;
    }

    public function index()
    {
        $user = Auth::user();
        $selectedCenter = session('selected_center');

        // اگر به دليلي session نباشه، کاربر رو به انتخاب مرکز بفرست
        if (!$selectedCenter) {
            return redirect()->route('user.select-center.index')
                ->with('error', 'لطفاً ابتدا مرکز خود را انتخاب کنید.');
        }

        $creditCard = $this->creditCardService->getCenterCreditCard($user->id, $selectedCenter->id);

        $userId = $user->id;
        $centerId = $selectedCenter->id;
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

        $transactions = $this->transactionService->getAllCenterTransactions($userId, $centerId);        

        return view('user.dashboard.dashboard', compact([
            'selectedCenter',
            'creditCard',
            'groupedItems',
            'transactions'
        ]));
    }
}