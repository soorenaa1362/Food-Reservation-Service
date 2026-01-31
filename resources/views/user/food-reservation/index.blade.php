@extends('layouts.master')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('convex/vendors/css/tables/datatable/datatables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('adminto/sweetalert2/sweetalert2.min.css') }}"/>

    {{-- <style>
        .disabled-row { opacity: 0.5; color: #6c757d; }
        .enabled-row { opacity: 1; font-weight: bold; color: #212529; }

        /* هماهنگ‌سازی با تم بلید اول */
        .card-header {
            background: #fff;
            border-bottom: 2px solid #28a745;
        }
        .card-title {
            font-weight: bold;
            color: #28a745;
        }
        table.table {
            border-color: #e3e3e3;
        }
        table.table th {
            background: #f8f8f8;
            font-weight: bold;
        }
        .btn-success {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
        }
        .shadow-z-2 {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
    </style> --}}
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="col-12">
        <div class="card">

            <div class="card-header">
                <div class="card-title-wrap bar-success d-flex justify-content-between">
                    <h4 class="card-title mb-0">برنامه غذایی</h4>
                </div>
            </div>

            <div class="card-body collapse show">
                <div class="card-block card-dashboard">

                    @if($creditCard && $creditCard->balance > 0)

                        <form action="{{ route('user.food-reservation.cart') }}" method="POST" id="foodReservationForm">
                            @csrf

                            <table class="table table-striped table-bordered base-style text-center">
                                <thead>
                                <tr>
                                    <th>رزرو</th>
                                    <th>تاریخ</th>
                                    <th>صبحانه (قیمت) - تعداد</th>
                                    <th>ناهار (قیمت) - تعداد</th>
                                    <th>شام (قیمت) - تعداد</th>
                                </tr>
                                </thead>

                                <tbody>
                                    @foreach ($days as $dayIndex => $day)
                                        <tr class="reservation-row disabled-row">
                                            <td>
                                                <input type="checkbox" class="form-check-input toggle-row">
                                                <!-- این hidden تاریخ میلادی خالص رو نگه می‌داره (اصلاح بدون تغییر) -->
                                                <input type="hidden" name="dates[{{ $dayIndex }}]" value="{{ $day['date'] }}" class="miladi-date">
                                            </td>

                                            <td>
                                                {{ \Morilog\Jalali\Jalalian::fromDateTime($day['date'])->format('Y/m/d') }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($day['date'])->format('l') }}
                                                </small>
                                            </td>

                                            {{-- صبحانه --}}
                                            <td>
                                                @foreach ($day['breakfast'] as $foodIndex => $food)
                                                    @if($food['deadline_passed'] ?? false)
                                                        <div class="text-center p-3 bg-light border rounded">
                                                            <small class="text-danger font-weight-bold">
                                                                <i class="fa fa-clock-o mr-1"></i>
                                                                {{ $food['message'] }}
                                                            </small>
                                                        </div>
                                                    @else
                                                        <div class="mb-1">
                                                            {{ $food['food_name'] }} ({{ number_format($food['price']) }})
                                                            <input type="hidden" name="breakfast[{{ $dayIndex }}][{{ $foodIndex }}][food_name]" value="{{ $food['food_name'] }}">
                                                            <input type="hidden" name="breakfast[{{ $dayIndex }}][{{ $foodIndex }}][price]" value="{{ $food['price'] }}">
                                                            <input type="number"
                                                                name="breakfast[{{ $dayIndex }}][{{ $foodIndex }}][quantity]"
                                                                value="0"
                                                                min="0"
                                                                max="{{ $food['available_portions'] }}"
                                                                class="form-control form-control-sm d-inline-block meal-input"
                                                                style="width:60px;"
                                                                {{ !$food['is_reservable'] ? 'disabled' : '' }}>
                                                            <small class="text-muted">/ {{ $food['available_portions'] }}</small>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </td>

                                            {{-- ناهار --}}
                                            <td>
                                                @foreach ($day['lunch'] as $foodIndex => $food)
                                                    @if($food['deadline_passed'] ?? false)
                                                        <div class="text-center p-3 bg-light border rounded">
                                                            <small class="text-danger font-weight-bold">
                                                                <i class="fa fa-clock-o mr-1"></i>
                                                                {{ $food['message'] }}
                                                            </small>
                                                        </div>
                                                    @else
                                                        <div class="mb-1">
                                                            {{ $food['food_name'] }} ({{ number_format($food['price']) }})
                                                            <input type="hidden" name="lunch[{{ $dayIndex }}][{{ $foodIndex }}][food_name]" value="{{ $food['food_name'] }}">
                                                            <input type="hidden" name="lunch[{{ $dayIndex }}][{{ $foodIndex }}][price]" value="{{ $food['price'] }}">
                                                            <input type="number"
                                                                name="lunch[{{ $dayIndex }}][{{ $foodIndex }}][quantity]"
                                                                value="0"
                                                                min="0"
                                                                max="{{ $food['available_portions'] }}"
                                                                class="form-control form-control-sm d-inline-block meal-input"
                                                                style="width:60px;"
                                                                {{ !$food['is_reservable'] ? 'disabled' : '' }}>
                                                            <small class="text-muted">/ {{ $food['available_portions'] }}</small>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </td>

                                            {{-- شام --}}
                                            <td>
                                                @foreach ($day['dinner'] as $foodIndex => $food)
                                                    @if($food['deadline_passed'] ?? false)
                                                        <div class="text-center p-3 bg-light border rounded">
                                                            <small class="text-danger font-weight-bold">
                                                                <i class="fa fa-clock-o mr-1"></i>
                                                                {{ $food['message'] }}
                                                            </small>
                                                        </div>
                                                    @else
                                                        <div class="mb-1">
                                                            {{ $food['food_name'] }} ({{ number_format($food['price']) }})
                                                            <input type="hidden" name="dinner[{{ $dayIndex }}][{{ $foodIndex }}][food_name]" value="{{ $food['food_name'] }}">
                                                            <input type="hidden" name="dinner[{{ $dayIndex }}][{{ $foodIndex }}][price]" value="{{ $food['price'] }}">
                                                            <input type="number"
                                                                name="dinner[{{ $dayIndex }}][{{ $foodIndex }}][quantity]"
                                                                value="0"
                                                                min="0"
                                                                max="{{ $food['available_portions'] }}"
                                                                class="form-control form-control-sm d-inline-block meal-input"
                                                                style="width:60px;"
                                                                {{ !$food['is_reservable'] ? 'disabled' : '' }}>
                                                            <small class="text-muted">/ {{ $food['available_portions'] }}</small>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="text-center">
                                <button type="button"
                                        class="btn btn-success btn-lg px-4 shadow-z-2"
                                        id="sendToCartBtn"
                                        data-toggle="modal"
                                        data-target="#sendToCart"
                                        disabled>
                                    ثبت رزرو
                                </button>
                            </div>
                        </form>

                    @else
                        {{-- همان کارت "موجودی کافی نیست" اما هماهنگ شده با تم --}}
                        <div class="card px-4 py-2 box-shadow-3 width-600 auth-card animate__animated animate__fadeIn text-center">

                            <h4 class="mb-2 text-danger">موجودی کافی نیست</h4>

                            <p class="text-muted">برای رزرو غذا باید کارت خود را شارژ کنید.</p>

                            <a href="{{ route('user.credit-card.increase') }}"
                               class="btn btn-success btn-lg mt-2">
                                افزایش اعتبار
                            </a>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

    @include('user.food-reservation.index-modal')

@endsection

@section('scripts')
    <script src="{{ asset('convex/js/components-modal.min.js') }}"></script>
    <script src="{{ asset('convex/vendors/js/datatable/datatables.min.js') }}"></script>
    <script src="{{ asset('adminto/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sendToCartBtn = document.getElementById('sendToCartBtn');
            const cartModal = new bootstrap.Modal(document.getElementById('sendToCart'));
            const submitBtn = document.getElementById('submitBtn');
            const totalAmountEl = document.getElementById('total-amount');
            const grandTotalEl = document.getElementById('grand-total');
            const cartItemsTbody = document.getElementById('cart-items-tbody');
            const cartItemsContainer = document.getElementById('cart-items-container');
            const emptySelection = document.getElementById('empty-selection');

            function updateSubmitButton() {
                const anyValidSelection = Array.from(document.querySelectorAll('.reservation-row')).some(row => {
                    const checkbox = row.querySelector('.toggle-row');
                    if (checkbox.checked) {
                        const inputs = row.querySelectorAll('.meal-input');
                        return Array.from(inputs).some(input => parseInt(input.value) > 0);
                    }
                    return false;
                });
                sendToCartBtn.disabled = !anyValidSelection;
            }

            // فعال/غیرفعال کردن ردیف با چک‌باکس
            document.querySelectorAll(".toggle-row").forEach(function (checkbox) {
                checkbox.addEventListener("change", function () {
                    let row = this.closest(".reservation-row");
                    let inputs = row.querySelectorAll(".meal-input");
                    if (this.checked) {
                        row.classList.remove("disabled-row");
                        row.classList.add("enabled-row");
                        inputs.forEach(input => input.disabled = false);
                    } else {
                        row.classList.remove("enabled-row");
                        row.classList.add("disabled-row");
                        inputs.forEach(input => {
                            input.disabled = true;
                            input.value = 0;
                        });
                    }
                    updateSubmitButton();
                });
            });

            // بروزرسانی دکمه وقتی تعداد تغییر کرد
            document.querySelectorAll(".meal-input").forEach(function (input) {
                input.addEventListener("input", function () {
                    updateSubmitButton();
                });
            });

            // باز کردن مودال
            sendToCartBtn.addEventListener('click', function(e) {
                e.preventDefault();
                generateCartPreview();
                cartModal.show();
            });

            // تولید پیش‌نمایش سبد خرید
            function generateCartPreview() {
                let total = 0;
                let rowCounter = 1;
                cartItemsTbody.innerHTML = '';
                const modalForm = document.getElementById('sendToCartModal');
                const existingHiddenInputs = modalForm.querySelectorAll('input[name^="cart_items"]');
                existingHiddenInputs.forEach(input => input.remove());

                document.querySelectorAll('.reservation-row').forEach((row, dayIndex) => {
                    const checkbox = row.querySelector('.toggle-row');
                    if (!checkbox.checked) return;

                    // گرفتن تاریخ میلادی خالص از hidden
                    const dateInput = row.querySelector('.miladi-date');
                    const dateValue = dateInput ? dateInput.value : ''; // مثال: 2026-01-03

                    // برای نمایش در مودال: تبدیل تقریبی به شمسی (اختیاری - می‌تونی حذف کنی)
                    const displayDate = miladiToShamsi(dateValue);

                    // تابع مشترک برای اضافه کردن آیتم (صبحانه، ناهار، شام)
                    function processMeal(mealType, mealFa) {
                        const inputs = row.querySelectorAll(`input[name^="${mealType}"][name*="quantity"]`);
                        inputs.forEach((input, foodIndex) => {
                            const quantity = parseInt(input.value);
                            if (quantity > 0) {
                                const foodNameInput = input.closest('.mb-1').querySelector('input[name*="[food_name]"]');
                                const priceInput = input.closest('.mb-1').querySelector('input[name*="[price]"]');
                                const foodName = foodNameInput ? foodNameInput.value : '';
                                const price = parseInt(priceInput ? priceInput.value : 0);
                                const lineTotal = price * quantity;

                                // اضافه کردن به جدول مودال
                                const rowHtml = `
                                    <tr>
                                        <th scope="row">${rowCounter++}</th>
                                        <td>${displayDate}</td>
                                        <td>${foodName}</td>
                                        <td>${mealFa}</td>
                                        <td>${quantity}</td>
                                        <td>${number_format(price)} تومان</td>
                                        <td class="text-success font-weight-bold">${number_format(lineTotal)} تومان</td>
                                    </tr>
                                `;
                                cartItemsTbody.innerHTML += rowHtml;
                                total += lineTotal;

                                // اضافه کردن hidden input به فرم مودال (تاریخ میلادی!)
                                addHiddenInputToModal(modalForm, dayIndex, foodIndex, mealType, foodName, price, quantity, dateValue);
                            }
                        });
                    }

                    processMeal('breakfast', 'صبحانه');
                    processMeal('lunch', 'ناهار');
                    processMeal('dinner', 'شام');
                });

                // نمایش نتایج
                if (total > 0 && cartItemsTbody.children.length > 0) {
                    cartItemsContainer.classList.remove('d-none');
                    emptySelection.classList.add('d-none');
                    totalAmountEl.textContent = number_format(total) + ' تومان';
                    grandTotalEl.textContent = number_format(total) + ' تومان';
                    submitBtn.disabled = false;
                } else {
                    cartItemsContainer.classList.add('d-none');
                    emptySelection.classList.remove('d-none');
                    submitBtn.disabled = true;
                }
            }

            // اضافه کردن hidden inputها به فرم مودال
            function addHiddenInputToModal(form, dayIndex, foodIndex, mealType, foodName, price, quantity, date) {
                const inputs = [
                    { name: `cart_items[${dayIndex}][${mealType}][${foodIndex}][food_name]`, value: foodName },
                    { name: `cart_items[${dayIndex}][${mealType}][${foodIndex}][price]`, value: price },
                    { name: `cart_items[${dayIndex}][${mealType}][${foodIndex}][quantity]`, value: quantity },
                    { name: `cart_items[${dayIndex}][${mealType}][${foodIndex}][date]`, value: date }
                ];
                inputs.forEach(input => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = input.name;
                    hiddenInput.value = input.value;
                    form.appendChild(hiddenInput);
                });
            }

            // فرمت اعداد فارسی
            function number_format(number) {
                return new Intl.NumberFormat('fa-IR').format(number);
            }

            // تبدیل تقریبی میلادی به شمسی برای نمایش در مودال (اختیاری)
            function miladiToShamsi(dateStr) {
                if (!dateStr) return '';
                const [gy, gm, gd] = dateStr.split('-').map(Number);

                let jy = gy - 621;
                let jm = gm - 3;
                let jd = gd + 10; // تقریبی اولیه

                if (jm <= 0) {
                    jm += 12;
                    jy--;
                }

                // تنظیم دقیق برای سال کبیسه و ماه‌ها
                const daysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, (jy % 4 === 3 ? 30 : 29)];
                while (jd > daysInMonth[jm - 1]) {
                    jd -= daysInMonth[jm - 1];
                    jm++;
                    if (jm > 12) {
                        jm = 1;
                        jy++;
                    }
                }

                return `${jy}/${jm.toString().padStart(2, '0')}/${jd.toString().padStart(2, '0')}`;
            }

            // فعال کردن inputهای disabled قبل از ارسال فرم اصلی
            document.getElementById('foodReservationForm').addEventListener('submit', function() {
                document.querySelectorAll('.meal-input').forEach(input => input.disabled = false);
            });

            // بستن مودال
            document.getElementById('cancelBtn').addEventListener('click', function() {
                cartModal.hide();
            });
        });
    </script>
@endsection