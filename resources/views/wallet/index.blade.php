@extends('layouts.master')

@section('content')
<div class="container py-5" dir="rtl">
    <div class="row g-4">
        {{-- الجانب الأيمن: كارت "ماهر" البلاتيني --}}
        <div class="col-lg-4">
            <div class="maheer-visa-card shadow-lg mb-4 position-relative overflow-hidden">
                {{-- تأثيرات بصرية فريحية --}}
                <div class="vibrant-bg"></div>
                <div class="glass-reflection"></div>

                <div class="card-content position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-5">
                        <div class="bank-identity">
                            <div class="premium-initial">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="platform-label">MAHEER PLATINUM</span>
                        </div>
                        <div class="visa-brand">
                            <i class="fab fa-cc-visa fa-3x text-white opacity-75"></i>
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="balance-title">الرصيد المتاح</span>
                        <h1 class="balance-display">
                            <span class="currency">$</span>{{ number_format(Auth::user()->wallet->balance, 2) }}
                        </h1>
                    </div>

                    <div class="d-flex justify-content-between align-items-end">
                        <div class="user-info">
                            <span class="label-txt">CARD HOLDER</span>
                            <span class="name-txt">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="gold-chip">
                            <div class="chip-line-v"></div>
                            <div class="chip-line-v"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- أزرار الأكشن بتصميم زجاجي مشرق --}}
            <div class="row g-3">
                <div class="col-6">
                    <a href="{{ route('wallet.deposit') }}" class="btn action-btn-glass charge">
                        <div class="icon-box"><i class="fas fa-wallet"></i></div>
                        <span>شحن الرصيد</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('withdraw.create') }}" class="btn action-btn-glass withdraw">
                        <div class="icon-box"><i class="fas fa-paper-plane"></i></div>
                        <span>سحب الأرباح</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- الجانب الأيسر: الجداول بتصميم عصري وتباين مريح --}}
        <div class="col-lg-8">
            {{-- 1. سجل طلبات السحب --}}
            <div class="vibrant-table-card mb-4">
                <div class="card-header-vibrant bg-primary-light">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-history text-primary"></i>
                        <h6 class="fw-bold m-0 text-primary">طلبات السحب النشطة</h6>
                    </div>
                    <span class="badge-vibrant pending">تحت المراجعة</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-vibrant align-middle">
                        <thead>
                            <tr>
                                <th>الوسيلة</th>
                                <th>المبلغ</th>
                                <th>التاريخ</th>
                                <th class="text-center">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(Auth::user()->withdrawRequests()->latest()->take(5)->get() as $request)
                                <tr>
                                    <td>
                                        <div class="method-badge">
                                            <i class="fas fa-university"></i>
                                            <span>{{ ucfirst(str_replace('_', ' ', $request->method)) }}</span>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-danger">-${{ number_format($request->amount, 2) }}</td>
                                    <td class="text-muted small">{{ $request->created_at->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                        @php
                                            $statusClass = $request->status == 'pending' ? 'pending' : ($request->status == 'approved' ? 'success' : 'danger');
                                            $statusText = $request->status == 'pending' ? 'انتظار' : ($request->status == 'approved' ? 'تم القبول' : 'مرفوض');
                                        @endphp
                                        <span class="badge-vibrant {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted small">لا توجد طلبات سحب حالية.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. سجل المعاملات المالية --}}
            <div class="vibrant-table-card">
                <div class="card-header-vibrant bg-success-light">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-list-ul text-success"></i>
                        <h6 class="fw-bold m-0 text-success">سجل المعاملات المالية</h6>
                    </div>
                    <button class="btn-export-vibrant"><i class="fas fa-download me-1"></i> Excel</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-vibrant align-middle">
                        <thead>
                            <tr>
                                <th>نوع العملية</th>
                                <th>التاريخ</th>
                                <th>المبلغ</th>
                                <th class="text-center">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions ?? [] as $transaction)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="trans-icon-circle shadow-sm">
                                                <i class="fas fa-exchange-alt"></i>
                                            </div>
                                            <div>
                                                <span class="d-block fw-bold text-dark" style="font-size: 13px;">عملية مالية</span>
                                                <span class="text-muted" style="font-size: 10px;">ID: #{{ $transaction->id }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted small">{{ $transaction->created_at->format('Y-m-d') }}</td>
                                    <td class="fw-bold text-dark">${{ number_format($transaction->amount, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge-vibrant {{ $transaction->status_color }}">
                                            {{ $transaction->status_arabic }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <img src="https://cdn-icons-png.flaticon.com/512/11545/11545300.png" width="60" class="opacity-50 mb-2">
                                        <p class="text-muted mb-0">سجل المعاملات فارغ حالياً.</p>
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

<style>
    /* الإعدادات العامة والألوان الفريحية */
    :root {
        --primary-vibrant: #4f46e5;
        --success-vibrant: #10b981;
        --card-grad: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --glass-white: rgba(255, 255, 255, 0.95);
    }

    /* كارت الفيزا الأسطوري */
    .maheer-visa-card {
        background: var(--card-grad);
        border-radius: 30px;
        min-height: 250px;
        padding: 30px;
        color: white;
        border: 4px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 25px 50px -12px rgba(99, 102, 241, 0.4);
    }

    .vibrant-bg {
        position: absolute;
        top: -20%; right: -10%; width: 250px; height: 250px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        border-radius: 50%;
    }

    .glass-reflection {
        position: absolute;
        top: 0; left: -100%; width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: skewX(-25deg);
        animation: slideShine 4s infinite;
    }

    @keyframes slideShine { 0% { left: -100%; } 100% { left: 150%; } }

    .premium-initial {
        width: 55px; height: 55px;
        background: #fff; border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        color: var(--primary-vibrant); font-weight: 900; font-size: 26px;
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }

    .platform-label { font-size: 11px; font-weight: 700; letter-spacing: 2px; margin-right: 12px; opacity: 0.9; }

    .balance-title { display: block; font-size: 13px; opacity: 0.8; margin-bottom: 5px; }
    .balance-display { font-size: 44px; font-weight: 800; letter-spacing: -1px; margin: 0; text-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .currency { font-size: 24px; color: #fbbf24; margin-left: 8px; vertical-align: top; }

    .label-txt { display: block; font-size: 9px; opacity: 0.6; letter-spacing: 2px; }
    .name-txt { font-weight: 700; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }

    .gold-chip {
        width: 50px; height: 38px; background: linear-gradient(135deg, #ffd700, #f59e0b);
        border-radius: 10px; display: flex; align-items: center; justify-content: space-around; padding: 5px;
    }
    .chip-line-v { width: 1px; height: 100%; background: rgba(0,0,0,0.1); }

    /* الأزرار الفريحية */
    .action-btn-glass {
        padding: 15px; border-radius: 22px; border: none;
        display: flex; flex-direction: column; align-items: center; gap: 8px;
        font-weight: 700; font-size: 14px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #fff; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }
    .action-btn-glass.charge { color: var(--primary-vibrant); }
    .action-btn-glass.withdraw { color: var(--success-vibrant); }

    .icon-box { width: 45px; height: 45px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: 0.3s; }
    .charge .icon-box { background: #eef2ff; }
    .withdraw .icon-box { background: #ecfdf5; }

    .action-btn-glass:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }

    /* الجداول المودرن */
    .vibrant-table-card {
        background: #fff; border-radius: 28px; border: 1px solid #f1f5f9;
        overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .card-header-vibrant {
        padding: 22px 28px; display: flex; justify-content: space-between; align-items: center; border: none;
    }
    .bg-primary-light { background-color: #f5f3ff; }
    .bg-success-light { background-color: #f0fdf4; }

    .table-vibrant thead th {
        background: #fafafa; padding: 18px 25px; border: none;
        color: #475569; font-size: 13px; font-weight: 700;
    }
    .table-vibrant tbody td { padding: 22px 25px; border-bottom: 1px solid #f8fafc; }

    .badge-vibrant {
        padding: 8px 18px; border-radius: 14px; font-size: 12px; font-weight: 700; display: inline-block;
    }
    .badge-vibrant.pending, .badge-vibrant.warning { background: #fffbeb; color: #b45309; }
    .badge-vibrant.success { background: #dcfce7; color: #15803d; }
    .badge-vibrant.danger { background: #fee2e2; color: #b91c1c; }

    .method-badge { display: flex; align-items: center; gap: 10px; font-weight: 700; color: #1e293b; }
    .method-badge i { color: #64748b; }

    .trans-icon-circle {
        width: 42px; height: 42px; border-radius: 14px; background: #fff;
        display: flex; align-items: center; justify-content: center; color: var(--primary-vibrant);
        border: 1px solid #f1f5f9;
    }

    .btn-export-vibrant {
        background: #fff; border: 1px solid #e2e8f0; padding: 6px 14px;
        border-radius: 12px; font-size: 12px; font-weight: 600; color: #64748b;
    }
</style>
@endsection
