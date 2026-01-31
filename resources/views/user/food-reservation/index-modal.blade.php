{{-- Send To Cart Modal --}}
<div class="modal fade text-left" id="sendToCart" tabindex="-1"
    role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true"
>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label class="modal-title text-text-bold-600" id="myModalLabel33">
                    <i class="icon-cart-plus"></i> فرم رزرو و پرداخت
                </label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('user.food-reservation.check-credit') }}" method="POST" id="sendToCartModal">
                @csrf 
                <div class="modal-body">
                    <div id="cart-items-container" class="d-none">
                        <div class="card-body p-3">
                            <div id="invoice-template" class="card-block">
                                <div id="invoice-items-details" class="pt-2">
                                    <div class="row">
                                        <div class="table-responsive col-sm-12">
                                            <table class="table table-bordered table-hover text-center">
                                                <thead class="table-success">
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
                                                    <!-- آیتم‌ها به صورت داینامیک اینجا اضافه می‌شوند -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-8 text-left">
                                            <div class="alert alert-info">
                                                <p>
                                                    مجموع مبلغ پرداختی مشخص شده است ، با کلیک روی دکمه ی رزرو و پرداخت در صورتی که 
                                                    اعتبار لازم را داشته باشید ، مبلغ از موجودی شما کسر شده و به لیست رزروهای خود
                                                    هدایت می شوید و در غیر اینصورت به فرم افزایش اعتبار ، تا برای شارژ اعتبار خود 
                                                    اقدام نمایید .
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <p class="lead h4 text-success font-weight-bold" id="total-amount">0 تومان</p>
                                            <table class="table table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td class="font-weight-bold">جمع کل:</td>
                                                        <td id="grand-total" class="font-weight-bold h5 text-success">0 تومان</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="empty-selection" class="text-center py-5">
                        <i class="icon-alert-circle fsize-60 text-muted mb-3"></i>
                        <h5 class="text-muted">هیچ موردی انتخاب نشده است</h5>
                        <p class="text-muted">لطفاً حداقل یک وعده غذایی را انتخاب کنید.</p>
                    </div>
                </div>               
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger mr-1" data-dismiss="modal" id="cancelBtn">
                        <i class="icon-trash"></i> لغو
                    </button>
                    <button type="submit" class="btn btn-success px-4" id="submitBtn" disabled>
                        <i class="icon-cart-plus"></i> رزرو و  پرداخت
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>