<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Center;
use App\Models\CreditCard;
use Illuminate\Http\Request;
use App\Services\MenuService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class FoodReservationController extends Controller
{
    protected MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index(Request $request)
    {
        // دریافت مرکز انتخاب‌شده از سشن (فرض می‌کنیم قبلاً در انتخاب مرکز ست شده)
        $selectedCenter = $request->session()->get('selected_center');

        if (!$selectedCenter || !isset($selectedCenter['id'])) {
            return redirect()->route('user.select-center.index') // یا هر روتی که داری
                ->with('error', 'لطفاً ابتدا یک مرکز را انتخاب کنید.');
        }

        $centerId = $selectedCenter['id'];
        $center = Center::findOrFail($centerId);

        // دریافت کارت اعتباری کاربر برای این مرکز
        $creditCard = CreditCard::where('user_id', auth()->id())
            ->where('center_id', $centerId)
            ->first();

        // اگر کارت اعتباری وجود نداشت، مقدار پیش‌فرض صفر
        $balance = $creditCard?->balance ?? 0;

        try {
            // دریافت منوهای ماه جاری (از امروز تا آخر ماه)
            $days = $this->menuService->getMenusForCurrentMonth($centerId);

            if (empty($days)) {
                return redirect()->back()->with('info', 'برای مرکز انتخاب‌شده در این ماه منوی غذایی ثبت نشده است.');
            }

            return view('user.food-reservation.index', compact('days', 'center', 'balance', 'creditCard'));

        } catch (\Exception $e) {
            \Log::error('Error loading food menus: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'center_id' => $centerId,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'خطایی در بارگذاری منوی غذا رخ داد. لطفاً مجدداً تلاش کنید.');
        }
    }

    public function checkCredit(Request $request)
    {
        $selectedCenter = $request->session()->get('selected_center');
        if (!$selectedCenter || !isset($selectedCenter['id'])) {
            return redirect()->back()->with('error', 'لطفاً ابتدا یک مرکز انتخاب کنید.');
        }

        $centerId = $selectedCenter['id'];
        $user = auth()->user();

        try {
            $result = $this->menuService->processReservation($request, $user->id, $centerId);

            if ($result['success']) {
                return redirect()->route('user.reserves.index')
                    ->with('success', 'رزرو غذای شما با موفقیت ثبت شد.');
            }

            // اگر موجودی کافی نبود
            if ($result['error_type'] === 'insufficient_balance') {
                $creditCard = CreditCard::where('user_id', $user->id)
                    ->where('center_id', $centerId)
                    ->first();

                return redirect()->route('user.credit-card.increase')
                    ->with('credit_card', $creditCard)
                    ->with('error', 'موجودی کارت اعتباری شما کافی نیست.');
            }

            // خطاهای دیگر (ددلاین، سهم تموم شده و ...)
            return redirect()->back()->with('error', $result['message']);

        } catch (\Exception $e) {
            \Log::error('Reservation failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'center_id' => $centerId,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'خطایی در ثبت رزرو رخ داد. لطفاً مجدداً تلاش کنید.');
        }
    }
}