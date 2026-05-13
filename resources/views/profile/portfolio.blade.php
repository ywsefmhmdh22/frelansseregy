@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

@php
    // جلب صورة البروفايل من Laravel Cloud (S3)
    $profilePhoto = $user->profile_image
        ? Storage::disk('s3')->url($user->profile_image)
        : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=6366f1&color=fff';
@endphp

<div class="elite-portfolio py-4 py-md-5" dir="rtl">
    <div class="container position-relative">

        {{-- رأس الصفحة بتصميم مطور --}}
        <div class="portfolio-hero text-center mb-5 pb-2">
            <div class="avatar-container mx-auto mb-4">
                <div class="avatar-ring-premium">
                    <img src="{{ $profilePhoto }}"
                         class="img-user animate__animated animate__zoomIn"
                         alt="الصورة الشخصية للمستقل {{ $user->name }}">
                    <div class="glow-effect"></div>
                </div>

                {{-- زر إضافة عمل جديد - يظهر للمالك فقط --}}
                @auth
                    @if(auth()->user()->id == $user->id)
                        <a href="{{ route('portfolio.create') }}" class="add-work-integrated animate__animated animate__bounceIn animate__delay-1s" title="إضافة عمل جديد">
                            <i class="fas fa-plus"></i>
                        </a>
                    @endif
                @endauth
            </div>

            <h1 class="hero-title fw-black text-dark mb-2 animate__animated animate__fadeInDown">متحف أعمال <span class="text-gradient-gold">{{ $user->name }}</span></h1>
            <p class="hero-subtitle text-secondary mx-auto px-2 animate__animated animate__fadeIn">
                حيث تلتقي المهارة بالإبداع، استعرض أدناه الرحلة المهنية والمشاريع التي صنعت الفرق.
            </p>
            <div class="designer-line mx-auto"></div>
        </div>

        {{-- شبكة الأعمال --}}
        <div class="row g-3 g-md-4 g-lg-5">
            @forelse($user->portfolios as $project)
                @php
                    // جلب صورة المشروع من Laravel Cloud (S3)
                    $projectImage = $project->image
                        ? Storage::disk('s3')->url($project->image)
                        : 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=800';
                @endphp
                <div class="col-12 col-md-6 col-xl-4 animate__animated animate__fadeInUp" style="animation-delay: {{ $loop->index * 0.1 }}s">
                    <div class="elite-card">
                        <div class="elite-card-preview">
                            <img src="{{ $projectImage }}"
                                 class="preview-img"
                                 alt="{{ $project->title }}">

                            <div class="elite-overlay">
                                <div class="overlay-top text-start w-100 p-3 text-end">
                                    <span class="badge-premium">إبداع</span>
                                </div>
                                <div class="overlay-center">
                                    <button class="btn-preview-circle" data-bs-toggle="modal" data-bs-target="#eliteModal{{ $project->id }}">
                                        <i class="fas fa-expand-alt"></i>
                                    </button>
                                </div>
                                <div class="overlay-bottom w-100 p-3 p-md-4 text-center">
                                    <a href="{{ $project->link ?? '#' }}" target="_blank" class="btn btn-blur-white rounded-pill px-4">
                                        <i class="fas fa-link ms-2"></i> معاينة الرابط
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="elite-card-body p-3 p-md-4 text-end">
                            <div class="d-flex justify-content-between align-items-start mb-2 flex-row-reverse">
                                <h3 class="card-title fw-black mb-0">{{ $project->title }}</h3>
                                <div class="project-year">{{ $project->completed_at ? \Carbon\Carbon::parse($project->completed_at)->year : '2026' }}</div>
                            </div>
                            <p class="card-text text-muted small mb-3">
                                {{ Str::limit($project->description, 85) }}
                            </p>
                            <div class="card-meta pt-3 mt-auto border-top d-flex align-items-center justify-content-between flex-row-reverse">
                                <div class="stars-rating">
                                    @for($i=0; $i<5; $i++) <i class="fas fa-star text-warning" style="font-size: 0.7rem;"></i> @endfor
                                </div>
                                <span class="tag-soft">#{{ $project->category ?? 'تطوير' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- المودال بتصميم سينمائي --}}
                <div class="modal fade elite-modal" id="eliteModal{{ $project->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content border-0 shadow-2xl rounded-5 overflow-hidden">
                            <div class="modal-body p-0">
                                <div class="row g-0">
                                    <div class="col-lg-7 bg-dark d-flex align-items-center justify-content-center">
                                        <img src="{{ $projectImage }}"
                                             class="img-fluid project-full-img"
                                             alt="{{ $project->title }}">
                                    </div>
                                    <div class="col-lg-5 p-4 p-md-5 text-end d-flex flex-column bg-white">
                                        <div class="d-flex align-items-center justify-content-between mb-4 flex-row-reverse">
                                            <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                                            <span class="text-primary-gradient fw-bold">تفاصيل المشروع</span>
                                        </div>
                                        <div class="modal-scrollable-content">
                                            <h2 class="fw-black mb-3 text-dark h3">{{ $project->title }}</h2>
                                            <div class="designer-line mb-4"></div>
                                            <p class="text-secondary fs-6 mb-4" style="line-height: 1.8;">
                                                {{ $project->description }}
                                            </p>
                                        </div>
                                        <div class="mt-auto pt-4 border-top">
                                            <div class="d-grid gap-2">
                                                @if($project->link)
                                                    <a href="{{ $project->link }}" target="_blank" class="btn btn-primary-gradient py-3 rounded-pill fw-black shadow-lg">زيارة المشروع <i class="fas fa-external-link-alt me-2"></i></a>
                                                @endif
                                                <button class="btn btn-light py-3 rounded-pill fw-bold" data-bs-dismiss="modal">إغلاق المعرض</button>
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
                        <lottie-player src="https://assets10.lottiefiles.com/packages/lf20_m6cuL6.json" background="transparent" speed="1" style="width: 250px; height: 250px; margin: 0 auto;" loop autoplay></lottie-player>
                        <h2 class="fw-black mt-4">المتحف بانتظار إبداعك</h2>
                        <p class="text-muted fs-6">لم يتم نشر أي أعمال بعد في هذا المعرض.</p>
                        @auth
                            @if(auth()->user()->id == $user->id)
                                <a href="{{ route('portfolio.create') }}" class="btn btn-primary-gradient px-5 py-3 rounded-pill fw-black mt-3">أضف عملك الأول الآن</a>
                            @endif
                        @endauth
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
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    }

    body { background-color: #f8fafc; font-family: 'Cairo', sans-serif; color: var(--elite-dark); overflow-x: hidden; }
    .fw-black { font-weight: 900; }

    /* هيرو سكشن */
    .avatar-container { position: relative; width: fit-content; }
    .avatar-ring-premium {
        position: relative;
        width: 140px; height: 140px;
        padding: 5px;
        background: var(--primary-gradient);
        border-radius: 50%;
        box-shadow: 0 15px 35px rgba(99, 102, 241, 0.3);
    }

    .img-user {
        width: 100%; height: 100%; object-fit: cover; border-radius: 50%;
        border: 4px solid white; position: relative; z-index: 2;
    }

    .add-work-integrated {
        position: absolute; bottom: 5px; left: -5px; width: 45px; height: 45px;
        background: var(--elite-dark); color: white; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; z-index: 10;
        text-decoration: none; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        border: 3px solid white; transition: all 0.3s ease;
    }
    .add-work-integrated:hover { background: var(--elite-primary); transform: scale(1.15) rotate(90deg); color: white; }

    .glow-effect { position: absolute; inset: -10px; border-radius: 50%; background: var(--elite-primary); filter: blur(25px); opacity: 0.15; z-index: 1; }

    .text-gradient-gold {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .text-primary-gradient { background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    .designer-line { width: 60px; height: 4px; background: var(--primary-gradient); border-radius: 50px; margin-top: 15px; }

    /* الكروت */
    .elite-card {
        background: white; border-radius: 30px; overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid #f1f5f9; height: 100%; display: flex; flex-direction: column;
    }
    .elite-card:hover { transform: translateY(-12px); box-shadow: 0 25px 50px rgba(0,0,0,0.08); }

    .elite-card-preview { position: relative; height: 260px; overflow: hidden; }
    .preview-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease; }

    .elite-overlay {
        position: absolute; inset: 0; background: rgba(15, 23, 42, 0.8);
        display: flex; flex-direction: column; align-items: center; justify-content: space-between;
        opacity: 0; transition: all 0.4s ease; backdrop-filter: blur(6px);
    }
    .elite-card:hover .elite-overlay { opacity: 1; }
    .elite-card:hover .preview-img { transform: scale(1.15); }

    .badge-premium { background: var(--primary-gradient); color: white; padding: 5px 15px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
    .btn-preview-circle { width: 60px; height: 60px; border-radius: 50%; background: white; color: var(--elite-primary); border: none; font-size: 1.4rem; transition: 0.3s; }
    .btn-preview-circle:hover { transform: scale(1.1); background: var(--elite-primary); color: white; }
    .btn-blur-white { background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3); color: white; font-weight: 700; transition: 0.3s; }
    .btn-blur-white:hover { background: white; color: var(--elite-dark); }

    .project-year { background: #f8fafc; padding: 4px 14px; border-radius: 12px; font-size: 0.8rem; font-weight: 800; color: var(--elite-primary); border: 1px solid #e2e8f0; }
    .tag-soft { background: #f0f3ff; color: var(--elite-primary); padding: 7px 16px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }

    .btn-primary-gradient { background: var(--primary-gradient); border: none; color: white; transition: 0.3s; }
    .btn-primary-gradient:hover { filter: brightness(1.1); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(99, 102, 241, 0.4); }

    .project-full-img { width: 100%; height: 100%; object-fit: contain; background: #000; }

    .modal-content { border-radius: 30px !important; }
    .modal-xl { max-width: 1200px; }

    @media (max-width: 768px) {
        .avatar-ring-premium { width: 110px; height: 110px; }
        .hero-title { font-size: 2rem; }
    }
</style>
@endsection
