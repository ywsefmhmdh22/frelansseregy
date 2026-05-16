@extends('layouts.master')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --brand-primary: #0f172a;
        --brand-accent: #10b981;
        --brand-blue: #3b82f6;
    }

    body {
        background-color: #f8fafc;
        font-family: 'Cairo', sans-serif;
    }

    /* تحسينات التجاوب الذكية */
    .checkout-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: clamp(1rem, 3vw, 3rem);
    }

    .custom-card {
        border: none;
        border-radius: 1.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        background: #ffffff;
        height: 100%;
    }

    .price-box {
        background: #f1f5f9;
        border-radius: 1rem;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
    }

    .currency-convert {
        background: rgba(16, 185, 129, 0.1);
        border-right: 4px solid var(--brand-accent);
        border-radius: 10px;
        padding: 1rem;
    }

    .btn-buy {
        background: var(--brand-primary);
        color: white;
        border: none;
        padding: 1rem;
        border-radius: 50px;
        font-weight: 700;
        width: 100%;
        transition: all 0.3s ease;
    }

    .btn-buy:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2);
        color: white;
    }

    .service-img {
        border-radius: 1rem;
        object-fit: cover;
        width: 100%;
        height: 200px;
    }

    /* ترتيب العناصر في الموبايل */
    @media (max-width: 768px) {
        .order-mobile-1 { order: 1; } /* ملخص الفاتورة يظهر أولاً */
        .order-mobile-2 { order: 2; } /* تفاصيل الخدمة تظهر ثانياً */
    }
</style>

<div class="checkout-container animate__animated animate__fadeIn">
    <h2 class="fw-900 text-dark mb-4 text-end" dir="rtl">
        <i class="fas fa-shield-check text-success me-2"></i>
        {{ $service->type === 'ready' ? 'شراء وتحميل فوري' : 'تأكيد عملية الشراء' }}
    </h2>

    {{-- رسالة النجاح لخدمات التحميل الفوري --}}
    @if(session('success') && session('ready_file_path'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 p-4 mb-4 text-end" dir="rtl">
            <div class="d-flex align-items-center flex-wrap">
                <div class="flex-grow-1">
                    <h4 class="fw-bold mb-2">تمت العملية بنجاح!</h4>
                    <p class="mb-3">ملفك جاهز للتحميل الآن:</p>
                    <a href="{{ Storage::disk('s3')->url(session('ready_file_path')) }}"
                       class="btn btn-success btn-lg rounded-pill px-5 fw-bold" download>
                        <i class="fas fa-download me-2"></i> تحميل الملف
                    </a>
                </div>
                <i class="fas fa-check-circle fa-4x text-success opacity-25 ms-auto"></i>
            </div>
        </div>
    @endif

    <div class="row g-4" dir="rtl">

        {{-- ملخص الفاتورة والدفع (الجهة اليسرى في الديسك توب / الأعلى في الموبايل) --}}
        <div class="col-lg-4 col-md-5 order-mobile-1">
            <div class="card custom-card p-4">
                <h5 class="fw-bold mb-4 border-bottom pb-2">ملخص الفاتورة</h5>

                <div class="price-box mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">السعر بالجنيه:</span>
                        <span class="fw-bold text-dark">{{ number_format($service->price, 2) }} ج.م</span>
                    </div>

                    <div class="currency-convert mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-dark small fw-bold">الخصم من المحفظة:</span>
                            <span class="fs-4 fw-900 text-success">{{ number_format($priceInUsd, 2) }} $</span>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                            <i class="fas fa-info-circle me-1"></i> سعر الصرف: 1$ = {{ $rate }} ج.م
                        </small>
                    </div>
                </div>

                <div class="mb-4 text-end">
                    <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block">رصيدك الحالي</small>
                            <span class="fw-bold {{ Auth::user()->wallet->balance >= $priceInUsd ? 'text-success' : 'text-danger' }}">
                                {{ number_format(Auth::user()->wallet->balance, 2) }} $
                            </span>
                        </div>
                        <i class="fas fa-wallet text-secondary opacity-50 fs-4"></i>
                    </div>
                </div>

                @if(!session('ready_file_path'))
                    <form action="{{ route('service.pay.wallet', $service->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-buy mb-3" {{ Auth::user()->wallet->balance < $priceInUsd ? 'disabled' : '' }}>
                            <i class="fas fa-lock me-2"></i> تأكيد الدفع والطلب
                        </button>
                    </form>
                @endif

                @if(Auth::user()->wallet->balance < $priceInUsd)
                    <div class="alert alert-soft-danger border-0 small text-center p-2 mb-0">
                        رصيدك غير كافٍ، يرجى <a href="{{ route('wallet.deposit') }}" class="fw-bold text-danger">شحن المحفظة</a>
                    </div>
                @endif

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0"><i class="fas fa-user-shield text-primary"></i> نظام دفع مشفر وآمن</p>
                </div>
            </div>
        </div>

        {{-- تفاصيل الخدمة (الجهة اليمنى) --}}
        <div class="col-lg-8 col-md-7 order-mobile-2">
            <div class="card custom-card overflow-hidden">
                <div class="row g-0">
                    <div class="col-xl-4 col-lg-5">
                        <img src="{{ $service->image ? Storage::disk('s3')->url($service->image) : asset('images/default-service.jpg') }}"
                             class="service-img h-100" alt="{{ $service->title }}">
                    </div>
                    <div class="col-xl-8 col-lg-7">
                        <div class="card-body p-4 text-end">
                            <div class="mb-2">
                                @if($service->type === 'ready')
                                    <span class="badge bg-success rounded-pill px-3">تسليم فوري</span>
                                @else
                                    <span class="badge bg-soft-primary text-primary rounded-pill px-3" style="background: rgba(59,130,246,0.1)">خدمة مخصصة</span>
                                @endif
                            </div>
                            <h4 class="fw-900 text-dark mb-3">{{ $service->title }}</h4>
                            <p class="text-muted small mb-4 lh-lg">{{ Str::limit($service->description, 200) }}</p>

                            <div class="d-inline-flex align-items-center p-2 bg-light rounded-pill px-3 border">
                                <div class="text-end me-3">
                                    <p class="mb-0 fw-bold small text-dark">{{ $service->user->name }}</p>
                                    <small class="text-muted" style="font-size: 0.65rem;">بائع موثوق لدى Worklyday</small>
                                </div>
                                <img src="{{ $service->user->profile_image ? Storage::disk('s3')->url($service->user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($service->user->name) }}"
                                     class="rounded-circle" width="35" height="35" style="object-fit: cover;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 text-end">
                <h6 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary ms-2"></i> شروط الخدمة:</h6>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-check-circle text-success ms-2"></i>
                            <span>حماية كاملة لأموالك عبر نظام الوساطة.</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-check-circle text-success ms-2"></i>
                            <span>دعم فني متخصص لحل أي نزاعات.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
