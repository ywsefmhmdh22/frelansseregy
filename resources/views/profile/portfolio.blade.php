@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<div class="elite-portfolio py-5" dir="rtl">
    <div class="container position-relative">

        {{-- زر إضافة عمل جديد (يظهر فقط لصاحب الحساب) --}}
        @auth
            @if(auth()->user()->id == $user->id)
                <div class="add-work-floating-zone animate__animated animate__bounceInUp">
                    <a href="{{ route('portfolio.create') }}" class="add-work-btn">
                        <div class="icon-box">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="text-box">إضافة عمل جديد للمعرض</div>
                    </a>
                </div>
            @endif
        @endauth

        {{-- رأس الصفحة بتصميم ناعم --}}
        <div class="portfolio-hero text-center mb-5 pb-4">
            <div class="avatar-ring-premium mx-auto mb-4">
                {{-- إضافة alt لصورة البروفايل --}}
                <img src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=6366f1&color=fff' }}"
                     class="img-user"
                     alt="الصورة الشخصية للمستقل {{ $user->name }}">
                <div class="glow-effect"></div>
            </div>
            <h1 class="display-3 fw-black text-dark mb-2">متحف أعمال <span class="text-gradient-gold">{{ $user->name }}</span></h1>
            <p class="text-secondary fs-5 mx-auto" style="max-width: 800px; opacity: 0.8;">
                حيث تلتقي المهارة بالإبداع، استعرض أدناه الرحلة المهنية والمشاريع التي صنعت الفرق.
            </p>
            <div class="designer-line mx-auto"></div>
        </div>

        {{-- شبكة الأعمال الاحترافية --}}
        <div class="row g-5">
            @forelse($user->portfolios as $project)
                <div class="col-xl-4 col-lg-6 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: {{ $loop->index * 0.15 }}s">
                    <div class="elite-card">
                        {{-- الجزء العلوي: الصورة --}}
                        <div class="elite-card-preview">
                            {{-- إضافة alt لصورة المشروع في الكارت --}}
                            <img src="{{ $project->image ? asset('storage/' . $project->image) : 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=800' }}"
                                 class="preview-img"
                                 alt="صورة عرض مشروع: {{ $project->title }}">

                            <div class="elite-overlay">
                                <div class="overlay-top text-start w-100 p-3">
                                    <span class="badge-premium">جديد</span>
                                </div>
                                <div class="overlay-center">
                                    <button class="btn-preview-circle" data-bs-toggle="modal" data-bs-target="#eliteModal{{ $project->id }}" aria-label="عرض تفاصيل المشروع">
                                        <i class="fas fa-expand-alt"></i>
                                    </button>
                                </div>
                                <div class="overlay-bottom w-100 p-4 text-center">
                                    <a href="{{ $project->link ?? '#' }}" target="_blank" class="btn btn-blur-white rounded-pill px-4">
                                        <i class="fas fa-link ms-2"></i> معاينة مباشرة
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- الجزء السفلي: البيانات --}}
                        <div class="elite-card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h3 class="card-title fw-black mb-0">{{ $project->title }}</h3>
                                <div class="project-year">{{ $project->completed_at ? \Carbon\Carbon::parse($project->completed_at)->year : '2026' }}</div>
                            </div>
                            <p class="card-text text-muted">
                                {{ Str::limit($project->description, 95) }}
                            </p>
                            <div class="card-meta pt-3 mt-3 border-top d-flex align-items-center justify-content-between">
                                <div class="stars-rating">
                                    @for($i=0; $i<5; $i++) <i class="fas fa-star text-warning small"></i> @endfor
                                </div>
                                <span class="tag-soft">#{{ $project->category ?? 'إبداع' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- نافذة التفاصيل السينمائية --}}
                <div class="modal fade elite-modal" id="eliteModal{{ $project->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content border-0 rounded-5 overflow-hidden">
                            <div class="modal-body p-0">
                                <div class="row g-0">
                                    <div class="col-lg-7 bg-black d-flex align-items-center justify-content-center">
                                        {{-- إضافة alt للصورة داخل المودال --}}
                                        <img src="{{ $project->image ? asset('storage/' . $project->image) : 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=800' }}"
                                             class="img-fluid project-full-img"
                                             alt="تفاصيل صورة المشروع كاملة: {{ $project->title }}">
                                    </div>
                                    <div class="col-lg-5 p-5 text-end d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                                <span class="text-primary fw-bold">تفاصيل المشروع</span>
                                            </div>
                                            <h2 class="fw-black mb-3 text-dark">{{ $project->title }}</h2>
                                            <p class="text-secondary fs-6 mb-4" style="line-height: 2;">
                                                {{ $project->description }}
                                            </p>
                                        </div>
                                        <div class="modal-footer-custom">
                                            <hr class="mb-4">
                                            <div class="d-grid gap-3">
                                                <a href="{{ $project->link ?? '#' }}" class="btn btn-primary-gradient py-3 rounded-pill fw-black shadow-lg">زيارة رابط العمل الحقيقي</a>
                                                <button class="btn btn-light py-3 rounded-pill fw-bold" data-bs-dismiss="modal">إغلاق النافذة</button>
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
                        <lottie-player src="https://assets10.lottiefiles.com/packages/lf20_m6cuL6.json" background="transparent" speed="1" style="width: 350px; height: 350px; margin: 0 auto;" loop autoplay></lottie-player>
                        <h2 class="fw-black mt-4">المتحف بانتظار إبداعك</h2>
                        <p class="text-muted fs-5">لم يتم نشر أي أعمال بعد في هذا المعرض الفاخر.</p>
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

    body { background-color: #fcfcfd; font-family: 'Cairo', sans-serif; color: var(--elite-dark); }
    .fw-black { font-weight: 900; }

    .text-gradient-gold {
        background: linear-gradient(135deg, var(--elite-primary), var(--elite-secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .designer-line { width: 100px; height: 6px; background: linear-gradient(to right, var(--elite-primary), var(--elite-secondary)); border-radius: 50px; margin-top: 20px; }

    .avatar-ring-premium { position: relative; width: 110px; height: 110px; }
    .img-user { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 4px solid white; box-shadow: 0 10px 20px rgba(0,0,0,0.1); position: relative; z-index: 2; }
    .glow-effect { position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 50%; background: var(--elite-primary); filter: blur(15px); opacity: 0.3; z-index: 1; }

    .elite-card {
        background: white; border-radius: 30px; overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01), 0 2px 4px -1px rgba(0,0,0,0.01);
        transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
        border: 1px solid #f1f5f9;
        height: 100%; display: flex; flex-direction: column;
    }
    .elite-card:hover { transform: translateY(-15px) scale(1.02); box-shadow: 0 30px 60px -12px rgba(0,0,0,0.12); }

    .elite-card-preview { position: relative; height: 280px; overflow: hidden; }
    .preview-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease; }
    .elite-card:hover .preview-img { transform: scale(1.1); filter: blur(2px); }

    .elite-overlay {
        position: absolute; inset: 0; background: rgba(15, 23, 42, 0.75);
        display: flex; flex-direction: column; align-items: center; justify-content: space-between;
        opacity: 0; transition: all 0.4s ease; backdrop-filter: blur(4px);
    }
    .elite-card:hover .elite-overlay { opacity: 1; }

    .btn-preview-circle {
        width: 60px; height: 60px; border-radius: 50%; background: white; color: var(--elite-primary);
        display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
        border: none; transform: scale(0.5); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .elite-card:hover .btn-preview-circle { transform: scale(1); }
    .btn-blur-white { background: rgba(255, 255, 255, 0.15); color: white; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px); }
    .badge-premium { background: var(--elite-gold); color: black; padding: 4px 12px; border-radius: 50px; font-weight: 800; font-size: 0.7rem; }

    .card-title { font-size: 1.35rem; color: #1e293b; }
    .project-year { background: #f1f5f9; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; color: #64748b; }
    .tag-soft { background: #eef2ff; color: var(--elite-primary); padding: 5px 15px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }

    .add-work-floating-zone { position: fixed; bottom: 30px; left: 30px; z-index: 1000; }
    .add-work-btn {
        display: flex; align-items: center; background: var(--elite-dark);
        padding: 8px; border-radius: 60px; text-decoration: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2); transition: all 0.3s ease;
    }
    .add-work-btn .icon-box {
        width: 50px; height: 50px; border-radius: 50%; background: var(--elite-primary);
        color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
    }
    .add-work-btn .text-box { color: white; padding: 0 20px; font-weight: 700; font-size: 1rem; }
    .add-work-btn:hover { transform: scale(1.05); background: #000; box-shadow: 0 15px 35px rgba(99,102,241,0.4); }

    .project-full-img { max-height: 80vh; object-fit: contain; }
    .btn-primary-gradient { background: linear-gradient(135deg, var(--elite-primary), var(--elite-secondary)); border: none; }

    @media (max-width: 768px) {
        .add-work-floating-zone { left: 50%; transform: translateX(-50%); width: 90%; }
        .add-work-btn { justify-content: center; }
        .display-3 { font-size: 2.5rem; }
        .elite-card-preview { height: 200px; }
    }
</style>
@endsection
