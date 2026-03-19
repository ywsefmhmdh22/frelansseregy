@extends('layouts.master')

@section('content')
<div class="container py-5" dir="rtl">
    <div class="row g-4">
        {{-- الجانب الأيمن: كارت المحفظة --}}
        <div class="col-lg-4">
            {{-- الكارت الذهبي/الأخضر الاحترافي --}}
            <div class="wallet-card-master p-4 shadow-lg mb-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <i class="fas fa-wallet fa-2x text-white-50"></i>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" width="50" class="opacity-75">
                </div>
                <div class="mb-4">
                    <span class="text-white-50 small d-block mb-1">الرصيد المتاح</span>
                    <h2 class="text-white fw-bold mb-0">{{ number_format($walletBalance, 2) }} <small class="h6">ج.م</small></h2>
                </div>
                <div class="d-flex justify-content-between align-items-end">
                    <div class="text-white">
                        <span class="text-white-50 d-block small" style="font-size: 10px;">صاحب المحفظة</span>
                        <span class="fw-bold">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="text-white text-start">
                        <span class="text-white-50 d-block small" style="font-size: 10px;">الحالة</span>
                        <span class="badge bg-white text-success rounded-pill px-3" style="font-size: 10px;">نشط</span>
                    </div>
                </div>
            </div>

            {{-- زراير سريعة --}}
            <div class="row g-2">
                <div class="col-6">
                    <a href="{{ route('wallet.deposit') }}" class="btn btn-primary w-100 py-3 rounded-4 shadow-sm">
                        <i class="fas fa-plus-circle d-block mb-1"></i> شحن
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('withdraw.create') }}" class="btn btn-outline-dark w-100 py-3 rounded-4 shadow-sm bg-white">
                        <i class="fas fa-arrow-down d-block mb-1"></i> سحب
                    </a>
                </div>
            </div>
        </div>

        {{-- الجانب الأيسر: سجل العمليات --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">سجل المعاملات المالية</h6>
                    <button class="btn btn-light btn-sm rounded-pill px-3">تصدير PDF</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">العملية</th>
                                    <th>التاريخ</th>
                                    <th>المبلغ</th>
                                    <th class="text-center">الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- مثال لعملية (تقدر تربطها بالـ Loop لاحقاً) --}}
                                @forelse($transactions ?? [] as $transaction)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="icon-circle bg-light text-primary">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </div>
                                                <div>
                                                    <span class="d-block fw-bold small">شراء خدمة</span>
                                                    <span class="text-muted" style="font-size: 11px;">رقم الطلب: #1234</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted small">2026-03-19</td>
                                        <td class="fw-bold text-danger">- 150.00 ج.م</td>
                                        <td class="text-center"><span class="badge bg-light-success text-success rounded-pill">مكتمل</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" width="80" class="opacity-25 mb-3">
                                            <p class="text-muted">لا توجد حركات مالية مسجلة حتى الآن.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .wallet-card-master {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        border-radius: 25px;
        position: relative;
        overflow: hidden;
        min-height: 200px;
    }
    .wallet-card-master::after {
        content: '';
        position: absolute;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        top: -100px;
        right: -100px;
    }
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-light-success { background-color: #dcfce7; }
    .table thead th { font-size: 13px; color: #64748b; padding: 15px; }
    .btn-primary { background-color: #10b981; border: none; }
    .btn-primary:hover { background-color: #059669; }
</style>
@endsection
