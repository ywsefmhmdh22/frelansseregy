 @extends('layouts.master')

@section('content')

<div class="dashboard-container py-5 px-lg-5" dir="rtl">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="sidebar-glass p-4 sticky-top">
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
                        <a href="{{ route('profile.edit') }}" class="nav-link-custom {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="fas fa-cog"></i></div>
                            <span>الإعدادات</span>
                        </a>
                    </div>

                    <div class="wallet-widget mt-5 p-3 text-center text-white">
                        <p class="small opacity-75 mb-1">الرصيد المتاح</p>
                        <h3 class="fw-bold mb-3">{{ number_format($walletBalance ?? 0, 2) }} <small class="fs-6">ج.م</small></h3>
                        <a href="{{ route('wallet.deposit') }}" class="btn btn-glass-white btn-sm w-100 rounded-pill">شحن الرصيد</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="top-header-bar d-flex justify-content-between align-items-center mb-4 p-3 glass-card">
                    <div class="welcome-text">
                        <h4 class="fw-bold text-dark mb-0">أهلاً، {{ explode(' ', auth()->user()->name)[0] }} ✨</h4>
                        <p class="text-muted small mb-0">لديك بعض التحديثات الجديدة اليوم.</p>
                    </div>

                     <div class="header-actions d-flex align-items-center gap-3">
    {{-- زرار الرسائل الجديد --}}
    <a href="{{ route('messages.chat', ['user' => Auth::id()]) }}" class="btn btn-white shadow-sm rounded-pill px-3 fw-bold position-relative border-0" style="height: 42px; display: flex; align-items: center;">
        <i class="far fa-envelope text-dark me-1"></i>
        <span class="text-dark d-none d-md-inline small">الرسائل</span>
        {{-- نقطة حمراء لو فيه رسائل غير مقروءة --}}
        @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
        @endif
    </a>

    {{-- الإشعارات (موجودة أصلاً) --}}
    <div class="dropdown">
        <button class="notification-trigger position-relative border-0 bg-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="far fa-bell"></i>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="notif-count">{{ auth()->user()->unreadNotifications->count() }}</span>
            @endif
        </button>

        <div class="dropdown-menu dropdown-menu-start notification-panel shadow-xl border-0 p-0">
            <div class="notif-header p-3 bg-light d-flex justify-content-between align-items-center border-bottom">
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
            <a href="{{ route('notifications.index') }}" class="notif-footer text-center p-2 d-block bg-light text-muted small text-decoration-none border-top">عرض الكل</a>
        </div>
    </div>

    <a href="{{ route('projects.create') }}" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm d-none d-md-block">
        <i class="fas fa-plus me-1"></i> أضف مشروعاً
    </a>
</div>
                    </div>
                </div>

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

                <div class="glass-card mb-4 overflow-hidden border-0">
                    <div class="p-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">مشاريعك الحالية <span class="badge bg-light text-dark fw-normal ms-2 small">آخر 5</span></h5>
                        <a href="{{ route('projects.my_projects') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 text-decoration-none">كل المشاريع</a>
                    </div>
                    <div class="table-responsive px-4 pb-4">
                        <table class="table custom-table align-middle">
                            <thead>
                                <tr>
                                    <th>المشروع</th>
                                    <th>العروض</th>
                                    <th>الميزانية</th>
                                    <th>الحالة</th>
                                    <th class="text-center">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myProjects->take(5) as $project)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="project-thumb me-3">
                                                <i class="fas fa-folder text-primary opacity-50"></i>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-dark mb-0">{{ Str::limit($project->title, 25) }}</div>
                                                <small class="text-muted extra-small">تاريخ النشر: {{ $project->created_at->format('Y/m/d') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="avatar-stack">
                                             <div class="offer-count-pill">{{ $project->proposals_count ?? 0 }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="price-dynamic">
                                            <span class="amount">{{ number_format($project->price) }}</span>
                                            <span class="curr text-success small fw-bold">{{ $project->currency ?? 'ج.م' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = $project->status == 'completed' ? 'bg-success' : ($project->admin_status == 'pending' ? 'bg-warning' : 'bg-primary');
                                            $statusText = $project->status == 'completed' ? 'مكتمل' : ($project->admin_status == 'pending' ? 'مراجعة' : 'نشط');
                                        @endphp
                                        <span class="badge {{ $statusClass }}-soft {{ $statusClass }}-text rounded-pill px-3 py-2 extra-small">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('projects.show', $project->id) }}" class="btn-action-circle shadow-sm">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">لا توجد مشاريع مضافة بعد.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="glass-card p-4 h-100">
                            <h6 class="fw-bold mb-4 text-dark"><i class="fas fa-history text-muted me-2"></i> الجدول الزمني للنشاط</h6>
                            <div class="modern-timeline">
                                @forelse(auth()->user()->notifications->take(3) as $notif)
                                <div class="timeline-step">
                                    <div class="step-icon"></div>
                                    <div class="step-content">
                                        <p class="mb-0 fw-bold small text-dark">{{ $notif->data['title'] ?? 'تحديث' }}</p>
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
                                <p class="text-white opacity-75 small mb-0">قم بإكمال ملفك الشخصي بنسبة 100% لزيادة ثقة المستقلين والحصول على عروض بجودة أفضل بنسبة 40%.</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="btn btn-white-blur btn-sm rounded-pill fw-bold mt-4">تطوير الملف الشخصي</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Glassmorphism Theme Variables */
:root {
    --glass-bg: rgba(255, 255, 255, 0.85);
    --glass-border: rgba(255, 255, 255, 0.4);
    --primary-main: #10b981;
    --primary-soft: #ecfdf5;
    --navy-main: #0f172a;
    --bg-light-soft: #f8fafc;
}

body {
    background: #f1f5f9;
    background-image: radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.05) 0px, transparent 50%),
                      radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.05) 0px, transparent 50%);
    min-height: 100vh;
}

