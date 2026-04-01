@extends('layouts.master')

@section('content')
<div class="container py-4">
    <div class="card bg-black text-white rounded-4 shadow-lg border-0 border-top border-success border-4">
        <div class="card-header bg-transparent p-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-0"><i class="fas fa-microchip text-success me-2"></i> الرادار المالي (Live)</h4>
                <small class="text-muted">مراقبة كافة عمليات السحب والإيداع بالثانية.</small>
            </div>
            <button class="btn btn-outline-success btn-sm rounded-pill"><i class="fas fa-download me-1"></i> تحميل كشف شامل</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-striped mb-0">
                    <thead>
                        <tr>
                            <th class="px-4">التوقيت (بالثانية)</th>
                            <th>المستخدم</th>
                            <th>النوع</th>
                            <th>المبلغ</th>
                            <th>الوسيلة</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allTransactions as $trx)
                        <tr>
                            <td class="px-4 text-info small">{{ $trx->created_at->format('Y-m-d H:i:s.u') }}</td>
                            <td><span class="fw-bold">{{ $trx->user->name }}</span></td>
                            <td>
                                @if($trx->type == 'deposit')
                                    <span class="text-success"><i class="fas fa-arrow-down me-1"></i> إيداع</span>
                                @else
                                    <span class="text-danger"><i class="fas fa-arrow-up me-1"></i> سحب</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ number_format($trx->amount, 2) }} ج.م</td>
                            <td><small>{{ $trx->payment_method ?? 'محفظة' }}</small></td>
                            <td><span class="badge bg-success small">مكتملة</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">الخزنة فارغة من العمليات اليوم.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $allTransactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
