@extends('layouts.master')

@section('content')

<div class="dashboard-container py-5 px-lg-5" dir="rtl">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="sidebar-glass p-4 sticky-top">
                    <div class="text-center mb-4">
                        <div class="profile-img-wrapper">
                            {{-- إضافة alt لصورة صاحب الحساب --}}
                            <img src="{{ auth()->user()->profile_image ? asset('storage/'.auth()->user()->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=10b981&color=fff' }}"
                                 class="profile-main-img shadow-lg"
                                 alt="صورة الملف الشخصي لـ {{ auth()->user()->name }}">
                        </div>
                        <h5 class="fw-bold mt-3 mb-0 text-dark">{{ auth()->user()->name }}</h5>
                        <p class="text-muted small mt-1">لوحة تحكم العميل</p>
                    </div>

                    <div class="nav-menu mt-4">
                        <a href="{{ route('client.dashboard') }}" class="nav-link-custom">
                            <div class="nav-icon"><i class="fas fa-rocket"></i></div>
                            <span>الرئيسية</span>
                        </a>
                        <a href="{{ route('projects.my_projects') }}" class="nav-link-custom">
                            <div class="nav-icon"><i class="fas fa-briefcase"></i></div>
                            <span>مشاريعي</span>
                        </a>
                        <a href="{{ route('freelancers.favorites') }}" class="nav-link-custom active">
                            <div class="nav-icon"><i class="fas fa-heart"></i></div>
                            <span>المفضلين</span>
                        </a>
                        <a href="{{ route('wallet.index') }}" class="nav-link-custom">
                            <div class="nav-icon"><i class="fas fa-wallet"></i></div>
                            <span>المحفظة</span>
                        </a>
                        <hr class="my-3 opacity-10">
                        <a href="{{ route('profile.edit') }}" class="nav-link-custom">
                            <div class="nav-icon"><i class="fas fa-cog"></i></div>
                            <span>الإعدادات</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="top-header-bar d-flex justify-content-between align-items-center mb-4 p-4 glass-card bg-gradient-fav text-white">
                    <div>
                        <h3 class="fw-bold mb-1">المستقلين المفضلين ❤️</h3>
                        <p class="mb-0 opacity-75">نخبة المبدعين الذين اخترت التعامل معهم دائماً.</p>
                    </div>
                    <div class="d-none d-md-block">
                        <i class="fas fa-users-beam fs-1 opacity-25" aria-hidden="true"></i>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="row g-4">
                    @forelse($favorites as $fav)
                    @php $freelancer = $fav->freelancer; @endphp
                    <div class="col-md-4">
                        <div class="glass-card freelancer-card p-4 text-center border-0 position-relative overflow-hidden">

                            <form action="{{ route('favorites.toggle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="freelancer_id" value="{{ $freelancer->id }}">
                                <button type="submit" class="remove-fav-btn" title="حذف من المفضلين" aria-label="حذف {{ $freelancer->name }} من المفضلين">
                                    <i class="fas fa-heart text-danger"></i>
                                </button>
                            </form>

                            <div class="freelancer-avatar-wrapper mb-3 mx-auto">
                                {{-- إضافة alt لصورة المستقل --}}
                                <img src="{{ $freelancer->profile_image ? asset('storage/'.$freelancer->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($freelancer->name).'&background=10b981&color=fff' }}"
                                     class="rounded-circle border border-4 border-white shadow-sm"
                                     width="90"
                                     height="90"
                                     alt="صورة المستقل: {{ $freelancer->name }}">

                                @if($freelancer->isOnline())
                                    <div class="online-dot" title="متصل الآن"></div>
                                @endif
                            </div>

                            <h6 class="fw-bold mb-1 text-dark">{{ $freelancer->name }}</h6>
                            <p class="text-muted small mb-3">{{ $freelancer->job_title ?? 'مستقل مبدع' }}</p>

                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <span class="badge bg-soft-warning text-warning rounded-pill px-2" aria-label="التقييم {{ number_format($freelancer->rating ?? 5.0, 1) }}">
                                    <i class="fas fa-star me-1" aria-hidden="true"></i> {{ number_format($freelancer->rating ?? 5.0, 1) }}
                                </span>
                                <span class="badge bg-soft-primary text-primary rounded-pill px-2">
                                    <i class="fas fa-check-circle me-1" aria-hidden="true"></i> {{ $freelancer->completed_projects_count ?? 0 }} مشروع
                                </span>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="{{ route('projects.create', ['freelancer_id' => $freelancer->id]) }}" class="btn btn-success rounded-pill btn-sm fw-bold">وظفه الآن</a>
                                <a href="{{ route('freelancer.profile', $freelancer->id) }}" class="btn btn-outline-light text-dark rounded-pill btn-sm border">الملف الشخصي</a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5 glass-card">
                        {{-- إضافة alt لصورة الحالة الفارغة --}}
                        <img src="https://illustrations.popsy.co/flat/taking-notes.svg"
                             width="200"
                             class="mb-4"
                             alt="رسم توضيحي يعبر عن قائمة مفضلة فارغة">
                        <h5 class="text-muted">قائمة المفضلين فارغة حالياً</h5>
                        <p class="text-muted small">تصفح المستقلين وأضف المبدعين لقائمتك للوصول إليهم بسرعة.</p>
                        <a href="/freelancers" class="btn btn-success rounded-pill px-4 mt-2">تصفح المستقلين</a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* المتغيرات والستايلات الأساسية */
:root {
    --primary-main: #10b981;
    --primary-soft: #ecfdf5;
}

.glass-card {
    background: white;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    transition: 0.3s ease;
}

.bg-gradient-fav {
    background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%);
}

.freelancer-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

.freelancer-avatar-wrapper {
    position: relative;
    width: fit-content;
}

.online-dot {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 15px;
    height: 15px;
    background: #22c55e;
    border: 3px solid #fff;
    border-radius: 50%;
}

.remove-fav-btn {
    position: absolute;
    top: 15px;
    left: 15px;
    background: white;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    box-shadow: 0 5px 10px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s;
    cursor: pointer;
    z-index: 10;
}

.remove-fav-btn:hover {
    background: #fee2e2;
    transform: scale(1.1);
}

.sidebar-glass { background: white; border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.03); }
.profile-main-img { width: 100px; height: 100px; object-fit: cover; border-radius: 30px; }
.nav-link-custom { display: flex; align-items: center; padding: 12px 18px; color: #64748b; text-decoration: none; border-radius: 16px; margin-bottom: 8px; font-weight: 600; }
.nav-link-custom.active { background: var(--primary-main); color: white; }
.bg-soft-warning { background: #fffbeb; }
.bg-soft-primary { background: #eff6ff; }
</style>

@endsection
