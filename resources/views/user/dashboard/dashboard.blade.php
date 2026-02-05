@extends('layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('custom/css/credit-card.css') }}">
@endsection

@section('title')
    داشبورد کاربر - سامانه رزرواسیون غذا
@endsection

@section('content')
    <div class="row d-flex justify-content-center">

        <div class="col-lg-5 col-md-5 col-11">
            <div class="card px-3 py-2 box-shadow-3">
                <div class="card-header">
                    <h4 class="text-center mb-0">اطلاعات کاربری شما</h4>
                </div>

                <div class="card-body collapse show">
                    <div class="card-block card-dashboard">
                        <div class="text-start small">
                            <div class="d-flex justify-content-between mb-2">
                                <span>نام :</span>
                                <b>{{ session('user_first_name') ?? Auth::user()->first_name }}</b>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>نام خانوادگی :</span>
                                <b>{{ session('user_last_name') ?? Auth::user()->last_name }}</b>
                            </div>

                            @if ($selectedCenter ?? false)
                                <hr class="my-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>مرکز انتخاب‌شده :</span>
                                    <b>{{ $selectedCenter->name }}</b>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>نوع مرکز :</span>
                                    <b>{{ $selectedCenter->type }}</b>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>آدرس مرکز :</span>
                                    <b>{{ $selectedCenter->address ?? 'بدون آدرس' }}</b>
                                </div>
                            @else
                                <div class="text-warning mt-3">
                                    هنوز هیچ مرکزی انتخاب نکرده‌اید.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 col-md-5 col-11">
            <div class="card px-3 py-2 box-shadow-3">
                <div class="card-header">
                    <h4 class="text-center mb-0">اطلاعات کارت اعتباری شما</h4>
                </div>

                <div class="card-body text-center">
                    <div class="mb-2 p-4 rounded bg-light text-dark border">
                        {{-- <h6 class="text-food-orange mb-3">اطلاعات کارت اعتباری شما</h6> --}}
                        <div class="text-start small">
                            <div class="d-flex justify-content-between mb-2">
                                <span>شماره کارت :</span>
                                <b dir="ltr">{{ $creditCard->card_number ?? $creditCard->id ?? '---' }}</b>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>موجودی کل:</span>
                                <b>{{ number_format($creditCard->balance ?? 0) }} تومان</b>
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
                </div>
            </div>
        </div>

        <div class="col-lg-11 col-md-11 col-11">
            <div class="card">
                <div class="card-header">
                    <div class="card-title-wrap bar-success d-flex justify-content-between">
                        <h4 class="card-title mb-0">لیست رزرو ها</h4>
                    </div>
                </div>

                <div class="card-body collapse show">
                    <div class="card-block card-dashboard">
                        <table class="table table-striped table-bordered base-style text-center">
                            <thead>
                                <tr>                                
                                    <th>تاریخ</th>
                                    <th>صبحانه</th>
                                    <th>ناهار</th>
                                    <th>شام</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($groupedItems as $date => $day)
                                    <tr>
                                        <!-- تاریخ -->
                                        <td>
                                            {{ \Morilog\Jalali\Jalalian::fromDateTime($date)->format('Y/m/d') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ \Morilog\Jalali\Jalalian::fromDateTime($date)->format('l') }}
                                            </small>
                                        </td>                               

                                        <!-- صبحانه -->
                                        <td>
                                            @if ($day['breakfast']->count())
                                                @foreach($day['breakfast'] as $item)
                                                    {{ $item->food_name }}  [ {{ $item->quantity }} پرس ] <br>
                                                @endforeach
                                            @else
                                                <span class="text-muted">——</span>
                                            @endif
                                        </td>

                                        <!-- ناهار -->
                                        <td>
                                            @if ($day['lunch']->count())
                                                @foreach($day['lunch'] as $item)
                                                    {{ $item->food_name }}  [ {{ $item->quantity }} پرس ] <br>
                                                @endforeach
                                            @else
                                                <span class="text-muted">——</span>
                                            @endif
                                        </td>

                                        <!-- شام -->
                                        <td>
                                            @if ($day['dinner']->count())
                                                @foreach($day['dinner'] as $item)
                                                    {{ $item->food_name }}  [ {{ $item->quantity }} پرس ] <br>
                                                @endforeach
                                            @else
                                                <span class="text-muted">——</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-info font-medium-2 p-4">برای این مرکز هنوز هیچ رزروی ثبت نشده است.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-11 col-md-11 col-11">
            <div class="card">
                <div class="card-header">
                    <div class="card-title-wrap bar-success d-flex justify-content-between">
                        <h4 class="card-title mb-0">لیست تراکنش ها</h4>
                    </div>
                </div>

                <div class="card-body collapse show">
                    <div class="card-block card-dashboard">
                        <table class="table table-striped table-bordered base-style text-center">
                            <thead>
                                <tr>                                                                    
                                    <th>تاریخ</th>
                                    <th>مبلغ</th>
                                    <th>نوع تراکنش</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr>                                        
                                        <td>
                                            {{ \Morilog\Jalali\Jalalian::fromDateTime($date)->format('Y/m/d') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ \Morilog\Jalali\Jalalian::fromDateTime($date)->format('l') }}
                                            </small>
                                        </td>                                         

                                        <td>
                                            {{ number_format($transaction->amount ?? 0) }} تومان
                                        </td>

                                        <td>
                                            <span class="font-medium-1 badge badge-{{ $transaction->type_class }}">
                                                {{ $transaction->type_text }}
                                            </span>
                                        </td>

                                        <td>
                                            @if($transaction->isPending())
                                                <span class="font-medium-1 badge badge-warning">در انتظار</span>
                                            @elseif($transaction->isSuccess())
                                                <span class="font-medium-1 badge badge-success text-white">انجام شده</span>
                                            @elseif($transaction->isFailed())
                                                <span class="font-medium-1 badge badge-danger text-white">عدم موفقیت</span>
                                            @elseif($transaction->isCancelled())
                                                <span class="font-medium-1 badge badge-secondary">لغو شده</span>
                                            @endif
                                        </td>

                                        <td>
                                            <a href="{{ route('user.transaction.show', $transaction->id) }}"
                                                class="info p-0" data-toggle="tooltip"
                                                data-placement="top" title="مشاهده"
                                            >
                                                <i class="icon-eye font-medium-3 mr-2"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-info font-medium-2 p-4">برای این مرکز هنوز هیچ تراکنشی ثبت نشده است.</td>
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

@section('scripts')
    <script src="{{ asset('custom/js/credit-card.js') }}"></script>
@endsection