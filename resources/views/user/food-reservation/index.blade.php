@extends('layouts.master')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('convex/vendors/css/tables/datatable/datatables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('adminto/sweetalert2/sweetalert2.min.css') }}"/>

    {{-- index-css --}}
    <style>
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

        /* استایل برای روزهایی که غذایی ندارند (هاشور زده) */
        .food-cell-empty {
            background-color: #f8f9fa; /* رنگ پس‌زمینه خیلی روشن */
            /* ایجاد خطوط مورب (هاشور) */
            background-image: repeating-linear-gradient(
                45deg,
                #f8f9fa,
                #f8f9fa 10px,
                #dee2e6 10px,
                #dee2e6 20px
            );
            color: #adb5bd; /* رنگ متن‌ها کاملاً خاکستری */
            text-align: center;
            vertical-align: middle;
            font-style: italic; /* متن‌ها اگر بودند مورب باشند */
            letter-spacing: 2px;
        }

        /* اطمینان از اینکه سطرها خیلی کمرنگ نمی‌شوند */
        .reservation-row {
            transition: all 0.3s;
        }
    </style>

    {{-- modal-css --}}
    <style>
        /* کارت اصلی مودال */
        .card {
            border-radius: 12px;
        }
        .shadow-z-2 {
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        /* سربرگ کارت شبیه جدول غذاها */
        .card-header {
            background: #fff;
            border-bottom: 2px solid #28a745;
        }
        .card-header .card-title {
            font-weight: bold;
            color: #28a745;
            margin-bottom: 0;
        }

        /* کارت نکات */
        .note-card {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 15px 20px;
            border-left: 4px solid #2196f3;
        }

        /* کارت خالی */
        .empty-card {
            background: #fff5e6;
            border-radius: 12px;
            border: 2px dashed #ffa726;
        }

        /* جدول */
        .table-hover tbody tr:hover {
            background-color: #e9f7ef;
            transition: background-color 0.3s;
        }

        .card-footer {
            border-top: none; /* حذف خط بالای فوتر */
        }

        /* دکمه‌ها */
        .btn-success {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            transition: all 0.3s;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }
        .btn-outline-danger {
            border-radius: 8px;
        }

        
    </style>

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
                                    @forelse ($days as $dayIndex => $day)
                                        @php
                                            // چک میکنیم آیا این روز غذایی دارد یا خیر
                                            $hasAnyFood = !empty($day['breakfast']) || !empty($day['lunch']) || !empty($day['dinner']);
                                            
                                            // کلاس سطر
                                            $rowClass = $hasAnyFood ? 'reservation-row disabled-row' : 'reservation-row';
                                            // وضعیت چک‌باکس
                                            $checkboxState = $hasAnyFood ? '' : 'disabled';
                                            // کلاس سلول‌های غذا (اگر غذایی نبود، هاشور بخوره)
                                            $cellClass = $hasAnyFood ? '' : 'food-cell-empty';
                                        @endphp

                                        <tr class="{{ $rowClass }}">
                                            <td style="vertical-align: middle;">
                                                <input type="checkbox" class="form-check-input toggle-row" {{ $checkboxState }}>
                                                <input type="hidden" name="dates[{{ $dayIndex }}]" value="{{ $day['date'] }}" class="miladi-date">
                                            </td>

                                            <td style="vertical-align: middle; opacity: {{ $hasAnyFood ? 1 : 0.5 }};">
                                                {{ \Morilog\Jalali\Jalalian::fromDateTime($day['date'])->format('Y/m/d') }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($day['date'])->format('l') }}
                                                </small>
                                            </td>

                                            {{-- صبحانه --}}
                                            <td class="{{ $cellClass }}">
                                                @if($hasAnyFood && !empty($day['breakfast']))
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
                                                @else
                                                    {{-- اگه غذایی نیست، یه کاراکتر فضایی یا نقطه می‌ذاریم که ارتفاع سلول حفظ بشه و هاشور دیده بشه --}}
                                                    <span>&bull;</span>
                                                @endif
                                            </td>

                                            {{-- ناهار --}}
                                            <td class="{{ $cellClass }}">
                                                @if($hasAnyFood && !empty($day['lunch']))
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
                                                @else
                                                    <span>&bull;</span>
                                                @endif
                                            </td>

                                            {{-- شام --}}
                                            <td class="{{ $cellClass }}">
                                                @if($hasAnyFood && !empty($day['dinner']))
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
                                                @else
                                                    <span>&bull;</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-muted text-info font-medium-2 p-4">برای این مرکز هنوز هیچ منوی غذایی ثبت نشده است.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            @if (!empty($days))                                                        
                                <div class="text-center">
                                    <button type="button"
                                        class="btn btn-success btn-lg px-4 shadow-z-2"
                                        id="sendToCartBtn"
                                        data-toggle="modal"
                                        data-target="#sendToCart"
                                        disabled
                                    >
                                        ثبت رزرو
                                    </button>
                                </div>
                            @endif
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
        // ────────────────────────────────────────────────
        // بخش ۱: وقتی صفحه لود شد، همه چیز رو آماده کن
        // ────────────────────────────────────────────────
        document.addEventListener("DOMContentLoaded", function () {

            const sendToCartBtn   = document.getElementById('sendToCartBtn');
            const cartModal       = new bootstrap.Modal(document.getElementById('sendToCart'));
            const submitBtn       = document.getElementById('submitBtn');
            const totalAmountEl   = document.getElementById('total-amount');
            const grandTotalEl    = document.getElementById('grand-total');
            const cartItemsTbody  = document.getElementById('cart-items-tbody');
            const cartItemsContainer = document.getElementById('cart-items-container');
            const emptySelection  = document.getElementById('empty-selection');

            // ────────────────────────────────────────────────
            // تابع: فعال/غیرفعال کردن دکمه ثبت رزرو
            // ────────────────────────────────────────────────
            function updateSubmitButton() {
                const anyValid = Array.from(document.querySelectorAll('.reservation-row')).some(row => {
                    const checkbox = row.querySelector('.toggle-row');
                    if (!checkbox.checked) return false;

                    const inputs = row.querySelectorAll('.meal-input');
                    return Array.from(inputs).some(input => parseInt(input.value) > 0);
                });

                sendToCartBtn.disabled = !anyValid;
            }

            // ────────────────────────────────────────────────
            // تابع: مدیریت وضعیت یک ردیف (تیک خورده یا نخورده)
            // ────────────────────────────────────────────────
            function toggleRow(row, shouldEnable) {
                const inputs = row.querySelectorAll('.meal-input');

                if (shouldEnable) {
                    row.classList.remove('disabled-row');
                    row.classList.add('enabled-row');
                    inputs.forEach(input => input.disabled = false);
                } else {
                    row.classList.remove('enabled-row');
                    row.classList.add('disabled-row');
                    inputs.forEach(input => {
                        input.disabled = true;
                        input.value = '0';          // مهم: صفر کردن مقدار
                    });
                }
            }

            // ────────────────────────────────────────────────
            // بخش ۲: مدیریت چک‌باکس‌ها (تیک زدن / برداشتن)
            // ────────────────────────────────────────────────
            document.querySelectorAll(".toggle-row").forEach(checkbox => {
                // وضعیت اولیه هر ردیف رو درست ست کن
                const row = checkbox.closest(".reservation-row");
                toggleRow(row, checkbox.checked);

                // رویداد تغییر تیک
                checkbox.addEventListener("change", function () {
                    toggleRow(row, this.checked);
                    updateSubmitButton();
                });
            });

            // ────────────────────────────────────────────────
            // بخش ۳: وقتی تعداد غذا تغییر کرد، دکمه رو چک کن
            // ────────────────────────────────────────────────
            document.querySelectorAll(".meal-input").forEach(input => {
                input.addEventListener("input", function () {
                    // جلوگیری از مقادیر نامعتبر
                    if (this.value < 0) this.value = 0;
                    if (this.max && this.value > parseInt(this.max)) {
                        this.value = this.max;
                    }
                    updateSubmitButton();
                });
            });

            // ────────────────────────────────────────────────
            // بخش ۴: کلیک روی دکمه → باز کردن مودال + ساخت پیش‌نمایش
            // ────────────────────────────────────────────────
            sendToCartBtn.addEventListener('click', function(e) {
                e.preventDefault();
                generateCartPreview();
                cartModal.show();
            });

            // ────────────────────────────────────────────────
            // تابع: ساخت محتوای جدول داخل مودال سبد خرید
            // ────────────────────────────────────────────────
            function generateCartPreview() {
                let total = 0;
                let rowCounter = 1;
                cartItemsTbody.innerHTML = '';

                const modalForm = document.getElementById('sendToCartModal');
                // پاک کردن hiddenهای قبلی
                modalForm.querySelectorAll('input[name^="cart_items"]').forEach(el => el.remove());

                document.querySelectorAll('.reservation-row').forEach((row, dayIndex) => {
                    const checkbox = row.querySelector('.toggle-row');
                    if (!checkbox.checked) return;

                    const dateInput = row.querySelector('.miladi-date');
                    const dateValue = dateInput ? dateInput.value : '';
                    const displayDate = miladiToShamsi(dateValue);

                    function processMeal(mealType, mealFa) {
                        const inputs = row.querySelectorAll(`input[name^="${mealType}"][name*="quantity"]`);
                        inputs.forEach((input, foodIndex) => {
                            const quantity = parseInt(input.value) || 0;
                            if (quantity <= 0) return;

                            const foodNameInput = input.closest('.mb-1').querySelector('input[name*="[food_name]"]');
                            const priceInput    = input.closest('.mb-1').querySelector('input[name*="[price]"]');

                            const foodName = foodNameInput ? foodNameInput.value : '';
                            const price    = parseInt(priceInput ? priceInput.value : 0);
                            const lineTotal = price * quantity;

                            // ردیف جدول مودال
                            cartItemsTbody.innerHTML += `
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

                            total += lineTotal;

                            // اضافه کردن hidden به فرم مودال
                            addHiddenInputToModal(modalForm, dayIndex, foodIndex, mealType, foodName, price, quantity, dateValue);
                        });
                    }

                    processMeal('breakfast', 'صبحانه');
                    processMeal('lunch',     'ناهار');
                    processMeal('dinner',    'شام');
                });

                // نمایش / مخفی کردن بخش‌ها
                if (total > 0 && cartItemsTbody.children.length > 0) {
                    cartItemsContainer.classList.remove('d-none');
                    emptySelection.classList.add('d-none');
                    totalAmountEl.textContent = number_format(total) + ' تومان';
                    grandTotalEl.textContent  = number_format(total) + ' تومان';
                    submitBtn.disabled = false;
                } else {
                    cartItemsContainer.classList.add('d-none');
                    emptySelection.classList.remove('d-none');
                    submitBtn.disabled = true;
                }
            }

            // ────────────────────────────────────────────────
            // تابع کمکی: اضافه کردن hidden input به فرم مودال
            // ────────────────────────────────────────────────
            function addHiddenInputToModal(form, dayIndex, foodIndex, mealType, foodName, price, quantity, date) {
                const fields = [
                    { name: `cart_items[${dayIndex}][${mealType}][${foodIndex}][food_name]`, value: foodName },
                    { name: `cart_items[${dayIndex}][${mealType}][${foodIndex}][price]`,     value: price },
                    { name: `cart_items[${dayIndex}][${mealType}][${foodIndex}][quantity]`,  value: quantity },
                    { name: `cart_items[${dayIndex}][${mealType}][${foodIndex}][date]`,      value: date }
                ];

                fields.forEach(field => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = field.name;
                    input.value = field.value;
                    form.appendChild(input);
                });
            }

            // ────────────────────────────────────────────────
            // تابع: فرمت عدد به فارسی
            // ────────────────────────────────────────────────
            function number_format(num) {
                return new Intl.NumberFormat('fa-IR').format(num);
            }

            // ────────────────────────────────────────────────
            // تابع: تبدیل تقریبی میلادی به شمسی (برای نمایش)
            // ────────────────────────────────────────────────
            function miladiToShamsi(dateStr) {
                if (!dateStr) return '';
                const [gy, gm, gd] = dateStr.split('-').map(Number);
                let jy = gy - 621;
                let jm = gm - 3;
                let jd = gd + 10;

                if (jm <= 0) { jm += 12; jy--; }

                const daysInMonth = [31,31,31,31,31,31,30,30,30,30,30, (jy % 4 === 3 ? 30 : 29)];
                while (jd > daysInMonth[jm - 1]) {
                    jd -= daysInMonth[jm - 1];
                    jm++;
                    if (jm > 12) { jm = 1; jy++; }
                }
                return `${jy}/${jm.toString().padStart(2,'0')}/${jd.toString().padStart(2,'0')}`;
            }

            // ────────────────────────────────────────────────
            // قبل از submit فرم اصلی → مطمئن شو inputهای غیرفعال صفر بمونن
            // ────────────────────────────────────────────────
            document.getElementById('foodReservationForm').addEventListener('submit', function() {
                document.querySelectorAll('.meal-input[disabled]').forEach(input => {
                    input.value = '0';
                });
            });

            // ────────────────────────────────────────────────
            // بستن مودال با دکمه cancel
            // ────────────────────────────────────────────────
            document.getElementById('cancelBtn')?.addEventListener('click', function() {
                cartModal.hide();
                location.reload();
            });
        });
    </script>
@endsection