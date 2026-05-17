@extends('layouts.master')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<style>
    /* التنسيقات الأساسية والمطورة الفخمة */
    body { background-color: #f4f7f6; font-family: 'Cairo', sans-serif; overflow-x: hidden; }

    .project-header-gradient {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 24px;
        padding: clamp(25px, 5vw, 50px) clamp(20px, 4vw, 40px);
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 15px 35px rgba(15, 23, 42, 0.15);
        position: relative;
        overflow: hidden;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.04);
    }

    /* نظام الخط الزمني المتجاوب (Stepper) */
    .stepper-container { display: flex; justify-content: space-between; position: relative; margin-bottom: 10px; flex-wrap: wrap; gap: 15px; }
    .stepper-item { text-align: center; position: relative; z-index: 2; flex: 1; min-width: 100px; }
    .stepper-item::before { content: ""; position: absolute; top: 15px; left: -50%; width: 100%; height: 2px; background: #e2e8f0; z-index: -1; }
    .stepper-item:first-child::before { content: none; }
    .step-dot { width: 30px; height: 30px; border-radius: 50%; background: #cbd5e1; display: inline-block; line-height: 30px; font-weight: bold; font-size: 12px; margin-bottom: 5px; color: white; }
    .stepper-item.active .step-dot { background: #10b981; box-shadow: 0 0 12px rgba(16, 185, 129, 0.4); }
    .stepper-item.active .step-label { color: #10b981; font-weight: bold; }
    .step-label { font-size: 0.75rem; color: #64748b; display: block; }

    .proposal-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-right: 5px solid transparent; }
    .proposal-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.06); }
    .proposal-card.selected-freelancer { border-right-color: #10b981; background-color: #f0fff4; }

    .avatar-circle { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .rating-stars { color: #ffc107; font-size: 0.85rem; }
    .extra-small { font-size: 0.75rem; }
    .status-badge-live { padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: bold; display: inline-flex; align-items: center; gap: 5px; }

    .project-main-img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 20px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }

    /* ميزانية العرض تفاعلية بالجنيه والدولار */
    .dual-price-display {
        background: rgba(255, 255, 255, 0.08);
        padding: 0.75rem 1.5rem;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .main-egp-val { font-size: clamp(1.5rem, 4vw, 2.2rem); font-weight: 900; color: #fff; }
    .sub-usd-val { font-size: 0.9rem; color: #94a3b8; font-weight: bold; display: block; margin-top: 2px; }

    /* 🔴 تنسيقات الشاشة المرعبة لقرار الإدارة 🔴 */
    #admin-lock-screen {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: black; z-index: 9999; display: flex; flex-direction: column;
        justify-content: center; align-items: center; color: red; text-align: center;
        animation: shake 0.5s infinite;
    }
    .horror-logo { width: 250px; filter: drop-shadow(0 0 20px red); margin-bottom: 20px; animation: pulse-red 1s infinite alternate; }
    .horror-text { font-size: 2rem; font-weight: 900; text-transform: uppercase; letter-spacing: 5px; text-shadow: 2px 2px 10px red; margin-top: 20px; font-family: 'Cairo', sans-serif; }
    @keyframes shake {
        0% { transform: translate(1px, 1px) rotate(0deg); }
        10% { transform: translate(-1px, -2px) rotate(-1deg); }
        30% { transform: translate(3px, 2px) rotate(0deg); }
        50% { transform: translate(-1px, 2px) rotate(1deg); }
        100% { transform: translate(1px, -2px) rotate(-1deg); }
    }
    @keyframes pulse-red { from { transform: scale(1); opacity: 1; } to { transform: scale(1.1); opacity: 0.7; } }

    /* معالجة الموبايل */
    @media (max-width: 767.98px) {
        .stepper-container { justify-content: center; }
        .stepper-item::before { content: none; }
        .project-header-gradient { text-align: center; }
        .dual-price-display { text-align: center !important; width: 100%; }
    }
</style>

{{-- الكود الخاص بالشاشة المرعبة (يظهر فقط إذا تم إلغاء المشروع بقرار إداري) --}}
@if($project->status == 'cancelled' && $project->admin_status == 'rejected')
<div id="admin-lock-screen">

    <div class="horror-text">
        <i class="fas fa-gavel fa-3x mb-4"></i><br>
        تم إغلاق هذا المشروع نهائياً بقرار من إدارة مهيير<br>
        <span style="font-size: 1.2rem; color: white;">بسبب وجود نزاع مالي وقانوني</span>
    </div>
    <p class="mt-4 text-white opacity-50">يتم إعادة توجيهك الآن.. لا تحاول العودة</p>
</div>

<script>
    setTimeout(function() {
        document.body.style.display = 'none';
        window.location.href = "{{ route('projects.index') }}";
    }, 4000);
</script>
@endif

<div class="container py-4 py-lg-5">
    {{-- تنبيهات --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 text-end" dir="rtl">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- الخط الزمني للمشروع --}}
    <div class="glass-card p-4 mb-4">
        <div class="stepper-container" dir="rtl">
            <div class="stepper-item {{ $project->status == 'open' ? 'active' : '' }}">
                <span class="step-dot">1</span>
                <span class="step-label">تلقي العروض</span>
            </div>
            <div class="stepper-item {{ $project->status == 'in_progress' ? 'active' : '' }}">
                <span class="step-dot">2</span>
                <span class="step-label">قيد التنفيذ</span>
            </div>
            <div class="stepper-item {{ $project->status == 'pending_delivery' ? 'active' : '' }}">
                <span class="step-dot">3</span>
                <span class="step-label">المراجعة</span>
            </div>
            <div class="stepper-item {{ $project->status == 'completed' ? 'active' : '' }}">
                <span class="step-dot">4</span>
                <span class="step-label">مكتمل</span>
            </div>
        </div>
    </div>

    {{-- الهيدر المحسن بالنظام المالي المزدوج --}}
    @php
        // جلب السعر ومعدل الصرف لحساب القيمة الموازية بالدولار لايف على السيرفر
        $egpBudget = $project->price;
        $exchangeRate = $rate ?? 50.0;
        $usdBudget = round($egpBudget / $exchangeRate, 2);
    @endphp

    <div class="project-header-gradient d-flex flex-column flex-md-row justify-content-between align-items-center gap-4 text-end" dir="rtl">
        <div>
            <div class="d-flex align-items-center gap-2 mb-3 justify-content-center justify-content-md-start flex-wrap">
                <span class="badge bg-white text-dark px-3 py-2 rounded-pill shadow-sm fw-bold extra-small">
                    <i class="fas fa-tag text-primary me-1"></i> {{ $project->type ?? 'عام' }}
                </span>
                <span class="status-badge-live bg-warning text-dark shadow-sm">
                    <i class="fas fa-info-circle me-1"></i>
                    @if($project->status == 'open') مفتوح @elseif($project->status == 'in_progress') قيد التنفيذ @elseif($project->status == 'pending_delivery') بانتظار التسليم @elseif($project->status == 'cancelled') ملغي @else مكتمل @endif
                </span>
            </div>
            <h1 class="fw-900 mb-3 fs-2 text-white">{{ $project->title }}</h1>
            <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3 opacity-75 small">
                <span><i class="far fa-user ms-1"></i> {{ $project->user->name }}</span>
                <span><i class="far fa-clock ms-1"></i> {{ $project->created_at->diffForHumans() }}</span>
            </div>
        </div>
        <div class="text-md-left text-center flex-shrink-0 dual-price-display">
            <div class="main-egp-val">{{ number_format($egpBudget) }} <span style="font-size: 1.2rem;">ج.م</span></div>
            <span class="sub-usd-val"><i class="fas fa-calculator me-1 extra-small"></i> ما يوازي: {{ number_format($usdBudget, 2) }} $</span>
            <div class="small text-white-50 mt-1">ميزانية المشروع التقديرية</div>
        </div>
    </div>

    <div class="row g-4" dir="rtl">
        <div class="col-lg-8">
            {{-- التعديل الجوهري: عرض الصورة من Laravel Cloud (S3) لضمان التوافق --}}
            @if($project->image_url)
                <img src="{{ Storage::disk('s3')->url($project->image_url) }}" class="project-main-img" alt="{{ $project->title }}" loading="lazy">
            @endif

            {{-- كارت "المستقل المختار" --}}
            @if($project->freelancer_id)
                <div class="glass-card p-4 mb-4 border-start border-4 border-success text-end">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($project->freelancer->name) }}&background=10b981&color=fff" class="avatar-circle">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">المستقل المختار: {{ $project->freelancer->name }}</h6>
                                <span class="badge bg-success bg-opacity-10 text-success extra-small mt-1 fw-bold">قيد العمل على المشروع</span>
                            </div>
                        </div>
                        <a href="{{ route('messages.chat', $project->freelancer_id) }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">مراسلة المستقل</a>
                    </div>
                </div>
            @endif

            {{-- إدارة المشروع --}}
            @if(auth()->check() && (auth()->id() == $project->user_id || auth()->id() == $project->freelancer_id))
                <div class="glass-card p-4 mb-4 border-start border-4 border-primary text-end">
                    <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-tasks text-primary ms-2"></i> إدارة المشروع</h5>

                    @if($project->status == 'in_progress' && auth()->id() == $project->freelancer_id)
                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-4 flex-wrap gap-2">
                            <span class="small fw-bold text-muted">لقد بدأت العمل على المشروع، هل انتهيت وتريد تسليم المخرجات؟</span>
                            <form action="{{ route('projects.requestDelivery', $project->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold btn-sm">طلب تسليم المشروع</button>
                            </form>
                        </div>
                    @elseif($project->status == 'pending_delivery' && auth()->id() == $project->user_id)
                        <div class="d-flex align-items-center justify-content-between bg-warning bg-opacity-10 p-3 rounded-4 border border-warning flex-wrap gap-2">
                            <span class="small fw-bold text-warning-emphasis">قام المستقل بتقديم طلب تسليم المشروع والمخرجات المكتملة.</span>
                            <a href="{{ route('projects.review', $project->id) }}" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold btn-sm">
                                قبول التسليم والتقييم
                            </a>
                        </div>
                    @elseif($project->status == 'completed')
                        <div class="alert alert-success rounded-4 mb-0 fw-bold border-0 shadow-sm small">
                            <i class="fas fa-check-double ms-2"></i> هذا المشروع مكتمل بنجاح، وتمت تصفية الحسابات المالية للمستقل.
                        </div>
                    @elseif($project->status == 'cancelled')
                        <div class="alert alert-danger rounded-4 mb-0 fw-bold border-0 shadow-sm small">
                            <i class="fas fa-times-circle ms-2"></i> هذا المشروع تم إلغاؤه وإغلاقه كليًا بقرار من الإدارة.
                        </div>
                    @endif
                </div>
            @endif

            {{-- وصف المشروع --}}
            <div class="glass-card p-4 mb-4 text-end">
                <h4 class="fw-bold mb-4 border-bottom pb-3 text-dark"><i class="fas fa-align-left text-success ms-2"></i> وصف المشروع</h4>
                <div class="project-description text-dark lh-lg" style="font-size: 1.05rem;">
                    {!! nl2br(e($project->description)) !!}
                </div>
            </div>

            {{-- المرفقات السحابية --}}
            @if($project->attachment_urls && count($project->attachment_urls) > 0)
                <div class="glass-card p-4 mb-4 text-end">
                    <h5 class="fw-bold mb-4 border-bottom pb-3 text-dark">
                        <i class="fas fa-paperclip text-primary ms-2"></i> ملفات توضيحية للمشروع (S3 Cloud)
                    </h5>
                    <div class="row g-3">
                        @foreach($project->attachment_urls as $url)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 border rounded-4 bg-light shadow-sm">
                                    <div class="flex-shrink-0 ms-3">
                                        @php
                                            $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                                            $icon = match($extension) {
                                                'pdf' => 'fa-file-pdf text-danger',
                                                'zip', 'rar' => 'fa-file-archive text-warning',
                                                'doc', 'docx' => 'fa-file-word text-primary',
                                                'jpg', 'jpeg', 'png', 'webp' => 'fa-file-image text-success',
                                                default => 'fa-file text-secondary'
                                            };
                                        @endphp
                                        <i class="fas {{ $icon }} fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden text-right">
                                        <p class="mb-0 text-truncate small fw-bold text-dark">ملحق توضيحي رقم {{ $loop->iteration }}</p>
                                        <a href="{{ Storage::disk('s3')->url($url) }}" target="_blank" class="text-decoration-none extra-small fw-bold text-primary d-inline-block mt-1">
                                            <i class="fas fa-external-link-alt ms-1"></i> عرض أو تحميل الملف السحابي
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- فورم تقديم العرض المطور بالنظام المالي الفوري --}}
            @if(auth()->check() && auth()->user()->role == 'freelancer' && auth()->id() != $project->user_id && $project->status == 'open')
                @php $alreadyApplied = $project->proposals->where('user_id', auth()->id())->first(); @endphp
                @if(!$alreadyApplied)
                    <div class="glass-card p-4 mb-5 border-top border-4 border-success shadow text-end">
                        <h4 class="fw-bold mb-4 text-dark"><i class="fas fa-paper-plane text-success ms-2"></i> قدّم عرضك الفني والمالي الآن</h4>
                        <form action="{{ route('proposals.store', $project->id) }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">قيمة العرض المالي المطلوب (بالجنيه المصري ج.م)</label>
                                    <div class="input-group">
                                        <input type="number" id="proposal_price_input" name="price" class="form-control rounded-4 shadow-sm fw-bold p-2 text-end" style="padding-left: 55px !important;" required>
                                    </div>
                                    {{-- صندوق الحساب اللحظي للعرض المالي بالدولار للمستقل --}}
                                    <small class="text-muted d-block mt-2 fw-bold" id="proposal_usd_live">
                                        <i class="fas fa-calculator me-1"></i> ما يوازي تقريباً: <span class="text-success" id="live_usd_text">0.00 $</span>
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">مدة التسليم المطلوبة (أيام)</label>
                                    <input type="number" name="duration" class="form-control rounded-4 shadow-sm p-2 text-end" placeholder="مثال: 5" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-muted">تفاصيل العرض وآلية التنفيذ</label>
                                    <textarea name="description" class="form-control rounded-4 shadow-sm p-3 text-end" rows="5" placeholder="اكتب بالتفصيل كيف ستقوم بتنفيذ المشروع ومراحل العمل..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success px-5 py-2 rounded-pill fw-bold shadow">إرسال العرض الفوري</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="alert alert-info rounded-4 border-0 shadow-sm mb-5 text-end fw-bold small bg-light text-primary border-start border-primary border-4" dir="rtl">
                        <i class="fas fa-info-circle ms-2"></i> لقد قمت بتقديم عرضك المالي والفني على هذا المشروع بالفعل وجاري مراجعته من قبل العميل.
                    </div>
                @endif
            @endif

            {{-- قائمة العروض المعدلة بالحسبة المالية المزدوجة --}}
            <div class="mt-5 text-end">
                <h4 class="fw-800 text-dark mb-4">العروض المقدمة على المشروع ({{ $project->proposals->count() }})</h4>
                @forelse($project->proposals as $proposal)
                    @php
                        $proposalEgp = $proposal->amount ?? $proposal->price;
                        $proposalUsd = round($proposalEgp / $exchangeRate, 2);
                    @endphp
                    <div class="glass-card p-4 mb-4 proposal-card @if($project->freelancer_id == $proposal->user_id) selected-freelancer @endif">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
                            <div class="d-flex gap-3 align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($proposal->user->name) }}&background=0f172a&color=fff" class="avatar-circle">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">
                                        {{ $proposal->user->name }}
                                        @if($project->freelancer_id == $proposal->user_id) <span class="badge bg-success ms-2 extra-small fw-bold">المنفذ المختار</span> @endif
                                    </h6>
                                    <div class="rating-stars">
                                        @for($i=1; $i<=5; $i++) <i class="fas fa-star {{ $i <= ($proposal->user->freelancer_rating ?? 0) ? 'text-warning' : 'text-light' }} extra-small"></i> @endfor
                                    </div>
                                </div>
                            </div>
                            <div class="text-md-left text-right">
                                <div class="h5 fw-900 text-success mb-1">{{ number_format($proposalEgp) }} ج.م</div>
                                <div class="extra-small text-muted fw-bold mb-1"><i class="fas fa-calculator opacity-50 me-1"></i> موازي: {{ number_format($proposalUsd, 2) }} $</div>
                                <div class="small text-secondary fw-bold">خلال {{ $proposal->duration }} أيام</div>
                            </div>
                        </div>
                        <hr class="my-3 opacity-5">
                        <div class="proposal-text text-secondary mb-4 small lh-base">{{ $proposal->description }}</div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="extra-small text-muted"><i class="far fa-clock ms-1"></i> تم التقديم {{ $proposal->created_at->diffForHumans() }}</span>
                            <div class="d-flex gap-2">
                                <a href="{{ route('messages.chat', $proposal->user->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">مراسلة</a>
                                @if(auth()->id() == $project->user_id && $project->status == 'open')
                                    <form action="{{ route('projects.assign', [$project->id, $proposal->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" onclick="return confirm('هل أنت متأكد من توظيف هذا المستقل لبدء العمل السحابي الآمن؟')">توظيف المستقل</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 glass-card rounded-4"><p class="text-muted mb-0 small fw-bold">لا توجد عروض مقدمة على المشروع حتى الآن، كن أول من يقدم!</p></div>
                @endforeach
            </div>
        </div>

        {{-- الجانب الأيسر --}}
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 20px; z-index: 10;">
                <div class="glass-card p-4 text-center mb-4 shadow-sm">
                    <h5 class="fw-bold mb-4 border-bottom pb-2 text-right text-dark"><i class="fas fa-user-tie text-muted ms-2"></i>عن صاحب المشروع</h5>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($project->user->name) }}&background=1e293b&color=fff" class="avatar-circle mb-3" style="width: 80px; height: 80px;">
                    <h6 class="fw-bold mb-2 text-dark">{{ $project->user->name }}</h6>
                    <div class="row g-2 mt-3" dir="rtl">
                        <div class="col-6 text-center border-left" style="border-left: 1px solid #e2e8f0;">
                            <div class="fw-900 text-dark fs-5">{{ $project->user->projects()->count() }}</div>
                            <div class="extra-small text-muted fw-bold">مشاريع مطروحة</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="fw-900 text-success fs-5">100%</div>
                            <div class="extra-small text-muted fw-bold">معدل الإتمام</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // نظام حساب سعر الصرف اللحظي لايف في فورم التقديم للمستقل
    document.addEventListener('DOMContentLoaded', function() {
        const rate = {{ $exchangeRate }};
        const proposalInput = document.getElementById('proposal_price_input');
        const liveUsdText = document.getElementById('live_usd_text');

        if(proposalInput) {
            proposalInput.addEventListener('input', function() {
                const egpVal = parseFloat(this.value);
                if(egpVal && egpVal > 0) {
                    const converted = (egpVal / rate).toFixed(2);
                    liveUsdText.innerText = converted + ' $';
                } else {
                    liveUsdText.innerText = '0.00 $';
                }
            });
        }
    });
</script>
@endsection
