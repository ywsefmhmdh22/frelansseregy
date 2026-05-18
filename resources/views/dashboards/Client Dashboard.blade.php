@extends('layouts.master')

@section('content')
@php
    $user = auth()->user();
    // تم ضبط المتغيرات لتستقبل البيانات من الكنترولر بشكل صحيح دون تصفيرها
    $orders = $orders ?? collect();
    $myProjects = $myProjects ?? collect();

    // التعديل الجديد: عرض الصورة من Laravel Cloud (S3) لضمان التوافق
    $profilePhoto = $user->profile_image
        ? Storage::disk('s3')->url($user->profile_image)
        : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=10b981&color=fff';
@endphp

{{-- إضافة مكتبة Axios لضمان الرفع السحابي --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
{{-- إضافة Animate.css للفخامة --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');
:root {
    --primary: #10b981;
    --primary-dark: #059669;
    --primary-light: #d1fae5;
    --secondary: #6366f1;
    --dark: #1e293b;
    --slate: #64748b;
    --glass: rgba(255, 255, 255, 0.9);
    --horror-red: #4c0505;
    --horror-light: #fecaca;
    /* أحجام مرنة مرعبة */
    --stat-font: clamp(1.2rem, 2.5vw, 1.8rem);
}

body { background-color: #f1f5f9; font-family: 'Cairo', sans-serif; color: var(--dark); overflow-x: hidden; }

/* تصميم الحاوية المتجاوب */
.dashboard-container {
    width: 100%;
    padding: clamp(0.5rem, 2vw, 2.5rem) !important;
}

.sidebar-glass {
    background: var(--glass);
    backdrop-filter: blur(12px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.6);
    transition: all 0.3s ease;
}

.glass-card { background: white; border-radius: 20px; border: 1px solid rgba(226, 232, 240, 0.7); transition: transform 0.3s ease; }

/* تحكم مرعب في صورة البروفايل */
.profile-img-wrapper { width: clamp(80px, 10vw, 100px); height: clamp(80px, 10vw, 100px); margin: 0 auto; position: relative; }
.profile-main-img { width: 100%; height: 100%; object-fit: cover; border-radius: 28px; border: 4px solid #fff; transition: opacity 0.3s; }

.edit-overlay { position: absolute; bottom: -2px; left: -2px; background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid #fff; z-index: 5; }

.nav-link-custom { display: flex; align-items: center; padding: 12px 16px; color: var(--slate); text-decoration: none; border-radius: 14px; margin-bottom: 6px; transition: 0.3s; font-weight: 600; }
.nav-link-custom:hover { background: #f1f5f9; color: var(--primary); transform: translateX(-5px); }
.nav-link-custom.active { background: var(--primary); color: white !important; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3); }

.wallet-widget { background: linear-gradient(135deg, #064e3b 0%, #10b981 100%); border-radius: 24px; color: white; }
.wallet-bg-icon { position: absolute; right: -10px; bottom: -10px; font-size: 4rem; opacity: 0.1; transform: rotate(-15deg); }

.btn-glass-white { background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: white; }
.stat-glass-card { background: white; border-radius: 20px; position: relative; transition: 0.3s; }
.stat-glass-card:hover { transform: translateY(-5px); }

.stat-icon-new { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; }

.custom-table thead th { background: #f8fafc; font-size: 0.75rem; padding: 15px; }

/*badges*/
.badge-status { padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
.badge-status.bg-success { background: #d1fae5 !important; color: #065f46; }
.badge-status.bg-warning { background: #fef3c7 !important; color: #92400e; }
.badge-status.bg-danger { background: #fee2e2 !important; color: #b91c1c; }
.badge-status.bg-horror { background: var(--horror-red) !important; color: white; border: 1px solid #b91c1c; }

.price-tag-modern { font-weight: 800; color: var(--primary-dark); font-size: 1.1rem; }
.wave { display: inline-block; animation: wave-animation 2.5s infinite; transform-origin: 70% 70%; }

/* الإشعارات المتقدمة */
.notif-item { transition: 0.3s; }
.notif-item.unread { background-color: #f0fdf4 !important; border-right: 4px solid var(--primary) !important; }
.notif-count { position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 0.65rem; padding: 2px 6px; border-radius: 50px; border: 2px solid white; font-weight: 900; }

/* Media Queries للتوافق المرعب */
@media (max-width: 991.98px) {
    .sidebar-glass { position: relative !important; top: 0 !important; margin-bottom: 2rem; }
    .top-header-bar { flex-direction: column; gap: 1rem; text-align: center; }
}

@media (max-width: 576px) {
    .stat-glass-card h4 { font-size: 1.2rem; }
    .custom-table { font-size: 0.8rem; }
    .btn-add-project span { display: none; }
    .action-btn-head span { display: none; }
}

@keyframes wave-animation { 0%, 100%, 60% { transform: rotate(0deg) } 10%, 30% { transform: rotate(14deg) } 20% { transform: rotate(-8deg) } 40% { transform: rotate(-4deg) } 50% { transform: rotate(10deg) } }
</style>

<div class="dashboard-container py-4 py-lg-5 animate__animated animate__fadeIn" dir="rtl">
    <div class="container-fluid">
        <div class="row g-4">
            {{-- Sidebar Area --}}
            <div class="col-lg-3">
                <aside class="sidebar-glass p-4 sticky-top shadow-sm" style="top: 20px; z-index: 100;">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block profile-ring" id="profileContainer">
                            <form id="profileImageForm" enctype="multipart/form-data">
                                @csrf
                                <div class="profile-img-wrapper">
                                    <img src="{{ $profilePhoto }}"
                                         class="profile-main-img shadow-lg"
                                         id="profilePreview"
                                         alt="{{ $user->name }}">

                                    <label for="profile_image_input" class="edit-overlay" title="تغيير الصورة">
                                        <i class="fas fa-camera" id="cameraIcon"></i>
                                        <i class="fas fa-circle-notch fa-spin d-none" id="uploadSpinner"></i>
                                    </label>
                                    <input type="file" id="profile_image_input" name="profile_image" class="d-none" accept="image/*">
                                </div>
                            </form>
                            <div class="status-pulse" data-bs-toggle="tooltip" title="متصل الآن"></div>
                        </div>
                        <h5 class="fw-bold mt-3 mb-0 text-dark">{{ $user->name }}</h5>
                        <div class="badge bg-soft-success text-success px-3 rounded-pill mt-2 fw-bold">
                            عضو بلاتيني <i class="fas fa-crown ms-1"></i>
                        </div>
                    </div>

                    <nav class="nav-menu mt-4">
                        <a href="{{ route('client.dashboard') }}" class="nav-link-custom {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="fas fa-th-large"></i></div>
                            <span>الرئيسية</span>
                        </a>

                        <a href="{{ route('purchased.services') }}" class="nav-link-custom {{ request()->routeIs('purchased.services') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="fas fa-shopping-cart"></i></div>
                            <span>الخدمات المشتراة</span>
                        </a>

                        <a href="{{ route('wallet.index') }}" class="nav-link-custom {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="fas fa-wallet"></i></div>
                            <span>المحفظة الرقمية</span>
                        </a>

                        <hr class="my-3 opacity-10">

                        <a href="{{ route('profile.settings') }}" class="nav-link-custom {{ request()->routeIs('profile.settings') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="fas fa-user-cog"></i></div>
                            <span>إعدادات الحساب</span>
                        </a>
                    </nav>

                    {{-- التعديل الجوهري لقراءة الرصيد المتفورمت والجاهز من الباك إند --}}
                    <div class="wallet-widget mt-5 p-4 text-center text-white shadow-lg position-relative overflow-hidden">
                        <div class="wallet-bg-icon"><i class="fas fa-wallet"></i></div>
                        <p class="small opacity-75 mb-1 position-relative">الرصيد المتاح</p>
                        <h3 class="fw-bold mb-3 position-relative" style="font-size: var(--stat-font);">{{ $formattedBalance }}</h3>
                        <a href="{{ route('wallet.deposit') }}" class="btn btn-glass-white btn-sm w-100 rounded-pill fw-bold position-relative">شحن الرصيد</a>
                    </div>
                </aside>
            </div>

            {{-- Main Content Area --}}
            <div class="col-lg-9">
                <header class="top-header-bar d-flex justify-content-between align-items-center mb-4 p-3 glass-card shadow-sm border-0">
                    <div class="welcome-text text-end">
                        <h4 class="fw-bold text-dark mb-0">أهلاً، {{ explode(' ', $user->name)[0] }} <span class="wave">👋</span></h4>
                        <p class="text-muted small mb-0 d-none d-sm-block">إليك ملخص كامل لنشاطك المالي والتقني.</p>
                    </div>

                    <div class="header-actions d-flex align-items-center gap-2 gap-md-3">
                        <a href="{{ route('projects.create') }}" class="btn btn-success rounded-pill px-3 px-md-4 fw-bold shadow-sm transition-hover btn-add-project">
                            <i class="fas fa-plus me-md-1"></i> <span>أضف مشروعاً</span>
                        </a>

                        <a href="{{ route('messages.chat', ['user' => $user->id]) }}" class="btn btn-white shadow-sm rounded-circle rounded-md-pill px-md-3 fw-bold position-relative border border-light action-btn-head">
                            <i class="far fa-envelope text-dark"></i>
                            <span class="text-dark d-none d-md-inline ms-1 small">الرسائل</span>
                            <span id="unread-messages-count-global" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none border border-2 border-white animate__animated animate__bounceIn" style="font-size: 0.65rem;">0</span>
                        </a>

                        <div class="dropdown">
                            <button class="notification-trigger position-relative border-0 bg-white shadow-sm rounded-circle p-2 action-btn-head" type="button" data-bs-toggle="dropdown">
                                <i class="far fa-bell text-dark"></i>
                                @if($user->unreadNotifications->count() > 0)
                                    <span class="notif-count">{{ $user->unreadNotifications->count() }}</span>
                                @endif
                            </button>

                            <div class="dropdown-menu dropdown-menu-start notification-panel shadow-xl border-0 p-0 rounded-4 mt-2" style="width: clamp(300px, 80vw, 380px);">
                                <div class="notif-header p-3 bg-light d-flex justify-content-between align-items-center border-bottom rounded-top-4">
                                    <span class="fw-bold">مركز الإشعارات</span>
                                    <a href="{{ route('notifications.markAllRead') }}" class="small text-success text-decoration-none fw-bold">تحديد الكل كمقروء</a>
                                </div>
                                <div class="notif-body scrollbar-thin" style="max-height: 400px; overflow-y: auto;">
                                    @forelse($user->notifications as $notif)
                                        <div class="notif-item {{ $notif->read_at ? '' : 'unread' }} p-3 border-bottom d-flex gap-3 align-items-start position-relative text-end">
                                            <div class="notif-icon-circle {{ $notif->read_at ? 'bg-light text-muted' : 'bg-success-soft text-success' }}" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #f0fdf4;">
                                                <i class="{{ $notif->data['icon'] ?? 'fas fa-shopping-cart text-success' }}"></i>
                                            </div>
                                            <div class="notif-content flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <p class="mb-0 small fw-bold text-dark">{{ $notif->data['title'] ?? 'عملية شراء ناجحة' }}</p>
                                                    <span class="extra-small text-muted" style="font-size: 0.6rem;"><i class="far fa-clock"></i> {{ $notif->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="mb-1 extra-small text-muted" style="line-height: 1.4;">{{ $notif->data['message'] }}</p>
                                                @if(isset($notif->data['seller_name']))
                                                    <div class="d-flex justify-content-between mt-2">
                                                        <span class="badge bg-light text-dark extra-small">البائع: {{ $notif->data['seller_name'] }}</span>
                                                        <span class="text-success fw-bold extra-small">{{ number_format($notif->data['amount'], 2) }}$</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-5 text-center">
                                            <i class="fas fa-bell-slash text-muted opacity-25 mb-3 fa-3x"></i>
                                            <p class="text-muted small">لا توجد إشعارات جديدة حالياً</p>
                                        </div>
                                    @endforelse
                                </div>
                                <a href="{{ route('notifications.index') }}" class="notif-footer text-center p-2 d-block bg-light text-muted small text-decoration-none border-top rounded-bottom-4 fw-bold">عرض كافة الإشعارات</a>
                            </div>
                        </div>
                    </div>
                </header>

                {{-- الإحصائيات --}}
                <div class="row g-3 mb-4">
                    @php
                        $stats_items = [
                            ['label' => 'المشاريع', 'val' => $stats['total_projects'] ?? $myProjects->count(), 'icon' => 'fas fa-briefcase', 'color' => '#3b82f6', 'gradient' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)'],
                            ['label' => 'تحت المراجعة', 'val' => $stats['pending_projects'] ?? $myProjects->where('admin_status', 'pending')->count(), 'icon' => 'fas fa-clock', 'color' => '#f59e0b', 'gradient' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)'],
                            ['label' => 'مكتملة', 'val' => $myProjects->where('status', 'completed')->count(), 'icon' => 'fas fa-check-double', 'color' => '#10b981', 'gradient' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)'],
                            ['label' => 'المصروفات', 'val' => number_format($orders->sum('price'), 2) . '$', 'icon' => 'fas fa-chart-pie', 'color' => '#8b5cf6', 'gradient' => 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)']
                        ];
                    @endphp
                    @foreach($stats_items as $stat)
                    <div class="col-6 col-md-3">
                        <div class="stat-glass-card p-3 h-100 shadow-sm border-0 position-relative overflow-hidden text-end">
                            <div class="stat-viz-bg" style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: {{ $stat['color'] }}"></div>
                            <div class="stat-icon-new shadow-sm mb-2" style="background: {{ $stat['gradient'] }}">
                                <i class="{{ $stat['icon'] }}"></i>
                            </div>
                            <h4 class="fw-bold mb-0 text-dark" style="font-size: var(--stat-font);">{{ $stat['val'] }}</h4>
                            <p class="text-muted extra-small mb-0 fw-bold">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- الخدمات المشتراة --}}
                <section class="glass-card mb-4 overflow-hidden border-0 shadow-sm">
                    <div class="p-4 d-flex justify-content-between align-items-center border-bottom bg-light-soft">
                        <h5 class="fw-bold mb-0 text-dark px-2">آخر المشتريات <i class="fas fa-shopping-bag ms-2 text-success opacity-50"></i></h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table align-middle mb-0 text-end">
                            <thead>
                                <tr>
                                    <th class="ps-4">الخدمة</th>
                                    <th class="d-none d-md-table-cell">تاريخ الشراء</th>
                                    <th>السعر</th>
                                    <th>حالة الطلب</th>
                                    <th class="text-center pe-4">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="project-icon-box ms-3 d-none d-sm-flex" style="background: #eff6ff; color: #3b82f6; width: 35px; height: 35px; border-radius: 10px; align-items: center; justify-content: center;">
                                                <i class="fas fa-cart-plus"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-0 small">{{ Str::limit($order->service->title ?? 'خدمة رقم ' . $order->service_id, 25) }}</div>
                                                <small class="text-muted extra-small">بواسطة: {{ $order->seller->name ?? 'مستقل' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell text-muted extra-small">{{ $order->created_at->format('Y/m/d') }}</td>
                                    <td>
                                        <div class="price-tag-modern">
                                            <span class="amount">{{ number_format($order->price, 2) }}</span>
                                            <span class="currency">$</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusMap = [
                                                'processing' => ['warning', 'جاري العمل'],
                                                'pending'    => ['secondary', 'معلق'],
                                                'delivered'  => ['info', 'تم التسليم'],
                                                'completed'  => ['success', 'مكتمل'],
                                                'disputed'   => ['danger', 'تحت التحكيم']
                                            ];
                                            $currentStatus = $statusMap[$order->status] ?? ['light', $order->status];

                                            if ($order->status == 'cancelled' && $order->admin_status == 'rejected') {
                                                $currentStatus = ['horror', 'ملغى بقرار الإدارة'];
                                            }
                                        @endphp
                                        <span class="badge-status bg-{{$currentStatus[0]}}">
                                            <span class="dot" style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                                            {{$currentStatus[1]}}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            @if($order->status == 'delivered')
                                                <a href="{{ route('orders.complete.view', $order->id) }}" class="btn btn-xs btn-success rounded-pill px-3 fw-bold">قبول</a>
                                            @endif
                                            <a href="{{ route('orders.show', $order->id) }}" class="btn border shadow-sm rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" title="عرض"><i class="fas fa-eye small text-primary"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <img src="https://cdn-icons-png.flaticon.com/512/11329/11329061.png" width="60" class="opacity-25 mb-3" alt="">
                                        <p class="text-muted small">لا توجد طلبات شراء حتى الآن.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- المشاريع الحالية --}}
                <section class="glass-card mb-4 overflow-hidden border-0 shadow-sm">
                    <div class="p-4 d-flex justify-content-between align-items-center border-bottom bg-light-soft">
                        <h5 class="fw-bold mb-0 text-dark px-2">مشاريعك المفتوحة <i class="fas fa-laptop-code ms-2 text-primary opacity-50"></i></h5>
                        <a href="{{ route('projects.my_projects') }}" class="btn btn-sm btn-link text-success fw-bold text-decoration-none">مشاهدة الكل <i class="fas fa-chevron-left small ms-1"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table align-middle mb-0 text-end">
                            <thead>
                                <tr>
                                    <th class="ps-4">عنوان المشروع</th>
                                    <th>العروض</th>
                                    <th>الحالة</th>
                                    <th class="text-center pe-4">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myProjects->take(5) as $project)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark mb-0 small">{{ Str::limit($project->title, 30) }}</div>
                                        <small class="text-muted extra-small d-block mt-1"><i class="far fa-clock me-1"></i> {{ $project->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="proposal-count" style="font-weight: 700;">
                                            <span class="count text-primary">{{ $project->proposals_count ?? 0 }}</span>
                                            <span class="label small text-muted">عرض</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $sIcon = '';
                                            if($project->admin_status == 'pending') { $sClass = 'warning'; $sText = 'مراجعة'; }
                                            elseif($project->status == 'open') { $sClass = 'success'; $sText = 'نشط'; }
                                            elseif($project->status == 'disputed') { $sClass = 'danger'; $sText = 'تحكيم'; }
                                            elseif($project->status == 'cancelled' && $project->admin_status == 'rejected') {
                                                $sClass = 'horror'; $sText = 'مغلق (نزاع)'; $sIcon = '<i class="fas fa-gavel me-1 extra-small"></i>';
                                            }
                                            else { $sClass = 'secondary'; $sText = 'مغلق'; }
                                        @endphp
                                        <span class="badge-status bg-{{$sClass}}">{!! $sIcon !!}{{ $sText }}</span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            @if(!in_array($project->status, ['completed', 'disputed', 'cancelled']))
                                            <button type="button"
                                                    class="btn btn-xs btn-outline-danger rounded-pill px-3 fw-bold"
                                                    onclick="openDisputeModal('{{ $project->id }}', 'project')">
                                                <i class="fas fa-balance-scale"></i> تحكيم
                                            </button>
                                            @endif

                                            <a href="{{ route('projects.show', $project->id) }}" class="btn border shadow-sm rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-chevron-left text-success small"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <p class="text-muted small">ابدأ الآن وأضف أول مشروع لك في Worklyday!</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

{{-- Modal التحكيم --}}
<div class="modal fade" id="disputeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4 pt-0">
                <div class="icon-box-dispute mb-3 mx-auto" style="width: 70px; height: 70px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-gavel text-danger fa-2x"></i>
                </div>
                <h4 class="fw-bold mb-3">طلب تحكيم الإدارة</h4>
                <div class="alert alert-warning border-0 small text-end" style="background: #fffbeb; font-size: 0.75rem;">
                    <ul class="mb-0">
                        <li>بمجرد الإرسال، ستتدخل الإدارة لمراجعة كافة المحادثات والملفات.</li>
                        <li>سيتم التواصل مع الطرفين للتحقق من جودة العمل.</li>
                        <li>سيتم الحكم بشفافية مطلقة لضمان حق المشتري والمستقل.</li>
                    </ul>
                </div>
                <p class="fw-bold text-dark mt-3 small">هل تريد تصعيد هذا النزاع للتحكيم الرسمي?</p>

                <form id="disputeForm" action="{{ route('dispute.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="item_id" id="dispute_item_id">
                    <input type="hidden" name="type" id="dispute_type">
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-danger rounded-pill py-2 fw-bold">نعم، أوافق على التحكيم</button>
                        <button type="button" class="btn btn-light rounded-pill py-2 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openDisputeModal(id, type) {
        document.getElementById('dispute_item_id').value = id;
        document.getElementById('dispute_type').value = type;
        var myModal = new bootstrap.Modal(document.getElementById('disputeModal'));
        myModal.show();
    }

    const DashboardManager = (() => {
        const updateMessagesCount = async () => {
            try {
                const response = await fetch('/messages/unread-count');
                const data = await response.json();
                const badge = document.getElementById('unread-messages-count-global');
                if (badge && data.count > 0) {
                    badge.innerText = data.count > 99 ? '99+' : data.count;
                    badge.classList.remove('d-none');
                }
            } catch (error) { console.warn('Messages update failed.'); }
        };

        const handleProfileImageUpload = () => {
            const fileInput = document.getElementById('profile_image_input');
            if (!fileInput) return;

            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('profile_image', file);
                formData.append('_token', '{{ csrf_token() }}');

                const preview = document.getElementById('profilePreview');
                const cameraIcon = document.getElementById('cameraIcon');
                const spinner = document.getElementById('uploadSpinner');
            });
        };
    })();
</script>
@endsection
