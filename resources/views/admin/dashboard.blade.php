 @extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="command-center-wrapper">
    <div id="particles-js"></div>

    <div class="container-fluid py-4 px-lg-5 position-relative" style="z-index: 2;">

        {{-- الهيدر العلوي --}}
        <div class="d-flex justify-content-between align-items-center mb-5 glass-header p-4 rounded-4 shadow-2xl border-start border-info border-5 animate__animated animate__fadeInDown">
            <div>
                <h1 class="fw-black text-white mb-1 tracking-tighter">
                    <span class="text-info">FOX</span> HUNTER <span class="fs-6 text-muted fw-light">v2.0</span>
                </h1>
                <div class="d-flex align-items-center gap-2">
                    <span class="status-dot-online"></span>
                    <p class="text-light opacity-75 mb-0 small">أهلاً يا سيد يوسف. "الرادار المالي" نشط.. الإمبراطورية تحت السيطرة.</p>
                </div>
            </div>
            <div class="stats-pills d-flex gap-3">
                <div class="stat-pill glass-card p-3 rounded-4 border border-secondary border-opacity-25 shadow-glow-blue">
                    <small class="text-info d-block fw-bold mb-1 text-uppercase">الخزنة المركزية</small>
                    <span class="fw-black fs-4 text-white" id="live-balance">{{ number_format($users->sum('balance')) }} <small class="fs-6 opacity-50">ج.م</small></span>
                </div>
                <div class="stat-pill bg-info p-3 rounded-4 shadow-glow-info text-center text-black">
                    <small class="fw-bold d-block mb-1">العملاء النشطون</small>
                    <span class="fw-black fs-4">{{ $users->filter(fn($u) => method_exists($u, 'isOnline') ? $u->isOnline() : false)->count() }}</span>
                </div>
            </div>
        </div>

        {{-- القائمة العلويّة --}}
        <div class="d-flex justify-content-center mb-5 animate__animated animate__fadeIn">
            <ul class="nav nav-pills gap-3 glass-nav p-2 rounded-pill shadow-lg" id="adminTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active rounded-pill px-4 fw-bold" id="registration-tab" data-bs-toggle="pill" data-bs-target="#registration" type="button">
                        <i class="fas fa-bolt me-2"></i>الطلبات الجديدة
                        @php $newRequests = $users->where('verification_status', 'unverified')->count(); @endphp
                        @if($newRequests > 0) <span class="badge bg-info ms-2">{{ $newRequests }}</span> @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="master-directory-tab" data-bs-toggle="pill" data-bs-target="#master-directory" type="button">
                        <i class="fas fa-shield-halved me-2"></i>سجلات الهيمنة
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="adminTabContent">
            {{-- قسم السجلات --}}
            <div class="tab-pane fade show active" id="master-directory" role="tabpanel">
                <div class="row g-4">
                    @foreach($users as $user)
                    <div class="col-xl-4 col-md-6 animate__animated animate__zoomIn">
                        <div class="dominion-card rounded-4 position-relative overflow-hidden">
                            <div class="card-status-bar {{ $user->role == 'freelancer' ? 'bg-success' : 'bg-info' }}"></div>

                            <div class="glass-card p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <div class="online-status">
                                        <span class="dot {{ method_exists($user, 'isOnline') && $user->isOnline() ? 'online' : 'offline' }}"></span>
                                        <small class="text-uppercase fw-black opacity-50">{{ method_exists($user, 'isOnline') && $user->isOnline() ? 'Active' : 'Idle' }}</small>
                                    </div>

                                    <div class="dropdown">
                                        <button class="btn btn-link text-white opacity-50 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark border border-secondary shadow-lg">
                                            {{-- رابط سجل العمليات الفعلي --}}
                                            <li><a class="dropdown-item py-2" href="{{ route('admin.user.transactions', $user->id) }}"><i class="fas fa-history me-2 text-info"></i>سجل العمليات</a></li>
                                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="showUserDetails({{ json_encode($user) }})"><i class="fas fa-envelope me-2 text-warning"></i>إرسال تنبيه</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="text-center mb-4">
                                    <div class="profile-main-img mb-3">
                                        <img src="https://ui-avatars.com/api/?name={{urlencode($user->name)}}&background=random&size=128" class="rounded-circle border border-3 border-dark shadow-lg" width="90">
                                        <div class="role-icon-float shadow-glow-{{ $user->role == 'freelancer' ? 'success' : 'info' }}">
                                            <i class="fas {{ $user->role == 'freelancer' ? 'fa-laptop-code' : 'fa-crown' }}"></i>
                                        </div>
                                    </div>
                                    <h5 class="fw-black text-white mb-0">{{ $user->name }}</h5>
                                    <div class="d-flex justify-content-center gap-2 mt-1">
                                        <span class="small text-info"><i class="fas fa-fingerprint me-1"></i> ID-{{ $user->id }}</span>
                                    </div>
                                </div>

                                <div class="quick-stats bg-black bg-opacity-40 rounded-3 p-3 mb-4 border border-white border-opacity-5">
                                    <div class="row text-center">
                                        <div class="col-6 border-end border-white border-opacity-10">
                                            <small class="d-block text-muted mb-1 small">الرصيد</small>
                                            <span class="text-success fw-black fs-5">{{ number_format($user->balance) }}</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="d-block text-muted mb-1 small">الحالة</small>
                                            <span class="text-info fw-black fs-6">{{ strtoupper($user->role) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="action-grid d-grid gap-2">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.user.impersonate', $user->id) }}" class="btn btn-action btn-glass-white flex-fill text-decoration-none">
                                            <i class="fas fa-user-secret"></i> <span>تقمص</span>
                                        </a>
                                        <button class="btn btn-action btn-glass-warning flex-fill" onclick="showIdModal('{{ asset('storage/'.$user->id_image) }}', '{{ asset('storage/'.$user->id_image_back) }}')">
                                            <i class="fas fa-id-card"></i> <span>البطاقة</span>
                                        </button>
                                        <button class="btn btn-action btn-glass-success flex-fill" onclick="showUserDetails({{ json_encode($user) }})">
                                            <i class="fas fa-list-ul"></i> <span>تفاصيل</span>
                                        </button>
                                    </div>
                                    <div class="d-flex gap-2">
                                        {{-- زر التعديل الحقيقي الذي يفتح صفحة التعديل --}}
                                        <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-action btn-glass-info flex-fill text-decoration-none">
                                            <i class="fas fa-user-edit"></i> <span>تعديل</span>
                                        </a>

                                        <form id="ban-form-{{ $user->id }}" action="{{ route('admin.user.ban', $user->id) }}" method="POST" class="flex-fill d-grid">
                                            @csrf
                                            <button type="button" class="btn btn-action btn-glass-danger w-100" onclick="confirmBan('{{ $user->id }}', '{{ $user->name }}')">
                                                <i class="fas fa-ban"></i> <span>حظر نهائي</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- المودالز --}}
