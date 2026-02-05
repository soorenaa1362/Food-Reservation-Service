<?php

namespace App\Services\CreditLedger;

use App\Models\CreditLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CreditLedgerService
{
    public function getUserCreditLedger()
    {
        $user = Auth::user();
        $selectedCenter = session('selected_center');
        $creditLedgers = CreditLedger::where('user_id', $user->id)
            ->where('center_id', $selectedCenter->id)->get();

        return $creditLedgers;
    }

    public function showCreditLedger($creditLedgerId)
    {
        $creditLedger = CreditLedger::findOrFail($creditLedgerId);
        
        return $creditLedger;
    }
}