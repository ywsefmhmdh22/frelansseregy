 @extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<div class="container-fluid py-4 px-lg-5 animate__animated animate__fadeIn" id="dashboard-wrapper" dir="rtl">

    {{-- 1. قسم المحفظة والحالة المالية --}}
    <div class="wallet-banner mb-4 p-4 rounded-5 shadow-lg border-0 position-relative overflow-hidden text-white">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-md-8 text-end">
                <h6 class="opacity-75 mb-2 fw-bold">إجمالي الرصيد (المتاح + المعلق)</h6>
                <h1 class="fw-black display-5 mb-0">
                    {{ number_format(($user->wallet->balance ?? 0) + ($pendingBalance ?? 0), 2) }}
                    <span class="fs-4">{{ $user->wallet->currency ?? 'ج.م' }}</span>
                </h1>
                <div class="d-flex gap-4 mt-3 opacity-90">
                    <div>
                        <small class="d-block">رصيدك المتاح</small>
                        <span class="fw-bold fs-5">{{ number_format($user->wallet->balance ?? 0, 2) }} {{ $user->wallet->currency ?? 'ج.م' }}</span>
                    </div>
                    <div class="border-start border-white border-opacity-25 ps-4">
                        <small class="d-block">أرباح قيد المراجعة</small>
                        <span class="fw-bold fs-5 text-warning">{{ number_format($pendingBalance ?? 0, 2) }} {{ $user->wallet->currency ?? 'ج.م' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-start mt-4 mt-md-0">
                <a href="{{ route('withdraw.create') }}" class="btn btn-white-glass rounded-pill px-5 py-3 fw-black shadow-sm hover-up">
                    <i class="fas fa-hand-holding-dollar me-2"></i> سحب الرصيد الآن
                </a>
            </div>
        </div>
        <div class="wallet-decoration-circle"></div>
    </div>

    {{-- 2. شبكة الإجراءات السريعة --}}
    <div class="row g-3 mb-5 text-center">
        <div class="col-6 col-md-2">
            <a href="{{ route('services.create') }}" class="quick-action-card bg-primary-gradient text-white rounded-4 p-3 shadow-sm d-block text-decoration-none">
                <div class="icon-box mb-2"><i class="fas fa-plus-circle fa-2x"></i></div>
                <span class="fw-bold small">خدمة جديدة</span>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ route('projects.index') }}" class="quick-action-card bg-dark-gradient text-white rounded-4 p-3 shadow-sm d-block text-decoration-none">
                <div class="icon-box mb-2"><i class="fas fa-search fa-2x"></i></div>
                <span class="fw-bold small">تصفح المشاريع</span>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ route('messages.chat', ['user' => $user->id]) }}" class="quick-action-card bg-info-gradient text-white rounded-4 p-3 shadow-sm d-block text-decoration-none">
                <div class="icon-box mb-2"><i class="fas fa-comments fa-2x"></i></div>
                <span class="fw-bold small">الرسائل</span>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ route('profile.portfolio', ['id' => $user->id]) }}" class="quick-action-card bg-warning-gradient text-white rounded-4 p-3 shadow-sm d-block text-decoration-none">
                <div class="icon-box mb-2"><i class="fas fa-briefcase fa-2x"></i></div>
                <span class="fw-bold small">معرض الأعمال</span>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <div class="quick-action-card {{ $user->verification_status == 'verified' ? 'bg-success-gradient' : 'bg-secondary-gradient' }} text-white rounded-4 p-3 shadow-sm">
                <div class="icon-box mb-2"><i class="fas {{ $user->verification_status == 'verified' ? 'fa-shield-alt' : 'fa-user-slash' }} fa-2x"></i></div>
                <span class="fw-bold small">{{ $user->verification_status == 'verified' ? 'حساب موثق' : 'غير موثق' }}</span>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ route('profile.settings') }}" class="quick-action-card bg-secondary-gradient text-white rounded-4 p-3 shadow-sm d-block text-decoration-none">
                <div class="icon-box mb-2"><i class="fas fa-user-edit fa-2x"></i></div>
                <span class="fw-bold small">الإعدادات</span>
            </a>
        </div>
    </div>

    <div class="row g-4 text-end">
        <div class="col-lg-8">
            {{-- 3. مركز القيادة والعمليات --}}
            <div class="glass-card p-4 rounded-5 shadow-sm border-0 mb-4 overflow-hidden position-relative">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-black text-dark mb-0"><i class="fas fa-chart-line text-primary me-2"></i> مركز القيادة والعمليات</h5>
                    <div class="status-indicator d-flex align-items-center bg-light px-3 py-1 rounded-pill">
                        <span class="small fw-bold text-success">النظام يعمل بكفاءة</span>
                        <span class="status-pulse ms-2"></span>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="p-4 rounded-4 bg-dark-gradient text-white h-100 position-relative overflow-hidden">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small opacity-75">مستوى البائع: <span class="text-info fw-bold">{{ ($proStatus['levelPercentage'] ?? 0) > 80 ? 'بائع محترف' : 'بائع نشط' }}</span></span>
                                <span class="fw-bold">{{ $proStatus['levelPercentage'] ?? 0 }}%</span>
                            </div>
                            <div class="progress mb-4" style="height: 12px; background: rgba(255,255,255,0.1); border-radius: 20px;">
                                <div class="progress-bar bg-primary-gradient" role="progressbar" style="width: {{ $proStatus['levelPercentage'] ?? 0 }}%;"></div>
                            </div>
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-white bg-opacity-10">
                                        <div class="small opacity-75">الموثوقية</div>
                                        <div class="fw-bold text-success">{{ $proStatus['reliability'] ?? 100 }}%</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-white bg-opacity-10">
                                        <div class="small opacity-75">التسليم</div>
                                        <div class="fw-bold text-info">{{ $proStatus['delivery'] ?? '100%' }}</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-white bg-opacity-10">
                                        <div class="small opacity-75">الرد</div>
                                        <div class="fw-bold text-warning">{{ $proStatus['response'] ?? 'سريع' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex flex-column gap-2">
                            {{-- هدف الأرباح الديناميكي --}}
                            <div class="goal-item p-3 rounded-4 border bg-white d-flex align-items-center shadow-sm">
                                <i class="fas fa-bullseye text-danger me-3 fs-4"></i>
                                <div class="flex-grow-1">
                                    @php
                                        $targetGoal = $user->earnings_goal ?? 5000; // هدف الفريلانسر أو 5000 افتراضي
                                        $currentEarnings = ($user->wallet->balance ?? 0) + ($pendingBalance ?? 0);
                                        $goalPercentage = min(($currentEarnings / $targetGoal) * 100, 100);
                                    @endphp
                                    <div class="fw-bold small">هدف الأرباح ({{ number_format($targetGoal) }} {{ $user->wallet->currency ?? 'ج.م' }})</div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-danger" style="width: {{ $goalPercentage }}%"></div>
                                    </div>
                                </div>
                                <span class="ms-3 fw-bold">{{ round($goalPercentage) }}%</span>
                            </div>
                            <div class="goal-item p-3 rounded-4 border bg-white d-flex align-items-center shadow-sm">
                                <i class="fas fa-star text-warning me-3 fs-4"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">التقييم العام</div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-warning" style="width: {{ ($projRating ?? 0) * 20 }}%"></div>
                                    </div>
                                </div>
                                <span class="ms-3 fw-bold">{{ number_format($projRating ?? 0, 1) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. إدارة الطلبات (الجدول) --}}
            <div class="glass-card rounded-5 shadow-sm border-0 overflow-hidden mb-5">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-black mb-0">إدارة الطلبات الجارية والمباعة</h5>
                </div>
                <div class="table-responsive text-end">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-0">الخدمة والعميل</th>
                                <th class="border-0 text-center">الحالة والتقييم</th>
                                <th class="border-0 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="service-icon-small bg-primary-soft rounded-3 p-2 me-3 text-primary">
                                            <i class="fas fa-box-open"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ Str::limit($order->service->title ?? 'مشروع مخصص', 35) }}</div>
                                            <small class="text-muted"><i class="fas fa-user me-1 small"></i> المشتري: {{ $order->buyer->name ?? 'مستخدم' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $statusClass = [
                                            'completed' => 'bg-success-soft text-success',
                                            'pending' => 'bg-warning-soft text-warning',
                                            'cancelled' => 'bg-danger-soft text-danger',
                                            'processing' => 'bg-info-soft text-info',
                                            'delivered' => 'bg-primary-soft text-primary'
                                        ][$order->status] ?? 'bg-secondary-soft text-dark';
                                    @endphp
                                    <span class="badge rounded-pill {{ $statusClass }} px-3 py-2 mb-1">
                                        {{ __($order->status) }}
                                    </span>

                                    {{-- إظهار التقييم إذا كان الطلب مكتمل --}}
                                    @if($order->status == 'completed' && $order->rating)
                                        <div class="rating-stars mt-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star small {{ $i <= $order->rating ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                                            @endfor
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group gap-2">
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-circle shadow-sm" title="عرض التفاصيل">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('messages.chat', ['user' => $order->buyer_id]) }}" class="btn btn-sm btn-outline-info rounded-circle shadow-sm" title="مراسلة المشتري">
                                            <i class="fas fa-comment-dots"></i>
                                        </a>

                                        @if($order->status == 'processing')
                                            <form action="{{ route('orders.submitDelivery', $order->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" title="طلب تسليم الخدمة">
                                                    <i class="fas fa-check-double me-1"></i> تسليم
                                                </button>
                                            </form>
                                        @endif

                                        @if($order->status != 'completed' && $order->status != 'cancelled')
                                            <a href="{{ route('orders.dispute', $order->id) }}" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="خلاف؟ تحكيم الإدارة">
                                                <i class="fas fa-gavel"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-5 text-muted">لا توجد طلبات جارية حالياً.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- العمود الأيسر (كرت الهوية) --}}
        <div class="col-lg-4">
            <div class="glass-card p-4 rounded-5 shadow-lg border-0 text-center position-relative overflow-hidden">
                <div class="profile-bg-accent"></div>
                <div class="position-relative mb-4 pt-4">
                    <div class="avatar-container mx-auto">
                        <img src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=6366f1&color=fff&size=150' }}"
                             class="rounded-circle border border-4 border-white shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                        @if(Auth::id() === $user->id)
                            <form action="{{ route('profile.update_image') }}" method="POST" enctype="multipart/form-data" id="image-form">
                                @csrf
                                <input type="file" name="profile_image" id="profile-input" class="d-none" onchange="this.form.submit();">
                                <label for="profile-input" class="avatar-edit-icon shadow-sm"><i class="fas fa-camera"></i></label>
                            </form>
                        @endif
                    </div>
                </div>
                <h4 class="fw-black mb-1">{{ $user->name }}</h4>
                <p class="text-primary fw-bold small mb-3">{{ $user->headline ?? 'مستقل على المنصة' }}</p>

                <div class="d-flex flex-wrap gap-1 justify-content-center mb-4">
                    @php
                        $skills = is_array($user->skills) ? $user->skills : (json_decode($user->skills, true) ?? explode(',', $user->skills ?? ''));
                    @endphp
                    @foreach(array_slice($skills, 0, 3) as $skill)
                        @if(!empty(trim($skill)))
                            <span class="skill-tag">#{{ trim($skill) }}</span>
                        @endif
                    @endforeach
                </div>

                <div class="stats-row d-flex justify-content-between bg-light rounded-4 p-3 mb-4">
                    <div class="text-center">
                        <div class="fw-black">{{ $completedProjectsCount ?? 0 }}</div>
                        <small class="text-muted">مشروع</small>
                    </div>
                    <div class="vr mx-3 opacity-25"></div>
                    <div class="text-center">
                        <div class="fw-black">{{ number_format($projRating ?? 0, 1) }}</div>
                        <small class="text-muted">تقييم</small>
                    </div>
                    <div class="vr mx-3 opacity-25"></div>
                    <div class="text-center">
                        <div class="fw-black">{{ $user->proposals()->count() }}</div>
                        <small class="text-muted">عرض</small>
                    </div>
                </div>

                <a href="{{ route('profile.settings') }}" class="btn btn-primary-gradient w-100 rounded-pill py-3 fw-black shadow-sm mb-2">
                    تعديل بياناتي
                </a>
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
        --danger-gradient: linear-gradient(135deg, #ef4444, #b91c1c);
    }

    body { background-color: #f8fafc; font-family: 'Cairo', sans-serif; }
    .fw-black { font-weight: 900; }
    .glass-card { background: white; border: 1px solid #edf2f7; transition: all 0.3s; border-radius: 2rem !important; }
    .glass-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.05) !important; }

    .wallet-banner { background: var(--primary-gradient); min-height: 200px; border-radius: 2.5rem !important; }
    .btn-white-glass { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; backdrop-filter: blur(10px); transition: 0.3s; }
    .btn-white-glass:hover { background: white; color: #6366f1; }

    .quick-action-card { transition: 0.3s; border: none; }
    .quick-action-card:hover { transform: scale(1.05); filter: brightness(1.1); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

    .status-pulse { width: 10px; height: 10px; background: #10b981; border-radius: 50%; display: inline-block; animation: pulse 2s infinite; }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

    .bg-success-soft { background: #ecfdf5; color: #065f46; }
    .bg-warning-soft { background: #fffbeb; color: #92400e; }
    .bg-info-soft { background: #eff6ff; color: #1e40af; }
    .bg-danger-soft { background: #fef2f2; color: #991b1b; }
    .bg-primary-soft { background: #eef2ff; color: #4338ca; }
    .bg-primary-gradient { background: var(--primary-gradient); color: white; }
    .bg-dark-gradient { background: var(--dark-gradient); color: white; }
    .bg-info-gradient { background: var(--info-gradient); color: white; }
    .bg-warning-gradient { background: var(--warning-gradient); color: white; }
    .bg-success-gradient { background: var(--success-gradient); color: white; }
    .bg-secondary-gradient { background: var(--secondary-gradient); color: white; }

    .skill-tag { font-size: 0.75rem; color: #6366f1; background: #f0f7ff; padding: 6px 14px; border-radius: 50px; font-weight: bold; border: 1px solid #e0e7ff; }

    .avatar-edit-icon { position: absolute; bottom: 0; left: 0; background: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #6366f1; border: 2px solid #6366f1; }
    .profile-bg-accent { position: absolute; top: 0; right: 0; left: 0; height: 100px; background: #f8fafc; z-index: 0; }

    .btn-group .btn { display: inline-flex; align-items: center; justify-content: center; }
    .rounded-circle { width: 32px; height: 32px; padding: 0; }
    .rating-stars i { font-size: 0.7rem; margin: 0 1px; }
</style>
@stop
