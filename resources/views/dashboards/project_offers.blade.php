@extends('layouts.master')

@section('content')
<div class="container py-5" dir="rtl">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">{{ $project->title }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark">العروض المقدمة ({{ $offers->count() }})</h2>
        </div>
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="fas fa-arrow-right me-1"></i> عودة
        </a>
    </div>

    <div class="row">
        <!-- تفاصيل المشروع المختصرة -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
                <h5 class="fw-bold mb-3">تفاصيل المشروع</h5>
                <p class="text-muted small mb-4">{{ Str::limit($project->description, 200) }}</p>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">الميزانية:</span>
                    <span class="fw-bold text-success">{{ number_format($project->price) }} ج.م</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">تاريخ النشر:</span>
                    <span class="fw-bold">{{ $project->created_at->format('Y-m-d') }}</span>
                </div>
                <hr>
                <div class="alert alert-info py-2 small border-0">
                    <i class="fas fa-info-circle me-1"></i> يمكنك اختيار عرض واحد فقط لبدء العمل.
                </div>
            </div>
        </div>

        <!-- قائمة العروض -->
        <div class="col-lg-8">
            @forelse($offers as $offer)
            <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden offer-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <!-- صورة المستقل -->
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($offer->user->name) }}&background=random"
                             class="rounded-circle shadow-sm" width="60" height="60">

                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $offer->user->name }}</h6>
                                    <div class="text-warning small mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <span class="text-muted ms-1">(4.5)</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <h5 class="fw-bold text-primary mb-0">{{ number_format($offer->bid_amount) }} ج.م</h5>
                                    <small class="text-muted">مدة التنفيذ: {{ $offer->delivery_time }} يوم</small>
                                </div>
                            </div>

                            <p class="text-dark mt-3 mb-4 offer-text">
                                {{ $offer->description }}
                            </p>

                            <div class="d-flex gap-2">
                                <button class="btn btn-success rounded-pill px-4 btn-sm fw-bold">
                                    <i class="fas fa-check me-1"></i> قبول العرض
                                </button>
                                <button class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-bold">
                                    <i class="fas fa-comments me-1"></i> تواصل معه
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <img src="https://illustrations.popsy.co/flat/web-design.svg" width="200" class="mb-3">
                <h5 class="text-muted">لا توجد عروض مقدمة حتى الآن.</h5>
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .offer-card { transition: transform 0.2s; border-right: 5px solid transparent !important; }
    .offer-card:hover { transform: translateY(-3px); border-right-color: #10b981 !important; }
    .offer-text { line-height: 1.7; font-size: 0.95rem; }
    .bg-soft-success { background: #ecfdf5; }
</style>
@endsection
