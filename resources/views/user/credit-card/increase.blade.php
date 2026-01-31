@extends('layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('custom/css/credit-card.css') }}">
@endsection

@section('title')
    کارت اعتباری - افزایش اعتبار
@endsection

@section('content')

<div class="row">
    <div class="col-12 d-flex align-items-center justify-content-center">
        <div class="card px-4 py-2 box-shadow-3 width-600 auth-card animate__animated animate__fadeIn">
          
            <div class="card-header text-center">
                <h4 class="text-food-orange">افزایش اعتبار</h4>
            </div>
            <div class="card-body text-center">
                {{-- پیام ها --}}
                @if (session('success') && is_array(session('success')))
                    @php $success = session('success'); @endphp
                    <div class="alert alert-success mb-2">
                        <div class="fw-bold">{{ $success['main'] ?? '' }}</div>
                        <div class="mt-1">مبلغ: {{ $success['amount'] ?? '' }}</div>
                        @if($success['tracking'] ?? false)
                            <div class="mt-1 small text-muted">
                                <i class="fas fa-receipt me-1"></i>
                                کد پیگیری: {{ $success['tracking'] }}
                            </div>
                        @endif
                    </div>
                @elseif(session('success'))
                    <div class="alert alert-success mb-2">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger mb-2">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger mb-2">
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif

                @if($creditCard ?? false)
                    {{-- <div class="mb-3 p-3 rounded bg-light text-dark">
                        <h6>اطلاعات کارت اعتباری</h6>
                        <div class="small mt-2">
                            <div>شماره کارت: <b>{{ $creditCard->id ?? '---' }}</b></div>
                            <div>موجودی قابل استفاده: <b class="text-success">{{ number_format($creditCard->usable_balance) }} تومان</b></div>
                        </div>
                    </div> --}}
                    <div class="mb-2 p-4 rounded bg-light text-dark border">
                        <h6 class="text-food-orange mb-3">اطلاعات کارت اعتباری شما</h6>
                        <div class="text-start small">
                            <div class="d-flex justify-content-between mb-2">
                                <span>شماره کارت:</span>
                                <b dir="ltr">{{ $creditCard->card_number ?? $creditCard->id ?? '---' }}</b>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>موجودی کل:</span>
                                <b>{{ number_format($creditCard->balance ?? 0) }} تومان</b>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>مبلغ رزرو شده:</span>
                                <b class="text-danger">{{ number_format($creditCard->reserved_amount ?? 0) }} تومان</b>
                            </div>
                            <div class="mt-3 p-2 bg-warning bg-opacity-20 rounded text-center">
                                <div class="text-lg">
                                    موجودی قابل استفاده:
                                    <strong class="text-white">
                                        {{ number_format($creditCard->balance ?? 0) }} تومان
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        اطلاعات کارت یافت نشد، لطفاً دوباره تلاش کنید.
                    </div>
                @endif

                @php
                    $selectedCenterId = session('selected_center_id') 
                        ?? ($center->id ?? null)
                        ?? auth()->user()->centers->pluck('id')->first();
                @endphp
                {{-- <form action="{{ route('user.transactions.start-payment') }}" method="POST" id="chargeForm"> --}}
                <form action="{{ route('user.credit-card.increase-balance') }}" method="POST" id="chargeForm">
                    @csrf
                    @method('PATCH')
                    
                    <!-- نمایش نام مرکز انتخاب شده برای کاربر -->
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-hospital me-2"></i>
                        مرکز انتخاب شده: 
                        <strong>{{ session('selected_center')->name ?? 'نامشخص' }}</strong>
                    </div>

                    <div class="mb-3">
                        <label class="mb-2 fw-bold">انتخاب مبلغ شارژ</label>
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            @foreach([50000, 100000, 200000, 500000] as $price)
                                <div class="amount-box cursor-pointer border rounded p-3 text-center fw-bold" 
                                    data-value="{{ $price }}" 
                                    style="min-width: 120px; transition: all 0.2s;">
                                    {{ number_format($price) }} تومان
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label class="float-start mb-1">مبلغ دلخواه (تومان)</label>
                        <input type="number" 
                            name="amount" 
                            id="amount"
                            class="form-control text-center py-2"
                            placeholder="مثلاً 150000" 
                            min="10000"
                            max="1000000"
                        >
                    </div>

                    <div id="summary" class="mt-3 d-none">
                        <div class="p-3 rounded bg-success bg-opacity-10 text-white font-medium-1 border border-success">
                            مبلغ قابل پرداخت: <span id="finalAmount" class="fw-bold"></span> تومان
                        </div>
                    </div>

                    <button id="payBtn" 
                        class="btn btn-lg btn-success w-100 mt-3 shadow-sm" 
                        disabled
                    >
                        <i class="fas fa-credit-card me-2"></i>
                        افزایش اعتبار (نسخه تست)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('custom/js/credit-card.js') }}"></script>
@endsection