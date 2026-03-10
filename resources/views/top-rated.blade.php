 @extends('layouts.master')

@section('content')
<style>
    .freelancer-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        transition: all 0.4s ease;
        position: relative;
    }
    .freelancer-card:hover {
        transform: translateY(-10px);
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    /* أوسمة الترتيب */
    .rank-1 { background: linear-gradient(45deg, #FFD700, #FFA500); }
    .rank-2 { background: linear-gradient(45deg, #C0C0C0, #808080); }
    .rank-3 { background: linear-gradient(45deg, #CD7F32, #8B4513); }
    .rank-default { background: #64748b; }

    .rank-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        color: #fff;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 800;
        z-index: 5;
    }
    .profile-img {
        width: 100px; height: 100px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #10b981;
    }
    .star-rating { color: #f59e0b; }
    .skill-tag {
        display: inline-block;
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        padding: 2px 10px;
        border-radius: 5px;
        font-size: 0.7rem;
        margin: 2px;
    }
</style>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">نخبة المستقلين</h1>
        <p class="text-muted">الأعلى تقييماً بناءً على تجارب العملاء الحقيقية</p>
    </div>

    <div class="row g-4">
        @forelse($freelancers as $index => $freelancer)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="freelancer-card p-4 text-center">
                    <div class="rank-badge {{ $index == 0 ? 'rank-1' : ($index == 1 ? 'rank-2' : ($index == 2 ? 'rank-3' : 'rank-default')) }}">
                        #{{ $index + 1 }}
                    </div>

                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ $freelancer->profile_image ? asset('storage/'.$freelancer->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($freelancer->name) }}" class="profile-img" alt="{{ $freelancer->name }}">

                        @if($freelancer->verification_status == 'verified')
                            <div class="position-absolute bottom-0 start-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:25px; height:25px; border:2px solid #fff;">
                                <i class="fas fa-check" style="font-size:10px;"></i>
                            </div>
                        @endif
                    </div>

                    <h5 class="fw-bold mb-1">{{ $freelancer->name }}</h5>
                    <p class="text-muted small mb-2">{{ $freelancer->country ?? 'مستقل' }} {{ $freelancer->city ? '- ' . $freelancer->city : '' }}</p>

                    <div class="star-rating mb-3">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="{{ $i <= $freelancer->freelancer_rating ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                        <span class="text-dark fw-bold ms-1">{{ number_format($freelancer->freelancer_rating, 1) }}</span>
                    </div>

                    <div class="mb-3" style="min-height: 50px;">
                        @if($freelancer->skills)
                            @foreach(explode(',', $freelancer->skills) as $skill)
                                <span class="skill-tag">{{ trim($skill) }}</span>
                            @endforeach
                        @else
                            <span class="text-muted small">لا توجد مهارات</span>
                        @endif
                    </div>

                    <div class="row stats-row border-top pt-3">
                        <div class="col-6">
                            <h6 class="mb-0 fw-bold">{{ $freelancer->total_reviews ?? 0 }}</h6>
                            <small class="text-muted">تقييم</small>
                        </div>
                        <div class="col-6 border-start">
                            <h6 class="mb-0 fw-bold">{{ $freelancer->balance > 0 ? 'نشط' : 'جديد' }}</h6>
                            <small class="text-muted">الحالة</small>
                        </div>
                    </div>

                    {{-- الزرار بعد تعديله ليفتح صفحة تفاصيل الأدمن المذكورة في الـ Route --}}
                    <a href="{{ route('admin.user.details', $freelancer->id) }}" class="boxed-btn w-100 mt-4 py-2 text-decoration-none">
                        تصفح الملف
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                <p class="lead text-muted">لا يوجد مستقلين مصنفين حالياً.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
