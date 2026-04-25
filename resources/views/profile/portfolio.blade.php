@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<div class="elite-portfolio py-4 py-md-5" dir="rtl">
    <div class="container position-relative">

        {{-- رأس الصفحة بتصميم مطور --}}
        <div class="portfolio-hero text-center mb-5 pb-2">
            <div class="avatar-container mx-auto mb-4">
                <div class="avatar-ring-premium">
                    <img src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=6366f1&color=fff' }}"
                         class="img-user"
                         alt="الصورة الشخصية للمستقل {{ $user->name }}">
                    <div class="glow-effect"></div>
                </div>

                {{-- زر إضافة عمل جديد - يظهر الآن فوق الصورة بشكل أنيق --}}
                @auth
                    @if(auth()->user()->id == $user->id)
                        <a href="{{ route('portfolio.create') }}" class="add-work-integrated animate__animated animate__zoomIn" title="إضافة عمل جديد">
                            <i class="fas fa-plus"></i>
                        </a>
                    @endif
                @endauth
            </div>

            <h1 class="hero-title fw-black text-dark mb-2">متحف أعمال <span class="text-gradient-gold">{{ $user->name }}</span></h1>
            <p class="hero-subtitle text-secondary mx-auto px-2">
                حيث تلتقي المهارة بالإبداع، استعرض أدناه الرحلة المهنية والمشاريع التي صنعت الفرق.
            </p>
            <div class="designer-line mx-auto"></div>
        </div>

        {{-- شبكة الأعمال --}}
        <div class="row g-3 g-md-4 g-lg-5">
            @forelse($user->portfolios as $project)
                <div class="col-12 col-md-6 col-xl-4 animate__animated animate__fadeInUp" style="animation-delay: {{ $loop->index * 0.1 }}s">
                    <div class="elite-card">
                        <div class="elite-card-preview">
                            <img src="{{ $project->image ? asset('storage/' . $project->image) : 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=800' }}"
                                 class="preview-img"
                                 alt="{{ $project->title }}">

                            <div class="elite-overlay">
                                <div class="overlay-top text-start w-100 p-3">
                                    <span class="badge-premium">جديد</span>
                                </div>
                                <div class="overlay-center">
                                    <button class="btn-preview-circle" data-bs-toggle="modal" data-bs-target="#eliteModal{{ $project->id }}">
                                        <i class="fas fa-expand-alt"></i>
                                    </button>
                                </div>
                                <div class="overlay-bottom w-100 p-3 p-md-4 text-center">
                                    <a href="{{ $project->link ?? '#' }}" target="_blank" class="btn btn-blur-white rounded-pill px-4">
                                        <i class="fas fa-link ms-2"></i> معاينة
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="elite-card-body p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="card-title fw-black mb-0">{{ $project->title }}</h3>
                                <div class="project-year">{{ $project->completed_at ? \Carbon\Carbon::parse($project->completed_at)->year : '2026' }}</div>
                            </div>
                            <p class="card-text text-muted small mb-3">
                                {{ Str::limit($project->description, 85) }}
                            </p>
                            <div class="card-meta pt-3 mt-auto border-top d-flex align-items-center justify-content-between">
                                <div class="stars-rating">
                                    @for($i=0; $i<5; $i++) <i class="fas fa-star text-warning" style="font-size: 0.7rem;"></i> @endfor
                                </div>
                                <span class="tag-soft">#{{ $project->category ?? 'إبداع' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- المودال --}}
                <div class="modal fade elite-modal" id="eliteModal{{ $project->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-sm-down">
                        <div class="modal-content border-0 overflow-hidden">
                            <div class="modal-body p-0">
                                <div class="row g-0">
                                    <div class="col-lg-7 bg-black d-flex align-items-center justify-content-center">
                                        <img src="{{ $project->image ? asset('storage/' . $project->image) : 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=800' }}"
                                             class="img-fluid project-full-img"
                                             alt="{{ $project->title }}">
                                    </div>
                                    <div class="col-lg-5 p-4 p-md-5 text-end d-flex flex-column">
                                        <div class="d-flex align-items-center justify-content-between mb-4">
                                            <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                                            <span class="text-primary fw-bold">تفاصيل المشروع</span>
                                        </div>
                                        <div class="modal-scrollable-content">
                                            <h2 class="fw-black mb-3 text-dark h4 h2-md">{{ $project->title }}</h2>
                                            <p class="text-secondary fs-6 mb-4" style="line-height: 1.8;">
                                                {{ $project->description }}
                                            </p>
                                        </div>
                                        <div class="mt-auto pt-4 border-top">
                                            <div class="d-grid gap-2">
                                                <a href="{{ $project->link ?? '#' }}" class="btn btn-primary-gradient py-3 rounded-pill fw-black shadow-lg">زيارة رابط العمل</a>
                                                <button class="btn btn-light py-3 rounded-pill fw-bold" data-bs-dismiss="modal">إغلاق</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="empty-state-luxury py-5">
                        <lottie-player src="https://assets10.lottiefiles.com/packages/lf20_m6cuL6.json" background="transparent" speed="1" class="lottie-responsive" loop autoplay></lottie-player>
                        <h2 class="fw-black mt-4">المتحف بانتظار إبداعك</h2>
                        <p class="text-muted fs-6">لم يتم نشر أي أعمال بعد في هذا المعرض.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');

    :root {
        --elite-primary: #6366f1;
        --elite-secondary: #a855f7;
        --elite-gold: #fbbf24;
        --elite-dark: #0f172a;
    }

    body { background-color: #fcfcfd; font-family: 'Cairo', sans-serif; color: var(--elite-dark); overflow-x: hidden; }
    .fw-black { font-weight: 900; }

    /* هيرو سكشن */
    .avatar-container {
        position: relative;
        width: fit-content;
    }

    .avatar-ring-premium {
        position: relative;
        width: clamp(100px, 20vw, 130px);
        height: clamp(100px, 20vw, 130px);
    }

    .img-user {
        width: 100%; height: 100%; object-fit: cover; border-radius: 50%;
        border: 4px solid white; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: relative; z-index: 2;
    }

    /* الزر المدمج فوق الصورة */
    .add-work-integrated {
        position: absolute;
        bottom: 5px;
        left: -5px;
        width: 40px;
        height: 40px;
        background: var(--elite-dark);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        text-decoration: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        border: 3px solid white;
        transition: all 0.3s ease;
    }
    .add-work-integrated:hover {
        background: var(--elite-primary);
        transform: scale(1.15) rotate(90deg);
        color: white;
    }

    .glow-effect { position: absolute; inset: -5px; border-radius: 50%; background: var(--elite-primary); filter: blur(20px); opacity: 0.2; z-index: 1; }

    /* تحسينات النصوص */
    .hero-title { font-size: clamp(1.8rem, 5vw, 3.2rem); }
    .hero-subtitle { font-size: clamp(0.9rem, 2vw, 1.15rem); max-width: 700px; opacity: 0.7; }
    .text-gradient-gold {
        background: linear-gradient(135deg, var(--elite-primary), var(--elite-secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .designer-line { width: 60px; height: 4px; background: linear-gradient(to right, var(--elite-primary), var(--elite-secondary)); border-radius: 50px; margin-top: 20px; }

    /* الكروت */
    .elite-card {
        background: white; border-radius: 28px; overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid #f1f5f9; height: 100%; display: flex; flex-direction: column;
    }
    @media (min-width: 992px) {
        .elite-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    }

    .elite-card-preview { position: relative; height: 240px; overflow: hidden; }
    .preview-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }

    .elite-overlay {
        position: absolute; inset: 0; background: rgba(15, 23, 42, 0.75);
        display: flex; flex-direction: column; align-items: center; justify-content: space-between;
        opacity: 0; transition: all 0.3s ease; backdrop-filter: blur(4px);
    }
    @media (hover: hover) {
        .elite-card:hover .elite-overlay { opacity: 1; }
        .elite-card:hover .preview-img { transform: scale(1.1); }
    }

    .btn-preview-circle {
        width: 55px; height: 55px; border-radius: 50%; background: white; color: var(--elite-primary);
        display: flex; align-items: center; justify-content: center; font-size: 1.3rem; border: none;
    }

    .project-year { background: #f8fafc; padding: 4px 12px; border-radius: 10px; font-size: 0.8rem; font-weight: 700; color: #64748b; border: 1px solid #edf2f7; }
    .tag-soft { background: #f0f3ff; color: var(--elite-primary); padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }

    /* المودال */
    .project-full-img { width: 100%; max-height: 50vh; object-fit: cover; }
    @media (min-width: 992px) {
        .project-full-img { max-height: 85vh; object-fit: contain; }
    }
    .btn-primary-gradient { background: linear-gradient(135deg, var(--elite-primary), var(--elite-secondary)); border: none; color: white; }

    .lottie-responsive { width: 200px; height: 200px; margin: 0 auto; }
</style>
@endsection
