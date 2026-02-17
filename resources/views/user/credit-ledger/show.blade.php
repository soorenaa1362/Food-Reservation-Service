@extends('layouts.master')

@section('content')
    <div class="row d-flex justify-content-center">
        <div class="col-lg-5 col-md-6 col-11">  <!-- مثل قبلی، col-md-6 فضای بهتری میده -->

            <div class="card px-3 py-3 box-shadow-3">  <!-- padding یکسان با قبلی -->

                <div class="card-header">
                    <h4 class="text-center mb-0">جزئیات اعتبار ثبت شده</h4>
                </div>

                <div class="card-body">
                    <div class="mb-3 py-3 px-3 rounded bg-light text-dark border border-1">

                        {{-- تاریخ --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-baseline mb-3 gap-2">
                            <span class="text-muted flex-shrink-0">روز و تاریخ :</span>
                            <div class="text-end">
                                <b class="d-block">
                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($creditLedger->created_at)->format('l j F Y') }}
                                </b>
                                {{-- تاریخ میلادی رو کامنت کردم، اگر لازم بود می‌تونی d-md-block کنی --}}
                                {{-- <small class="text-muted d-block d-md-inline">({{ $creditLedger->created_at->format('Y/m/d') }})</small> --}}
                            </div>
                        </div>

                        {{-- مبلغ --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">مبلغ :</span>
                            <b dir="ltr" class="{{ $creditLedger->isIncrease() ? 'text-success' : 'text-danger' }} fs-5">
                                {{ number_format(abs($creditLedger->amount)) }}
                                تومان
                            </b>
                        </div>

                        {{-- مانده قبل از --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">مانده قبل از تراکنش:</span>
                            <b dir="ltr" class="fs-5">
                                {{ number_format($creditLedger->balance_before) }}
                                <span class="text-muted fs-6">تومان</span>
                            </b>
                        </div>

                        {{-- مانده بعد از --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">مانده بعد از تراکنش:</span>
                            <b dir="ltr" class="fs-5">
                                {{ number_format($creditLedger->balance_after) }}
                                <span class="text-muted fs-6">تومان</span>
                            </b>
                        </div>

                        {{-- نوع تراکنش --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">نوع تراکنش :</span>
                            {{-- <span class="badge bg-{{ $creditLedger->type_class }} text-white px-3 py-2 font-medium-1">
                                <i class="ft-arrow-{{ $creditLedger->type == \App\Models\CreditLedger::TYPE_INCREASE ? 'up' : 'down' }}"></i>
                                {{ $creditLedger->type_text }}
                            </span> --}}
                            <span class="badge bg-{{ $creditLedger->getTypeBadgeClass() }} text-white px-3 py-2 font-small-3">
                                <i class="ft-arrow-{{ $creditLedger->isIncrease() ? 'up' : 'down' }}"></i>
                                {{ $creditLedger->getTypeLabel() }}
                            </span>
                        </div>

                        {{-- منبع تراکنش --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">منبع تراکنش :</span>
                            {{-- <span class="badge bg-{{ $creditLedger->source_type_class }} text-white px-3 py-2 font-medium-1">
                                {{ $creditLedger->source_type_text }}
                            </span> --}}
                            <span class="badge bg-{{ $creditLedger->getSourceBadgeClass() }} text-white px-3 py-2 font-small-3">
                                {{ $creditLedger->getSourceLabel() }}
                            </span>
                        </div>

                        {{-- توضیحات --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-baseline mt-3 pt-3 border-top gap-2">
                            <span class="text-muted flex-shrink-0">توضیحات :</span>
                            <span class="text-dark text-end">
                                {{ $creditLedger->description ?? '—' }}
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection