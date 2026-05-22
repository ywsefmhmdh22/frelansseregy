@extends('layouts.master')

@section('content')
<div class="container py-4">
    <div class="card bg-dark text-white rounded-4 shadow-lg border-0">
        <div class="card-header bg-transparent border-secondary p-4">
            <h4 class="fw-bold mb-0"><i class="fas fa-gavel text-warning me-2"></i> محكمة التحكيم والنزاعات</h4>
            <p class="text-muted small mb-0">هنا يتم الفصل بين العميل والمستقل لتحقيق العدالة المطلقة.</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-black">
                        <tr>
                            <th class="px-4">كود النزاع</th>
                            <th>المشروع</th>
                            <th>الأطراف</th>
                            <th>الميزانية</th>
                            <th>الحالة</th>
                            <th class="text-center">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disputes as $dispute)
                        <tr>
                            <td class="px-4 fw-bold text-info">#DS-{{ $dispute->id }}</td>
                            <td>{{ $dispute->title }}</td>
                            <td>
                                {{-- تم إضافة المعامل ?-> لتجنب خطأ محاولة القراءة من null --}}
                                <div class="small">العميل: {{ $dispute->user?->name ?? 'مستخدم محذوف' }}</div>
                                <div class="small text-muted">المستقل: {{ $dispute->freelancer?->name ?? 'مستقل محذوف' }}</div>
                            </td>
                            <td class="text-success fw-bold">{{ number_format($dispute->price) }} ج.م</td>
                            <td><span class="badge bg-danger animate-pulse">قيد المراجعة</span></td>
                            <td class="text-center">
                                <a href="{{ route('admin.disputes.show', $dispute->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">فتح التحقيق</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">لا توجد نزاعات مفتوحة حالياً. المنصة في أمان!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
