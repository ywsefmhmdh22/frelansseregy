@extends('layouts.master')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid py-4 px-lg-5 animate__animated animate__fadeIn">

    {{-- رأس الصفحة --}}
    <div class="row mb-5 align-items-center bg-white p-4 rounded-4 shadow-sm border-end border-5 border-success">
        <div class="col-md-7 text-end">
            <h2 class="fw-extrabold text-dark mb-1">
                @if(Auth::id() === $user->id)
                    طابت أوقاتك، {{ $user->name }} ✨
                @else
                    ملف المستقل: {{ $user->name }}
                @endif
            </h2>
            <p class="text-secondary mb-0">لديه {{ $user->proposals()->where('status', 'pending')->count() }} عروض قيد الانتظار.</p>
        </div>
        <div class="col-md-5 text-md-start mt-3 mt-md-0">
            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                <a href="{{ route('projects.index') }}" class="btn btn-success px-4 py-2 fw-bold">تصفح المشاريع</a>

                @if(Auth::id() === $user->id)
                    {{-- التعديل هنا: الزر أصبح رابطاً لصفحة جديدة --}}
                    <a href="{{ route('services.create') }}" class="btn btn-primary px-4 py-2 fw-bold">
                        <i class="fas fa-plus-circle me-1"></i> إضافة خدمة للبيع
                    </a>

                    <a href="{{ route('messages.chat', ['user' => Auth::id()]) }}" class="btn btn-dark px-4 py-2 fw-bold position-relative">
                        <i class="fas fa-envelope me-1"></i> الرسائل
                        @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle"></span>
                        @endif
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- العمود الأيمن: الجداول --}}
        <div class="col-lg-8">
            {{-- مشاريع قيد التنفيذ --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-tasks me-2 text-primary"></i>مشاريع قيد التنفيذ</h5>
                    <span class="badge bg-primary rounded-pill small">العمل الحالي</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive text-end">
                        <table class="table table-hover align-middle mb-0" dir="rtl">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3 border-0">المشروع</th>
                                    <th class="border-0">الموعد النهائي</th>
                                    <th class="border-0 text-center">الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workingProjects as $workingProject)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="fw-bold text-dark">{{ $workingProject->title }}</div>
                                            <small class="text-success fw-bold">
                                                {{ number_format($workingProject->final_price ?? $workingProject->price, 2) }} {{ $workingProject->currency }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="text-muted small">
                                                <i class="far fa-calendar-alt me-1"></i> {{ $workingProject->duration }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if(Auth::id() === $user->id)
                                                @if($workingProject->status == 'pending_delivery')
                                                    <button class="btn btn-warning btn-sm rounded-pill px-3 disabled">
                                                        <i class="fas fa-hourglass-half me-1"></i> بانتظار قبول العميل
                                                    </button>
                                                @else
                                                    <form action="{{ route('projects.requestDelivery', $workingProject->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">
                                                            <i class="fas fa-check-double me-1"></i> طلب تسليم
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <span class="badge bg-light text-secondary">قيد المتابعة</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="fas fa-briefcase fa-3x mb-3 d-block text-light"></i>
                                            لا توجد مشاريع قيد التنفيذ حالياً.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- آخر العروض --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-dark">آخر العروض المقدمة</h5>
                </div>
                <div class="card-body p-0 text-end">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-end" dir="rtl">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3 border-0">المشروع</th>
                                    <th class="border-0">العرض</th>
                                    <th class="border-0">الحالة</th>
                                    <th class="text-center border-0">عرض</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->proposals()->latest()->take(5)->get() as $proposal)
                                    <tr>
                                        <td class="px-4">
                                            <div class="fw-bold text-dark">{{ $proposal->project->title }}</div>
                                            <small class="text-muted">{{ $proposal->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td class="fw-bold text-success">{{ $proposal->amount }}</td>
                                        <td>
                                            <span class="badge-status {{ $proposal->project->status == 'open' ? 'status-pending' : 'status-accepted' }}">
                                                {{ $proposal->project->status == 'open' ? 'مفتوح' : 'تم الاختيار' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('projects.show', $proposal->project_id) }}" class="btn btn-sm btn-light rounded-circle">
                                                <i class="fas fa-external-link-alt text-primary"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- العمود الأيسر --}}
        <div class="col-lg-4 text-end">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                <div class="position-relative d-inline-block mx-auto">
                    <img src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=10b981&color=fff&size=100' }}"
                         class="rounded-circle shadow mb-2" style="width: 100px; height: 100px; object-fit: cover;">

                    @if(Auth::id() === $user->id)
                        <label for="avatar_input" class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle p-2 shadow-sm" style="cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-camera fa-xs"></i>
                        </label>
                        <form id="avatar-form" action="{{ route('profile.update_image') }}" method="POST" enctype="multipart/form-data" class="d-none">
                            @csrf
                            <input type="file" id="avatar_input" name="profile_image" onchange="document.getElementById('avatar-form').submit()">
                        </form>
                    @endif
                </div>
                <h5 class="fw-black text-dark mt-3">{{ $user->name }}</h5>
                <div class="text-warning mb-2">
                    @for($i=1;$i<=5;$i++)
                        <i class="fa{{ $i <= ($user->freelancer_rating ?? 0) ? 's':'r' }} fa-star"></i>
                    @endfor
                </div>
            </div>

            {{-- المحفظة --}}
            @if(Auth::id() === $user->id)
                <div class="card bg-success text-white mt-3 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="opacity-75 mb-1 small">رصيدك المتاح</h6>
                                <h3 class="fw-bold mb-0">
                                    {{ number_format($user->wallet->balance ?? 0, 2) }}
                                    <span class="small" style="font-size: 14px;">{{ $user->wallet->currency ?? 'ج.م' }}</span>
                                </h3>
                            </div>
                             <a href="{{ route('withdraw.create') }}" class="btn btn-light btn-sm rounded-pill fw-bold px-3 shadow-sm">
                                سحب <i class="fas fa-wallet ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card bg-warning text-dark mt-3 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h6 class="opacity-75 mb-1 small">أرباح معلقة (قيد التنفيذ)</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($pendingBalance, 2) }} <span class="small" style="font-size: 14px;">ج.م</span></h3>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .badge-status{ font-size:11px; font-weight:800; padding:5px 12px; border-radius:50px; }
    .status-pending{ background:#fffbeb; color:#b45309; }
    .status-accepted{ background:#ecfdf5; color:#065f46; }
    .fw-black{ font-weight:900; }
    .fw-extrabold{ font-weight:800; }
    .table th{ color:#6b7280; font-size:13px; }
</style>

@endsection
