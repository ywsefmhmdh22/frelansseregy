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
        --neon-yellow: #f59e0b;
        --neon-red: #ef4444;
        --text-muted: #94a3b8;
    }

    body { background-color: var(--bg-dark); font-family: 'Cairo', sans-serif; color: #fff; overflow-x: hidden; }
    .dashboard-wrapper { padding: 30px; background: radial-gradient(circle at 0% 0%, rgba(14, 165, 233, 0.08), transparent 50%); min-height: 100vh; }

    /* Stat Cards */
    .stat-card {
        background: var(--card-bg);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        padding: 22px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .stat-card:hover { transform: translateY(-10px); border-color: var(--neon-blue); box-shadow: 0 20px 50px rgba(0,0,0,0.5); }

    .stat-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; margin-bottom: 15px;
    }

    /* Navbar */
    .glass-navbar {
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px; padding: 12px 25px; margin-bottom: 40px;
    }

    .btn-advanced-glow {
        background: linear-gradient(45deg, #0ea5e9, #8b5cf6);
        border: none; color: white; font-weight: 800;
        padding: 10px 20px; border-radius: 12px;
        transition: 0.3s;
    }
    .btn-advanced-glow:hover { box-shadow: 0 0 25px rgba(14, 165, 233, 0.6); transform: scale(1.05); }

    /* Tables */
    .user-table-card { background: var(--card-bg); border-radius: 28px; border: 1px solid rgba(255, 255, 255, 0.05); padding: 25px; }

    .badge-notify {
        position: absolute; top: -5px; right: -5px;
        padding: 4px 7px; border-radius: 50%;
        background: var(--neon-red); color: white; font-size: 10px;
        border: 2px solid var(--bg-dark);
    }

    /* Filter UI */
    .filter-btn-group { background: rgba(0,0,0,0.3); padding: 6px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.05); }
    .filter-btn { border: none; background: transparent; color: var(--text-muted); padding: 8px 18px; border-radius: 12px; font-weight: bold; transition: 0.3s; }
    .filter-btn.active { background: var(--neon-blue); color: white; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4); }

    /* التحكم في ظهور الأقسام */
    .section-content { display: none; }
    .section-active { display: block !important; }

    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
</style>

