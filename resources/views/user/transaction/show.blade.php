@extends('layouts.master')

@section('content')
    <div class="row d-flex justify-content-center">
        <div class="col-lg-5 col-md-6 col-11"> 

            <div class="card px-3 py-3 box-shadow-3">  <!-- کمی padding بیشتر برای راحتی -->

                <div class="card-header">
                    <h4 class="text-center mb-0">جزئیات تراکنش ثبت شده</h4>
                </div>

                <div class="card-body">
                    <div class="mb-3 py-3 px-3 rounded bg-light text-dark border border-1">

                        {{-- تاریخ --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-baseline mb-3 gap-2">
                            <span class="text-muted flex-shrink-0">روز و تاریخ :</span>
                            <div class="text-end">
                                <b class="d-block">
                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($transaction->created_at)->format('l j F Y') }}
                                </b>
                                {{-- <small class="text-muted d-block d-md-inline">
                                    ({{ $transaction->created_at->format('Y/m/d') }})
                                </small> --}}
                            </div>
                        </div>

                        {{-- مبلغ --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">مبلغ :</span>
                            <b dir="ltr" class="text-success fs-5">
                                {{ number_format($transaction->amount) }}
                                <span class="text-muted fs-6">تومان</span>
                            </b>
                        </div>

                        {{-- نوع تراکنش --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">نوع تراکنش :</span>
                            <span class="badge bg-{{ $transaction->type_class }} text-white font-medium-1 px-3 py-2">
                                {{ $transaction->type_text }}
                            </span>
                        </div>

                        {{-- وضعیت --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">وضعیت :</span>
                            @switch($transaction->status)
                                @case(\App\Models\Transaction::STATUS_PENDING)
                                    <span class="badge bg-warning text-white px-3 py-2">در انتظار</span>
                                    @break
                                @case(\App\Models\Transaction::STATUS_SUCCESS)
                                    <span class="badge bg-success text-white px-3 py-2">موفق</span>
                                    @break
                                @case(\App\Models\Transaction::STATUS_FAILED)
                                    <span class="badge bg-danger text-white px-3 py-2">ناموفق</span>
                                    @break
                                @case(\App\Models\Transaction::STATUS_CANCELLED)
                                    <span class="badge bg-secondary text-white px-3 py-2">لغو شده</span>
                                    @break
                                @default
                                    <span class="badge bg-dark px-3 py-2">نامشخص</span>
                            @endswitch
                        </div>

                        {{-- توضیحات --}}
                        @if($transaction->description)
                            <div class="d-flex flex-wrap justify-content-between mt-3 pt-3 border-top gap-2">
                                <span class="text-muted flex-shrink-0">توضیحات :</span>
                                <span class="text-dark text-end">{{ $transaction->description }}</span>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            
        </div>        

        <div class="col-lg-11 col-md-11 col-11">
            <div class="card">
                <div class="card-header">
                    <div class="card-title-wrap bar-success d-flex justify-content-between">
                        <h4 class="card-title mb-0">لیست اعتبارهای مرتبط با این تراکنش</h4>
                    </div>
                </div>

                <div class="card-body collapse show">
                    <div class="card-block card-dashboard">
                        <table class="table table-striped table-bordered base-style text-center table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>تاریخ</th>
                                    <th>مبلغ</th>
                                    <th>نوع تغییر</th>
                                    <th>کارت اعتبار</th>
                                    <th>منبع</th>
                                    <th>مانده قبل</th>
                                    <th>مانده بعد</th>
                                    <th>توضیحات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transaction->ledgers as $ledger)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <strong>
                                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($ledger->created_at ?? $transaction->created_at)->format('j F Y') }}
                                                </strong>
                                                <small class="text-muted">
                                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($ledger->created_at ?? $transaction->created_at)->format('H:i') }}
                                                </small>
                                            </div>
                                        </td>

                                        <td dir="ltr">
                                            <span class="{{ $ledger->type == \App\Models\CreditLedger::TYPE_INCREASE ? 'text-success' : 'text-danger' }} fw-bold">
                                                {{ number_format(abs($ledger->amount)) }}
                                            </span>
                                            <small class="text-muted d-block">
                                                {{ $ledger->type == \App\Models\CreditLedger::TYPE_INCREASE ? '(+)' : '(-)' }} تومان
                                            </small>
                                        </td>

                                        <td>
                                            <span class="badge bg-{{ $ledger->type_class }} text-white px-3 py-2">
                                                {{ $ledger->type_text }}
                                            </span>
                                        </td>

                                        <td>
                                            @if($ledger->creditCard)
                                                <small class="text-muted d-block">کارت #{{ $ledger->credit_card_id }}</small>
                                                <strong>{{ $ledger->creditCard->title ?? '—' }}</strong>
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge bg-{{ $ledger->source_type_class }} text-white px-3 py-2">
                                                {{ $ledger->source_type_text }}
                                            </span>
                                        </td>

                                        <td dir="ltr" class="fw-medium">
                                            {{ number_format($ledger->balance_before) }} تومان
                                        </td>

                                        <td dir="ltr" class="fw-medium">
                                            {{ number_format($ledger->balance_after) }} تومان
                                        </td>

                                        <td class="text-start">
                                            {{ $ledger->description ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            هیچ تغییری در اعتبار برای این تراکنش ثبت نشده است.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>    
@endsection