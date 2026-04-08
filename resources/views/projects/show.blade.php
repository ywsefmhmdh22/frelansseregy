@extends('layouts.master')

@section('content')
<style>
    /* التنسيقات الأساسية والمطورة */
    body { background-color: #f4f7f6; font-family: 'Cairo', sans-serif; }

    .project-header-gradient {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 24px;
        padding: 50px 40px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(42, 82, 152, 0.2);
        position: relative;
        overflow: hidden;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.05);
    }

    /* نظام الخط الزمني (Stepper) */
    .stepper-container { display: flex; justify-content: space-between; position: relative; margin-bottom: 30px; }
    .stepper-item { text-align: center; position: relative; z-index: 2; flex: 1; }
    .stepper-item::before { content: ""; position: absolute; top: 15px; left: -50%; width: 100%; height: 2px; background: #e0e0e0; z-index: -1; }
    .stepper-item:first-child::before { content: none; }
    .step-dot { width: 30px; height: 30px; border-radius: 50%; background: #e0e0e0; display: inline-block; line-height: 30px; font-weight: bold; font-size: 12px; margin-bottom: 5px; color: white; }
    .stepper-item.active .step-dot { background: #28a745; box-shadow: 0 0 10px rgba(40, 167, 69, 0.5); }
    .stepper-item.active .step-label { color: #28a745; font-weight: bold; }
    .step-label { font-size: 0.75rem; color: #888; display: block; }

    .proposal-card { transition: transform 0.3s ease; border-right: 5px solid transparent; }
    .proposal-card:hover { transform: translateY(-5px); }
    .proposal-card.selected-freelancer { border-right-color: #28a745; background-color: #f0fff4; }

    .avatar-circle { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .rating-stars { color: #ffc107; font-size: 0.85rem; }
    .extra-small { font-size: 0.75rem; }
    .status-badge-live { padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: bold; }

    .project-main-img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 20px; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>

<div class="container py-5">
    {{-- تنبيهات --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- الخط الزمني للمشروع --}}
    <div class="glass-card p-4 mb-4">
        <div class="stepper-container">
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

    {{-- الهيدر --}}
    <div class="project-header-gradient d-flex flex-column flex-md-row justify-content-between align-items-center gap-4">
        <div class="text-center text-md-end">
            <div class="d-flex align-items-center gap-2 mb-2 justify-content-center justify-content-md-start">
                <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-tag me-1"></i> {{ $project->type ?? 'عام' }}
                </span>
                <span class="status-badge-live bg-warning text-dark shadow-sm">
                    <i class="fas fa-info-circle me-1"></i>
                    @if($project->status == 'open') مفتوح @elseif($project->status == 'in_progress') قيد التنفيذ @elseif($project->status == 'pending_delivery') بانتظار التسليم @else مكتمل @endif
                </span>
            </div>
            <h1 class="fw-bold mb-2">{{ $project->title }}</h1>
            <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3 opacity-75">
                <span><i class="far fa-user me-1"></i> {{ $project->user->name }}</span>
                <span><i class="far fa-clock me-1"></i> {{ $project->created_at->diffForHumans() }}</span>
            </div>
        </div>
        <div class="text-center">
            <div class="h3 fw-bold mb-0 text-white">{{ number_format($project->price) }} {{ $project->currency }}</div>
            <div class="small opacity-75">ميزانية المشروع التقديرية</div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- تعديل عرض صورة المشروع هنا --}}
            @if($project->image_url)
                <img src="{{ asset('storage/' . $project->image_url) }}" class="project-main-img" alt="{{ $project->title }}">
            @endif

            {{-- كارت "المستقل المختار" --}}
            @if($project->freelancer_id)
                <div class="glass-card p-4 mb-4 border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ $project->freelancer->name }}&background=28a745&color=fff" class="avatar-circle">
                            <div>
                                <h6 class="fw-bold mb-0">المستقل المختار: {{ $project->freelancer->name }}</h6>
                                <span class="badge bg-success-soft text-success extra-small">قيد العمل على المشروع</span>
                            </div>
                        </div>
                        <a href="{{ route('messages.chat', $project->freelancer_id) }}" class="btn btn-primary btn-sm rounded-pill px-4">مراسلة</a>
                    </div>
                </div>
            @endif

            {{-- إدارة المشروع --}}
            @if(auth()->check() && (auth()->id() == $project->user_id || auth()->id() == $project->freelancer_id))
                <div class="glass-card p-4 mb-4 border-start border-4 border-primary">
                    <h5 class="fw-bold mb-3"><i class="fas fa-tasks text-primary me-2"></i> إدارة المشروع</h5>

                    @if($project->status == 'in_progress' && auth()->id() == $project->freelancer_id)
                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-4">
                            <span>لقد بدأت العمل، هل انتهيت؟</span>
                            <form action="{{ route('projects.requestDelivery', $project->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">طلب تسليم المشروع</button>
                            </form>
                        </div>
                    @elseif($project->status == 'pending_delivery' && auth()->id() == $project->user_id)
                        <div class="d-flex align-items-center justify-content-between bg-warning bg-opacity-10 p-3 rounded-4 border border-warning">
                            <span>قام المستقل بطلب التسليم.</span>
                            <a href="{{ route('projects.review', $project->id) }}" class="btn btn-success rounded-pill px-4 shadow-sm">
                                قبول التسليم والتقييم
                            </a>
                        </div>
                    @elseif($project->status == 'completed')
                        <div class="alert alert-success rounded-4 mb-0">
                            <i class="fas fa-check-double me-2"></i> هذا المشروع مكتمل بنجاح.
                        </div>
                    @endif
                </div>
            @endif

            {{-- وصف المشروع --}}
            <div class="glass-card p-4 mb-4">
                <h4 class="fw-bold mb-4 border-bottom pb-3"><i class="fas fa-align-left text-success me-2"></i> وصف المشروع</h4>
                <div class="project-description text-dark" style="line-height: 2; font-size: 1.1rem;">
                    {!! nl2br(e($project->description)) !!}
                </div>
            </div>

            {{-- المرفقات --}}
            @if($project->attachment_urls && count($project->attachment_urls) > 0)
                <div class="glass-card p-4 mb-4">
                    <h5 class="fw-bold mb-4 border-bottom pb-3">
                        <i class="fas fa-paperclip text-primary me-2"></i> ملفات توضيحية للمشروع
                    </h5>
                    <div class="row g-3">
                        @foreach($project->attachment_urls as $url)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 border rounded-4 bg-light shadow-sm">
                                    <div class="flex-shrink-0 me-3">
                                        @php
                                            $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                                            $icon = match($extension) {
                                                'pdf' => 'fa-file-pdf text-danger',
                                                'zip', 'rar' => 'fa-file-archive text-warning',
                                                'doc', 'docx' => 'fa-file-word text-primary',
                                                'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image text-success',
                                                default => 'fa-file text-secondary'
                                            };
                                        @endphp
                                        <i class="fas {{ $icon }} fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="mb-0 text-truncate small fw-bold">ملحق رقم {{ $loop->iteration }}</p>
                                        <a href="{{ asset('storage/' . $url) }}" target="_blank" class="text-decoration-none extra-small text-primary">
                                            <i class="fas fa-external-link-alt me-1"></i> عرض أو تحميل
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- فورم تقديم العرض --}}
            @if(auth()->check() && auth()->user()->role == 'freelancer' && auth()->id() != $project->user_id && $project->status == 'open')
                @php $alreadyApplied = $project->proposals->where('user_id', auth()->id())->first(); @endphp
                @if(!$alreadyApplied)
                    <div class="glass-card p-4 mb-5 border-top border-4 border-success shadow">
                        <h4 class="fw-bold mb-4">قدم عرضك الفني</h4>
                        <form action="{{ route('proposals.store', $project->id) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">قيمة العرض ({{ $project->currency }})</label>
                                    <input type="number" name="price" class="form-control rounded-4 shadow-sm" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">مدة التسليم (أيام)</label>
                                    <input type="number" name="duration" class="form-control rounded-4 shadow-sm" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">تفاصيل التنفيذ</label>
                                    <textarea name="description" class="form-control rounded-4 shadow-sm" rows="5" placeholder="كيف ستنفذ المشروع؟" required></textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-success px-5 py-2 rounded-pill fw-bold shadow">إرسال العرض</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="alert alert-info rounded-4 border-0 shadow-sm mb-5">
                        <i class="fas fa-info-circle me-2"></i> لقد قدمت عرضك بالفعل.
                    </div>
                @endif
            @endif

            {{-- قائمة العروض --}}
            <div class="mt-5">
                <h4 class="fw-bold mb-4">العروض المقدمة ({{ $project->proposals->count() }})</h4>
                @forelse($project->proposals as $proposal)
                    <div class="glass-card p-4 mb-4 proposal-card @if($project->freelancer_id == $proposal->user_id) selected-freelancer @endif">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div class="d-flex gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ $proposal->user->name }}" class="avatar-circle">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">
                                        {{ $proposal->user->name }}
                                        @if($project->freelancer_id == $proposal->user_id) <span class="badge bg-success ms-2 small">المنفذ المختار</span> @endif
                                    </h6>
                                    <div class="rating-stars">
                                        @for($i=1; $i<=5; $i++) <i class="fas fa-star {{ $i <= ($proposal->user->freelancer_rating ?? 0) ? 'text-warning' : 'text-light' }} small"></i> @endfor
                                    </div>
                                </div>
                            </div>
                            <div class="text-md-end">
                                <div class="h5 fw-bold text-success mb-0">{{ number_format($proposal->amount ?? $proposal->price) }} {{ $project->currency }}</div>
                                <div class="small text-muted">خلال {{ $proposal->duration }} أيام</div>
                            </div>
                        </div>
                        <hr class="my-3 opacity-25">
                        <div class="proposal-text text-secondary mb-4 small">{{ $proposal->description }}</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="extra-small text-muted"><i class="far fa-clock me-1"></i> {{ $proposal->created_at->diffForHumans() }}</span>
                            <div class="d-flex gap-2">
                                <a href="{{ route('messages.chat', $proposal->user->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">مراسلة</a>
                                @if(auth()->id() == $project->user_id && $project->status == 'open')
                                    <form action="{{ route('projects.assign', [$project->id, $proposal->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" onclick="return confirm('توظيف هذا المستقل؟')">توظيف</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 glass-card"><p class="text-muted mb-0">لا توجد عروض بعد.</p></div>
                @endforelse
            </div>
        </div>

        {{-- الجانب الأيسر --}}
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 20px;">
                <div class="glass-card p-4 text-center mb-4 shadow-sm">
                    <h5 class="fw-bold mb-4 border-bottom pb-2 text-start">عن صاحب المشروع</h5>
                    <img src="https://ui-avatars.com/api/?name={{ $project->user->name }}" class="avatar-circle mb-3" style="width: 80px; height: 80px;">
                    <h6 class="fw-bold mb-1">{{ $project->user->name }}</h6>
                    <div class="row g-2 mt-3">
                        <div class="col-6 text-center border-end">
                            <div class="fw-bold text-dark">{{ $project->user->projects()->count() }}</div>
                            <div class="extra-small text-muted">مشاريع</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="fw-bold text-dark">100%</div>
                            <div class="extra-small text-muted">إتمام</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
