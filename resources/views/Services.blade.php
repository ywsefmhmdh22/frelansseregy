@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

{{-- القسم الرئيسي --}}
<section class="services-section py-5 position-relative overflow-hidden" style="background: #f8fafc; min-height: 100vh;">

    <div class="bright-bg-glow"></div>

    <div class="container position-relative" style="z-index: 2;">

        {{-- رسائل النجاح --}}
        @if(session('success'))
            <div class="alert alert-bright-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 text-end animate__animated animate__flipInX" role="alert">
                <div class="d-flex align-items-center justify-content-end">
                    <strong class="text-dark">{{ session('success') }}</strong>
                    <i class="fas fa-check-circle ms-2 text-success"></i>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- العنوان --}}
        <div class="text-center mb-5 pb-3 animate__animated animate__fadeInDown">
            <h2 class="fw-black text-dark display-5 position-relative d-inline-block pb-3" style="letter-spacing: -1px;">
                نخبة <span class="text-primary-gradient">الخدمات</span> الإبداعية
                <span class="bright-header-line"></span>
            </h2>
            <p class="text-muted mt-3 fs-5 mx-auto" style="max-width: 600px; font-weight: 400;">استكشف عالمًا من الاحترافية، حيث يلتقي الإبداع بالتميز لإنجاز رؤيتك</p>
        </div>

        {{-- أزرار الفلترة --}}
        <div class="services-filter-tabs mb-5 animate__animated animate__fadeInUp">
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <button class="filter-tab active" data-filter="all">
                    <i class="fas fa-th-large me-2"></i> الكل
                </button>
                <button class="filter-tab" data-filter="normal">
                    <i class="fas fa-project-diagram me-2"></i> خدمات مشاريع
                </button>
                <button class="filter-tab" data-filter="ready">
                    <i class="fas fa-bolt me-2"></i> خدمات جاهزة
                </button>
            </div>
        </div>

        <div class="row g-4 justify-content-center" id="services-grid">
            @forelse($allData as $service)
                @php
                    $isReady = ($service->type === 'ready');
                @endphp

                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 service-card-wrapper animate__animated animate__fadeInUp"
                     data-category="{{ $service->type }}">

                    <div class="bright-service-card h-100 border-0 shadow-sm rounded-5 overflow-hidden d-flex flex-column position-relative bg-white">

                        {{-- حاوية الصورة --}}
                        <div class="card-image-wrapper position-relative overflow-hidden">
                            {{-- عرض صورة الخدمة من S3 --}}
                            <img src="{{ Storage::disk('s3')->url($service->image) }}"
                                 alt="{{ $service->title }}"
                                 class="w-100 h-100 object-fit-cover transition-transform main-service-img">

                            {{-- شارة نوع الخدمة --}}
                            <div class="type-badge position-absolute top-0 start-0 m-3 shadow-sm {{ $isReady ? 'bg-ready' : 'bg-public' }}">
                                <i class="fas {{ $isReady ? 'fa-bolt' : 'fa-briefcase' }} me-1"></i>
                                {{ $isReady ? 'خدمة جاهزة' : 'مشروع عادي' }}
                            </div>

                            {{-- السعر بالدولار --}}
                            <div class="bright-price-badge position-absolute bottom-0 end-0 m-3 shadow-lg">
                                <small>$</small>
                                <span class="fw-black">{{ number_format($service->price, 0) }}</span>
                            </div>
                        </div>

                        {{-- تفاصيل الخدمة --}}
                        <div class="card-body p-4 text-end d-flex flex-column" dir="rtl">
                            <div class="user-info-top d-flex align-items-center mb-3 pb-2 border-bottom border-light">
                                {{-- صورة صاحب الخدمة --}}
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($service->user->name) }}&color=7F9CF5&background=EBF4FF"
                                     class="rounded-circle border border-2 border-white shadow-sm"
                                     style="width: 35px; height: 35px; object-fit: cover;"
                                     alt="user">

                                <div class="me-2 d-flex flex-column text-end">
                                    <span class="fw-bold text-dark small">{{ $service->user->name }}</span>
                                    <span class="text-primary small" style="font-size: 0.7rem;">مستقل موثق <i class="fas fa-check-circle"></i></span>
                                </div>
                            </div>

                            <h5 class="fw-bold mb-2 text-dark service-title">{{ $service->title }}</h5>

                            <p class="text-muted small mb-4 line-clamp-2 flex-grow-1" style="font-weight: 400;">
                                {{ Str::limit($service->description, 90) }}
                            </p>

                            <div class="d-grid pt-2">
                                <a href="{{ route('services.checkout', $service->id) }}"
                                   class="btn btn-bright-action rounded-pill fw-bold py-2 transition-all">
                                   تفاصيل الخدمة
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="empty-state-container animate__animated animate__zoomIn">
                        <i class="fas fa-search fa-3x text-muted opacity-25 mb-3"></i>
                        <h4 class="text-dark fw-bold">لا توجد خدمات حالياً</h4>
                        <p class="text-muted">جرب تغيير الفلتر أو العودة لاحقاً</p>
                    </div>
                </div>
            @endforelse

            <div id="no-filter-results" class="col-12 text-center py-5 d-none animate__animated animate__fadeIn">
                <i class="fas fa-filter fa-3x text-muted opacity-25 mb-3"></i>
                <h4 class="text-dark fw-bold">لا توجد نتائج لهذا التصنيف</h4>
                <p class="text-muted">اختر تصنيفاً آخر لاستكشاف الخدمات</p>
            </div>
        </div>
    </div>