/* Sidebar Styling - Lower Z-index than dropdown */
.sidebar-glass {
    background: var(--glass-bg);
    backdrop-filter: blur(15px);
    border: 1px solid var(--glass-border);
    border-radius: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.03);
    z-index: 1000;
}

.glass-card {
    background: #fff;
    border-radius: 24px;
    border: 1px solid rgba(0,0,0,0.02);
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    position: relative;
}

/* Profile Styling */
.profile-img-wrapper {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto;
}
.profile-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 40px;
    border: 4px solid #fff;
}
.edit-overlay {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: var(--primary-main);
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
    border: 3px solid #fff;
    transition: 0.3s;
}
.edit-overlay:hover { transform: scale(1.1) rotate(10deg); }

/* Navigation Links */
.nav-link-custom {
    display: flex;
    align-items: center;
    padding: 12px 18px;
    color: #64748b;
    text-decoration: none;
    border-radius: 16px;
    margin-bottom: 8px;
    transition: 0.3s;
    font-weight: 600;
}
.nav-link-custom:hover {
    background: var(--primary-soft);
    color: var(--primary-main);
}
.nav-link-custom.active {
    background: var(--primary-main);
    color: white !important;
    box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
}
.nav-icon { width: 30px; font-size: 18px; }

/* Notification Trigger & Panel - Fix Z-index */
.header-actions .dropdown {
    position: relative;
    z-index: 2000; /* Higher than sidebar */
}

.notification-trigger {
    width: 45px;
    height: 45px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #64748b;
    transition: 0.3s;
    cursor: pointer;
}
.notification-trigger:hover { background: #f8fafc; color: var(--primary-main); }

.notif-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
}

.notification-panel {
    width: 350px;
    border-radius: 20px;
    position: absolute;
    top: 100%;
    margin-top: 15px !important;
    left: 0 !important;
    right: auto !important;
}

.notif-item.unread { background: #f0fdf4; }

.bg-success-soft { background: rgba(16, 185, 129, 0.1); }
.notif-icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Stats Cards */
.stat-glass-card {
    background: #fff;
    border-radius: 20px;
    transition: 0.3s;
    border: 1px solid rgba(0,0,0,0.01);
}
.stat-glass-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.05); }
.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

/* Table Styling */
.custom-table thead th {
    border: none;
    background: var(--bg-light-soft);
    color: #94a3b8;
    text-transform: uppercase;
    font-size: 11px;
    padding: 15px;
}
.price-dynamic .amount { font-size: 18px; font-weight: 800; color: var(--navy-main); }

.btn-action-circle {
    width: 35px;
    height: 35px;
    background: #fff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-main);
    text-decoration: none;
    transition: 0.3s;
    border: 1px solid #f1f5f9;
}
.btn-action-circle:hover { background: var(--primary-main); color: #fff; }

.offer-count-pill {
    background: var(--bg-light-soft);
    padding: 5px 12px;
    border-radius: 10px;
    font-weight: bold;
    color: var(--navy-main);
}

/* Special Cards */
.wallet-widget {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 20px;
}
.btn-glass-white {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
}
.btn-glass-white:hover { background: white; color: var(--primary-main); }

.promo-card { background: #1e293b; border-radius: 24px; }
.promo-shapes {
    position: absolute;
    top: -50px;
    right: -50px;
    width: 150px;
    height: 150px;
    background: rgba(16, 185, 129, 0.2);
    border-radius: 50%;
}
.btn-white-blur {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(5px);
    color: white;
    border: 1px solid rgba(255,255,255,0.2);
    text-decoration: none;
}
.btn-white-blur:hover { background: white; color: #1e293b; }

.extra-small { font-size: 11px; }

/* Timeline Modern */
.modern-timeline { padding-right: 15px; position: relative; }
.timeline-step { position: relative; padding-bottom: 25px; padding-right: 25px; }
.timeline-step::before {
    content: '';
    position: absolute;
    right: 5px;
    top: 0;
    height: 100%;
    width: 2px;
    background: #f1f5f9;
}
.step-icon {
    position: absolute;
    right: 0;
    top: 5px;
    width: 12px;
    height: 12px;
    background: var(--primary-main);
    border-radius: 50%;
    border: 3px solid #fff;
    z-index: 2;
}

/* Badge colors */
.bg-success-soft-text { color: #10b981; }
.bg-warning-soft { background: rgba(245, 158, 11, 0.1); }
.bg-warning-text { color: #f59e0b; }
.bg-primary-soft { background: rgba(59, 130, 246, 0.1); }
.bg-primary-text { color: #3b82f6; }

/* Scrollbar styling */
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: #f1f1f1; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تأثير الظهور التدريجي للكروت
        const cards = document.querySelectorAll('.stat-glass-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease-in-out, transform 0.5s ease-in-out';
                card.style.opacity = '1';
            }, index * 100);
        });
    });
</script>

@endsection
