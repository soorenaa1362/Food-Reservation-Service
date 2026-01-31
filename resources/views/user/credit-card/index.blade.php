@extends('layouts.master')

@section('styles')
    <style>
        .amount-box {
            background: #f3f3f3;
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
            border: 2px solid transparent;
        }
        .amount-box:hover, .amount-box.active {
            background: #ff7f00;
            color: white;
            border-color: #e66a00;
        }
    </style>
@endsection

@section('title')
    کارت اعتباری - وضعیت
@endsection

@section('content')
    <div class="row">
        <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="card px-4 py-3 box-shadow-3 width-600 auth-card animate__animated animate__fadeIn">

                <!-- نمایش مرکز انتخاب شده - مینیمال و تمیز -->
                @if($selectedCenter)
                    <div class="d-flex justify-content-between align-items-center mb-4 px-3 pt-2 border-bottom pb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-building text-food-orange"></i>
                            <div>
                                <strong class="text-dark">{{ $selectedCenter['name'] }}</strong>
                                <small class="text-muted d-block">{{ $selectedCenter['address'] }}</small>
                            </div>
                        </div>
                        <a href="{{ route('user.select-center.index') }}" class="btn btn-sm btn-outline-secondary">
                            تغییر مرکز
                        </a>
                    </div>
                @endif

                <div class="card-header text-center">
                    <h4 class="text-uppercase text-bold-500 text-food-orange">
                        وضعیت کارت اعتباری
                    </h4>
                </div>

                <div class="card-body text-center">

                    {{-- پیام‌های فلش --}}
                    @if (session('success'))
                        <div class="alert alert-success mb-3 animate__animated animate__pulse">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mb-3 animate__animated animate__pulse">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3 animate__animated animate__pulse">
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    {{-- نمایش کارت اعتباری --}}
                    @if($creditCard)
                        <div class="mb-4 p-4 rounded bg-light text-dark border border-success shadow-sm animate__animated animate__zoomIn">
                            <h6 class="text-success mb-3">
                                موجودی قابل استفاده
                            </h6>
                            <h2 class="fw-bold text-success">
                                {{ number_format($creditCard->usable_balance) }} تومان
                            </h2>

                            <div class="row text-start mt-4 small">
                                <div class="col-6">
                                    <div class="text-muted">موجودی کل</div>
                                    <div class="fw-bold">{{ number_format($creditCard->balance) }} تومان</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted">مبلغ رزرو شده</div>
                                    <div class="fw-bold text-warning">{{ number_format($creditCard->reserved_amount) }} تومان</div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <small class="text-muted d-block">
                                آخرین تراکنش:
                                {{ $creditCard->last_transaction_at?->diffForHumans() ?? 'ندارد' }}
                            </small>
                        </div>

                        <div class="d-flex gap-3 justify-content-center mt-4">
                            <a href="{{ route('user.credit-card.increase') }}" class="btn btn-lg btn-warning flex-fill">
                                افزایش اعتبار
                            </a>
                        </div>

                    @else
                        <div class="alert alert-warning p-4 animate__animated animate__shakeX">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h5>کارت اعتباری ثبت نشده!</h5>
                            <p class="mb-3">
                                برای مرکز <strong>{{ $selectedCenter['name'] ?? 'انتخاب‌شده' }}</strong>
                                هنوز کارت اعتباری ندارید.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script>
        $(document).ready(function() {
            // افکت pulse روی دکمه‌ها
            $('button, a.btn').on('focus', function() {
                $(this).addClass('animate__animated animate__pulse');
            }).on('blur', function() {
                $(this).removeClass('animate__animated animate__pulse');
            });

            // افکت ورود باکس اصلی
            $('.bg-light.border-success, .alert-warning').addClass('animate__animated animate__fadeInUp');
        });
    </script>
@endsection