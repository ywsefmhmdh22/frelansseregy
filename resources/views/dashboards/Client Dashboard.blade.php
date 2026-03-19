@extends('layouts.master')

@section('content')

<div class="dashboard-container py-5 px-lg-5" dir="rtl">
    <div class="container-fluid">
        <div class="row g-4">
            {{-- Sidebar Area --}}
            <div class="col-lg-3">
                <div class="sidebar-glass p-4 sticky-top" style="top: 20px;">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block profile-ring">
                            <form id="profileImageForm" action="{{ route('profile.update_image') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="profile-img-wrapper">
                                    <img src="{{ auth()->user()->profile_image ? asset('storage/'.auth()->user()->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=10b981&color=fff' }}"
                                         class="profile-main-img shadow-lg"
                                         id="profilePreview">

                                    <label for="profile_image_input" class="edit-overlay">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input type="file" id="profile_image_input" name="profile_image" class="d-none" onchange="document.getElementById('profileImageForm').submit();">
                                </div>
                            </form>
                            <div class="status-pulse"></div>
                        </div>
                        <h5 class="fw-bold mt-3 mb-0 text-dark">{{ auth()->user()->name }}</h5>
                        <div class="badge bg-soft-success text-success px-3 rounded-pill mt-2">عضو بلاتيني <i class="fas fa-crown ms-1"></i></div>
                    </div>

                    <div class="nav-menu mt-4">
                        <a href="{{ route('client.dashboard') }}" class="nav-link-custom {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="fas fa-rocket"></i></div>
                            <span>الرئيسية</span>
                        </a>

                        <a href="{{ route('freelancers.favorites') }}" class="nav-link-custom {{ request()->routeIs('freelancers.favorites') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="fas fa-star"></i></div>
                            <span>المفضلين</span>
                        </a>
                        <a href="{{ route('wallet.index') }}" class="nav-link-custom {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="fas fa-wallet"></i></div>
                            <span>المحفظة</span>
                        </a>
                        <hr class="my-3 opacity-10">
                        <a href="{{ route('profile.settings') }}" class="nav-link-custom {{ request()->routeIs('profile.settings') ? 'active' : '' }}">
    <div class="nav-icon"><i class="fas fa-cog"></i></div>
    <span>الإعدادات</span>
