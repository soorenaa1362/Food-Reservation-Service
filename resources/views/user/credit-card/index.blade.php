@extends('layouts.master')

@section('styles')
    <style>
        /* استایل کارت اصلی */
        .main-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 25px;
        }
        
        /* کارت موجودی */
        .balance-card {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .balance-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.3;
        }
        
        /* کارت اطلاعات */
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #e9ecef;
            transition: all 0.2s;
        }
        
        .info-card:hover {
            background: #fff;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        
        .info-label {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
        
        /* کارت مرکز */
        .center-card {
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        /* دکمه افزایش اعتبار */
        .btn-increase {
            background: linear-gradient(135deg, #ff7f00, #ff5500);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(255, 127, 0, 0.2);
        }
        
        .btn-increase:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 127, 0, 0.3);
            color: white;
        }
        
        /* آیکون‌ها */
        .info-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        /* نوار پیشرفت */
        .progress-bar {
            height: 6px;
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
            margin-top: 15px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: white;
            border-radius: 3px;
            width: 0;
            transition: width 1s ease;
        }
        
        /* کارت نکات */
        .note-card {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 5px;
            border-right: 4px solid #2196f3;
        }
        
        /* کارت خالی */
        .empty-card {
            background: #fff5e6;
            border-radius: 10px;
            padding: 40px 30px;
            text-align: center;
            border: 2px dashed #ffa726;
        }
    </style>
@endsection

@section('title')
    کارت اعتباری - وضعیت
@endsection

@section('content')
    <div class="row">
        <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="card px-4 py-3 box-shadow-3 width-600 auth-card">
                <!-- هدر کارت -->
                <div class="card-header text-center border-bottom pb-3">
                    <h4 class="text-uppercase text-bold-500 text-food-orange">
                        وضعیت کارت اعتباری
                    </h4>
                </div>
                
                <div class="card-body">
                    <!-- پیام‌های فلش -->
                    @if (session('success'))
                        <div class="alert alert-success mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-3"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-3"></i>
                                <div>{{ session('error') }}</div>
                            </div>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3">
                            @foreach ($errors->all() as $error)
                                <div><i class="fas fa-times me-2"></i> {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif                                    
                    
                    @if($creditCard)
                        <!-- کارت موجودی -->
                        {{-- <div class="balance-card mb-2 mt-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="opacity-75 mb-2">موجودی قابل استفاده</div>
                                    <h2 class="fw-bold mb-0">
                                        {{ number_format($creditCard->balance) }}
                                        <small class="fs-4">تومان</small>
                                    </h2>
                                </div>
                                <i class="icon-wallet fa-4x text-white"></i>
                            </div>
                            
                            <!-- نوار پیشرفت -->
                            @php
                                $percentage = $creditCard->balance > 0 
                                    ? min(100, ($creditCard->usable_balance / $creditCard->balance) * 100) 
                                    : 100;
                            @endphp
                            <div class="progress-bar">
                                <div class="progress-fill" data-width="{{ $percentage }}"></div>
                            </div>
                        </div> --}}
                        
                        <div class="row mt-3">

                            <div class="col-md-6 mb-2">
                                <div class="info-card">
                                    <div class="d-flex align-items-center">
                                        {{-- <div class="info-icon bg-primary bg-opacity-10 text-primary"> --}}
                                            <i class="icon-credit-card fa-3x text-primary px-1 mx-2"></i>
                                        {{-- </div> --}}
                                        <div class="flex-grow-1">
                                            <div class="info-label">شماره کارت</div>
                                            <div class="info-value" dir="ltr">{{ $creditCard->card_number ?? $creditCard->id }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="info-card">
                                    <div class="d-flex align-items-center">
                                        {{-- <div class="info-icon bg-info bg-opacity-10 text-info"> --}}
                                            <i class="icon-bar-chart fa-3x text-info px-1 mx-2"></i>
                                        {{-- </div> --}}
                                        <div class="flex-grow-1">
                                            <div class="info-label">وضعیت</div>
                                            <div class="info-value text-success">فعال</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="info-card">
                                    <div class="d-flex align-items-center">
                                        {{-- <div class="info-icon bg-success bg-opacity-10 text-success"> --}}
                                        <i class="icon-wallet fa-3x text-success px-1 mx-2"></i>
                                        {{-- </div> --}}
                                        <div class="flex-grow-1">
                                            <div class="info-label">موجودی کل</div>
                                            <div class="info-value">{{ number_format($creditCard->balance) }} تومان</div>
                                        </div>
                                    </div>
                                </div>
                            </div>                                                                                                      

                        </div>
                        
                        <!-- کارت نکات مهم -->
                        <div class="note-card">
                            <div class="d-flex align-items-start">
                                <i class="icon-bulb text-primary fa-lg me-3"></i>
                                <div>
                                    <h6 class="fw-bold text-primary mb-3">نکات مهم</h6>
                                    <ul class="mb-0 ps-3">
                                        <li class="mb-2">موجودی قابل استفاده مبلغی است که هم‌اکنون می‌توانید خرج کنید</li>
                                        <li class="mb-2">مبلغ رزرو شده برای سفارشات در حال پردازش، قابل استفاده نمی‌باشد</li>
                                        <li>برای افزایش اعتبار بر روی دکمه زیر کلیک کنید</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- دکمه افزایش اعتبار -->
                        <div class="text-center mt-2 pt-3">
                            <a href="{{ route('user.credit-card.increase') }}" class="btn-increase">
                                <i class="fas fa-plus-circle me-2"></i>
                                افزایش اعتبار
                            </a>
                        </div>
                        
                    @else
                        <!-- کارت خالی (عدم وجود کارت) -->
                        <div class="empty-card">
                            <div class="mb-4">
                                <i class="fas fa-credit-card fa-4x text-warning"></i>
                            </div>
                            <h4 class="fw-bold text-warning mb-3">کارت اعتباری ثبت نشده!</h4>
                            <p class="text-muted mb-4">
                                برای مرکز <strong>{{ $selectedCenter['name'] ?? 'انتخاب‌شده' }}</strong>
                                هنوز کارت اعتباری ندارید.
                            </p>
                            <button class="btn-increase">
                                <i class="fas fa-plus-circle me-2"></i>
                                ایجاد کارت اعتباری جدید
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // انیمیشن نوار پیشرفت
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                const width = progressFill.getAttribute('data-width');
                setTimeout(() => {
                    progressFill.style.width = width + '%';
                }, 500);
            }
        });
    </script>
@endsection