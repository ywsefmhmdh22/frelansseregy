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
    .filter-btn-group {
        background: rgba(0,0,0,0.3);
        padding: 6px;
        border-radius: 15px;
        border: 1px solid rgba(255,255,255,0.05);
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .filter-btn { border: none; background: transparent; color: var(--text-muted); padding: 8px 18px; border-radius: 12px; font-weight: bold; transition: 0.3s; font-size: 13px; }
    .filter-btn.active { background: var(--neon-blue); color: white; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4); }

    .section-content { display: none; }
    .section-active { display: block !important; }

    /* Identity Images in Modal */
    .id-card-preview { width: 100%; border-radius: 12px; border: 2px dashed #0ea5e9; margin-top: 10px; cursor: pointer; transition: 0.3s; }
    .id-card-preview:hover { transform: scale(1.02); }

    .swal2-popup-custom {
        border-radius: 25px !important;
        border: 1px solid var(--neon-blue) !important;
        width: 800px !important;
    }

    .info-label { color: var(--neon-blue); font-weight: bold; min-width: 100px; display: inline-block; }
    .skill-badge { background: rgba(14, 165, 233, 0.1); color: #fff; padding: 2px 8px; border-radius: 6px; font-size: 11px; border: 1px solid rgba(14, 165, 233, 0.3); margin-right: 4px; }
</style>

<div class="dashboard-wrapper">
    {{-- Navbar --}}
    <nav class="glass-navbar d-flex flex-wrap justify-content-between align-items-center animate__animated animate__fadeInDown gap-3">
        <div class="logo d-flex align-items-center">
            <h4 class="mb-0 fw-900" style="font-family: 'Orbitron'; letter-spacing: 2px;">
                <i class="fas fa-shield-halved text-info me-2"></i>FOX<span class="text-info">ACCOUNTING</span>
            </h4>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3">
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
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-4 mb-4">
            <div>
                <h5 class="fw-bold mb-1"><i class="fas fa-users-cog me-2 text-info"></i>إدارة النظام الشاملة</h5>
                <p class="text-muted small mb-0">تحكم كامل في الحسابات، المالية، والنزاعات</p>
            </div>

            <div class="filter-btn-group">
                <button class="filter-btn active" onclick="switchSection('users', this); filterRole('all')">الكل</button>
                <button class="filter-btn" onclick="switchSection('users', this); filterRole('freelancer')">المستقلين</button>
                <button class="filter-btn" onclick="switchSection('users', this); filterRole('client')">العملاء</button>
                <button class="filter-btn" onclick="switchSection('projects', this)">مشاريع معلقة</button>
                <button class="filter-btn" onclick="switchSection('deposits', this)">شحن</button>
                <button class="filter-btn" onclick="switchSection('withdrawals', this)">سحب</button>
                <button class="filter-btn" onclick="switchSection('disputes', this)">نزاعات</button>
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
                        <tr class="user-row animate__animated animate__fadeIn" data-status="{{ $user->verification_status }}" data-role="{{ $user->role }}">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @php
                                        $p_img = $user->profile_image ? asset('storage/' . $user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=random';
                                    @endphp
                                    <img src="{{ $p_img }}" class="rounded-circle border border-secondary" width="40" height="40" style="object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0 small fw-bold">{{ $user->name }}</h6>
                                        <small class="text-muted" style="font-size: 10px">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                 <span class="badge bg-dark border {{ $user->role == 'client' ? 'border-success text-success' : 'border-info text-info' }}" style="font-size: 10px">
                                    {{ strtoupper($user->role) }}
                                 </span>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($user->wallet->balance ?? 0) }} ج.م</td>
                            <td>
                                @if($user->verification_status == 'pending')
                                    <span class="badge bg-warning text-dark animate__animated animate__flash animate__infinite">
                                        <i class="fas fa-spinner fa-spin me-1"></i> مراجعة
                                    </span>
                                @elseif($user->verification_status == 'verified')
                                    <span class="text-success small fw-bold"><i class="fas fa-check-double me-1"></i> موثق</span>
                                @else
                                    <span class="text-danger small fw-bold"><i class="fas fa-times-circle me-1"></i> غير موثق</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    @if($user->is_profile_completed == 0)
                                        <button onclick="approveUser('{{$user->id}}', 'activation')" class="btn btn-sm btn-success px-3" title="تفعيل بسيط">
                                            تفعيل الحساب
                                        </button>
                                    @else
                                        <button onclick="showVerifyModal({{ json_encode($user) }})" class="btn btn-sm btn-info px-3" title="توثيق نهائي">
                                            توثيق وتفعيل نهائي
                                        </button>
                                    @endif

                                    <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>

                                    <button onclick="banUser('{{$user->id}}')" class="btn btn-sm btn-outline-warning" title="حظر">
                                        <i class="fas fa-ban"></i>
                                    </button>

                                    <button onclick="deleteUser('{{$user->id}}')" class="btn btn-sm btn-outline-danger" title="حذف نهائي">
                                        <i class="fas fa-trash-alt"></i>
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
                            <td>{{ $project->user->name ?? 'N/A' }}</td>
                            <td class="text-info fw-bold">{{ number_format($project->price, 2) }}</td>
                            <td class="small text-muted">{{ $project->created_at->format('Y-m-d') }}</td>
                            <td class="text-center">
                                <button onclick="approveProject('{{$project->id}}')" class="btn btn-sm btn-success">موافقة</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">لا توجد مشاريع معلقة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Deposits Table --}}
        <div id="depositsSection" class="section-content">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead class="text-muted small border-bottom border-secondary">
                        <tr>
                            <th>المستخدم</th>
                            <th>المبلغ</th>
                            <th>الوسيلة</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits ?? [] as $deposit)
                        <tr>
                            <td>{{ $deposit->user->name }}</td>
                            <td class="text-success fw-bold">+ {{ number_format($deposit->amount) }}</td>
                            <td><span class="badge bg-secondary">{{ $deposit->method }}</span></td>
                            <td><span class="badge bg-{{ $deposit->status == 'completed' ? 'success' : 'warning' }}">{{ $deposit->status }}</span></td>
                            <td class="small text-muted">{{ $deposit->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">لا توجد عمليات شحن</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Withdrawals Table --}}
        <div id="withdrawalsSection" class="section-content">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead class="text-muted small border-bottom border-secondary">
                        <tr>
                            <th>المستخدم</th>
                            <th>المبلغ</th>
                            <th>البيانات</th>
                            <th>الحالة</th>
                            <th class="text-center">التحكم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals ?? [] as $withdraw)
                        <tr>
                            <td>{{ $withdraw->user->name }}</td>
                            <td class="text-danger fw-bold">- {{ number_format($withdraw->amount) }}</td>
                            <td class="small text-muted">{{ $withdraw->payment_details }}</td>
                            <td><span class="badge bg-{{ $withdraw->status == 'pending' ? 'warning' : 'success' }}">{{ $withdraw->status }}</span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" onclick="processWithdraw('{{$withdraw->id}}', '{{$withdraw->user->name}}', '{{$withdraw->amount}}')">إجراء</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">لا توجد طلبات سحب</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Disputes Table --}}
        <div id="disputesSection" class="section-content">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead class="text-muted small border-bottom border-secondary">
                        <tr>
                            <th>المشروع</th>
                            <th>الأطراف</th>
                            <th>القيمة</th>
                            <th>الحالة</th>
                            <th class="text-center">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disputedProjects ?? [] as $dispute)
                        <tr>
                            <td>{{ $dispute->project->title ?? 'N/A' }}</td>
                            <td>{{ $dispute->client->name }} vs {{ $dispute->freelancer->name }}</td>
                            <td class="text-success fw-bold">{{ number_format($dispute->amount) }} ج.م</td>
                            <td><span class="badge bg-danger">مفتوح</span></td>
                            <td class="text-center">
                                <a href="{{ route('admin.disputes.show', $dispute->id) }}" class="btn btn-sm btn-outline-danger px-3 rounded-pill">دخول المحكمة</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">لا توجد نزاعات نشطة</td></tr>
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

        const sectionMap = {
            'users': 'usersSection',
            'projects': 'projectsSection',
            'deposits': 'depositsSection',
            'withdrawals': 'withdrawalsSection',
            'disputes': 'disputesSection'
        };

        if(sectionMap[section]) {
            document.getElementById(sectionMap[section]).classList.add('section-active');
        }

        if(btn) btn.classList.add('active');
    }

    // --- فلترة حسب الدور ---
    function filterRole(role) {
        const rows = document.querySelectorAll('.user-row');
        rows.forEach(row => {
            const userRole = row.getAttribute('data-role');
            if (role === 'all') {
                row.style.display = '';
            } else {
                row.style.display = (userRole === role) ? '' : 'none';
            }
        });
    }

    // --- عرض بيانات التوثيق الشاملة ---
     function showVerifyModal(user) {
    // تجهيز المهارات كـ Badges
    let skillsHtml = '';
    if (user.skills) {
        let skillsArray = user.skills.split(',');
        skillsHtml = skillsArray.map(s => `<span class="skill-badge">${s.trim()}</span>`).join('');
    } else {
        skillsHtml = '<span class="text-muted">لا يوجد</span>';
    }

    /**
     * تعديل جلب الصور ليتوافق مع Laravel Cloud (S3)
     * نقوم بفحص ما إذا كان المسار يبدأ بـ http لضمان عدم تكرار الرابط إذا كان قادماً من السيرفر جاهزاً
     * أو نقوم ببناء الرابط باستخدام الدومين الخاص بالتخزين السحابي
     */
    const storageBase = "https://frelansseregy.s3.amazonaws.com/"; // تأكد من مطابقة هذا الرابط لإعدادات AWS_URL لديك

    const getFullUrl = (path) => {
        if (!path) return null;
        if (path.startsWith('http')) return path;
        // إزالة /storage/ من بداية المسار إذا وجدت لتجنب التكرار مع رابط S3
        const cleanPath = path.replace(/^\/?storage\//, '');
        return storageBase + cleanPath;
    };

    let profilePic = user.profile_image
        ? getFullUrl(user.profile_image)
        : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random`;

    let idFront = getFullUrl(user.id_image);
    let idBack = getFullUrl(user.id_image_back);

    Swal.fire({
        title: `<span style="color:#0ea5e9; font-family:Orbitron;">USER VERIFICATION DOSSIER</span>`,
        width: '800px',
        background: '#0f172a',
        html: `
            <div class="text-start" style="font-size:13px; color: #e2e8f0;">
                <div class="row g-3">
                    <div class="col-md-4 text-center border-end border-secondary">
                        <img src="${profilePic}" class="rounded-circle border border-info mb-2" width="100" height="100" style="object-fit:cover;">
                        <h5 class="mb-0 text-info fw-bold">${user.name}</h5>
                        <p class="text-muted small">${(user.role || '').toUpperCase()}</p>
                        <hr class="border-secondary">
                        <div class="text-start ps-2">
                            <p class="mb-1"><span class="info-label">رقم الهوية:</span> ${user.id_number || '---'}</p>
                            <p class="mb-1"><span class="info-label">الهاتف:</span> ${user.phone || '---'}</p>
                            <p class="mb-1"><span class="info-label">الموقع:</span> ${user.country || ''}, ${user.city || ''}</p>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="mb-3">
                            <h6 class="text-info fw-bold"><i class="fas fa-briefcase me-2"></i>التخصص (Headline)</h6>
                            <p class="bg-dark p-2 rounded border border-secondary">${user.headline || 'لم يتم تحديده'}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-info fw-bold"><i class="fas fa-tags me-2"></i>المهارات (Skills)</h6>
                            <div class="d-flex flex-wrap gap-1">${skillsHtml}</div>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-info fw-bold"><i class="fas fa-info-circle me-2"></i>النبذة التعريفية (Bio)</h6>
                            <div class="bg-dark p-2 rounded border border-secondary" style="max-height:80px; overflow-y:auto;">
                                ${user.bio || 'لا توجد نبذة'}
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="text-center text-info fw-bold mb-3 border-top border-secondary pt-3">وثائق الهوية الرسمية</h6>
                        <div class="row">
                            <div class="col-6 text-center">
                                <small class="text-muted d-block mb-1">الوجه الأمامي (Front)</small>
                                <img src="${idFront || ''}" class="id-card-preview img-fluid rounded border border-secondary"
                                     onclick="window.open(this.src)"
                                     style="cursor:pointer; max-height:200px;"
                                     onerror="this.src='https://placehold.co/400x250/1e293b/0ea5e9?text=No+Front+Image'">
                            </div>
                            <div class="col-6 text-center">
                                <small class="text-muted d-block mb-1">الوجه الخلفي (Back)</small>
                                <img src="${idBack || ''}" class="id-card-preview img-fluid rounded border border-secondary"
                                     onclick="window.open(this.src)"
                                     style="cursor:pointer; max-height:200px;"
                                     onerror="this.src='https://placehold.co/400x250/1e293b/0ea5e9?text=No+Back+Image'">
                            </div>
                        </div>
                    </div>
                </div>
            </div>`,
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            container: 'my-swal-container',
            popup: 'my-swal-popup',
        }
    });
}
            `,
            background: '#0f172a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check-circle me-2"></i> توثيق واعتماد الحساب',
            cancelButtonText: 'إغلاق',
            confirmButtonColor: '#10b981',
            customClass: { popup: 'swal2-popup-custom' }
        }).then((result) => {
            if (result.isConfirmed) {
                approveUser(user.id, 'verification');
            }
        });
    }

    // --- توثيق/تفعيل المستخدم ---
    function approveUser(id, type) {
        let title = type === 'activation' ? 'تفعيل الحساب؟' : 'إتمام التوثيق النهائي؟';
        Swal.fire({
            title: title,
            text: "سيتم تغيير حالة التوثيق للمستخدم فوراً",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'إلغاء',
            background: '#141923', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'جاري المعالجة...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                fetch(`/admin/user/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ type: type })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({ icon: 'success', title: 'تمت العملية بنجاح', showConfirmButton: false, timer: 1500 });
                        setTimeout(() => location.reload(), 1500);
                    }
                });
            }
        });
    }

    // --- حظر المستخدم ---
    function banUser(id) {
        Swal.fire({
            title: 'حظر المستخدم؟',
            text: "سيتم تقييد وصول الحساب للمنصة فوراً",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'نعم، حظر',
            background: '#141923', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/user/${id}/ban`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({ icon: 'success', title: 'تم الحظر بنجاح', showConfirmButton: false, timer: 1500 });
                        setTimeout(() => location.reload(), 1500);
                    }
                });
            }
        });
    }

    // --- حذف المستخدم نهائياً من قاعدة البيانات ---
     function deleteUser(id) {
    Swal.fire({
        title: 'هل أنت متأكد تماماً؟',
        text: "سيتم حذف المستخدم نهائياً من قاعدة البيانات!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'نعم، احذف نهائياً',
        background: '#0f172a',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            // انتبه لهذا المسار: يجب أن يطابق تماماً ما وضعته في web.php
            fetch(`/admin/user/${id}/delete`, {
                method: 'DELETE', // تأكد أن النوع DELETE
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('تم الحذف!', data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('خطأ!', data.message, 'error');
                }
            })
            .catch(err => Swal.fire('خطأ!', 'المسار غير موجود أو حدث خطأ بالخادم', 'error'));
        }
    });
}

    // --- معالجة طلب السحب ---
    function processWithdraw(id, userName, amount) {
        Swal.fire({
            title: `<span style="color:#0ea5e9">اتخاذ قرار بشأن طلب سحب</span>`,
            html: `
                <div class="text-start mb-3" style="font-size:14px">
                    <p class="mb-1">المستخدم: <b>${userName}</b></p>
                    <p>المبلغ: <b class="text-success">${amount} ج.م</b></p>
                </div>
                <select id="swal-status" class="form-select bg-dark text-white border-secondary mb-3">
                    <option value="approve">✅ موافقة على السحب</option>
                    <option value="reject">❌ رفض طلب السحب</option>
                </select>
                <textarea id="swal-message" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="اكتب رسالة الإشعار للمستخدم هنا..."></textarea>
            `,
            background: '#141923',
            color: '#fff',
            showCancelButton: true,
            confirmButtonText: 'تنفيذ القرار',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#0ea5e9',
            customClass: { popup: 'swal2-popup-custom' },
            preConfirm: () => {
                const status = document.getElementById('swal-status').value;
                const message = document.getElementById('swal-message').value;
                if (!message) {
                    Swal.showValidationMessage('يرجى كتابة رسالة توضيحية للمستخدم');
                }
                return { status: status, message: message };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/withdraw/${id}/process`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(result.value)
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('تم التنفيذ!', 'تم معالجة طلب السحب بنجاح', 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                });
            }
        });
    }

    // --- بحث سريع في الجدول ---
    document.getElementById('dbSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        document.querySelectorAll("tbody tr").forEach(row => {
            row.style.display = (row.innerText.toLowerCase().indexOf(value) > -1) ? "" : "none";
        });
    });

    // --- رسم بياني صغير (Mini Chart) ---
    const ctx = document.getElementById('miniGrowthChart');
    if(ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['', '', '', '', '', ''],
                datasets: [{
                    data: [12, 19, 15, 25, 22, 30],
                    borderColor: '#10b981',
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: true,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    }
</script>
@endsection