</a>
                    </div>

                    <div class="wallet-widget mt-5 p-3 text-center text-white shadow-lg">
                        <p class="small opacity-75 mb-1">الرصيد المتاح</p>
                        <h3 class="fw-bold mb-3">{{ number_format($walletBalance ?? 0, 2) }} <small class="fs-6">ج.م</small></h3>
                        <a href="{{ route('wallet.deposit') }}" class="btn btn-glass-white btn-sm w-100 rounded-pill">شحن الرصيد</a>
                    </div>
                </div>
            </div>

            {{-- Main Content Area --}}
            <div class="col-lg-9">
                {{-- Header Bar --}}
                <div class="top-header-bar d-flex justify-content-between align-items-center mb-4 p-3 glass-card">
                    <div class="welcome-text">
                        <h4 class="fw-bold text-dark mb-0">أهلاً، {{ explode(' ', auth()->user()->name)[0] }} ✨</h4>
                        <p class="text-muted small mb-0">لديك بعض التحديثات الجديدة اليوم.</p>
                    </div>

                    <div class="header-actions d-flex align-items-center gap-3">
                        <a href="{{ route('messages.chat', ['user' => Auth::id()]) }}" class="btn btn-white shadow-sm rounded-pill px-3 fw-bold position-relative border-0" style="height: 42px; display: flex; align-items: center;">
                            <i class="far fa-envelope text-dark me-1"></i>
                            <span class="text-dark d-none d-md-inline small">الرسائل</span>
                            @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                            @endif
                        </a>

                        <div class="dropdown">
                            <button class="notification-trigger position-relative border-0 bg-white shadow-sm rounded-circle p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 42px; height: 42px;">
                                <i class="far fa-bell text-dark"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="notif-count">{{ auth()->user()->unreadNotifications->count() }}</span>
                                @endif
                            </button>

                            <div class="dropdown-menu dropdown-menu-start notification-panel shadow-xl border-0 p-0 rounded-4 mt-2">
                                <div class="notif-header p-3 bg-light d-flex justify-content-between align-items-center border-bottom rounded-top-4">
                                    <span class="fw-bold">الإشعارات</span>
                                    <a href="{{ route('notifications.markAllRead') }}" class="small text-success text-decoration-none">تحديد الكل كمقروء</a>
                                </div>
                                <div class="notif-body scrollbar-thin" style="max-height: 350px; overflow-y: auto;">
                                    @forelse(auth()->user()->notifications as $notif)
                                        <div class="notif-item {{ $notif->read_at ? '' : 'unread' }} p-3 border-bottom d-flex gap-3 align-items-start position-relative">
                                            <div class="notif-icon-circle {{ $notif->read_at ? 'bg-light text-muted' : 'bg-success-soft text-success' }}">
                                                <i class="{{ $notif->data['icon'] ?? 'fas fa-info-circle' }}"></i>
                                            </div>
                                            <div class="notif-content flex-grow-1 text-end">
                                                <p class="mb-0 small fw-bold text-dark">{{ $notif->data['title'] ?? 'إشعار جديد' }}</p>
                                                <p class="mb-1 extra-small text-muted">{{ $notif->data['message'] ?? 'هناك تحديث جديد.' }}</p>
                                                <span class="extra-small opacity-50">{{ $notif->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-4 text-center">
                                            <p class="text-muted small">لا توجد إشعارات</p>
                                        </div>
                                    @endforelse
                                </div>
                                <a href="{{ route('notifications.index') }}" class="notif-footer text-center p-2 d-block bg-light text-muted small text-decoration-none border-top rounded-bottom-4">عرض الكل</a>
                            </div>
                        </div>

                        <a href="{{ route('projects.create') }}" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm d-none d-md-block">
                            <i class="fas fa-plus me-1"></i> أضف مشروعاً
                        </a>
                    </div>
                </div>

                {{-- Stats Cards --}}
                <div class="row g-3 mb-4 text-center">
                    @php
                        $stats = [
                            ['label' => 'المشاريع', 'val' => $myProjects->count(), 'icon' => 'fas fa-layer-group', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.1)'],
                            ['label' => 'قيد المراجعة', 'val' => $myProjects->where('admin_status', 'pending')->count(), 'icon' => 'fas fa-hourglass-half', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)'],
                            ['label' => 'مكتملة', 'val' => $myProjects->where('status', 'completed')->count(), 'icon' => 'fas fa-check-circle', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)'],
                            ['label' => 'العروض', 'val' => $myProjects->sum('proposals_count'), 'icon' => 'fas fa-paper-plane', 'color' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.1)']
                        ];
                    @endphp
                    @foreach($stats as $stat)
                    <div class="col-6 col-md-3">
                        <div class="stat-glass-card p-3 h-100">
                            <div class="stat-icon mx-auto mb-2 shadow-sm" style="color: {{ $stat['color'] }}; background: {{ $stat['bg'] }}">
                                <i class="{{ $stat['icon'] }}"></i>
                            </div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $stat['val'] }}</h4>
                            <p class="text-muted small mb-0">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Projects Table --}}
                <div class="glass-card mb-4 overflow-hidden border-0">
                    <div class="p-4 d-flex justify-content-between align-items-center border-bottom bg-light-soft">
                        <h5 class="fw-bold mb-0 text-dark">مشاريعك الحالية <span class="badge bg-white text-dark fw-normal ms-2 small border shadow-sm">آخر 5</span></h5>
                        <a href="{{ route('projects.my_projects') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 text-decoration-none">كل المشاريع</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">المشروع</th>
                                    <th>العروض</th>
                                    <th>الميزانية</th>
                                    <th>حالة الخدمة</th>
                                    <th class="text-center pe-4">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myProjects->take(5) as $project)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center text-end">
                                            <div class="project-icon-box ms-3">
                                                <i class="fas fa-briefcase text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-0">{{ Str::limit($project->title, 35) }}</div>
                                                <small class="text-muted extra-small"><i class="far fa-calendar-alt me-1"></i> {{ $project->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="offer-badge">
                                            <span class="num">{{ $project->proposals_count ?? 0 }}</span>
                                            <span class="lab">عرض</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="price-tag">
                                            <span class="amount">{{ number_format($project->price) }}</span>
                                            <span class="currency">{{ $project->currency ?? 'ج.م' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            if($project->admin_status == 'pending') {
                                                $sClass = 'warning'; $sText = 'تحت المراجعة'; $sIcon = 'fa-clock';
                                            } elseif($project->status == 'open') {
                                                $sClass = 'success'; $sText = 'نشط (يستقبل عروض)'; $sIcon = 'fa-bolt';
                                            } elseif($project->status == 'in_progress') {
                                                $sClass = 'primary'; $sText = 'قيد التنفيذ'; $sIcon = 'fa-spinner fa-spin';
                                            } elseif($project->status == 'completed') {
                                                $sClass = 'info'; $sText = 'مكتمل'; $sIcon = 'fa-check-double';
                                            } else {
                                                $sClass = 'secondary'; $sText = 'مغلق'; $sIcon = 'fa-lock';
                                            }
                                        @endphp
                                        <div class="status-indicator d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-{{$sClass}}-soft text-{{$sClass}} border border-{{$sClass}} border-opacity-10">
                                            <i class="fas {{$sIcon}} extra-small"></i>
                                            <span class="extra-small fw-bold">{{ $sText }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="{{ route('projects.show', $project->id) }}" class="btn-action-view" data-bs-toggle="tooltip" title="عرض التفاصيل">
                                            <i class="fas fa-arrow-left"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="60" class="opacity-25 mb-3">
                                        <p class="text-muted small">لا توجد مشاريع مضافة في حسابك حالياً.</p>
                                        <a href="{{ route('projects.create') }}" class="btn btn-sm btn-success rounded-pill">أضف أول مشروع الآن</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Activity & Promo --}}
                <div class="row g-4 mb-5">
                    <div class="col-md-7">
                        <div class="glass-card p-4 h-100">
                            <h6 class="fw-bold mb-4 text-dark d-flex align-items-center">
                                <span class="p-2 bg-light rounded-3 ms-2"><i class="fas fa-stream text-success"></i></span>
                                آخر التحديثات
                            </h6>
                            <div class="modern-timeline">
                                @forelse(auth()->user()->notifications->take(3) as $notif)
                                <div class="timeline-step">
                                    <div class="step-icon"></div>
                                    <div class="step-content text-end">
                                        <p class="mb-0 fw-bold small text-dark">{{ $notif->data['title'] ?? 'تحديث جديد' }}</p>
                                        <p class="text-muted extra-small mb-0">{{ $notif->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4">
                                    <p class="text-muted small">لا توجد نشاطات مسجلة</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="promo-card h-100 p-4 d-flex flex-column justify-content-between overflow-hidden position-relative shadow-lg border-0">
                            <div class="promo-shapes"></div>
                            <div class="position-relative">
                                <h5 class="fw-bold text-white mb-3">نصيحة المنصة 💡</h5>
                                <p class="text-white opacity-75 small mb-0">قم بإكمال ملفك الشخصي بنسبة 100% لزيادة ثقة المستقلين والحصول على عروض بجودة أفضل.</p>
                            </div>
                             {{-- السطر الجديد المعدل --}}
<a href="{{ route('profile.settings') }}" class="btn btn-glass-white btn-sm rounded-pill fw-bold mt-4">تطوير الملف الشخصي</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Glassmorphism Core */
:root {
    --glass-bg: rgba(255, 255, 255, 0.9);
    --glass-border: rgba(255, 255, 255, 0.5);
    --primary-main: #10b981;
    --primary-soft: #ecfdf5;
    --navy-main: #0f172a;
    --bg-light-soft: #f8fafc;
}

body {
    background: #f4f7fa;
    background-image: radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.03) 0px, transparent 50%),
                      radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.03) 0px, transparent 50%);
}

.sidebar-glass {
    background: var(--glass-bg);
    backdrop-filter: blur(15px);
    border: 1px solid var(--glass-border);
    border-radius: 30px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.03);
}