<div class="dashboard-wrapper">
    {{-- Navbar --}}
    <nav class="glass-navbar d-flex justify-content-between align-items-center animate__animated animate__fadeInDown">
        <div class="logo d-flex align-items-center">
            <h4 class="mb-0 fw-900" style="font-family: 'Orbitron'; letter-spacing: 2px;">
                <i class="fas fa-shield-halved text-info me-2"></i>FOX<span class="text-info">ACCOUNTING</span>
            </h4>
        </div>

        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.disputes.index') }}" class="btn btn-outline-danger rounded-pill px-4 fw-bold position-relative">
                <i class="fas fa-gavel me-2"></i>محكمة النزاعات
                @if($activeDisputesCount > 0)
                <span class="ms-2 badge bg-danger animate__animated animate__heartBeat animate__infinite">{{ $activeDisputesCount }}</span>
                @endif
            </a>

            <button class="btn btn-advanced-glow" onclick="Swal.fire('قريباً', 'جاري تطوير مديول الذكاء الاصطناعي للإحصائيات', 'info')">
                <i class="fas fa-microchip me-2"></i>إحصائيات متقدمة
            </button>

            <div class="position-relative ms-2" style="cursor:pointer" onclick="switchSection('users', document.getElementById('btnFilterPending'))" role="button">
                <i class="fas fa-user-check text-warning fs-4"></i>
                @if($pendingUsers->count() > 0)
                <span class="badge-notify animate__animated animate__swing animate__infinite">{{ $pendingUsers->count() }}</span>
                @endif
            </div>

            <img src="https://ui-avatars.com/api/?name=Admin&background=0ea5e9&color=fff" class="rounded-circle border border-info ms-2" width="42">
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
                    <span class="text-info"><i class="fas fa-check-circle me-2"></i>موثق: {{ $users->where('verification_status', 'verified')->count() }}</span>
                    <span class="text-warning"><i class="fas fa-clock me-2"></i>انتظار: {{ $pendingUsers->count() }}</span>
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
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: var(--neon-purple);"><i class="fas fa-briefcase"></i></div>
                <h6 class="text-muted fw-bold mb-3">المشاريع</h6>
                <h2 class="fw-900 mb-2">{{ $projectStats['total'] }}</h2>
                <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.05);">
                    @php $p_perc = $projectStats['total'] > 0 ? ($projectStats['completed'] / $projectStats['total']) * 100 : 0; @endphp
                    <div class="progress-bar bg-info" style="width: {{ $p_perc }}%"></div>
                </div>
                <div class="small text-muted">مكتمل بنسبة {{ round($p_perc) }}%</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card text-center d-flex flex-column align-items-center justify-content-center">
                <h6 class="text-muted fw-bold mb-2">مؤشر النمو</h6>
                <h2 class="fw-900 mb-0 {{ $growthRate >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($growthRate, 1) }}%
                </h2>
                <div style="width: 100%; height: 40px;">
                     <canvas id="miniGrowthChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Management Card --}}
    <div class="user-table-card animate__animated animate__fadeInUp">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-4">
            <div>
                <h5 class="fw-bold mb-1"><i class="fas fa-users-cog me-2 text-info"></i>إدارة النظام</h5>
                <p class="text-muted small mb-0">تحكم كامل في الحسابات والمشاريع</p>
            </div>

            <div class="filter-btn-group d-flex">
                <button class="filter-btn active" onclick="switchSection('users', this); filterUsers('all')">المستخدمين</button>
                <button id="btnFilterPending" class="filter-btn" onclick="switchSection('users', this); filterUsers('pending')">
                    توثيق المعلقين
                    @if($pendingUsers->count() > 0)
                    <span class="ms-2 badge bg-warning text-dark">{{ $pendingUsers->count() }}</span>
                    @endif
                </button>
                <button class="filter-btn" onclick="switchSection('projects', this)">
                    المشاريع المعلقة
                    @if(isset($pendingProjects) && $pendingProjects->count() > 0)
                    <span class="ms-2 badge bg-danger">{{ $pendingProjects->count() }}</span>
                    @endif
                </button>
            </div>

            <div class="position-relative">
                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="dbSearch" class="form-control bg-dark border-secondary rounded-pill ps-5 text-white" placeholder="بحث سريع...">
            </div>
        </div>

        {{-- Users Table Section --}}
        <div id="usersSection" class="section-content section-active">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead class="text-muted small border-bottom border-secondary">
                        <tr>
                            <th>المستخدم</th>
                            <th>الدور</th>
                            <th>الرصيد</th>
                            <th>حالة التوثيق</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        @foreach($users as $user)
                        <tr class="user-row animate__animated animate__fadeIn" data-status="{{ $user->verification_status }}">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{urlencode($user->name)}}&background=random" class="rounded-circle" width="38">
                                    <div>
                                        <h6 class="mb-0 small fw-bold">{{ $user->name }}</h6>
                                        <small class="text-muted" style="font-size: 10px">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                 <span class="badge bg-dark border {{ $user->role == 'admin' ? 'border-danger text-danger' : 'border-info text-info' }}" style="font-size: 10px">
                                    {{ strtoupper($user->role) }}
                                 </span>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($user->wallet->balance ?? 0) }} ج.م</td>
                            <td>
                                @if($user->verification_status == 'pending')
                                    <span class="badge bg-warning text-dark animate__animated animate__flash animate__infinite">
                                        <i class="fas fa-spinner fa-spin me-1"></i> قيد المراجعة
                                    </span>
                                @elseif($user->verification_status == 'verified')
                                    <span class="text-success small fw-bold"><i class="fas fa-check-double me-1"></i> موثق</span>
                                @else
                                    <span class="text-danger small fw-bold"><i class="fas fa-times-circle me-1"></i> غير موثق</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    @if($user->verification_status !== 'verified')
                                        <button onclick="approveUser('{{$user->id}}')" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> توثيق
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                    <button onclick="banUser('{{$user->id}}')" class="btn btn-sm btn-outline-danger" title="حظر">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pending Projects Table Section --}}
        <div id="projectsSection" class="section-content">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead class="text-muted small border-bottom border-secondary">
                        <tr>
                            <th>عنوان المشروع</th>
                            <th>العميل</th>
                            <th>الميزانية</th>
                            <th>التاريخ</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingProjects ?? [] as $project)
                        <tr class="animate__animated animate__fadeIn">
                            <td>
                                <h6 class="mb-0 small fw-bold">{{ $project->title }}</h6>
                                <span class="badge bg-warning text-dark" style="font-size: 9px">PENDING REVIEW</span>
                            </td>
                            <td>{{ $project->user->name ?? 'N/A' }}</td> {{-- تعديل هنا: user بدل client --}}

                            <td class="text-info fw-bold">
                                {{ number_format($project->price, 2) }} {{-- تعديل هنا: price بدل budget --}}
                                <small style="font-size: 10px">{{ $project->currency }}</small>
                            </td>

                            <td class="small text-muted">{{ $project->created_at->format('Y-m-d') }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button onclick="approveProject('{{$project->id}}')" class="btn btn-sm btn-success shadow-sm">
                                        <i class="fas fa-check-circle me-1"></i> موافقة
                                    </button>
                                    <button onclick="rejectProject('{{$project->id}}')" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times"></i> رفض
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fs-2 mb-3"></i>
                                <p>لا توجد مشاريع معلقة حالياً</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // --- تبديل الأقسام الرئيسي ---
    function switchSection(section, btn) {
        document.querySelectorAll('.section-content').forEach(s => s.classList.remove('section-active'));
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));

        if(section === 'users') {
            document.getElementById('usersSection').classList.add('section-active');
        } else {
            document.getElementById('projectsSection').classList.add('section-active');
        }

        if(btn) btn.classList.add('active');
    }

    // --- تصفية صفوف المستخدمين ---
    function filterUsers(status) {
        const rows = document.querySelectorAll('.user-row');
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            if (status === 'all') {
                row.style.display = '';
            } else {
                row.style.display = (rowStatus === status) ? '' : 'none';
            }
        });
    }

    // --- توثيق المستخدم (Approve) ---
    function approveUser(id) {
        Swal.fire({
            title: 'توثيق الحساب؟',
            text: "سيتم منح المستخدم كافة الصلاحيات على المنصة",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'تفعيل الآن',
            cancelButtonText: 'إلغاء',
            background: '#141923', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'جاري المعالجة...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch(`/admin/user/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({ icon: 'success', title: 'تم التوثيق!', text: data.message, showConfirmButton: false, timer: 1500 });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: data.message });
                    }
                }).catch(err => {
                    Swal.fire({ icon: 'error', title: 'خطأ في الاتصال', text: 'تأكد من وجود الـ Route الصحيح' });
                });
            }
        });
    }

    // --- الموافقة على مشروع ---
    function approveProject(id) {
        Swal.fire({
            title: 'الموافقة على المشروع؟',
            text: "سيتم نشر المشروع ليتمكن المستقلون من التقديم",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'موافقة ونشر',
            cancelButtonText: 'إلغاء',
            background: '#141923', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/projects/${id}/approve`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(() => {
                    Swal.fire('تم!', 'تم نشر المشروع بنجاح', 'success');
                    setTimeout(() => location.reload(), 1500);
                });
            }
        });
    }

    // --- حظر المستخدم (Ban) ---
    function banUser(id) {
        Swal.fire({
            title: 'حظر المستخدم؟',
            text: "سيتم تقييد وصول الحساب للمنصة فوراً",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'نعم، حظر',
            cancelButtonText: 'تراجع',
            background: '#141923', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'جاري الحظر...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch(`/admin/user/${id}/ban`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({ icon: 'success', title: 'تم الحظر', text: data.message, showConfirmButton: false, timer: 1500 });
                        setTimeout(() => location.reload(), 1500);
                    }
                });
            }
        });
    }

    // --- البحث السريع ---
    document.getElementById('dbSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.section-active tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
        });
    });

    // --- رسم بياني مصغر للنمو ---
    document.addEventListener('DOMContentLoaded', function() {
        const ctxGrowth = document.getElementById('miniGrowthChart').getContext('2d');
        new Chart(ctxGrowth, {
            type: 'line',
            data: {
                labels: [1, 2, 3, 4, 5],
                datasets: [{
                    data: [12, 19, 13, 25, {{ $growthRate }}],
                    borderColor: '{{ $growthRate >= 0 ? "#10b981" : "#ef4444" }}',
                    borderWidth: 2, pointRadius: 0, fill: false, tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { display: false }, y: { display: false } },
                plugins: { legend: { display: false } }
            }
        });
    });
</script>
@endsection
