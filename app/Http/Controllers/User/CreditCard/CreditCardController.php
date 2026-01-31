<?php

namespace App\Http\Controllers\User\CreditCard;

use App\Models\CreditCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Center\CenterService;
use App\Services\CreditCard\CreditCardService;

class CreditCardController extends Controller
{
    protected $creditCardService, $centerService;

    public function __construct(CreditCardService $creditCardService, CenterService $centerService)
    {
        $this->creditCardService = $creditCardService;
        $this->centerService = $centerService;
    }

    public function index()
    {
        $user = Auth::user();
        $selectedCenter = $this->centerService->getSelectedCenter();

        if (!$selectedCenter) {
            return redirect()->route('user.select-center.index')
                ->with('warning', 'لطفاً ابتدا یک مرکز انتخاب کنید.');
        }

        $centerId = $selectedCenter['id'];

        $creditCard = $this->creditCardService->getUserCreditCard($user->id, $centerId);

        return view('user.credit-card.index', [
            'creditCard'     => $creditCard,
            'selectedCenter' => $selectedCenter,
        ]);
    }


    public function increase()
    {
        $user = Auth::user();        

        $selectedCenter = $this->centerService->getSelectedCenter();

        if (!$selectedCenter || !isset($selectedCenter['id'])) {
            return redirect()->route('user.select-center')
                ->with('error', 'لطفاً ابتدا یک مرکز انتخاب کنید.');
        }

        $centerId = $selectedCenter['id']; 

        $creditCard = $this->creditCardService->getUserCreditCard($user->id, $centerId);

        if (!$creditCard) {
            return redirect()->back()->with('error', 'کارت اعتباری برای این مرکز پیدا نشد. ابتدا کارت بسازید.');
        }

        return view('user.credit-card.increase', compact([
            'creditCard', 
            'selectedCenter'
        ]));
    }


    // public function increaseBalance(Request $request)
    // {
    //     $user     = Auth::user();
    //     $center   = $this->centerService->getSelectedCenter();
    //     $centerId = $center['id'];
    //     $userId   = $user->id;

    //     $request->validate([
    //         'amount' => 'required|integer|min:10000|max:1000000',
    //     ]);

    //     $amount = $request->integer('amount');

    //     $result = $this->creditCardService->increaseBalance(
    //         userId: $userId,
    //         centerId: $centerId,
    //         amount: $amount
    //     );

    //     if (!$result['success']) {
    //         return redirect()->back()->with('error', $result['message'] ?? 'خطا در افزایش موجودی.');
    //     }

    //     return redirect()->back()->with('success',
    //         "پرداخت با موفقیت انجام شد! مبلغ " . number_format($amount) . " تومان به موجودی شما اضافه شد."
    //     );
    // }


    
    public function increaseBalance(Request $request)
    {
        $user = Auth::user();
        
        // دریافت center_id از سشن (نه از ورودی فرم)
        $centerId = session('selected_center_id');
        
        if (!$centerId) {
            return redirect()->route('user.centers.select')
                ->with('error', 'لطفاً ابتدا یک مرکز انتخاب کنید.');
        }

        $request->validate([
            'amount' => 'required|integer|min:10000|max:1000000',
        ]);

        $amount = $request->integer('amount');

        $result = $this->creditCardService->increaseBalance(
            userId: $user->id,
            centerId: $centerId,
            amount: $amount
        );

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message'] ?? 'خطا در افزایش موجودی.');
        }

        // ثبت تراکنش در جدول transactions
        $this->createTransaction($user->id, $centerId, $amount);

        return redirect()->back()->with([
            'success' => [
                'main' => 'شارژ تستی با موفقیت انجام شد!',
                'amount' => number_format($amount) . ' تومان',
                'tracking' => 'TEST-' . time()
            ]
        ]);
    }

    private function createTransaction(int $userId, int $centerId, int $amount)
    {
        DB::table('transactions')->insert([
            'user_id' => $userId,
            'center_id' => $centerId,
            'amount' => $amount,
            'gateway' => 'test_mode',
            'authority' => 'TEST_AUTH_' . time(),
            'ref_id' => 'TEST_' . time() . '_' . rand(1000, 9999),
            'status' => 1, // success
            'description' => 'شارژ اعتبار (حالت تست)',
            'meta' => json_encode([
                'test' => true,
                'timestamp' => now()->toDateTimeString(),
                'ip' => request()->ip()
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
