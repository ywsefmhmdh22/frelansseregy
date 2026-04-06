@extends('layouts.master')

@section('content')
<div class="container py-5" dir="rtl">
    <div class="row g-4">
        {{-- الجانب الأيمن: كارت الفيزا المطور --}}
        <div class="col-lg-4">
            {{-- كارت فيزا احترافي --}}
            <div class="visa-card p-4 shadow-lg mb-4 position-relative overflow-hidden">
                {{-- الدوائر الخلفية للجماليات --}}
                <div class="circle-1"></div>
                <div class="circle-2"></div>

                <div class="d-flex justify-content-between align-items-start mb-5 position-relative">
                    <div class="bank-logo">
                        {{-- الحرف الأول من اسم المستخدم --}}
                        <div class="user-initial">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" width="60" class="visa-logo">
                </div>

                <div class="mb-4 position-relative">
                    <span class="text-white-50 small d-block mb-1">الرصيد المتاح</span>
                    <h2 class="text-white fw-bold mb-0">
                        {{ number_format($walletBalance, 2) }}
                        <span style="font-size: 14px; font-weight: normal;">EGP</span>
                    </h2>
                </div>

                <div class="d-flex justify-content-between align-items-end position-relative">
                    <div class="text-white">
                        <span class="text-white-50 d-block mb-1" style="font-size: 10px; letter-spacing: 1px;">CARD HOLDER</span>
                        <span class="fw-bold text-uppercase" style="letter-spacing: 1px;">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="chip">
                        <div class="chip-line"></div>
                        <div class="chip-line"></div>
                        <div class="chip-line"></div>
                        <div class="chip-line"></div>
                    </div>
                </div>
            </div>

            {{-- زراير سريعة --}}
            <div class="row g-2">
                <div class="col-6">
                    {{-- تم التعديل هنا: استخدام اسم الروت الصحيح wallet.deposit.view --}}
                    <a href="{{ route('wallet.deposit') }}" class="btn btn-action-charge w-100 py-3 rounded-4 shadow-sm">
                        <i class="fas fa-plus-circle d-block mb-1"></i> شحن الرصيد
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('withdraw.create') }}" class="btn btn-action-withdraw w-100 py-3 rounded-4 shadow-sm">
                        <i class="fas fa-arrow-down d-block mb-1"></i> سحب الأرباح
                    </a>
                </div>
            </div>
        </div>

        {{-- الجانب الأيسر: سجل العمليات --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-2 mt-lg-0">
                <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0 text-dark">سجل المعاملات المالية</h6>
                    <button class="btn btn-light btn-sm rounded-pill px-3 border"><i class="fas fa-file-export me-1"></i> تصدير</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">نوع العملية</th>
                                    <th>التاريخ</th>
                                    <th>المبلغ</th>
                                    <th class="text-center">الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions ?? [] as $transaction)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="icon-circle bg-light text-primary">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </div>
                                                <div>
                                                    <span class="d-block fw-bold small">عملية مالية</span>
                                                    <span class="text-muted" style="font-size: 11px;">#{{ $transaction->id }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted small">{{ $transaction->created_at->format('Y-m-d') }}</td>
                                        <td class="fw-bold text-dark">{{ number_format($transaction->amount, 2) }} ج.م</td>
                                        <td class="text-center"><span class="badge bg-light-success text-success rounded-pill">مكتمل</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" width="80" class="opacity-25 mb-3 grey-scale">
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
    /* تصميم كارت الفيزا المطور */
    .visa-card {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border-radius: 20px;
        min-height: 230px;
        color: white;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .circle-1 {
        position: absolute;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.03);
        border-radius: 50%;
        top: -150px;
        right: -100px;
    }

    .circle-2 {
        position: absolute;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.02);
        border-radius: 50%;
        bottom: -100px;
        left: -50px;
    }

    /* شعار اسم المستخدم */
    .user-initial {
        width: 45px;
        height: 45px;
        background: linear-gradient(45deg, #fbbf24, #f59e0b);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 22px;
        color: #1e293b;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    /* شريحة الفيزا (الذهبية) */
    .chip {
        width: 40px;
        height: 30px;
        background: linear-gradient(135deg, #e5e7eb 0%, #9ca3af 100%);
        border-radius: 6px;
        padding: 4px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2px;
        opacity: 0.8;
    }
    .chip-line { border: 1px solid rgba(0,0,0,0.1); border-radius: 2px; }

    /* زراير التحكم */
    .btn-action-charge {
        background: #10b981;
        color: white;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-action-charge:hover {
        background: #059669;
        color: white;
        transform: translateY(-3px);
    }

    .btn-action-withdraw {
        background: #f8fafc;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    .btn-action-withdraw:hover {
        background: #f1f5f9;
        transform: translateY(-3px);
    }

    /* أيقونات الجدول */
    .icon-circle {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-light-success { background-color: #dcfce7; }
    .grey-scale { filter: grayscale(1); }
</style>
@endsection