.glass-card {
    background: #fff;
    border-radius: 24px;
    border: 1px solid rgba(0,0,0,0.02);
    box-shadow: 0 8px 25px rgba(0,0,0,0.02);
}

.profile-img-wrapper {
    position: relative;
    width: 110px;
    height: 110px;
    margin: 0 auto;
}
.profile-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 35px;
    border: 3px solid #fff;
}
.edit-overlay {
    position: absolute;
    bottom: -5px;
    left: -5px;
    background: var(--primary-main);
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid #fff;
    transition: 0.3s;
}
.edit-overlay:hover { transform: scale(1.1); background: #059669; }

.status-pulse {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 15px;
    height: 15px;
    background: #10b981;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.nav-link-custom {
    display: flex;
    align-items: center;
    padding: 12px 18px;
    color: #64748b;
    text-decoration: none;
    border-radius: 15px;
    margin-bottom: 5px;
    transition: 0.3s;
}
.nav-link-custom:hover { background: var(--primary-soft); color: var(--primary-main); }
.nav-link-custom.active { background: var(--primary-main); color: white !important; }
.nav-icon { width: 30px; font-size: 1.1rem; }

.wallet-widget {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 20px;
}
.btn-glass-white {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.4);
    color: white;
}
.btn-glass-white:hover { background: #fff; color: var(--primary-main); }

/* Table Styling */
.custom-table thead th {
    background: var(--bg-light-soft);
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    padding: 18px 15px;
    border-bottom: 1px solid #f1f5f9;
}
.project-icon-box {
    width: 40px;
    height: 40px;
    background: var(--primary-soft);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.offer-badge {
    background: #f1f5f9;
    padding: 5px 12px;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: fit-content;
}
.offer-badge .num { font-weight: 800; color: var(--navy-main); }
.offer-badge .lab { font-size: 9px; color: #94a3b8; }

.btn-action-view {
    width: 38px;
    height: 38px;
    background: #fff;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    border: 1px solid #e2e8f0;
    transition: 0.3s;
}
.btn-action-view:hover {
    background: var(--primary-main);
    color: white;
    transform: translateX(-3px);
}

.promo-card { background: #1e293b; border-radius: 24px; color: white; }
.promo-shapes {
    position: absolute;
    top: -20px;
    left: -20px;
    width: 100px;
    height: 100px;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 50%;
    filter: blur(30px);
}

.modern-timeline .timeline-step { position: relative; padding-right: 25px; padding-bottom: 20px; border-right: 2px solid #f1f5f9; }
.timeline-step .step-icon { position: absolute; right: -7px; top: 0; width: 12px; height: 12px; background: #10b981; border-radius: 50%; border: 2px solid white; }

.bg-warning-soft { background: rgba(245, 158, 11, 0.1); }
.bg-success-soft { background: rgba(16, 185, 129, 0.1); }
.bg-primary-soft { background: rgba(59, 130, 246, 0.1); }

.extra-small { font-size: 11px; }
.notif-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: white;
    font-size: 9px;
    padding: 2px 5px;
    border-radius: 50%;
    border: 2px solid white;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltip init
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Entrance Animation
        const elements = document.querySelectorAll('.stat-glass-card, .glass-card');
        elements.forEach((el, i) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(15px)';
            setTimeout(() => {
                el.style.transition = 'all 0.5s ease-out';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, i * 100);
        });
    });
</script>

@endsection
