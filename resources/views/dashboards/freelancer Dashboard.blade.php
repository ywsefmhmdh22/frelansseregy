@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
{{-- مكتبة التنبيهات الاحترافية --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- مكتبة Axios للرفع السحابي --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

@php
    // التعديل الجديد: عرض صورة المستقل من Laravel Cloud (S3) لضمان التوافق
    $profilePhoto = $user->profile_image
        ? Storage::disk('s3')->url($user->profile_image)
        : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=6366f1&color=fff&size=150';
@endphp

<div class="container-fluid py-4 px-lg-5 animate__animated animate__fadeIn" id="dashboard-wrapper" dir="rtl">

    {{-- 1. قسم المحفظة والحالة المالية --}}
    <div class="wallet-banner mb-4 p-4 rounded-5 shadow-lg border-0 position-relative overflow-hidden text-white">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-md-8 text-end">
                <h6 class="opacity-75 mb-2 fw-bold">إجمالي الرصيد (المتاح + المعلق)</h6>
                <h1 class="fw-black display-5 mb-0">
                    {{ number_format($totalBalance, 2) }}
                    <span class="fs-4">$</span>
                </h1>
                <div class="d-flex flex-wrap gap-3 mt-3 opacity-90">
                    <div class="bg-white bg-opacity-10 p-2 px-3 rounded-4 border border-white border-opacity-10">
                        <small class="d-block opacity-75">الرصيد المتاح للسحب</small>
                        <span class="fw-bold fs-5 text-white">{{ number_format($availableBalance, 2) }} $</span>
                    </div>
                    <div class="bg-white bg-opacity-10 p-2 px-3 rounded-4 border border-white border-opacity-10">
                        <small class="d-block opacity-75">أرباح معلقة</small>
                        <span class="fw-bold fs-5 text-warning">{{ number_format($pendingBalance, 2) }} $</span>

                        {{-- قسم العداد التنازلي المحدث --}}
                        <div class="mt-1" style="font-size: 0.75rem;">
                            <i class="fas fa-history me-1"></i>
                            @if($nextUnlockDate)
                                متبقي: <span id="unlock-countdown" data-time="{{ $nextUnlockDate }}" class="fw-bold text-white">جاري الحساب...</span>
                            @else
                                <span class="fw-bold text-white-50">لا توجد دفعات معلقة</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-start mt-4 mt-md-0">
                <a href="{{ route('withdraw.create') }}" class="btn btn-white-glass rounded-pill px-5 py-3 fw-black shadow-sm hover-up">
                    <i class="fas fa-hand-holding-dollar me-2" aria-hidden="true"></i> سحب الرصيد الآن
                </a>
            </div>
        </div>
        <div class="wallet-decoration-circle"></div>
    </div>

    {{-- 2. شبكة الإجراءات السريعة --}}
    <div class="row g-3 mb-5 text-center">
        <div class="col-6 col-md-2">
            @php
                $notifications = auth()->user()->unreadNotifications()->latest()->take(10)->get();
                $notifCount = $notifications->count();
            @endphp
            <div class="dropdown h-100">
                <button class="quick-action-card bg-danger-gradient text-white rounded-4 p-3 shadow-sm d-block w-100 h-100 border-0 position-relative" data-bs-toggle="dropdown">
                    @if($notifCount > 0)
                        <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-white text-danger shadow-sm animate__animated animate__swing animate__infinite" style="z-index: 10; margin-top: 5px;">
                            {{ $notifCount }}
                        </span>
                    @endif
                    <div class="icon-box mb-2"><i class="fas fa-bell fa-2x"></i></div>
                    <span class="fw-bold small">الإشعارات</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 mt-2" style="width: 300px; max-height: 400px; overflow-y: auto;">
                    <div class="p-3 border-bottom bg-light fw-bold text-dark text-end d-flex justify-content-between">
                        <span>التنبيهات الأخيرة</span>
                        <i class="fas fa-stream opacity-25"></i>
                    </div>
                    @forelse($notifications as $notif)
                        <a href="{{ route('notifications.read', $notif->id) }}" class="dropdown-item text-end p-3 border-bottom border-light">
                            <div class="small fw-bold text-wrap">{{ $notif->data['title'] ?? 'إشعار جديد' }}</div>
                            <div class="x-small text-muted text-wrap">{{ Str::limit($notif->data['message'] ?? 'تم اختيارك لتنفيذ مشروع', 60) }}</div>
                            <small class="text-primary" style="font-size: 0.7rem;">{{ $notif->created_at->diffForHumans() }}</small>
                        </a>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-check-circle d-block mb-2 opacity-25 fa-2x"></i>
                            لا توجد إشعارات جديدة
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <a href="{{ route('services.create') }}" class="quick-action-card bg-primary-gradient text-white rounded-4 p-3 shadow-sm d-block text-decoration-none h-100">
                <div class="icon-box mb-2"><i class="fas fa-plus-circle fa-2x" aria-hidden="true"></i></div>
                <span class="fw-bold small">خدمة جديدة</span>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ route('projects.index') }}" class="quick-action-card bg-dark-gradient text-white rounded-4 p-3 shadow-sm d-block text-decoration-none h-100">
                <div class="icon-box mb-2"><i class="fas fa-search fa-2x" aria-hidden="true"></i></div>
                <span class="fw-bold small">تصفح المشاريع</span>
            </a>
        </div>

        <div class="col-6 col-md-2">
            <a href="{{ route('messages.chat', ['user' => $user->id]) }}" class="quick-action-card bg-info-gradient text-white rounded-4 p-3 shadow-sm d-block text-decoration-none position-relative h-100">
                @php
                    $unreadCount = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
                @endphp
                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-danger shadow-sm border border-2 border-white animate__animated animate__heartBeat animate__infinite"
                          style="z-index: 10; margin-top: 5px; font-size: 0.7rem; padding: 0.4em 0.65em;">
                        {{ $unreadCount > 9 ? '+9' : $unreadCount }}
                    </span>
                @endif
                <div class="icon-box mb-2"><i class="fas fa-comments fa-2x" aria-hidden="true"></i></div>
                <span class="fw-bold small">الرسائل</span>
            </a>
        </div>

        <div class="col-6 col-md-2">
            <a href="{{ route('profile.portfolio', ['id' => $user->id]) }}" class="quick-action-card bg-warning-gradient text-white rounded-4 p-3 shadow-sm d-block text-decoration-none h-100">
                <div class="icon-box mb-2"><i class="fas fa-briefcase fa-2x" aria-hidden="true"></i></div>
                <span class="fw-bold small">معرض الأعمال</span>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <div class="quick-action-card {{ $user->verification_status == 'verified' ? 'bg-success-gradient' : 'bg-secondary-gradient' }} text-white rounded-4 p-3 shadow-sm h-100">
                <div class="icon-box mb-2"><i class="fas {{ $user->verification_status == 'verified' ? 'fa-shield-alt' : 'fa-user-slash' }} fa-2x" aria-hidden="true"></i></div>
                <span class="fw-bold small">{{ $user->verification_status == 'verified' ? 'حساب موثق' : 'غير موثق' }}</span>
            </div>
        </div>
    </div>

     <div class="row g-4 text-end">
    <div class="col-lg-8">
        {{-- 3. مركز القيادة والعمليات --}}
        <div class="glass-card p-4 rounded-5 shadow-sm border-0 mb-4 overflow-hidden position-relative">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-black text-dark mb-0"><i class="fas fa-chart-line text-primary me-2" aria-hidden="true"></i> مركز القيادة والعمليات</h5>
                <div class="status-indicator d-flex align-items-center bg-light px-3 py-1 rounded-pill">
                    <span class="small fw-bold text-success">النظام يعمل بكفاءة</span>
                    <span class="status-pulse ms-2"></span>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-7">
                    <div class="p-4 rounded-4 bg-dark-gradient text-white h-100 position-relative overflow-hidden">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small opacity-75">مستوى البائع: <span class="text-info fw-bold">{{ $proStatus['levelPercentage'] > 80 ? 'بائع محترف' : 'بائع نشط' }}</span></span>
                            <span class="fw-bold">{{ $proStatus['levelPercentage'] }}%</span>
                        </div>
                        <div class="progress mb-4" style="height: 12px; background: rgba(255,255,255,0.1); border-radius: 20px;">
                            <div class="progress-bar bg-primary-gradient" role="progressbar" style="width: {{ $proStatus['levelPercentage'] }}%;" aria-valuenow="{{ $proStatus['levelPercentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="p-2 rounded-3 bg-white bg-opacity-10">
                                    <div class="small opacity-75">الموثوقية</div>
                                    <div class="fw-bold text-success">{{ $proStatus['reliability'] }}%</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 bg-white bg-opacity-10">
                                    <div class="small opacity-75">التسليم</div>
                                    <div class="fw-bold text-info">{{ $proStatus['delivery'] }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 bg-white bg-opacity-10">
                                    <div class="small opacity-75">الرد</div>
                                    <div class="fw-bold text-warning">{{ $proStatus['response'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="d-flex flex-column gap-2 h-100 justify-content-center">
                        <div class="goal-item p-3 rounded-4 border bg-white d-flex align-items-center shadow-sm">
                            <i class="fas fa-bullseye text-danger me-3 fs-4" aria-hidden="true"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold small">هدف الأرباح ($5,000)</div>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $quickGoals['income']['percentage'] }}%" role="progressbar"></div>
                                </div>
                            </div>
                            <span class="ms-3 fw-bold">{{ $quickGoals['income']['text'] }}</span>
                        </div>
                        <div class="goal-item p-3 rounded-4 border bg-white d-flex align-items-center shadow-sm">
                            <i class="fas fa-star text-warning me-3 fs-4" aria-hidden="true"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold small">التقييم العام</div>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $quickGoals['rating']['percentage'] }}%" role="progressbar"></div>
                                </div>
                            </div>
                            <span class="ms-3 fw-bold">{{ $quickGoals['rating']['text'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. إدارة مبيعات الخدمات --}}
        <div class="glass-card rounded-5 shadow-sm border-0 overflow-hidden mb-4">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-black mb-0 text-dark">إدارة مبيعات الخدمات</h5>
                <i class="fas fa-shopping-bag opacity-25"></i>
            </div>
            <div class="table-responsive text-end">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 border-0">الخدمة والعميل</th>
                            <th class="border-0 text-center">النوع</th>
                            <th class="border-0 text-center">الحالة</th>
                            <th class="border-0 text-center">تحكيم الإدارة</th>
                            <th class="border-0 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="service-icon-small bg-primary-soft rounded-3 p-2 me-3 text-primary">
                                        <i class="fas {{ ($order->service && $order->service->type == 'ready') ? 'fa-bolt text-warning' : 'fa-box-open' }}" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $order->service ? Str::limit($order->service->title, 35) : 'طلب مخصص' }}</div>
                                        <small class="text-muted"><i class="fas fa-user me-1 small" aria-hidden="true"></i> العميل: {{ $order->buyer->name ?? 'مستخدم' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($order->service && $order->service->type == 'ready')
                                    <span class="badge bg-warning-gradient text-white rounded-pill px-2" style="font-size: 0.65rem;">جاهزة للتسليم</span>
                                @else
                                    <span class="badge bg-light text-dark border rounded-pill px-2" style="font-size: 0.65rem;">خدمة عادية</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $displayStatus = ($order->service && $order->service->type == 'ready') ? 'completed' : $order->status;
                                    $statusClasses = [
                                        'completed' => 'bg-success-soft text-success',
                                        'pending' => 'bg-warning-soft text-warning',
                                        'cancelled' => 'bg-danger-soft text-danger',
                                        'processing' => 'bg-info-soft text-info',
                                        'disputed' => 'bg-dark text-white'
                                    ];
                                    $statusClass = $statusClasses[$displayStatus] ?? 'bg-secondary-soft text-dark';
                                @endphp
                                <span class="badge rounded-pill {{ $statusClass }} px-3 py-2 mb-1">
                                    {{ __($displayStatus) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <form id="dispute-order-{{ $order->id }}" action="{{ route('dispute.store') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $order->id }}">
                                    <input type="hidden" name="type" value="service">
                                    <button type="button" onclick="confirmDisputeAction(event, 'dispute-order-{{ $order->id }}')" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-size: 0.75rem;">
                                        <i class="fas fa-gavel me-1"></i> طلب تحكيم
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <div class="btn-group gap-2">
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="عرض التفاصيل"><i class="fas fa-eye"></i></a>
                                    @if($order->service && $order->service->type != 'ready')
                                        <a href="{{ route('messages.chat', ['user' => $order->buyer_id]) }}" class="btn btn-sm btn-outline-info rounded-circle" title="مراسلة العميل"><i class="fas fa-comment-dots"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">لا توجد مبيعات حالياً.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 5. سجل المشاريع --}}
        <div class="glass-card rounded-5 shadow-sm border-0 overflow-hidden mb-4">
            <div class="p-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                <h5 class="fw-black mb-0 text-dark"><i class="fas fa-tasks text-primary me-2"></i> سجل المشاريع والعروض</h5>
                <span class="badge bg-light text-dark rounded-pill border px-3 py-2" style="font-size: 0.7rem;">إجمالي العروض: {{ $user->proposals->count() }}</span>
            </div>
            <div class="table-responsive text-end">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">المشروع</th>
                            <th class="text-center">حالة المشروع</th>
                            <th class="text-center">حالة الرصيد</th>
                            <th class="text-center">تحكيم الإدارة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->proposals as $proposal)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="fw-bold text-dark">{{ Str::limit($proposal->project->title ?? 'مشروع', 40) }}</div>
                            </td>
                            <td class="text-center">
                                @php
                                    $projStatus = $proposal->project->status ?? 'open';
                                    $projBadges = ['open' => 'bg-success-gradient', 'processing' => 'bg-warning-gradient', 'completed' => 'bg-info-gradient', 'disputed' => 'bg-dark'];
                                    $projBadge = $projBadges[$projStatus] ?? 'bg-secondary-gradient';
                                @endphp
                                <span class="badge {{ $projBadge }} text-white px-3 py-2 rounded-pill small">
                                    {{ __($projStatus) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($proposal->project && $proposal->project->selected_proposal_id == $proposal->id)
                                    <span class="badge bg-light {{ $projStatus == 'completed' ? 'text-success border-success' : 'text-primary border-primary' }} border px-3 py-2 rounded-pill">
                                        {{ $projStatus == 'completed' ? 'رصيد متاح' : 'رصيد معلق' }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form id="dispute-project-{{ $proposal->id }}" action="{{ route('dispute.store') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $proposal->project_id }}">
                                    <input type="hidden" name="type" value="project">
                                    <button type="button" onclick="confirmDisputeAction(event, 'dispute-project-{{ $proposal->id }}')" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-size: 0.75rem;">
                                        <i class="fas fa-gavel me-1"></i> طلب تحكيم
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('projects.show', $proposal->project_id) }}" class="btn btn-sm btn-light border rounded-pill px-3 hover-up">تفاصيل</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">لا يوجد عروض مقدمة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- الجدول الجديد المضاف: إدارة وتعديل الخدمات المرفوعة الخاصة بالمستخدم --}}
        <div class="glass-card rounded-5 shadow-sm border-0 overflow-hidden mb-5">
            <form action="{{ route('services.update_bulk') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                    <h5 class="fw-black mb-0 text-dark"><i class="fas fa-concierge-bell text-primary me-2"></i> الخدمات المرفوعة وإعداداتها</h5>
                    <button type="submit" class="btn btn-sm btn-success-gradient rounded-pill px-4 fw-bold hover-up shadow-sm">
                        <i class="fas fa-save me-1"></i> إعادة حفظ التعديلات
                    </button>
                </div>
                <div class="table-responsive text-end">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">اسم الخدمة</th>
                                <th class="text-center" style="width: 130px;">السعر ($)</th>
                                <th class="text-center" style="width: 150px;">حالة الخدمة</th>
                                <th class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $userServices = \App\Models\Service::where('user_id', auth()->id())->get();
                            @endphp
                            @forelse($userServices as $index => $service)
                            <tr>
                                <td class="px-4 py-3">
                                    <input type="hidden" name="services[{{ $index }}][id]" value="{{ $service->id }}">
                                    <input type="text" name="services[{{ $index }}][title]" class="form-control form-control-sm rounded-3 border-light shadow-sm text-end fw-bold text-dark" value="{{ $service->title }}" required>
                                </td>
                                <td class="text-center">
                                    <input type="number" name="services[{{ $index }}][price]" class="form-control form-control-sm rounded-3 border-light shadow-sm text-center fw-bold" value="{{ $service->price }}" min="5" step="0.01" required>
                                </td>
                                <td class="text-center">
                                    <select name="services[{{ $index }}][status]" class="form-select form-select-sm rounded-3 border-light shadow-sm fw-bold">
                                        <option value="active" {{ $service->status == 'active' ? 'selected' : '' }}>نشطة</option>
                                        <option value="draft" {{ $service->status == 'draft' ? 'selected' : '' }}>مسودة</option>
                                        <option value="paused" {{ $service->status == 'paused' ? 'selected' : '' }}>موقفة مؤقتاً</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('services.show', $service->id) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="عرض الخدمة على المنصة"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted">لم تقم برفع أي خدمات حتى الآن.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

    </div>

    {{-- العمود الأيسر (بروفايل المستخدم) --}}
    <div class="col-lg-4">
        <div class="glass-card p-4 rounded-5 shadow-lg border-0 text-center position-relative overflow-hidden mb-4">
            <div class="profile-bg-accent"></div>
            <div class="position-relative mb-4 pt-4">
                <div class="avatar-container mx-auto position-relative" style="width: 120px;" id="freelancerAvatarContainer">
                    {{-- عرض الصورة برابط S3 --}}
                    <img src="{{ $profilePhoto }}"
                         id="freelancerPreview"
                         class="rounded-circle border border-4 border-white shadow-sm"
                         style="width: 120px; height: 120px; object-fit: cover;">

                    @if(Auth::id() === $user->id)
                        <form id="freelancerImageForm" enctype="multipart/form-data">
                            @csrf
                            {{-- حقل الرفع المخفي --}}
                            <input type="file" name="profile_image" id="freelancer-input" class="d-none" accept="image/*">

                            {{-- أيقونة الكاميرا والـ Spinner --}}
                            <label for="freelancer-input" class="avatar-edit-icon shadow-sm" id="cameraLabel">
                                <i class="fas fa-camera" id="cameraIcon"></i>
                                <i class="fas fa-circle-notch fa-spin d-none text-primary" id="uploadSpinner"></i>
                            </label>
                        </form>
                    @endif
                </div>
            </div>
            <h4 class="fw-black mb-1 text-dark">{{ $user->name }}</h4>
            <p class="text-primary fw-bold small mb-3">{{ $user->headline ?? 'مستقل على المنصة' }}</p>

            <div class="d-flex flex-wrap gap-1 justify-content-center mb-4">
                @php
                    $userSkills = is_string($skills) ? explode(',', $skills) : ($skills ?? []);
                @endphp
                @foreach(array_slice($userSkills, 0, 5) as $skill)
                    <span class="skill-tag">#{{ trim($skill) }}</span>
                @endforeach
            </div>

            <div class="stats-row d-flex justify-content-between bg-light rounded-4 p-3 mb-4">
                <div class="text-center flex-grow-1">
                    <div class="fw-black">{{ ($completedServicesCount ?? 0) + ($completedProjectsCount ?? 0) }}</div>
                    <small class="text-muted">المكتمل</small>
                </div>
                <div class="vr opacity-25"></div>
                <div class="text-center flex-grow-1">
                    <div class="fw-black">{{ number_format($projRating ?? 0, 1) }}</div>
                    <small class="text-muted">تقييم</small>
                </div>
                <div class="vr opacity-25"></div>
                <div class="text-center flex-grow-1">
                    <div class="fw-black">{{ $user->proposals()->count() }}</div>
                    <small class="text-muted">عرض</small>
                </div>
            </div>

            <a href="{{ route('profile.settings') }}" class="btn btn-primary-gradient w-100 rounded-pill py-3 fw-black shadow-sm mb-2 hover-up">
                <i class="fas fa-cog me-2"></i> تعديل بياناتي
            </a>
        </div>

        <div class="glass-card p-4 rounded-5 shadow-sm border-0 bg-dark-gradient text-white">
            <div class="d-flex align-items-center">
                <div class="bg-white bg-opacity-10 p-3 rounded-circle me-3">
                    <i class="fas fa-shield-halved text-info fs-4"></i>
                </div>
                <div class="text-end">
                    <h6 class="mb-1 fw-bold">حماية المعاملات</h6>
                    <small class="opacity-75">جميع مدفوعاتك محمية بنظام التحكيم الذكي.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap');
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1, #a855f7);
        --dark-gradient: linear-gradient(135deg, #1e293b, #0f172a);
        --success-gradient: linear-gradient(135deg, #10b981, #059669);
        --info-gradient: linear-gradient(135deg, #0ea5e9, #2563eb);
        --warning-gradient: linear-gradient(135deg, #f59e0b, #d97706);
        --secondary-gradient: linear-gradient(135deg, #64748b, #334155);
        --danger-gradient: linear-gradient(135deg, #f43f5e, #e11d48);
    }

    body { background-color: #f8fafc; font-family: 'Cairo', sans-serif; overflow-x: hidden; }
    .fw-black { font-weight: 900; }
    .glass-card { background: white; border: 1px solid #edf2f7; transition: all 0.3s; border-radius: 2rem !important; }
    .glass-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.05) !important; }

    .wallet-banner { background: var(--primary-gradient); min-height: 200px; border-radius: 2.5rem !important; }
    .btn-white-glass { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; backdrop-filter: blur(10px); transition: 0.3s; }
    .btn-white-glass:hover { background: white; color: #6366f1; }

    .quick-action-card { transition: 0.3s; border: none; text-decoration: none !important; }
    .quick-action-card:hover { transform: scale(1.05); filter: brightness(1.1); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

    .status-pulse { width: 10px; height: 10px; background: #10b981; border-radius: 50%; display: inline-block; animation: pulse 2s infinite; }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

    .bg-success-soft { background: #ecfdf5; color: #065f46; }
    .bg-warning-soft { background: #fffbeb; color: #92400e; }
    .bg-info-soft { background: #eff6ff; color: #1e40af; }
    .bg-primary-soft { background: #eef2ff; color: #4338ca; }
    .bg-danger-soft { background: #fff1f2; color: #9f1239; }

    .bg-primary-gradient { background: var(--primary-gradient); }
    .bg-dark-gradient { background: var(--dark-gradient); }
    .bg-info-gradient { background: var(--info-gradient); }
    .bg-warning-gradient { background: var(--warning-gradient); }
    .bg-success-gradient { background: var(--success-gradient); }
    .bg-secondary-gradient { background: var(--secondary-gradient); }
    .bg-danger-gradient { background: var(--danger-gradient); }

    .skill-tag { font-size: 0.75rem; color: #6366f1; background: #f0f7ff; padding: 6px 14px; border-radius: 50px; font-weight: bold; border: 1px solid #e0e7ff; }

    .avatar-edit-icon { position: absolute; bottom: 0; left: 0; background: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #6366f1; border: 2px solid #6366f1; z-index: 10; transition: 0.3s; }
    .avatar-edit-icon:hover { background: #6366f1; color: white; }
    .profile-bg-accent { position: absolute; top: 0; right: 0; left: 0; height: 100px; background: #f8fafc; z-index: 0; }
    .hover-up { transition: 0.3s; }
    .hover-up:hover { transform: translateY(-3px); }

    .wallet-decoration-circle { position: absolute; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%; top: -100px; left: -50px; z-index: 1; }
    .dropdown-item:active { background-color: #6366f1; }
    .x-small { font-size: 0.8rem; }

    .table thead th { font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .progress-bar { transition: width 1s ease-in-out; }

    @media (max-width: 768px) {
        .wallet-banner { text-align: center !important; }
        .wallet-banner .text-end { text-align: center !important; }
        .wallet-banner .d-flex { justify-content: center; }
    }
</style>

<script>
    // 1. نظام الرفع السحابي المتطور
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('freelancer-input');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('profile_image', file);
                formData.append('_token', '{{ csrf_token() }}');

                // تأثيرات بصرية
                const preview = document.getElementById('freelancerPreview');
                const cameraIcon = document.getElementById('cameraIcon');
                const spinner = document.getElementById('uploadSpinner');

                preview.style.opacity = '0.5';
                cameraIcon.classList.add('d-none');
                spinner.classList.remove('d-none');

                // الرفع لـ S3 عبر الكنترولر المحدث
                axios.post("{{ route('profile.update_image') }}", formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                .then(res => {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم التحديث!',
                        text: 'تم رفع صورتك الشخصية للسحاب بنجاح.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'فشل الرفع',
                        text: err.response?.data?.message || 'تأكد من اتصالك بالسحاب وحجم الصورة.'
                    });
                    preview.style.opacity = '1';
                    cameraIcon.classList.remove('d-none');
                    spinner.classList.add('d-none');
                });
            });
        }
    });

    // 2. نظام التحكيم
    function confirmDisputeAction(event, formId) {
        event.preventDefault();
        Swal.fire({
            title: 'تنبيه بفتح نزاع',
            text: "سيتم تحويل حالة المشروع إلى نزاع لكي يتم التواصل من الإدارة مع الطرفين. تضمن المنصة لكل مستخدم حقوقه المالية.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'نعم، فتح نزاع',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    // 3. نظام العداد التنازلي للرصيد المعلق
    const countdownEl = document.getElementById('unlock-countdown');
    if (countdownEl && countdownEl.dataset.time) {
        const unlockDate = new Date(countdownEl.dataset.time).getTime();
        const timer = setInterval(() => {
            const now = new Date().getTime();
            const diff = unlockDate - now;
            if (diff <= 0) {
                countdownEl.innerHTML = "جاهز للصرف!";
                clearInterval(timer);
                return;
            }
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            countdownEl.innerHTML = `${days}ي و ${hours}س و ${mins}د`;
        }, 60000);
    }
</script>

@endsection