<div class="modal fade" id="idViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-card border-info">
            <div class="modal-header border-0 text-white">
                <h5 class="modal-title fw-black"><i class="fas fa-fingerprint me-2 text-info"></i>الوثائق الرسمية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-2 small">وجه البطاقة</p>
                        <img id="idFrontImg" src="" class="img-fluid rounded-3 border border-secondary shadow-lg">
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-2 small">ظهر البطاقة</p>
                        <img id="idBackImg" src="" class="img-fluid rounded-3 border border-secondary shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-info">
            <div class="modal-header border-0 text-white border-bottom border-white border-opacity-10">
                <h5 class="modal-title fw-black"><i class="fas fa-user-circle me-2 text-info"></i>ملف البيانات الكامل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="userDetailsBody"></div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Orbitron:wght@400;900&display=swap');
    :root { --glass-bg: rgba(13, 17, 23, 0.9); --neon-blue: #0ea5e9; --neon-green: #10b981; --neon-red: #ef4444; }
    body { font-family: 'Cairo', sans-serif; background: #05070a; color: #fff; overflow-x: hidden; }
    .fw-black { font-weight: 900; }
    #particles-js { position: fixed; width: 100%; height: 100%; top: 0; left: 0; z-index: 1; }
    .glass-card { background: var(--glass-bg); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.08); }
    .dominion-card { transition: 0.5s; border: 1px solid transparent; cursor: pointer; }
    .dominion-card:hover { transform: translateY(-12px); border-color: var(--neon-blue); }
    .btn-action { border-radius: 12px; font-weight: 700; font-size: 0.75rem; padding: 12px 5px; display: flex; flex-direction: column; align-items: center; gap: 4px; border: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
    .btn-glass-danger { background: rgba(239, 68, 68, 0.1); color: var(--neon-red); }
    .btn-glass-danger:hover { background: var(--neon-red); color: #fff; box-shadow: 0 0 20px var(--neon-red); }
    .btn-glass-info { background: rgba(14, 165, 233, 0.1); color: var(--neon-blue); }
    .btn-glass-info:hover { background: var(--neon-blue); color: #000; }
    .swal2-popup { background: var(--glass-bg) !important; backdrop-filter: blur(15px); border: 1px solid var(--neon-red) !important; color: white !important; }
</style>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
    particlesJS("particles-js", { "particles": { "number": { "value": 60 }, "color": { "value": "#0ea5e9" }, "opacity": { "value": 0.2 }, "line_linked": { "enable": true, "opacity": 0.1 }, "move": { "enable": true, "speed": 1 } } });

    function showIdModal(front, back) {
        document.getElementById('idFrontImg').src = front;
        document.getElementById('idBackImg').src = back;
        new bootstrap.Modal(document.getElementById('idViewerModal')).show();
    }

    function showUserDetails(user) {
        let body = document.getElementById('userDetailsBody');
        body.innerHTML = `
            <div class="detail-item d-flex justify-content-between p-2 border-bottom border-white border-opacity-5">
                <span class="text-muted small">الاسم:</span><span class="text-white fw-bold">${user.name}</span>
            </div>
            <div class="detail-item d-flex justify-content-between p-2 border-bottom border-white border-opacity-5">
                <span class="text-muted small">البريد:</span><span class="text-info">${user.email}</span>
            </div>
            <div class="detail-item d-flex justify-content-between p-2 border-bottom border-white border-opacity-5">
                <span class="text-muted small">الرصيد:</span><span class="text-success fw-bold">${user.balance} ج.م</span>
            </div>
            <div class="detail-item d-flex justify-content-between p-2">
                <span class="text-muted small">تاريخ الانضمام:</span><span class="text-white">${new Date(user.created_at).toLocaleDateString('ar-EG')}</span>
            </div>
        `;
        new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
    }

    function confirmBan(userId, userName) {
        let timerInterval;
        Swal.fire({
            title: 'تأكيد الحظر الإمبراطوري!',
            html: `أنت على وشك طرد <b>${userName}</b> من النظام.<br>سيتم التنفيذ خلال <b></b> ثانية.`,
            icon: 'warning',
            timer: 5000,
            timerProgressBar: true,
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نفذ الآن!',
            cancelButtonText: 'إلغاء',
            didOpen: () => {
                const b = Swal.getHtmlContainer().querySelector('b');
                timerInterval = setInterval(() => {
                    b.textContent = Math.ceil(Swal.getTimerLeft() / 1000);
                }, 100);
            },
            willClose: () => { clearInterval(timerInterval); }
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                document.getElementById('ban-form-' + userId).submit();
            }
        });
    }
</script>
@endsection
