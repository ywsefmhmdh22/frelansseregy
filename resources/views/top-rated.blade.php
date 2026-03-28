 @extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;900&family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>

<style>
    #vanta-canvas {
        background-color: #000000;
        background-image: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.95)), url('https://www.transparenttextures.com/patterns/dark-marble.png');
        min-height: 100vh;
        padding: 120px 0;
        font-family: 'Cairo', sans-serif;
        color: #fff;
        position: relative;
        overflow-x: hidden;
    }

    .container.position-relative { z-index: 10 !important; }

    .golden-title {
        font-family: 'Cinzel', serif;
        font-weight: 900;
        font-size: clamp(3rem, 8vw, 5rem);
        background: linear-gradient(to bottom, #fff 20%, #d4af37 50%, #856404 80%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-transform: uppercase;
        letter-spacing: -2px;
        line-height: 1.1;
        margin-bottom: 15px;
        filter: drop-shadow(0 0 15px rgba(212, 175, 55, 0.3));
    }

    .gilded-card {
        background: rgba(15, 15, 15, 0.6);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 40px;
        padding: 60px 30px;
        transition: all 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        position: relative;
        z-index: 5;
        box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        pointer-events: auto;
    }

    .gilded-card:hover {
        transform: translateY(-20px);
        background: rgba(20, 20, 20, 0.9);
        border-color: rgba(212, 175, 55, 0.5);
    }

    .avatar-museum {
        width: 140px; height: 140px; margin: 0 auto 25px; border-radius: 50%;
        padding: 6px; background: linear-gradient(135deg, #d4af37, #856404);
        position: relative; z-index: 2;
    }

    .avatar-museum img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 4px solid #0a0a0a; }

    .rating-giant {
        font-family: 'Cinzel', serif; font-size: 3rem; font-weight: 900;
        background: linear-gradient(to bottom, #fff, #d4af37);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    .reward-system { background: rgba(0,0,0,0.3); border-radius: 20px; padding: 15px; margin-top: 25px; }

    .progress-3d { height: 6px; background: rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden; }

    .progress-bar-3d {
        height: 100%; background: linear-gradient(90deg, #d4af37, #fff, #d4af37);
        background-size: 200% auto; animation: shine 3s linear infinite;
    }

    @keyframes shine { to { background-position: 200% center; } }

    .btn-royal {
        background: linear-gradient(135deg, #d4af37, #856404); color: #000 !important;
        border-radius: 50px; padding: 18px 30px; font-weight: 900;
        text-transform: uppercase; margin-top: 35px; display: inline-block; width: 100%;
    }
</style>

<div id="vanta-canvas">
    <div class="container position-relative">
        <div class="text-center mb-5 pb-4 animate__animated animate__fadeInDown">
            <h1 class="golden-title">Elite League</h1>
            <p class="text-secondary fs-5 mx-auto">نخبة المبرمجين الحاصلين على أعلى التقييمات في المنصة</p>
        </div>

        <div class="row g-5">
            {{-- تصفية المستقلين الذين تقييمهم 4 أو أكثر --}}
            @php
                $filteredFreelancers = $freelancers->where('freelancer_rating', '>=', 4);
            @endphp

            @forelse($filteredFreelancers as $index => $freelancer)
                @php
                    // استخدام العمود الجديد الذي أضفناه في الميجريشن لعد المشاريع الممتازة
                    $excellentProjects = $freelancer->excellent_projects_count ?? 0;

                    // حساب النسبة المئوية بناءً على المشاريع الممتازة (الهدف 10 مشاريع للفل الكامل)
                    $progressPercent = min(($excellentProjects / 10) * 100, 100);
                @endphp

                <div class="col-xl-4 col-lg-6">
                    <div class="gilded-card text-center animate__animated animate__fadeInUp" style="animation-delay: {{ $index * 0.1 }}s">

                        <div class="card-rank" style="position: absolute; top: 25px; right: 35px; font-size: 3.5rem; color: rgba(212, 175, 55, 0.1);">#{{ $index + 1 }}</div>

                        <div class="avatar-museum">
                            <img src="{{ $freelancer->profile_image ? asset('storage/'.$freelancer->profile_image) : 'https://ui-avatars.com/api/?background=111&color=d4af37&size=200&name='.urlencode($freelancer->name) }}">
                        </div>

                        <h3 class="fw-black text-white mb-1">{{ $freelancer->name }}</h3>
                        <p class="text-warning small mb-4 fw-bold">ELITE DEVELOPER</p>

                        <div class="rating-giant">{{ number_format($freelancer->freelancer_rating, 1) }}</div>

                        <div class="reward-system">
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted fw-bold" style="font-size: 9px;">EXCELLENT PROJECTS (>4⭐)</span>
                                <span class="text-white fw-bold" style="font-size: 9px;">{{ $excellentProjects }} / 10</span>
                            </div>
                            <div class="progress-3d">
                                <div class="progress-bar-3d" style="width: {{ $progressPercent }}%"></div>
                            </div>
                        </div>

                        <a href="{{ route('profile.portfolio', $freelancer->id) }}" class="btn btn-royal">
                            استكشاف المشاريع <i class="fas fa-chevron-right ms-2"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-secondary py-5">
                    <h3 class="fw-light">بانتظار وصول النخبة...</h3>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        VANTA.NET({
            el: "#vanta-canvas",
            mouseControls: true,
            touchControls: true,
            color: 0xd4af37,
            backgroundColor: 0x0,
            points: 12.00,
            maxDistance: 20.00,
            spacing: 16.00,
            mouseInteraction: true
        });
    });
</script>
@endsection
