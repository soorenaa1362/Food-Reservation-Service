<?php

namespace App\Http\Controllers\User;

use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller; 
use App\Services\Center\CenterService;


class SelectCenterController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $centers = $user->centers()->orderBy('id')->get();

        if ($centers->count() === 1) {
            $this->storeSessionData($centers->first()->id);
            return redirect()->route('user.dashboard')
                ->with('success', 'مرکز به طور خودکار انتخاب شد.');
        }

        if ($centers->count() === 0) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'شما به هیچ مرکزی دسترسی ندارید. لطفاً با مدیر سیستم تماس بگیرید.');
        }

        return view('user.select-center', compact('centers'));
    }

    public function select(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:centers,id',
        ]);

        $centerId = $request->input('center_id');
        $user = Auth::user();

        if (!$user->centers()->where('center_id', $centerId)->exists()) {
            return back()->with('error', 'مرکز انتخاب‌شده معتبر نیست.');
        }

        $this->storeSessionData($centerId);

        return redirect()->route('user.dashboard')
            ->with('success', 'مرکز با موفقیت انتخاب شد.');
    }

    private function storeSessionData(int $centerId): void
    {
        $user = Auth::user();
        $center = Center::findOrFail($centerId);

        session([
            'selected_center_id' => $centerId,
            'selected_center'     => $center,

            // اطلاعات decrypt شده کاربر
            'user_full_name'      => $user->full_name,
            'user_first_name'     => $user->first_name,
            'user_last_name'      => $user->last_name,
            // کد ملی و موبایل رو عمداً ذخیره نمی‌کنیم (امنیتی)
        ]);
    }
}