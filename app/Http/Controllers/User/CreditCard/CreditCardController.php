<?php

namespace App\Http\Controllers\User\CreditCard;

use App\Models\CreditCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Center\CenterService;
use App\Services\CreditCard\CreditCardService;
use App\Services\Transaction\TransactionService;

class CreditCardController extends Controller
{
    protected $creditCardService, $centerService;

    public function __construct(
        CreditCardService $creditCardService, 
        CenterService $centerService,
        TransactionService $transactionService
    )
    {
        $this->creditCardService = $creditCardService;
        $this->centerService = $centerService;
        $this->transactionService = $transactionService;
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
        $transaction = $this->transactionService->createTransactionForIncreaseBalance($user->id, $centerId, $amount);

        return redirect()->back()->with([
            'success' => [
                'main' => 'شارژ تستی با موفقیت انجام شد!',
                'amount' => number_format($amount) . ' تومان',
                'tracking' => 'TEST-' . time()
            ]
        ]);
    }
}
