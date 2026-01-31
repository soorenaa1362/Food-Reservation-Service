@extends('auth.layouts.master')

@section('title')
    ورود به سامانه
@endsection

@section('content')
    <div class="row full-height-vh bg-image">
        <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="card border-0 shadow-lg" style="max-width: 400px; width: 100%; border-radius: 24px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">
                <div class="text-center pt-5 pb-4">
                    <div class="mb-4 hover-lift">
                        {{-- <img src="{{ asset('custom/img/food-logo.jpg') }}" class="rounded-circle shadow" width="80" height="80"> --}}
                    </div>
                    <h4 class="mb-2" style="color: #2d68b5; psychedelic font-weight: 600;">
                        ورود با کد تأیید
                    </h4>
                    <p class="text-muted font-small-3">
                        کد ملی ۱۰ رقمی خود را وارد کنید
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

                    <form action="{{ route('sendOTP') }}" method="POST" id="otpForm">
                        @csrf
                        <div class="form-group mb-4">
                            <div class="input-group input-group-lg">
                                <input
                                    name="nationalCode"
                                    type="text"
                                    class="form-control text-center"
                                    style="
                                        border-radius: 16px;
                                        font-size: 1.2rem;
                                        letter-spacing: 6px;
                                        border: 2px solid #e0e0e0;
                                        height: 58px;
                                    "
                                    pattern="[0-9]{10}"
                                    maxlength="10"
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
                                        <i class="fa fa-id-card fa-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

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
                            <span class="btn-text">دریافت کد تأیید</span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm mr-2"></span>
                                در حال ارسال...
                            </span>
                        </button>
                    </form>
                </div>

                <div class="text-center pb-4">
                    <small class="text-muted">
                        با ورود، <a href="#" style="color: #5a67d8;">قوانین و مقررات</a> را پذیرفته‌اید
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .full-height-vh {
            min-height: 100vh;
        }

        /* پس‌زمینه تصویری */
        .bg-image {
            background: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.55)),
                url('{{ asset('custom/img/food-logo.jpg') }}') center center / cover no-repeat fixed;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            transition: .3s;
        }

        input::placeholder {
            color: #bbb !important;
        }

        /* برای موبایل هم قشنگ بشه */
        @media (max-width: 576px) {
            .bg-image {
                background-attachment: scroll;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.querySelector('input[name="nationalCode"]');
            const btn = document.getElementById('submitBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');

            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 10) {
                    btn.disabled = true;
                    btnText.classList.add('d-none');
                    btnLoading.classList.remove('d-none');
                    setTimeout(() => document.getElementById('otpForm').submit(), 600);
                }
            });
        });
    </script>
@endsection