@extends('auth.layouts.master')

@section('title')
    انتخاب مرکز
@endsection

@section('content')
    <div class="row full-height-vh bg-image">
        <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="card border-0 shadow-lg" style="max-width: 520px; width: 100%; border-radius: 24px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">

                <!-- هدر -->
                <div class="text-center pt-5 pb-1">
                    <div class="mb-2 hover-lift">
                        {{-- <img src="{{ asset('custom/img/food-logo.jpg') }}" class="rounded-circle shadow" width="80" height="80" alt="لوگو"> --}}
                    </div>
                    {{-- <h4 class="mb-1" style="color: #2d68b5; font-weight: 600;">
                        خوش آمدید، {{ Auth::user()->full_name }}
                    </h4> --}}
                    <p class="text-muted font-small-3">
                        لطفاً مرکز مورد نظر خود را انتخاب کنید
                    </p>
                </div>

                <div class="px-4 pb-5">
                    <!-- پیام‌ها -->
                    @if (session('success'))
                        <div class="alert alert-success small mb-4 text-center rounded-pill">{{ session('success') }}</div>
                    @endif
                    @if (session('error') || $errors->any())
                        <div class="alert alert-danger small mb-4 text-center rounded-pill">
                            {{ session('error') ?? $errors->first() }}
                        </div>
                    @endif

                    <!-- لیست مراکز با اسکرول محدود به حدود ۲ کارت -->
                    <div class="centers-list-container">
                        @forelse ($centers as $center)
                            <form action="{{ route('user.select-center.select') }}" method="POST" class="mb-1">
                                @csrf
                                <input type="hidden" name="center_id" value="{{ $center->id }}">
                                <button type="submit"
                                    class="btn text-white w-100 shadow-lg d-flex align-items-center justify-content-between px-4 py-2 rounded-3"
                                    style="
                                        background: linear-gradient(135deg, #667eea, #764ba2);
                                        font-size: 1.15rem;
                                        min-height: 88px;
                                        transition: all 0.3s ease;
                                    "
                                    onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 15px 30px rgba(102,126,234,0.4)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.2)'">
                                    <div class="d-flex align-items-center gap-4">
                                        <i class="fa fa-hospital-o fa-2x opacity-75 p-2"></i>
                                        <div class="text-start">
                                            <div class="fw-bold fs-5">{{ $center->name }}</div>
                                            <small class="opacity-80">{{ $center->type }}</small><br>
                                        </div>
                                    </div>
                                    <i class="fa fa-chevron-left fa-lg"></i>
                                </button>
                            </form>
                        @empty
                            <div class="alert alert-info rounded-pill text-center py-3">
                                هیچ مرکزی برای شما ثبت نشده است.
                            </div>
                        @endforelse
                    </div>

                    <!-- دکمه خروج -->
                    <div class="mt-4 text-center">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger px-5 py-2 rounded-pill">
                                <i class="fa fa-sign-out me-2"></i>
                                خروج از سامانه
                            </button>
                        </form>
                    </div>
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
                url('{{ asset('custom/img/food-logo.jpg') }}') center/cover no-repeat fixed;
        }
        .hover-lift:hover { transform: translateY(-5px); transition: .3s; }

        /* ناحیه لیست مراکز — فقط حدود ۲ کارت نمایش بده، بقیه اسکرول */
        .centers-list-container {
            max-height: 220px;           /* حدود ۲ کارت (هر کارت ~88px + margin) */
            overflow-y: auto;
            padding-right: 8px;          /* فضای اسکرول‌بار */
            margin-bottom: 20px;
        }

        /* اسکرول‌بار زیبا */
        .centers-list-container::-webkit-scrollbar {
            width: 8px;
        }
        .centers-list-container::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
            border-radius: 10px;
        }
        .centers-list-container::-webkit-scrollbar-thumb {
            background: rgba(102, 126, 234, 0.5);
            border-radius: 10px;
        }
        .centers-list-container::-webkit-scrollbar-thumb:hover {
            background: rgba(102, 126, 234, 0.7);
        }

        @media (max-width: 576px) {
            .card { margin: 15px; max-width: 100%; }
            .bg-image { background-attachment: scroll; }
            .btn { font-size: 1rem !important; }
            .centers-list-container { max-height: 200px; }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                const btn = this.querySelector('button[type="submit"]');
                if (btn && !btn.disabled) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm text-center"></span> در حال ورود...';
                }
            });
        });
    </script>
@endsection