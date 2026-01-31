@extends('auth.layouts.master')
@section('title')
    تأیید کد ورود
@endsection

@section('content')
    <div class="row full-height-vh bg-image">
        <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="card border-0 shadow-lg" style="max-width: 400px; width: 100%; border-radius: 24px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">
                <div class="text-center pt-5 pb-4">
                    <div class="mb-4 hover-lift">
                        {{-- <img src="{{ asset('custom/img/food-logo.jpg') }}" class="rounded-circle shadow" width="80" height="80"> --}}
                    </div>
                    <h4 class="mb-2" style="color: #2d68b5; font-weight: 600;">
                        تأیید کد ورود
                    </h4>
                    <p class="text-muted font-small-3">
                        کد ۵ رقمی ارسال‌شده به تلفن همراه خود را وارد کنید
                    </p>
                </div>

                <div class="px-4 pb-5">
                    <!-- پیام‌ها -->
                    @if (session('success'))
                        <div class="alert alert-success small mb-3 text-center">{{ session('success') }}</div>
                    @endif
                    @if (session('error') || $errors->any())
                        <div class="alert alert-danger small mb-3 text-center">
                            {{ session('error') ?: $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('verifyOTP') }}" method="POST" id="verifyForm">
                        @csrf
                        <input type="hidden" name="nationalCode" value="{{ $nationalCode }}">

                        <!-- فیلد کد OTP -->
                        <div class="form-group mb-4">
                            <div class="input-group input-group-lg">
                                <input
                                    name="code"
                                    type="text"
                                    class="form-control text-center @error('code') is-invalid @enderror"
                                    style="
                                        border-radius: 16px;
                                        font-size: 1.4rem;
                                        letter-spacing: 8px;
                                        border: 2px solid #e0e0e0;
                                        height: 58px;
                                    "
                                    pattern="[0-9]{5}"
                                    maxlength="5"
                                    inputmode="numeric"
                                    required
                                    autofocus
                                >
                                <div class="input-group-append">
                                    <span class="input-group-text" style="
                                        background: linear-gradient(135deg, #667eea, #764ba2);
                                        color: white;
                                        border: none;
                                        border-radius: 16px;
                                        margin-right: -50px;
                                        z-index: 10;
                                        width: 58px;
                                        height: 58px;
                                    ">
                                        <i class="fa fa-lock fa-lg"></i>
                                    </span>
                                </div>
                                @error('code')
                                    <span class="invalid-feedback text-center d-block mt-2">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- انتخاب نقش (فقط اگر چند نقش داشته باشد) -->
                        {{-- @if(count($roles) > 1)
                            <div class="form-group mb-4">
                                <select name="role_id" id="roleSelect" class="form-control form-control-lg" style="border-radius: 16px; height: 58px;" required>
                                    <option value="" disabled selected>انتخاب نقش</option>
                                    @foreach($roles as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        @endif --}}

                        <button
                            type="submit"
                            id="submitBtn"
                            class="btn btn-block text-white text-uppercase font-medium-2 shadow-lg"
                            style="
                                height: 58px;
                                border-radius: 50px;
                                background: linear-gradient(135deg, #667eea, #764ba2);
                                font-size: 1.1rem;
                                letter-spacing: 1px;
                            ">
                            <span class="btn-text">تأیید و ورود</span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm mr-2"></span>
                                در حال ورود...
                            </span>
                        </button>
                    </form>
                </div>

                <div class="text-center pb-4">
                    <small class="text-muted">
                        کد را دریافت نکردید؟ <a href="{{ route('sendOTP') }}" style="color: #5a67d8;">ارسال مجدد</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .full-height-vh { min-height: 100vh; }
        .bg-image {
            background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.55)),
                url('{{ asset('custom/img/food-logo.jpg') }}') center center / cover no-repeat fixed;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            transition: .3s;
        }
        input::placeholder { color: #bbb !important; opacity: 1; }
        @media (max-width: 576px) {
            .bg-image { background-attachment: scroll; }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.querySelector('input[name="code"]');
            const roleSelect = document.getElementById('roleSelect');
            const btn = document.getElementById('submitBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');
            const form = document.getElementById('verifyForm');
            const hasMultipleRoles = !!roleSelect;

            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');

                if (this.value.length === 5) {
                    if (hasMultipleRoles) {
                        roleSelect.focus();
                    } else {
                        // ارسال خودکار وقتی نقش فقط یکی باشه
                        btn.disabled = true;
                        btnText.classList.add('d-none');
                        btnLoading.classList.remove('d-none');
                        setTimeout(() => form.submit(), 600);
                    }
                }
            });

            // اگر چند نقش باشه → با انتخاب نقش، خودکار ارسال بشه
            if (hasMultipleRoles) {
                roleSelect.addEventListener('change', function () {
                    if (input.value.length === 5) {
                        btn.disabled = true;
                        btnText.classList.add('d-none');
                        btnLoading.classList.remove('d-none');
                        setTimeout(() => form.submit(), 600);
                    }
                });
            }
        });
    </script>
@endsection