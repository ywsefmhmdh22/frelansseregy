@extends('layouts.master')

@section('content')
@php
    $user = auth()->user();
    // جعل الرصيد دائماً منسوباً للدولار وإلغاء الاعتماد على متغير العملة القادم من الكونترولر
    $walletBalance = $walletBalance ?? 0;
    $orders = $orders ?? collect();
    $myProjects = $myProjects ?? collect();
    $currency = '$'; // تثبيت العملة دولار

    $profilePhoto = $user->profile_image
        ? asset('storage/'.$user->profile_image)
        : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=10b981&color=fff';
@endphp

<div class="dashboard-container py-4 py-lg-5 px-2 px-lg-5" dir="rtl">
    <div class="container-fluid">
        <div class="row g-4">
            {{-- Sidebar Area --}}
            <div class="col-lg-3">
                <aside class="sidebar-glass p-4 sticky-top shadow-sm" style="top: 20px; z-index: 100;">
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

                    <div class="wallet-widget mt-5 p-4 text-center text-white shadow-lg position-relative overflow-hidden">
                        <div class="wallet-bg-icon"><i class="fas fa-wallet"></i></div>
                        <p class="small opacity-75 mb-1 position-relative">الرصيد المتاح</p>
                        <h3 class="fw-bold mb-3 position-relative">{{ number_format($walletBalance, 2) . ' ' . $currency }}</h3>
                        <a href="{{ route('wallet.deposit') }}" class="btn btn-glass-white btn-sm w-100 rounded-pill fw-bold position-relative">شحن الرصيد</a>
                    </div>
                </aside>
            </div>

            {{-- Main Content Area --}}
            <div class="col-lg-9">
                <header class="top-header-bar d-flex justify-content-between align-items-center mb-4 p-3 glass-card shadow-sm border-0">
                    <div class="welcome-text">
                        <h4 class="fw-bold text-dark mb-0">أهلاً، {{ explode(' ', $user->name)[0] }} <span class="wave">👋</span></h4>
                        <p class="text-muted small mb-0 d-none d-sm-block">إليك ملخص سريع لنشاطك اليوم.</p>
                    </div>

                    <div class="header-actions d-flex align-items-center gap-2 gap-md-3">
                        <a href="{{ route('projects.create') }}" class="btn btn-success rounded-pill px-3 px-md-4 fw-bold shadow-sm transition-hover btn-add-project">
                            <i class="fas fa-plus me-md-1"></i> <span class="d-none d-md-inline">أضف مشروعاً</span>
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
                                            <p class="text-muted small">لا توجد إشعارات حالياً</p>
                                        </div>
                                    @endforelse
                                </div>
                                <a href="{{ route('notifications.index') }}" class="notif-footer text-center p-2 d-block bg-light text-muted small text-decoration-none border-top rounded-bottom-4">عرض الكل</a>
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
                            ['label' => 'العروض المستلمة', 'val' => $myProjects->sum('proposals_count'), 'icon' => 'fas fa-paper-plane', 'color' => '#8b5cf6', 'gradient' => 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)']
                        ];
                    @endphp
                    @foreach($stats_items as $stat)
                    <div class="col-6 col-md-3">
                        <div class="stat-glass-card p-3 h-100 shadow-sm border-0 position-relative overflow-hidden">
                            <div class="stat-viz-bg" style="background: {{ $stat['color'] }}"></div>
                            <div class="stat-icon-new shadow-sm mb-2" style="background: {{ $stat['gradient'] }}">
                                <i class="{{ $stat['icon'] }}"></i>
                            </div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $stat['val'] }}</h4>
                            <p class="text-muted extra-small mb-0 fw-bold">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- الخدمات المشتراة --}}
                <section class="glass-card mb-4 overflow-hidden border-0 shadow-sm">
                    <div class="p-4 d-flex justify-content-between align-items-center border-bottom bg-light-soft">
                        <h5 class="fw-bold mb-0 text-dark px-2">الخدمات المشتراة <i class="fas fa-shopping-bag ms-2 text-success opacity-50"></i></h5>
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
                                            <div class="project-icon-box ms-3 d-none d-sm-flex">
                                                <i class="fas fa-cart-plus text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-0">{{ Str::limit($order->service->title ?? 'خدمة رقم ' . $order->service_id, 25) }}</div>
                                                <small class="text-muted extra-small">بواسطة: {{ $order->seller->name ?? 'مستقل' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell text-muted small">{{ $order->created_at->format('Y/m/d') }}</td>
                                    <td>
                                        <div class="price-tag-modern">
                                            <span class="amount">{{ number_format($order->price, 2) }}</span>
                                            <span class="currency">{{ $currency }}</span>
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

                                            // تحسين عرض حالة الإلغاء نتيجه نزاع
                                            if ($order->status == 'cancelled' && $order->admin_status == 'rejected') {
                                                $currentStatus = ['horror', 'ملغى بقرار الإدارة'];
                                            }
                                        @endphp
                                        <span class="badge-status bg-{{$currentStatus[0]}}">
                                            <span class="dot"></span>
                                            @if($currentStatus[0] == 'horror') <i class="fas fa-gavel me-1 extra-small"></i> @endif
                                            {{$currentStatus[1]}}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            @if($order->status == 'delivered')
                                                <a href="{{ route('orders.complete.view', $order->id) }}" class="btn btn-xs btn-success rounded-pill px-3 fw-bold">قبول</a>
                                            @endif

                                            {{-- زر تحكيم الإدارة للخدمات --}}
                                            @if(!in_array($order->status, ['completed', 'disputed', 'cancelled']))
                                            <button type="button"
                                                    class="btn btn-xs btn-outline-danger rounded-pill px-2 fw-bold"
                                                    onclick="openDisputeModal('{{ $order->id }}', 'service')">
                                                <i class="fas fa-gavel"></i> تحكيم
                                            </button>
                                            @endif

                                            <a href="{{ route('orders.show', $order->id) }}" class="btn-action-view" title="عرض"><i class="fas fa-eye"></i></a>
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
                                        <div class="fw-bold text-dark mb-0">{{ Str::limit($project->title, 30) }}</div>
                                        <small class="text-muted extra-small d-block mt-1"><i class="far fa-clock me-1"></i> منذ {{ $project->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="proposal-count">
                                            <span class="count">{{ $project->proposals_count ?? 0 }}</span>
                                            <span class="label">عرض</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $sIcon = '';
                                            if($project->admin_status == 'pending') { $sClass = 'warning'; $sText = 'مراجعة'; }
                                            elseif($project->status == 'open') { $sClass = 'success'; $sText = 'نشط'; }
                                            elseif($project->status == 'disputed') { $sClass = 'danger'; $sText = 'تحكيم'; }
                                            // تحسين عرض حالة المشروع الملغى نتيجه نزاع وبقرار إداري
                                            elseif($project->status == 'cancelled' && $project->admin_status == 'rejected') {
                                                $sClass = 'horror'; $sText = 'مغلق (نزاع)'; $sIcon = '<i class="fas fa-gavel me-1 extra-small"></i>';
                                            }
                                            else { $sClass = 'secondary'; $sText = 'مغلق'; }
                                        @endphp
                                        <span class="badge-status bg-{{$sClass}}">{!! $sIcon !!}{{ $sText }}</span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            {{-- زر تحكيم الإدارة للمشاريع --}}
                                            @if(!in_array($project->status, ['completed', 'disputed', 'cancelled']))
                                            <button type="button"
                                                    class="btn btn-xs btn-outline-danger rounded-pill px-3 fw-bold"
                                                    onclick="openDisputeModal('{{ $project->id }}', 'project')">
                                                <i class="fas fa-balance-scale"></i> تحكيم الإدارة
                                            </button>
                                            @endif

                                            <a href="{{ route('projects.show', $project->id) }}" class="btn-action-view text-success border-success border-opacity-25" title="عرض">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <p class="text-muted small">ابدأ الآن وأضف أول مشروع لك!</p>
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
                <div class="icon-box-dispute mb-3 mx-auto">
                    <i class="fas fa-gavel text-danger fa-3x"></i>
                </div>
                <h4 class="fw-bold mb-3">طلب تحكيم الإدارة</h4>
                <div class="alert alert-warning border-0 small text-end" style="background: #fffbeb;">
                    <ul class="mb-0">
                        <li>بمجرد الإرسال، ستتدخل الإدارة لمراجعة كافة المحادثات والملفات.</li>
                        <li>سيتم التواصل مع الطرفين هاتفياً أو عبر الرسائل للتحقق.</li>
                        <li>سيتم الحكم بشفافية مطلقة (إنهاء الصفقة أو إلغاؤها كلياً).</li>
                        <li>تضمن الإدارة عودة المستحقات لصاحب الحق بناءً على تقييم الموقف.</li>
                    </ul>
                </div>
                <p class="fw-bold text-dark mt-3">هل أنت متأكد من رغبتك في تصعيد النزاع للتحكيم؟</p>

                <form id="disputeForm" action="{{ route('dispute.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="item_id" id="dispute_item_id">
                    <input type="hidden" name="type" id="dispute_type">
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-danger rounded-pill py-2 fw-bold">نعم، أوافق على تحكيم الإدارة</button>
                        <button type="button" class="btn btn-light rounded-pill py-2 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
    --horror-red: #4c0505; /* لون أحمر داكن جداً ومرعب */
    --horror-light: #fecaca;
}
body { background-color: #f1f5f9; font-family: 'Cairo', sans-serif; color: var(--dark); }
.sidebar-glass { background: var(--glass); backdrop-filter: blur(12px); border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.6); }
.glass-card { background: white; border-radius: 20px; border: 1px solid rgba(226, 232, 240, 0.7); }
.profile-img-wrapper { width: 100px; height: 100px; margin: 0 auto; position: relative; }
.profile-main-img { width: 100%; height: 100%; object-fit: cover; border-radius: 28px; border: 4px solid #fff; }
.edit-overlay { position: absolute; bottom: -2px; left: -2px; background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid #fff; }
.nav-link-custom { display: flex; align-items: center; padding: 12px 16px; color: var(--slate); text-decoration: none; border-radius: 14px; margin-bottom: 6px; transition: 0.3s; font-weight: 600; }
.nav-link-custom:hover { background: #f1f5f9; color: var(--primary); }
.nav-link-custom.active { background: var(--primary); color: white !important; }
.wallet-widget { background: linear-gradient(135deg, #064e3b 0%, #10b981 100%); border-radius: 24px; color: white; }
.wallet-bg-icon { position: absolute; right: -10px; bottom: -10px; font-size: 4rem; opacity: 0.1; transform: rotate(-15deg); }
.btn-glass-white { background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: white; }
.stat-glass-card { background: white; border-radius: 20px; position: relative; }
.stat-icon-new { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; }
.custom-table thead th { background: #f8fafc; font-size: 0.75rem; padding: 15px; }
.badge-status { padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
.badge-status.bg-success { background: #d1fae5 !important; color: #065f46; }
.badge-status.bg-warning { background: #fef3c7 !important; color: #92400e; }
.badge-status.bg-danger { background: #fee2e2 !important; color: #b91c1c; }
/* ستايل الرعب الجديد لحكم الإدارة القاطع */
.badge-status.bg-horror { background: var(--horror-red) !important; color: white; border: 1px solid #b91c1c; box-shadow: 0 0 5px rgba(185, 28, 28, 0.5); }
.badge-status.bg-horror .dot { background-color: #fee2e2; animation: pulse-red 1.5s infinite; }

.price-tag-modern { font-weight: 800; color: var(--primary-dark); }
.wave { display: inline-block; animation: wave-animation 2.5s infinite; transform-origin: 70% 70%; }
.btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.icon-box-dispute { width: 80px; height: 80px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

@keyframes wave-animation { 0%, 100%, 60% { transform: rotate(0deg) } 10%, 30% { transform: rotate(14deg) } 20% { transform: rotate(-8deg) } 40% { transform: rotate(-4deg) } 50% { transform: rotate(10deg) } }
@keyframes pulse-red { 0% { box-shadow: 0 0 0 0 rgba(254, 226, 226, 0.7); } 70% { box-shadow: 0 0 0 5px rgba(254, 226, 226, 0); } 100% { box-shadow: 0 0 0 0 rgba(254, 226, 226, 0); } }

@media (max-width: 768px) { .welcome-text h4 { font-size: 1.1rem; } .sidebar-glass { margin-bottom: 20px; } }
</style>

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
        return {
            init: () => {
                updateMessagesCount();
                setInterval(updateMessagesCount, 20000);
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el) });
            }
        };
    })();
    document.addEventListener('DOMContentLoaded', DashboardManager.init);
</script>
@endsection
