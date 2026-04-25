@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<div class="works-portfolio-wrapper" dir="rtl">
    <div class="container py-5">
        {{-- Header Section --}}
        <div class="text-center mb-5 animate__animated animate__fadeInDown">
            <h1 class="display-4 fw-800 text-dark mb-3">إبداعات <span class="gradient-text">خبير</span></h1>
            <p class="lead text-muted mx-auto mb-4" style="max-width: 650px;">
                استكشف نخبة المشاريع التي نالت استحسان العملاء بتقييمات استثنائية.
            </p>
            <div class="header-line-custom mx-auto"></div>
        </div>

        {{-- Projects Grid --}}
        <div class="row g-4 justify-content-center">
            @forelse($works as $index => $work)
                {{-- جلب التقييم من علاقة التقييمات، وإذا لم يوجد نفترض 5 نجوم للجمالية --}}
                @php
                    $rating = $work->reviews_avg_rating ?? ($work->order?->review?->rating ?? 5.0);
                @endphp

                @if($rating >= 4)
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 animate__animated animate__zoomIn"
                     style="animation-delay: {{ $index * 0.1 }}s">
                    <div class="work-card-premium h-100">
                        <div class="card-inner-content">
                            {{-- Image Section --}}
                            <div class="project-media">
                                {{-- استخدام المسار المخزن في الداتابيز projects/covers/... --}}
                                <img src="{{ $work->cover_image ? asset('storage/' . $work->cover_image) : 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=500' }}"
                                     class="project-img"
                                     alt="{{ $work->title }}"
                                     loading="lazy"
                                     onerror="this.src='https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=500'">

                                <div class="media-overlay">
                                    <a href="{{ route('projects.show', $work->id) }}" class="btn-explore">
                                        <span>عرض التفاصيل</span>
                                        <i class="fas fa-eye ms-2"></i>
                                    </a>
                                </div>

                                {{-- التقييم الحقيقي --}}
                                <div class="rating-float-badge">
                                    <i class="fas fa-star text-warning me-1"></i>
                                    <span class="fw-bold">{{ number_format($rating, 1) }}</span>
                                </div>
                            </div>

                            {{-- Card Info --}}
                            <div class="content-info p-4">
                                <div class="freelancer-strip d-flex align-items-center mb-3">
                                    @php
                                        $fName = $work->freelancer?->name ?? 'خبير مستقل';
                                        $fAvatar = $work->freelancer?->profile_image ? asset('storage/' . $work->freelancer->profile_image) : "https://ui-avatars.com/api/?name=".urlencode($fName)."&background=00d2ff&color=fff";
                                    @endphp
                                    <div class="avatar-ring">
                                        <img src="{{ $fAvatar }}" class="f-avatar" alt="freelancer">
                                    </div>
                                    <div class="ms-3 me-2 text-start">
                                        <h6 class="m-0 fw-bold text-dark small-text">{{ $fName }}</h6>
                                        <span class="text-muted tiny-text">بواسطة: {{ $work->client?->name ?? 'عميل مميز' }}</span>
                                    </div>
                                </div>

                                <h5 class="project-title mb-3 text-start text-truncate-2">{{ $work->title }}</h5>

                                <div class="footer-meta d-flex justify-content-between align-items-center pt-3 border-top">
                                    <span class="status-pill">
                                        <i class="fas fa-check-double me-1"></i> تم التسليم
                                    </span>
                                    <div class="project-date tiny-text text-muted">
                                        <i class="far fa-calendar-alt me-1"></i> {{ $work->created_at?->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @empty
                <div class="col-12 text-center py-5 animate__animated animate__fadeIn">
                    <div class="empty-state-card p-5 shadow-sm rounded-4">
                        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">لا توجد أعمال مكتملة حالياً</h4>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);
        --glass-bg: rgba(255, 255, 255, 0.98);
        --soft-shadow: 0 15px 35px rgba(0,0,0,0.07);
    }

    /* تحسين الخطوط والتنسيق العام */
    .fw-800 { font-weight: 800; }
    .gradient-text {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-line-custom {
        height: 4px; width: 60px;
        background: var(--primary-gradient);
        border-radius: 10px;
    }

    /* الكارت وتأثير الظهور المفتوح */
    .work-card-premium {
        background: var(--glass-bg);
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: var(--soft-shadow);
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    /* تأثير تفتيح/فتح الكارت عند التحويم */
    .work-card-premium:hover {
        transform: scale(1.03) translateY(-10px);
        box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        z-index: 10;
    }

    .project-media {
        position: relative;
        height: 240px;
        overflow: hidden;
        background: #f8f9fa;
    }

    .project-img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 1.2s cubic-bezier(0.19, 1, 0.22, 1);
    }

    .work-card-premium:hover .project-img {
        transform: scale(1.15);
    }

    /* طبقة العرض فوق الصورة */
    .media-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(58, 123, 213, 0.9), transparent);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: 0.4s all ease;
    }

    .work-card-premium:hover .media-overlay {
        opacity: 1;
    }

    .btn-explore {
        background: #fff;
        color: #3a7bd5;
        padding: 12px 24px;
        border-radius: 50px;
        text-decoration: none !important;
        font-weight: 700;
        transform: translateY(20px);
        transition: 0.5s all ease;
    }

    .work-card-premium:hover .btn-explore {
        transform: translateY(0);
    }

    /* التقييم العائم */
    .rating-float-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.95);
        padding: 6px 14px;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        z-index: 5;
        border: 1px solid #ffc107;
        backdrop-filter: blur(5px);
    }

    /* معلومات المستقل */
    .avatar-ring {
        width: 45px; height: 45px;
        border-radius: 50%;
        padding: 2px;
        background: var(--primary-gradient);
    }

    .f-avatar {
        width: 100%; height: 100%;
        border-radius: 50%;
        border: 2px solid white;
    }

    .project-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.6;
        height: 3.2rem;
        overflow: hidden;
    }

    .status-pill {
        background: #eefdf5;
        color: #16a34a;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    /* متجاوب مع الموبايل */
    @media (max-width: 768px) {
        .project-media { height: 200px; }
        .display-4 { font-size: 2.2rem; }
    }

    /* نص مختصر لسطرين */
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
