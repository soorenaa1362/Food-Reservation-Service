@extends('layouts.master')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('convex/vendors/css/tables/datatable/datatables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('adminto/sweetalert2/sweetalert2.min.css') }}"/>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger text-center">
            {{ session('error') }}
        </div>
    @endif

    <div class="col-12">
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

    @include('user.food-reservation.index-modal')

@endsection

@section('scripts')
    <script src="{{ asset('convex/js/components-modal.min.js') }}"></script>
    <script src="{{ asset('convex/js/tooltip.js') }}"></script>
    <script src="{{ asset('convex/vendors/js/datatable/datatables.min.js') }}"></script>
    <script src="{{ asset('convex/js/data-tables/datatable-styling.js') }}"></script>
    <!-- Sweet Alerts js -->
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

            let selectedItems = [];

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

            // رویداد تغییر چک‌باکس‌ها
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

            // رویداد تغییر فیلدهای تعداد
            document.querySelectorAll(".meal-input").forEach(function (input) {
                input.addEventListener("input", function () {
                    updateSubmitButton();
                });
            });

            // هنگام کلیک روی دکمه ثبت رزرو
            sendToCartBtn.addEventListener('click', function(e) {
                e.preventDefault();
                generateCartPreview();
                cartModal.show();
            });

            // تولید پیش‌نمایش سبد خرید
            function generateCartPreview() {
                selectedItems = [];
                let total = 0;
                let rowCounter = 1;

                // پاک کردن جدول قبلی
                cartItemsTbody.innerHTML = '';

                // پاک کردن فیلدهای مخفی قبلی (برای جلوگیری از تکرار)
                const modalForm = document.getElementById('sendToCartModal');
                const existingHiddenInputs = modalForm.querySelectorAll('input[name^="cart_items"]');
                existingHiddenInputs.forEach(input => input.remove());

                // جمع‌آوری آیتم‌های انتخاب شده
                document.querySelectorAll('.reservation-row').forEach((row, dayIndex) => {
                    const checkbox = row.querySelector('.toggle-row');
                    if (checkbox.checked) {
                        const dateInput = row.querySelector('input[name^="dates"]');
                        const date = dateInput ? dateInput.value : '';
                        const persianDate = document.querySelector(`tr.reservation-row:nth-child(${dayIndex + 1}) td:nth-child(2)`).textContent.trim();

                        // صبحانه
                        const breakfastInputs = row.querySelectorAll('input[name^="breakfast"][name*="quantity"]');
                        breakfastInputs.forEach((input, foodIndex) => {
                            if (parseInt(input.value) > 0) {
                                const foodNameInput = input.closest('.mb-1').querySelector('input[name*="[food_name]"]');
                                const priceInput = input.closest('.mb-1').querySelector('input[name*="[price]"]');
                                
                                const foodName = foodNameInput ? foodNameInput.value : '';
                                const price = parseInt(priceInput.value);
                                const quantity = parseInt(input.value);
                                const lineTotal = price * quantity;

                                selectedItems.push({
                                    dayIndex: dayIndex,
                                    date: persianDate,
                                    food_name: foodName,
                                    meal_type: 'breakfast',
                                    quantity: quantity,
                                    price: price,
                                    total: lineTotal
                                });

                                // اضافه کردن به جدول
                                const rowHtml = `
                                    <tr>
                                        <th scope="row">${rowCounter++}</th>
                                        <td>${persianDate}</td>
                                        <td>${foodName}</td>
                                        <td>صبحانه</td>
                                        <td>${quantity}</td>
                                        <td>${number_format(price)} تومان</td>
                                        <td class="text-success font-weight-bold">${number_format(lineTotal)} تومان</td>
                                    </tr>
                                `;
                                cartItemsTbody.innerHTML += rowHtml;
                                total += lineTotal;

                                // اضافه کردن فیلد مخفی به فرم مودال
                                addHiddenInputToModal(modalForm, dayIndex, foodIndex, 'breakfast', foodName, price, quantity, persianDate);
                            }
                        });

                        // ناهار
                        const lunchInputs = row.querySelectorAll('input[name^="lunch"][name*="quantity"]');
                        lunchInputs.forEach((input, foodIndex) => {
                            if (parseInt(input.value) > 0) {
                                const foodNameInput = input.closest('.mb-1').querySelector('input[name*="[food_name]"]');
                                const priceInput = input.closest('.mb-1').querySelector('input[name*="[price]"]');
                                
                                const foodName = foodNameInput ? foodNameInput.value : '';
                                const price = parseInt(priceInput.value);
                                const quantity = parseInt(input.value);
                                const lineTotal = price * quantity;

                                selectedItems.push({
                                    dayIndex: dayIndex,
                                    date: persianDate,
                                    food_name: foodName,
                                    meal_type: 'lunch',
                                    quantity: quantity,
                                    price: price,
                                    total: lineTotal
                                });

                                const rowHtml = `
                                    <tr>
                                        <th scope="row">${rowCounter++}</th>
                                        <td>${persianDate}</td>
                                        <td>${foodName}</td>
                                        <td>ناهار</td>
                                        <td>${quantity}</td>
                                        <td>${number_format(price)} تومان</td>
                                        <td class="text-success font-weight-bold">${number_format(lineTotal)} تومان</td>
                                    </tr>
                                `;
                                cartItemsTbody.innerHTML += rowHtml;
                                total += lineTotal;

                                // اضافه کردن فیلد مخفی به فرم مودال
                                addHiddenInputToModal(modalForm, dayIndex, foodIndex, 'lunch', foodName, price, quantity, persianDate);
                            }
                        });

                        // شام
                        const dinnerInputs = row.querySelectorAll('input[name^="dinner"][name*="quantity"]');
                        dinnerInputs.forEach((input, foodIndex) => {
                            if (parseInt(input.value) > 0) {
                                const foodNameInput = input.closest('.mb-1').querySelector('input[name*="[food_name]"]');
                                const priceInput = input.closest('.mb-1').querySelector('input[name*="[price]"]');
                                
                                const foodName = foodNameInput ? foodNameInput.value : '';
                                const price = parseInt(priceInput.value);
                                const quantity = parseInt(input.value);
                                const lineTotal = price * quantity;

                                selectedItems.push({
                                    dayIndex: dayIndex,
                                    date: persianDate,
                                    food_name: foodName,
                                    meal_type: 'dinner',
                                    quantity: quantity,
                                    price: price,
                                    total: lineTotal
                                });

                                const rowHtml = `
                                    <tr>
                                        <th scope="row">${rowCounter++}</th>
                                        <td>${persianDate}</td>
                                        <td>${foodName}</td>
                                        <td>شام</td>
                                        <td>${quantity}</td>
                                        <td>${number_format(price)} تومان</td>
                                        <td class="text-success font-weight-bold">${number_format(lineTotal)} تومان</td>
                                    </tr>
                                `;
                                cartItemsTbody.innerHTML += rowHtml;
                                total += lineTotal;

                                // اضافه کردن فیلد مخفی به فرم مودال
                                addHiddenInputToModal(modalForm, dayIndex, foodIndex, 'dinner', foodName, price, quantity, persianDate);
                            }
                        });
                    }
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

            // تابع برای اضافه کردن فیلدهای مخفی به فرم مودال
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

            // تابع فرمت اعداد
            function number_format(number) {
                return new Intl.NumberFormat('fa-IR').format(number);
            }

            // هنگام ارسال فرم اصلی، فیلدهای disabled را فعال کن
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
