@extends('layouts.master')

@section('content')
<div class="container py-5">
    <div class="glass-card rounded-4 overflow-hidden border border-white border-opacity-10 animate__animated animate__fadeIn">
        <div class="p-4 bg-info bg-opacity-10 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-black text-white mb-0">سجل عمليات: <span class="text-info">{{ $user->name }}</span></h4>
                <small class="text-muted small">كشف حساب مالي تفصيلي</small>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-info rounded-pill px-4">رجوع للوحة التحكم</a>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="px-4 py-3">المعرف</th>
                        <th>النوع</th>
                        <th>المبلغ</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                    <tr class="border-bottom border-white border-opacity-5">
                        <td class="px-4 fw-bold text-info">#TRX-{{ $trx->id }}</td>
                        <td>
                            <span class="badge {{ $trx->type == 'deposit' ? 'bg-success' : 'bg-danger' }} bg-opacity-25 text-{{ $trx->type == 'deposit' ? 'success' : 'danger' }}">
                                {{ $trx->type == 'deposit' ? 'إيداع' : 'سحب' }}
                            </span>
                        </td>
                        <td class="fw-black text-white">{{ number_format($trx->amount) }} ج.م</td>
                        <td><span class="text-muted small">مكتملة</span></td>
                        <td class="text-muted small">{{ $trx->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">لا توجد حركات مالية مسجلة لهذا العميل.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-top border-white border-opacity-5">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
