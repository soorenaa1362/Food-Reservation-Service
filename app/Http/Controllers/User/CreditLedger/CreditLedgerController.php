<?php

namespace App\Http\Controllers\User\CreditLedger;

use App\Models\CreditLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Center\CenterService;
use App\Services\CreditLedger\CreditLedgerService;

class CreditLedgerController extends Controller
{
    protected $creditLedgerService;

    public function __construct(CreditLedgerService $creditLedgerService)
    {
        $this->creditLedgerService = $creditLedgerService;
    }

    public function index()
    {
        $creditLedgers = $this->creditLedgerService->getUserCreditLedger();
        
        return view('user.credit-ledger.index', compact([
            'creditLedgers'
        ]));
    }

    public function show($creditLedgerId)
    {
        $creditLedger = $this->creditLedgerService->showCreditledger($creditLedgerId);
        
        return view('user.credit-ledger.show', compact([
            'creditLedger'
        ]));    
    }
}
