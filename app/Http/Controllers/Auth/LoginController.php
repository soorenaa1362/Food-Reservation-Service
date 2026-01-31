<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function sendOTP(Request $request)
    {
        $request->validate([
            'nationalCode' => 'required|digits:10',
        ]);

        $nationalCode = trim($request->input('nationalCode'));

        $result = $this->authService->sendVerificationCode($nationalCode);

        if (!$result['success']) {
            return back()->with('error', 'شما مجاز به ورود به این سامانه نیستید.');
        }

        return redirect()->route('verify')
            ->with('nationalCode', $nationalCode)
            ->with('success', 'کد تأیید به شماره موبایل شما ارسال شد.');
    }

    public function showVerifyForm(Request $request)
    {
        $nationalCode = $request->session()->get('nationalCode');

        if (!$nationalCode) {
            return redirect()->route('login')->with('error', 'سشن منقضی شده است. مجدداً تلاش کنید.');
        }

        return view('auth.verify', compact('nationalCode'));
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'nationalCode' => 'required|digits:10',
            'code'         => 'required|digits:5',
        ]);

        $nationalCode = $request->input('nationalCode');
        $code         = $request->input('code');

        $user = $this->authService->verifyOTPAndLogin($nationalCode, $code);

        if (!$user) {
            return back()->with('error', 'کد تأیید نامعتبر یا منقضی شده است.');
        }

        Auth::login($user);

        // تعیین نقش فعال (فعلاً ساده — بعداً می‌تونی چندنقشی کنی)
        // $activeRole = $user->roles->pluck('name')->first() ?? 'user';
        // session(['active_role' => $activeRole]);

        // if ($activeRole === 'admin') {
        //     return redirect()->route('admin.dashboard')->with('success', 'خوش آمدید مدیر محترم!');
        // }

        return redirect()->route('user.select-center.index')->with('success', 'ورود با موفقیت انجام شد.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'با موفقیت خارج شدید.');
    }
}