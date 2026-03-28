 @extends('layouts.master')

@section('content')
<div class="container py-5 text-end" dir="rtl">
    {{-- Header Section --}}
    <div class="text-center mb-5 position-relative">
        <h1 class="display-4 fw-800 text-dark mb-3">إبداعات <span class="text-success">خبير</span></h1>
        <p class="lead text-muted mx-auto" style="max-width: 600px;">هنا حيث تتحول الأفكار إلى واقع ملموس.. استعرض أفضل ما قدمه مستقلونا بتقييمات استثنائية.</p>
        <div class="header-line mx-auto"></div>
    </div>

    {{-- Advanced Filter --}}
    <div class="d-flex flex-wrap gap-3 mb-5 justify-content-center">
        <button class="filter-btn active">الكل</button>
        <button class="filter-btn">برمجة وتطوير</button>
        <button class="filter-btn">تصميم وإبداع</button>
        <button class="filter-btn">تسويق رقمي</button>
        <button class="filter-btn">كتابة وترجمة</button>
    </div>

    <div class="row g-4">
        @forelse($works as $work)
        <div class="col-lg-4 col-md-6">
            <div class="modern-work-card">
                <div class="card-inner">
                    {{-- Image Wrapper --}}
                    <div class="image-wrapper">
                        {{-- تم التعديل لعرض الصورة من عمود image_url مباشرة --}}
                        <img src="{{ $work->image_url }}"
                             class="img-fluid"
                             alt="{{ $work->title }}"
                             onerror="this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=500&auto=format&fit=crop'">

                        <div class="card-overlay">
                            <a href="{{ route('projects.show', $work->id) }}" class="view-project-btn">عرض المشروع <i class="fas fa-arrow-left ms-2"></i></a>
                        </div>
                        <div class="rating-badge">
                            <i class="fas fa-star text-warning"></i> {{ number_format($work->freelancer_rating ?? 4.9, 1) }}
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="content-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            {{-- استخدام ? لتجنب الخطأ في حالة عدم وجود freelancer --}}
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($work->freelancer?->name ?? 'User') }}&background=10b981&color=fff" class="author-avatar" alt="freelancer">
                            <div class="ms-3 me-2 text-start">
                                {{-- استخدام ? و الـ Null Coalesce لعرض اسم بديل آمن --}}
                                <h6 class="mb-0 fw-bold text-dark">{{ $work->freelancer?->name ?? 'مستقل متميز' }}</h6>
                                <small class="text-muted">لصالح: {{ $work->client?->name ?? 'مشروع خاص' }}</small>
                            </div>
                        </div>
                        <h5 class="work-title mb-0">{{ $work->title }}</h5>
                    </div>

                    {{-- Bottom Wave or Line --}}
                    <div class="card-footer-line"></div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="empty-state">
                <i class="fas fa-rocket fa-4x text-muted mb-4"></i>
                <h3 class="text-muted">المشاريع الكبرى في طريقها إليك!</h3>
            </div>
        </div>
        @endforelse
    </div>
</div>

<style>
    .fw-800 { font-weight: 800; }

    .filter-btn {
        background: white;
        border: 2px solid #e2e8f0;
        padding: 10px 25px;
        border-radius: 50px;
        color: #64748b;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .filter-btn:hover, .filter-btn.active {
        background: #10b981;
        border-color: #10b981;
        color: white;
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
    }

    .modern-work-card { perspective: 1000px; height: 100%; }

    .card-inner {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .modern-work-card:hover .card-inner {
        transform: translateY(-15px) rotateX(5deg);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.1);
    }

    .image-wrapper { position: relative; overflow: hidden; height: 240px; }

    .image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .modern-work-card:hover .image-wrapper img { transform: scale(1.15); }

    .card-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to bottom, transparent, rgba(16, 185, 129, 0.9));
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.4s ease;
    }

    .modern-work-card:hover .card-overlay { opacity: 1; }

    .view-project-btn {
        background: white;
        color: #10b981;
        padding: 12px 25px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 800;
        transform: translateY(20px);
        transition: all 0.4s ease;
    }

    .modern-work-card:hover .view-project-btn { transform: translateY(0); }

    .rating-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.9);
        padding: 5px 15px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.85rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        z-index: 2;
    }

    .author-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        border: 2px solid white;
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        object-fit: cover;
    }

    .work-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.4;
        text-align: right;
    }

    .card-footer-line {
        height: 5px;
        width: 0;
        background: linear-gradient(90deg, #10b981, #3b82f6);
        transition: width 0.5s ease;
    }

    .modern-work-card:hover .card-footer-line { width: 100%; }

    .header-line {
        height: 4px;
        width: 60px;
        background: #10b981;
        border-radius: 10px;
        margin-top: 15px;
    }
</style>
@endsection
