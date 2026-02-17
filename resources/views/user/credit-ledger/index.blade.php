@extends('layouts.master')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('convex/vendors/css/tables/datatable/datatables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('adminto/sweetalert2/sweetalert2.min.css') }}"/>
@endsection

@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title-wrap bar-success d-flex justify-content-between">
                    <h4 class="card-title mb-0">تاریخچه ی اعتبارات</h4>
                </div>
            </div>

            <div class="card-body collapse show">
                <div class="card-block card-dashboard">
                    <table class="table table-striped table-bordered base-style text-center">
                        <thead>
                            <tr>                                
                                <th>تاریخ</th>
                                <th>مبلغ (تومان)</th>
                                <th>مانده ی قبل</th>
                                <th>مانده ی بعد</th>
                                <th>نوع تراکنش</th>
                                <th>منبع تراکنش</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($creditLedgers as $creditLedger)
                                <tr>
                                    <td>
                                        {{ \Morilog\Jalali\Jalalian::fromDateTime($creditLedger->created_at)->format('Y/m/d') }}
                                        <br>
                                        <small class="text-muted">
                                            {{ \Morilog\Jalali\Jalalian::fromDateTime($creditLedger->created_at)->format('l') }}
                                        </small>
                                    </td> 
                                    <td>
                                        <span class="d-inline-block {{ $creditLedger->isIncrease() ? 'text-success' : 'text-danger' }}">
                                            {{ number_format(abs($creditLedger->amount)) }}
                                            {{ $creditLedger->isIncrease() ? '+' : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ number_format($creditLedger->balance_before) }} تومان
                                    </td>
                                    <td>
                                        {{ number_format($creditLedger->balance_after) }} تومان
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $creditLedger->getTypeBadgeClass() }} text-white">
                                            <i class="ft-arrow-{{ $creditLedger->isIncrease() ? 'up' : 'down' }}"></i>
                                            {{ $creditLedger->getTypeLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $creditLedger->getSourceBadgeClass() }} text-white">
                                            {{ $creditLedger->getSourceLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('user.credit-ledger.show', $creditLedger->id) }}"
                                            class="info p-0" data-toggle="tooltip"
                                            data-placement="top" title="مشاهده"
                                        >
                                            <i class="icon-eye font-medium-3 mr-2"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted text-info font-medium-2 p-4">برای این مرکز هنوز هیچ چیزی ثبت نشده است.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('convex/js/components-modal.min.js') }}"></script>
    <script src="{{ asset('convex/js/tooltip.js') }}"></script>
    <script src="{{ asset('convex/vendors/js/datatable/datatables.min.js') }}"></script>
    <script src="{{ asset('convex/js/data-tables/datatable-styling.js') }}"></script>
@endsection