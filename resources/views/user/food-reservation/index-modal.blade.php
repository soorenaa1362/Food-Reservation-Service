{{-- Send To Cart Modal --}}
<div class="modal fade text-left" id="sendToCart" tabindex="-1"
    role="dialog" aria-labelledby="sendToCartLabel" aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0">

            <form action="{{ route('user.food-reservation.check-credit') }}" method="POST" id="sendToCartModal">
                @csrf
                <div class="modal-body p-0">

                    {{-- کارت اصلی محتوا --}}
                    <div class="card shadow-z-2 rounded-3 m-3">

                        {{-- سربرگ کارت مشابه لیست غذاها --}}
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="icon-cart-plus me-2"></i> پیش‌ نمایش رزرو
                            </h5>
                        </div>

                        {{-- بدنه کارت --}}
                        <div class="card-body p-3">

                            {{-- جدول سبد خرید --}}
                            <div id="cart-items-container" class="d-none">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered text-center align-middle">
                                        <thead class="table-success text-secondary text-uppercase">
                                            <tr>
                                                <th>#</th>
                                                <th>تاریخ</th>
                                                <th>غذا</th>
                                                <th>وعده</th>
                                                <th>تعداد</th>
                                                <th>قیمت واحد</th>
                                                <th>مبلغ کل</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cart-items-tbody">
                                            <!-- آیتم‌ها به صورت داینامیک اضافه می‌شوند -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-8">
                                        <div class="note-card">
                                            <p class="mb-0">
                                                مجموع مبلغ پرداختی مشخص شده است. با کلیک روی دکمه رزرو و پرداخت،
                                                مبلغ از موجودی اعتبار شما کسر می گردد و شما به لیست رزروها هدایت 
                                                می‌شوید.
                                                در غیر اینصورت به فرم افزایش اعتبار هدایت خواهید شد.                                                
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <p class="lead h4 text-success fw-bold" id="total-amount">0 تومان</p>
                                        <table class="table table-borderless text-end">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold">جمع کل:</td>
                                                    <td id="grand-total" class="fw-bold h5 text-success">0 تومان</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- وقتی هیچ آیتمی انتخاب نشده --}}
                            <div id="empty-selection" class="empty-card text-center m-0 p-4">
                                <i class="icon-alert-circle fa-4x text-warning mb-3"></i>
                                <h5 class="fw-bold text-warning mb-2">هیچ موردی انتخاب نشده است</h5>
                                <p class="text-muted mb-0">لطفاً حداقل یک وعده غذایی را انتخاب کنید.</p>
                            </div>

                        </div>

                        {{-- فوتر کارت بدون خط --}}
                        <div class="card-footer d-flex justify-content-between border-0 p-3">
                            <button type="button" class="btn btn-outline-danger" id="cancelBtn" data-bs-dismiss="modal">
                                <i class="icon-trash me-1"></i> لغو
                            </button>
                            <button type="submit" class="btn btn-success btn-lg px-4" id="submitBtn" disabled>
                                <i class="icon-cart-plus me-1"></i> رزرو و پرداخت
                            </button>
                        </div>

                    </div> {{-- پایان کارت اصلی محتوا --}}
                </div>
            </form>
        </div>
    </div>
</div>
