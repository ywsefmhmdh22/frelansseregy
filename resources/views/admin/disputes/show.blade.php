@extends('layouts.master')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-dark text-white rounded-4 shadow-lg border-0">
                <div class="card-header bg-transparent border-secondary p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0"><i class="fas fa-search-plus text-primary me-2"></i> تفاصيل التحقيق في النزاع</h4>
                        <p class="text-muted small mb-0">كود النزاع: #DS-{{ $dispute->id }}</p>
                    </div>
                    <a href="{{ route('admin.disputes.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">عودة للمحكمة</a>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-info border-bottom border-secondary pb-2 mb-3">موضوع النزاع</h5>
                            <h3>{{ $dispute->title }}</h3>
                            <p class="text-light opacity-75">{{ $dispute->description }}</p>

                            <div class="mt-4 p-3 bg-black rounded-3">
                                <h6 class="text-warning">الميزانية المتنازع عليها:</h6>
                                <span class="h4 fw-bold text-success">{{ number_format($dispute->price) }} ج.م</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <h5 class="text-info border-bottom border-secondary pb-2 mb-3">أطراف النزاع</h5>
                            <div class="mb-3">
                                <label class="text-muted small d-block">صاحب المشروع (العميل):</label>
                                <span class="fw-bold">{{ $dispute->user->name }}</span>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small d-block">المستقل:</label>
                                <span class="fw-bold text-primary">{{ $dispute->freelancer->name }}</span>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary my-4">

                    <div class="text-center py-3">
                        <h5 class="mb-3">اتخاذ قرار نهائي</h5>
                        <div class="d-flex justify-content-center gap-3">

                            {{-- زر تحويل المبلغ للمستقل --}}
                            <form action="{{ route('admin.disputes.release', $dispute->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success px-4 rounded-pill" onclick="return confirm('هل أنت متأكد من تحويل المبلغ للمستقل وإغلاق النزاع؟')">تحويل المبلغ للمستقل</button>
                            </form>

                            {{-- زر إعادة المبلغ للعميل --}}
                            <form action="{{ route('admin.disputes.refund', $dispute->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger px-4 rounded-pill" onclick="return confirm('هل أنت متأكد من إعادة المبلغ للعميل وإلغاء المشروع؟')">إعادة المبلغ للعميل</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
