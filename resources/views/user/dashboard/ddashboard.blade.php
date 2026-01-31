@extends('layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('custom/css/credit-card.css') }}">
@endsection

@section('title')
    داشبورد کاربر - سامانه رزرواسیون غذا
@endsection

@section('content')
    <div class="row">
        <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="card px-4 py-2 box-shadow-3 width-600 auth-card animate__animated animate__fadeIn">

                <div class="card-header text-center">
                    <h4 class="text-food-orange">داشبورد کاربر</h4>
                </div>

                <div class="card-body text-center">

                    {{-- پیام‌ها --}}
                    @if (session('success'))
                        <div class="alert alert-success mb-2">{{ session('success') }}</div>
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

                    {{-- اطلاعات کاربر و مرکز --}}
                    <div class="mb-4 p-4 rounded bg-light text-dark border">
                        <h6 class="text-food-orange mb-3">اطلاعات کاربری شما</h6>
                        <div class="text-start small">
                            {{-- <div class="d-flex justify-content-between mb-2">
                                <span>نام کامل:</span>
                                <b>{{ session('user_full_name') ?? Auth::user()->full_name }}</b>
                            </div> --}}
                            <div class="d-flex justify-content-between mb-2">
                                <span>نام :</span>
                                <b>{{ session('user_first_name') ?? Auth::user()->first_name }}</b>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>نام خانوادگی :</span>
                                <b>{{ session('user_last_name') ?? Auth::user()->last_name }}</b>
                            </div>

                            <!-- شماره تماس رو نمی‌ذاریم چون هش شده و نباید نمایش بدیم -->

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

                    {{-- دکمه‌های اصلی --}}
                    <div class="mt-3">
                        <a href="{{ route('user.select-center.index') }}" class="btn btn-lg btn-success w-100 mb-2">
                            انتخاب مرکز دیگر
                        </a>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-lg btn-danger w-100">
                            خروج از سامانه
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