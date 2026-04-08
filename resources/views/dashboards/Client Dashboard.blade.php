@extends('layouts.master')

@section('content')
@php
    // تعريف المستخدم والبيانات الأساسية
    $user = auth()->user();
    $walletBalance = $walletBalance ?? 0;
    $orders = $orders ?? collect();
    $myProjects = $myProjects ?? collect();

    /** * ديناميكية العملة:
     * نعتمد الآن على walletCurrency المرسل من الكنترولر
     * وإذا لم يوجد، نستخدم القيمة الافتراضية
     */
    $currency = $walletCurrency ?? 'ج.م';

    // منطق جلب الصورة
    $profilePhoto = $user->profile_image
        ? asset('storage/'.$user->profile_image)
        : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=10b981&color=fff';
@endphp

<div class="dashboard-container py-5 px-lg-5" dir="rtl">
    <div class="container-fluid">
        <div class="row g-4">
            {{-- Sidebar Area --}}
            <div class="col-lg-3">
                <aside class="sidebar-glass p-4 sticky-top" style="top: 20px; z-index: 100;">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block profile-ring">
                            <form id="profileImageForm" action="{{ route('profile.update_image') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="profile-img-wrapper">
                                    <img src="{{ $profilePhoto }}"
                                         class="profile-main-img shadow-lg"
                                         id="profilePreview"
                                         alt="{{ $user->name }}">

                                    <label for="profile_image_input" class="edit-overlay" title="تغيير الصورة">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input type="file" id="profile_image_input" name="profile_image" class="d-none" onchange="document.getElementById('profileImageForm').submit();">
                                </div>
                            </form>
                            <div class="status-pulse" data-bs-toggle="tooltip" title="متصل الآن"></div>
                        </div>
                        <h5 class="fw-bold mt-3 mb-0 text-dark">{{ $user->name }}</h5>
                        <div class="badge bg-soft-success text-success px-3 rounded-pill mt-2">
                            عضو بلاتيني <i class="fas fa-crown ms-1"></i>
                        </div>
                    </div>

                    <nav class="nav-menu mt-4">
                        <a href="{{ route('client.dashboard') }}" class="nav-link-custom {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="fas fa-rocket"></i></div>
                            <span>الرئيسية</span>
                        </a>

                        <a href="{{ route('purchased.services') }}" class="nav-link-custom {{ request()->routeIs('purchased.services') ? 'active' : '' }}">
                            <div class="nav-icon"><i class="fas fa-shopping-bag"></i></div>
                            <span>الخدمات المشتراة</span>
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
                    </nav>

                    {{-- تم التعديل: المحفظة تستخدم الآن التنسيق القادم من الكنترولر --}}
                    <div class="wallet-widget mt-5 p-3 text-center text-white shadow-lg">
                        <p class="small opacity-75 mb-1">الرصيد المتاح</p>
                        <h3 class="fw-bold mb-3">{{ $formattedBalance ?? number_format($walletBalance, 2) . ' ' . $currency }}</h3>
                        <a href="{{ route('wallet.deposit') }}" class="btn btn-glass-white btn-sm w-100 rounded-pill fw-bold">شحن الرصيد</a>
                    </div>
                </aside>
            </div>

            {{-- Main Content Area --}}
            <div class="col-lg-9">
                <header class="top-header-bar d-flex justify-content-between align-items-center mb-4 p-3 glass-card shadow-sm">
                    <div class="welcome-text">
                        <h4 class="fw-bold text-dark mb-0">أهلاً، {{ explode(' ', $user->name)[0] }} ✨</h4>
                        <p class="text-muted small mb-0">لديك بعض التحديثات الجديدة اليوم.</p>
                    </div>

                    <div class="header-actions d-flex align-items-center gap-3">
                        <a href="{{ route('messages.chat', ['user' => $user->id]) }}" class="btn btn-white shadow-sm rounded-pill px-3 fw-bold position-relative border border-light" style="height: 42px; display: flex; align-items: center;">
                            <i class="far fa-envelope text-dark me-1"></i>
                            <span class="text-dark d-none d-md-inline small">الرسائل</span>
                            <span id="unread-messages-count-global"
                                  class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none border border-2 border-white animate__animated animate__bounceIn"
                                  style="font-size: 0.65rem; padding: 0.4em 0.6em; z-index: 5;">
                                0
                            </span>
                        </a>

                        <div class="dropdown">
                            <button class="notification-trigger position-relative border-0 bg-white shadow-sm rounded-circle p-2" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 42px; height: 42px;">
                                <i class="far fa-bell text-dark"></i>
                                @if($user->unreadNotifications->count() > 0)
                                    <span class="notif-count">{{ $user->unreadNotifications->count() }}</span>
                                @endif
                            </button>

                            <div class="dropdown-menu dropdown-menu-start notification-panel shadow-xl border-0 p-0 rounded-4 mt-2" style="width: 320px;">
                                <div class="notif-header p-3 bg-light d-flex justify-content-between align-items-center border-bottom rounded-top-4">
                                    <span class="fw-bold">الإشعارات</span>
                                    <a href="{{ route('notifications.markAllRead') }}" class="small text-success text-decoration-none">تحديد الكل كمقروء</a>
                                </div>
                                <div class="notif-body scrollbar-thin" style="max-height: 350px; overflow-y: auto;">
                                    @forelse($user->notifications->take(10) as $notif)
                                        <div class="notif-item {{ $notif->read_at ? '' : 'unread' }} p-3 border-bottom d-flex gap-3 align-items-start position-relative text-end">
                                            <div class="notif-icon-circle {{ $notif->read_at ? 'bg-light text-muted' : 'bg-success-soft text-success' }}">
                                                <i class="{{ $notif->data['icon'] ?? 'fas fa-info-circle' }}"></i>
                                            </div>
                                            <div class="notif-content flex-grow-1">
                                                <p class="mb-0 small fw-bold text-dark">{{ $notif->data['title'] ?? 'إشعار جديد' }}</p>
                                                <p class="mb-1 extra-small text-muted">{{ $notif->data['message'] ?? 'هناك تحديث جديد.' }}</p>
                                                <span class="extra-small opacity-50"><i class="far fa-clock me-1"></i> {{ $notif->created_at->diffForHumans() }}</span>
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

                        <a href="{{ route('projects.create') }}" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm d-none d-md-block transition-hover">
                            <i class="fas fa-plus me-1"></i> أضف مشروعاً
                        </a>
                    </div>
                </header>

                {{-- الخدمات المشتراة --}}
                <section class="glass-card mb-4 overflow-hidden border-0 shadow-sm">
                    <div class="p-4 d-flex justify-content-between align-items-center border-bottom bg-light-soft">
                        <h5 class="fw-bold mb-0 text-dark">الخدمات التي اشتريتها <span class="badge bg-white text-dark fw-normal ms-2 small border shadow-sm">متابعة الطلبات</span></h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table align-middle mb-0 text-end">
                            <thead>
                                <tr>
                                    <th class="ps-4">الخدمة / المستقل</th>
                                    <th>تاريخ الشراء</th>
                                    <th>السعر</th>
                                    <th>حالة الطلب</th>
                                    <th class="text-center pe-4">التحكم</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="project-icon-box ms-3">
                                                <i class="fas fa-shopping-cart text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-0">{{ Str::limit($order->service->title ?? 'خدمة رقم ' . $order->service_id, 30) }}</div>
                                                <small class="text-muted extra-small">المستقل: {{ $order->seller->name ?? 'مستقل' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $order->created_at->format('Y/m/d') }}</td>
                                    <td>
                                        <div class="price-tag fw-bold text-primary">
                                            {{-- تم التعديل: إذا كان لديك دالة فورمات في موديل الطلبات يفضل استخدامها --}}
                                            <span class="amount">{{ number_format($order->price, 2) }}</span>
                                            <span class="currency">{{ $order->currency ?? $currency }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusMap = [
                                                'processing' => ['warning', 'قيد التنفيذ', ''],
                                                'pending'    => ['secondary', 'بانتظار التأكيد', ''],
                                                'delivered'  => ['info', 'تم التسليم', 'animate-pulse'],
                                                'completed'  => ['success', 'مكتمل', '']
                                            ];
                                            $currentStatus = $statusMap[$order->status] ?? ['light', $order->status, ''];
                                        @endphp
                                        <span class="badge bg-{{$currentStatus[0]}}-soft text-{{$currentStatus[0]}} rounded-pill px-3 py-2 {{$currentStatus[2]}}">
                                            {{$currentStatus[1]}}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        @if($order->status == 'delivered')
                                            <a href="{{ route('orders.complete.view', $order->id) }}" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm">
                                                قبول <i class="fas fa-check-double ms-1"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('orders.show', $order->id) }}" class="btn-action-view" data-bs-toggle="tooltip" title="عرض تفاصيل الطلب">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-box-open fa-3x opacity-20 mb-3"></i>
                                            <p class="text-muted small">لم تقم بشراء أي خدمات بعد.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- الإحصائيات --}}
                <div class="row g-3 mb-4 text-center">
                    @php
                        $stats_items = [
                            ['label' => 'المشاريع', 'val' => $stats['total_projects'] ?? $myProjects->count(), 'icon' => 'fas fa-layer-group', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.1)'],
                            ['label' => 'قيد المراجعة', 'val' => $stats['pending_projects'] ?? $myProjects->where('admin_status', 'pending')->count(), 'icon' => 'fas fa-hourglass-half', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)'],
                            ['label' => 'مكتملة', 'val' => $myProjects->where('status', 'completed')->count(), 'icon' => 'fas fa-check-circle', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)'],
                            ['label' => 'العروض', 'val' => $myProjects->sum('proposals_count'), 'icon' => 'fas fa-paper-plane', 'color' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.1)']
                        ];
                    @endphp
                    @foreach($stats_items as $stat)
                    <div class="col-6 col-md-3">
                        <div class="stat-glass-card p-3 h-100 shadow-sm border-0">
                            <div class="stat-icon mx-auto mb-2 shadow-sm" style="color: {{ $stat['color'] }}; background: {{ $stat['bg'] }}">
                                <i class="{{ $stat['icon'] }}"></i>
                            </div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $stat['val'] }}</h4>
                            <p class="text-muted extra-small mb-0 fw-bold">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- المشاريع الحالية بالميزانية الديناميكية --}}
                <section class="glass-card mb-4 overflow-hidden border-0 shadow-sm">
                    <div class="p-4 d-flex justify-content-between align-items-center border-bottom bg-light-soft">
                        <h5 class="fw-bold mb-0 text-dark">مشاريعك الحالية <span class="badge bg-white text-dark fw-normal ms-2 small border shadow-sm">آخر 5</span></h5>
                        <a href="{{ route('projects.my_projects') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 text-decoration-none fw-bold">كل المشاريع</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table align-middle mb-0 text-end">
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
                                        <div class="d-flex align-items-center">
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
                                        <div class="offer-badge shadow-sm">
                                            <span class="num">{{ $project->proposals_count ?? 0 }}</span>
                                            <span class="lab">عرض</span>
                                        </div>
                                    </td>
                                    <td>
                                        {{-- تم التعديل: استخدام formatted_price القادم من الكنترولر --}}
                                        <div class="price-tag fw-bold">
                                            <span class="amount">{{ $project->formatted_price }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            if($project->admin_status == 'pending') { $sClass = 'warning'; $sText = 'تحت المراجعة'; $sIcon = 'fa-clock'; }
                                            elseif($project->status == 'open') { $sClass = 'success'; $sText = 'نشط'; $sIcon = 'fa-bolt'; }
                                            elseif($project->status == 'in_progress') { $sClass = 'primary'; $sText = 'قيد التنفيذ'; $sIcon = 'fa-spinner fa-spin'; }
                                            else { $sClass = 'secondary'; $sText = 'مكتمل/مغلق'; $sIcon = 'fa-check'; }
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
                                        <p class="text-muted small">لا توجد مشاريع مضافة حالياً.</p>
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

<style>
/* (نفس الستايل الخاص بك مع إضافة بسيطة للعملة) */
:root {
    --primary-main: #10b981;
    --primary-hover: #059669;
    --primary-soft: #ecfdf5;
    --navy-main: #0f172a;
    --slate-text: #64748b;
    --border-light: rgba(226, 232, 240, 0.8);
    --glass-bg: rgba(255, 255, 255, 0.95);
}

body { background: #f8fafc; font-family: 'Cairo', sans-serif; overflow-x: hidden; }

/* تصميم الهيكل */
.sidebar-glass {
    background: var(--glass-bg);
    backdrop-filter: blur(15px);
    border-radius: 25px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
}

.glass-card {
    background: #fff;
    border-radius: 20px;
    border: 1px solid var(--border-light);
}

.profile-img-wrapper { width: 110px; height: 110px; margin: 0 auto; position: relative; }
.profile-main-img {
    width: 100%; height: 100%; object-fit: cover;
    border-radius: 30px; border: 4px solid #fff;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.profile-img-wrapper:hover .profile-main-img { transform: scale(1.05); }

.edit-overlay {
    position: absolute; bottom: -2px; left: -2px;
    background: var(--primary-main); color: white;
    width: 32px; height: 32px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; border: 3px solid #fff; transition: 0.3s;
}

.nav-link-custom {
    display: flex; align-items: center; padding: 12px 18px;
    color: var(--slate-text); text-decoration: none;
    border-radius: 14px; margin-bottom: 8px;
    transition: all 0.3s ease; font-weight: 600;
}
.nav-link-custom.active {
    background: var(--primary-main); color: white !important;
    box-shadow: 0 8px 15px rgba(16, 185, 129, 0.2);
}

.wallet-widget {
    background: linear-gradient(135deg, var(--primary-main) 0%, var(--primary-hover) 100%);
    border-radius: 22px; transition: 0.4s;
}
.btn-glass-white { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); color: white; backdrop-filter: blur(5px); }

.custom-table thead th {
    background: #fcfdfe; color: var(--slate-text);
    font-size: 0.85rem; text-transform: uppercase;
    border-bottom: 1px solid #f1f5f9; padding: 15px 10px;
}
.btn-action-view {
    width: 38px; height: 38px; background: #fff;
    border-radius: 12px; display: inline-flex;
    align-items: center; justify-content: center;
    color: var(--slate-text); border: 1px solid var(--border-light);
}

.status-pulse {
    position: absolute; bottom: 8px; right: 8px;
    width: 14px; height: 14px; background: var(--primary-main);
    border-radius: 50%; border: 2px solid white;
    animation: pulse 2s infinite;
}
@keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

.stat-glass-card { background: white; border-radius: 18px; transition: transform 0.3s ease; }
.stat-icon { width: 45px; height: 45px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

.price-tag .amount { font-size: 1rem; color: inherit; }
</style>

{{-- السكريبت الخاص بك كما هو --}}
<script>
    const DashboardManager = (() => {
        let lastMessagesCount = -1;

        const updateMessagesCount = async () => {
            try {
                const response = await fetch('/messages/unread-count');
                if (!response.ok) throw new Error('Network error');
                const data = await response.json();

                const badge = document.getElementById('unread-messages-count-global');
                if (!badge) return;

                const count = data.count;
                if (count > 0) {
                    badge.innerText = count > 99 ? '99+' : count;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }
                lastMessagesCount = count;
            } catch (error) { console.warn('Messages update failed.'); }
        };

        return {
            init: () => {
                updateMessagesCount();
                setInterval(updateMessagesCount, 15000);
            }
        };
    })();

    document.addEventListener('DOMContentLoaded', DashboardManager.init);
</script>
@endsection
