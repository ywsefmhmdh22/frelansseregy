@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Orbitron:wght@400;700;900&display=swap');

    :root {
        --bg-dark: #0b0e14;
        --card-bg: rgba(20, 25, 35, 0.85);
        --neon-blue: #0ea5e9;
        --neon-purple: #8b5cf6;
        --neon-green: #10b981;
        --text-muted: #94a3b8;
    }

    body { background-color: var(--bg-dark); font-family: 'Cairo', sans-serif; color: #fff; }
    .dashboard-wrapper { padding: 30px; background: radial-gradient(circle at 0% 0%, rgba(14, 165, 233, 0.08), transparent 50%); }

    .stat-card {
        background: var(--card-bg);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        padding: 22px;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover { transform: translateY(-8px); border-color: var(--neon-blue); box-shadow: 0 15px 40px rgba(0,0,0,0.4); }

    .stat-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; margin-bottom: 15px;
    }

    .glass-navbar {
        background: rgba(15, 23, 42, 0.9);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px; padding: 12px 25px; margin-bottom: 40px;
    }

    .btn-advanced-glow {
        background: linear-gradient(45deg, #0ea5e9, #8b5cf6);
        border: none; color: white; font-weight: 800;
        padding: 10px 20px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
        transition: 0.3s;
    }
    .btn-advanced-glow:hover { box-shadow: 0 0 25px rgba(14, 165, 233, 0.6); transform: scale(1.02); }

    .dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
    .online { background: var(--neon-green); box-shadow: 0 0 10px var(--neon-green); }
    .offline { background: #ef4444; }

    .user-table-card { background: var(--card-bg); border-radius: 28px; border: 1px solid rgba(255, 255, 255, 0.05); padding: 25px; }

    .badge-notify {
        position: absolute; top: -5px; right: -5px;
        padding: 4px 7px; border-radius: 50%;
        background: #ef4444; color: white; font-size: 10px;
    }

    .filter-btn-group {
        background: rgba(0,0,0,0.2);
        padding: 5px;
        border-radius: 15px;
        border: 1px solid rgba(255,255,255,0.05);
    }
    .filter-btn {
        border: none;
        background: transparent;
        color: var(--text-muted);
        padding: 8px 20px;
        border-radius: 12px;
        font-weight: bold;
        transition: 0.3s;
    }
    .filter-btn.active {
        background: var(--neon-blue);
        color: white;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
    }
</style>

<div class="dashboard-wrapper">
    {{-- Navbar --}}
    <nav class="glass-navbar d-flex justify-content-between align-items-center animate__animated animate__fadeInDown">
        <div class="logo d-flex align-items-center">
            <h4 class="mb-0 fw-900" style="font-family: 'Orbitron'; letter-spacing: 2px;">FOX<span class="text-info">ACCOUNTING</span></h4>
        </div>

        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.disputes.index') }}" class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-sm position-relative">
                <i class="fas fa-gavel me-2"></i>محكمة النزاعات
                @if($activeDisputesCount > 0)
                <span class="ms-2 badge bg-danger animate__animated animate__heartBeat animate__infinite">{{ $activeDisputesCount }}</span>
                @endif
            </a>

            <button class="btn btn-advanced-glow" onclick="loadAdvancedStats()">
                <i class="fas fa-microchip me-2"></i>إحصائيات متقدمة
            </button>

            {{-- إشعار المشاريع الجديدة - تم إصلاح Interactive Element --}}
            <div class="position-relative ms-2" style="cursor:pointer"
                 onclick="filterUsers('projects_pending')"
                 onkeydown="if(event.key==='Enter') filterUsers('projects_pending')"
                 role="button" tabindex="0" aria-label="عرض المشاريع المعلقة">
                <i class="fas fa-file-invoice text-warning fs-4"></i>
                @if($projectStats['pending'] > 0)
                <span class="badge-notify animate__animated animate__pulse animate__infinite" style="background: #f59e0b;">{{ $projectStats['pending'] }}</span>
                @endif
            </div>

            {{-- إشعار المستخدمين الجدد - تم إصلاح Interactive Element --}}
            <div class="position-relative ms-2" style="cursor:pointer"
                 onclick="filterUsers('pending')"
                 onkeydown="if(event.key==='Enter') filterUsers('pending')"
                 role="button" tabindex="0" aria-label="عرض المستخدمين الجدد">
                <i class="fas fa-user-plus text-info fs-4"></i>
                @if($pendingUsers->count() > 0)
                <span class="badge-notify animate__animated animate__swing animate__infinite">{{ $pendingUsers->count() }}</span>
                @endif
            </div>

            <img src="https://ui-avatars.com/api/?name=Youssef&background=0ea5e9&color=fff"
                 alt="صورة المسؤول"
                 class="rounded-circle border border-info ms-2" width="42">
        </div>
    </nav>

    {{-- Stats Cards Row --}}
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(14, 165, 233, 0.1); color: var(--neon-blue);"><i class="fas fa-users"></i></div>
                <h6 class="text-muted fw-bold mb-3">قاعدة المستخدمين</h6>
                <h2 class="fw-900 mb-2">{{ $users->count() }}</h2>
                <div class="small d-flex flex-column gap-1">
                    <span style="color: #f59e0b;"><i class="fas fa-user-shield me-2"></i>إداريين: {{ $adminsCount }}</span>
                    <span class="text-success"><i class="fas fa-user-tie me-2"></i>مستقلين: {{ $freelancersCount }}</span>
                    <span class="text-info"><i class="fas fa-user me-2"></i>عملاء: {{ $clientsCount }}</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--neon-green);"><i class="fas fa-vault"></i></div>
                <h6 class="text-muted fw-bold mb-3">الخزنة المركزية</h6>
                <h2 class="fw-900 mb-1 text-success">{{ number_format($totalBalance) }} <small class="fs-6">ج.م</small></h2>
                <hr style="border-color: rgba(255,255,255,0.1)">
                <div class="d-flex justify-content-between small text-muted">
                    <span>إجمالي المحافظ: <b>{{ $totalWallets }}</b></span>
                    <span class="text-white">بأرصدة: <b>{{ $activeWalletsCount }}</b></span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: var(--neon-purple);"><i class="fas fa-briefcase"></i></div>
                <h6 class="text-muted fw-bold mb-3">المشاريع الكلية</h6>
                <h2 class="fw-900 mb-2">{{ $projectStats['total'] }}</h2>
                <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.05);">
                    <div class="progress-bar bg-info" style="width: 60%"></div>
                </div>
                <div class="row g-0 small text-center">
                    <div class="col-4 border-end border-secondary border-opacity-25">
                        <span class="d-block fw-bold text-warning">{{ $projectStats['pending'] }}</span>
                        <small style="font-size: 8px">قيد المراجعة</small>
                    </div>
                    <div class="col-4 border-end border-secondary border-opacity-25">
                        <span class="d-block fw-bold text-info">{{ $projectStats['in_progress'] }}</span>
                        <small style="font-size: 8px">جاري</small>
                    </div>
                    <div class="col-4">
                        <span class="d-block fw-bold text-success">{{ $projectStats['completed'] }}</span>
                        <small style="font-size: 8px">مكتمل</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card text-center d-flex flex-column align-items-center">
                <h6 class="text-muted fw-bold mb-2">مؤشر النمو الحقيقي</h6>
                <h2 class="fw-900 mb-0 {{ $growthRate >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($growthRate, 1) }}%
                    <i class="fas fa-{{ $growthRate >= 0 ? 'arrow-trend-up' : 'arrow-trend-down' }} fs-5 ms-2"></i>
                </h2>
                <p class="small text-muted mb-3">مقارنة بالشهر الماضي</p>
                <div style="width: 100%; height: 60px;">
                     <canvas id="miniGrowthChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts & Activity Row --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="user-table-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-chart-area me-2 text-info"></i>تحليل الإيرادات الحقيقي</h5>
                    <div class="badge bg-dark border border-secondary p-2 small">Real-time Data Sync <i class="fas fa-sync fa-spin ms-2"></i></div>
                </div>
                <canvas id="mainRevenueChart" style="max-height: 340px;"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="user-table-card h-100">
                <h5 class="fw-bold mb-4 text-warning"><i class="fas fa-rss me-2"></i>آخر حركات النظام</h5>
                <div class="activity-feed">
                    @foreach($users->sortByDesc('updated_at')->take(6) as $u)
                    <div class="d-flex align-items-center gap-3 mb-4 p-2 rounded-3"
                         style="background: rgba(255,255,255,0.02); transition: 0.3s;"
                         onmouseover="this.style.background='rgba(14,165,233,0.05)'"
                         onmouseout="this.style.background='rgba(255,255,255,0.02)'"
                         onfocus="this.style.background='rgba(14,165,233,0.05)'"
                         tabindex="0"
                         role="article">
                        <div class="position-relative">
                            <img src="https://ui-avatars.com/api/?name={{urlencode($u->name)}}&background=random"
                                 alt="بروفايل {{ $u->name }}"
                                 class="rounded-circle" width="42">
                            <span class="position-absolute bottom-0 end-0 dot {{ method_exists($u, 'isOnline') && $u->isOnline() ? 'online' : 'offline' }}"></span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small fw-bold">{{ $u->name }}</h6>
                            <small class="text-muted d-block" style="font-size: 10px;">{{ $u->updated_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-dark text-info border border-info" style="font-size: 9px;">{{ strtoupper($u->role) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button class="btn btn-outline-info btn-sm w-100 rounded-pill mt-2">مراقبة كافة التحركات</button>
            </div>
        </div>
    </div>

    {{-- إدارة قاعدة البيانات --}}
    <div class="user-table-card animate__animated animate__fadeInUp">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-4">
            <div>
                <h5 class="fw-bold mb-1">إدارة الأصول البشرية والمالية</h5>
                <p class="text-muted small mb-0">التحكم الكامل في صلاحيات وحالات الحسابات</p>
            </div>

            <div class="filter-btn-group d-flex">
                <button class="filter-btn active" onclick="filterUsers('all', this)">
                    <i class="fas fa-list me-1"></i> الكل
                </button>

                {{-- زر المشاريع الجديدة --}}
                <button class="filter-btn position-relative" onclick="filterUsers('projects_pending', this)">
                    <i class="fas fa-file-signature me-1"></i> مشاريع معلقة
                    @if($projectStats['pending'] > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 9px;">
                        {{ $projectStats['pending'] }}
                    </span>
                    @endif
                </button>

                <button class="filter-btn position-relative" onclick="filterUsers('pending', this)">
                    <i class="fas fa-user-clock me-1"></i> مستخدمين جدد
                    @if($pendingUsers->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px;">
                        {{ $pendingUsers->count() }}
                    </span>
                    @endif
                </button>
            </div>

            <div class="d-flex gap-2">
                <input type="text" id="dbSearch" class="form-control bg-dark border-secondary rounded-pill px-4 text-white" style="width: 250px;" placeholder="ابحث باسم المستخدم...">
                <button class="btn btn-info rounded-pill" aria-label="فلترة متقدمة"><i class="fas fa-filter"></i></button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle" id="mainUserTable">
                <thead class="text-muted small border-bottom border-secondary">
                    <tr>
                        <th>المستخدم</th>
                        <th>الدور</th>
                        <th>الرصيد</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات الإمبراطورية</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    @foreach($users as $user)
                    <tr class="user-row" data-status="{{ $user->verification_status }}">
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{urlencode($user->name)}}&background=random"
                                     alt="أفاتار {{ $user->name }}"
                                     class="rounded-circle" width="35">
                                <div>
                                    <h6 class="mb-0 small fw-bold">{{ $user->name }}</h6>
                                    <small class="text-muted">#{{ $user->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                             @if($user->role == 'admin') <span class="badge bg-danger rounded-pill">الإدارة</span>
                             @elseif($user->role == 'freelancer') <span class="badge bg-success rounded-pill">مستقل</span>
                             @else <span class="badge bg-info rounded-pill">عميل</span> @endif
                        </td>
                        <td class="fw-bold {{ ($user->wallet->balance ?? 0) > 0 ? 'text-success' : 'text-muted' }}">
                            {{ number_format($user->wallet->balance ?? 0) }} ج.م
                        </td>
                        <td>
                            @if($user->verification_status == 'pending')
                                <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i> قيد المراجعة</span>
                            @else
                                <span class="d-flex align-items-center gap-1">
                                    <span class="dot {{ method_exists($user, 'isOnline') && $user->isOnline() ? 'online' : 'offline' }}"></span>
                                    <small>{{ method_exists($user, 'isOnline') && $user->isOnline() ? 'متصل' : 'أوفلاين' }}</small>
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                @if($user->verification_status == 'pending')
                                    <button onclick="approveUser('{{$user->id}}')" class="btn btn-sm btn-success" title="قبول التسجيل"><i class="fas fa-check"></i></button>
                                @endif
                                <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-sm btn-outline-info" title="تعديل"><i class="fas fa-pen"></i></a>
                                <button onclick="banUser('{{$user->id}}')" class="btn btn-sm btn-outline-danger" title="حظر"><i class="fas fa-ban"></i></button>
                                <button onclick="resetWallet('{{$user->id}}')" class="btn btn-sm btn-outline-warning" title="تصفير محفظة"><i class="fas fa-undo"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // 1. وظيفة الفلترة الذكية
    function filterUsers(status, btn) {
        if(btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        if (status === 'projects_pending') {
            Swal.fire({
                title: 'توجيه للمشاريع المعلقة',
                text: 'جاري نقلك إلى صفحة مراجعة المشاريع الجديدة...',
                icon: 'info',
                timer: 1500,
                showConfirmButton: false,
                background: '#141923', color: '#fff'
            }).then(() => {
                window.location.href = "/admin/projects/pending";
            });
            return;
        }

        const rows = document.querySelectorAll('.user-row');
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            if (status === 'all') {
                row.style.display = '';
            } else if (status === 'pending') {
                row.style.display = (rowStatus === 'pending') ? '' : 'none';
            } else if (status === 'active') {
                row.style.display = (rowStatus !== 'pending') ? '' : 'none';
            }
        });
    }

    // 2. وظيفة الموافقة
    function approveUser(id) {
        Swal.fire({
            title: 'تفعيل الحساب؟',
            text: "هل تريد الموافقة على انضمام هذا المستخدم؟",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'نعم، تفعيل الآن',
            background: '#141923',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/user/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw response;
                    return response.json();
                })
                .then(data => {
                    Swal.fire('تم التفعيل!', data.message || 'تم تفعيل حساب المستخدم بنجاح', 'success')
                    .then(() => location.reload());
                }).catch(err => {
                    console.error(err);
                    Swal.fire('خطأ!', 'حدث خطأ في النظام', 'error');
                });
            }
        });
    }

    // 3. وظيفة الحظر
    function banUser(id) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم استبعاد هذا المستخدم من النظام!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'نعم، حظر!',
            background: '#141923', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/user/${id}/ban`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(() => location.reload());
            }
        });
    }

    // 4. وظيفة التصفير
    function resetWallet(id) {
        Swal.fire({
            title: 'تصفير الرصيد؟',
            text: "سيتم حذف كافة المبالغ في محفظة المستخدم، لا يمكن التراجع!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'تصفير الآن',
            confirmButtonColor: '#f59e0b',
            background: '#141923', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/user/${id}/reset-wallet`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    Swal.fire('تم!', 'تم تصفير المحفظة بنجاح', 'success')
                    .then(() => location.reload());
                });
            }
        });
    }

    function loadAdvancedStats() {
        Swal.fire({
            title: 'جاري سحب البيانات المتقدمة...',
            background: '#141923', color: '#fff',
            timer: 1000, showConfirmButton: false,
            didOpen: () => { Swal.showLoading() },
        }).then(() => {
            window.location.href = "{{ route('admin.finance.radar') }}";
        });
    }

    // --- Chart Logic ---
    const ctxRevenue = document.getElementById('mainRevenueChart').getContext('2d');
    const grad = ctxRevenue.createLinearGradient(0, 0, 0, 400);
    grad.addColorStop(0, 'rgba(14, 165, 233, 0.4)');
    grad.addColorStop(1, 'rgba(14, 165, 233, 0)');

    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
            datasets: [{
                label: 'صافي التدفقات المالية',
                data: [15000, 22000, 19000, 32000, 28000, {{ $totalBalance }}],
                borderColor: '#0ea5e9',
                backgroundColor: grad,
                fill: true, tension: 0.4, pointRadius: 6
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    const ctxGrowth = document.getElementById('miniGrowthChart').getContext('2d');
    new Chart(ctxGrowth, {
        type: 'line',
        data: {
            labels: [1, 2, 3, 4, 5],
            datasets: [{
                data: [10, 25, 15, 30, {{ $growthRate }}],
                borderColor: '{{ $growthRate >= 0 ? "#10b981" : "#ef4444" }}',
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { x: { display: false }, y: { display: false } },
            plugins: { legend: { display: false } }
        }
    });

    document.getElementById('dbSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.user-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
        });
    });
</script>
@endsection