</section>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap');

    :root {
        --primary-main: #0ea5e9;
        --secondary-main: #6366f1;
        --bg-light: #f8fafc;
        --ready-color: #f59e0b;
        --public-color: #10b981;
    }

    body { font-family: 'Cairo', sans-serif; background-color: var(--bg-light); color: #334155; }
    .fw-black { font-weight: 900; }
    .text-primary-gradient {
        background: linear-gradient(90deg, var(--primary-main), var(--secondary-main));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .services-filter-tabs .filter-tab {
        background: white;
        border: 2px solid transparent;
        color: #64748b;
        padding: 10px 28px;
        border-radius: 50px;
        transition: all 0.3s ease;
        font-weight: 700;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        cursor: pointer;
    }

    .services-filter-tabs .filter-tab:hover { transform: translateY(-2px); color: var(--primary-main); }
    .services-filter-tabs .filter-tab.active { background: var(--primary-main); color: white; box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.4); }

    .type-badge { font-size: 0.7rem; padding: 5px 12px; border-radius: 50px; color: white; font-weight: 700; z-index: 10; }
    .bg-ready { background: var(--ready-color); }
    .bg-public { background: var(--public-color); }

    .bright-service-card { transition: all 0.4s ease; border: 1px solid #e2e8f0 !important; }
    .bright-service-card:hover { transform: translateY(-10px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05) !important; border-color: var(--primary-main) !important; }

    .card-image-wrapper { height: 200px; position: relative; }
    .main-service-img { transition: transform 0.8s ease; }
    .bright-service-card:hover .main-service-img { transform: scale(1.08); }

    .bright-price-badge { background: white; color: var(--primary-main); padding: 8px 15px; border-radius: 15px; border-right: 4px solid var(--primary-main); z-index: 10; }
    .btn-bright-action { background: #f1f5f9; color: #475569; border: none; }
    .bright-service-card:hover .btn-bright-action { background: var(--primary-main); color: white; }

    .bright-bg-glow { position: absolute; top: -10%; left: -10%; width: 40%; height: 40%; background: radial-gradient(circle, rgba(14, 165, 233, 0.1) 0%, rgba(248, 250, 252, 0) 70%); }
    .bright-header-line { position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 60px; height: 4px; background: var(--primary-main); border-radius: 10px; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.filter-tab');
        const serviceItems = document.querySelectorAll('.service-card-wrapper');
        const noResults = document.getElementById('no-filter-results');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const filterValue = button.getAttribute('data-filter');
                let foundAny = false;

                serviceItems.forEach(item => {
                    const itemCategory = item.getAttribute('data-category');

                    if (filterValue === 'all' || filterValue === itemCategory) {
                        item.style.display = 'block';
                        foundAny = true;
                        item.classList.add('animate__fadeInUp');
                    } else {
                        item.style.display = 'none';
                        item.classList.remove('animate__fadeInUp');
                    }
                });

                if (!foundAny) {
                    noResults.classList.remove('d-none');
                } else {
                    noResults.classList.add('d-none');
                }
            });
        });
    });
</script>

@endsection
