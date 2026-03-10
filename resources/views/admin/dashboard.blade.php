 @extends('layouts.master')

@section('content')
<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-5 bg-white p-4 rounded-4 shadow-sm border-start border-5 border-dark">
        <div>
            <h2 class="fw-bold text-dark mb-1"><i class="fas fa-user-shield me-2 text-primary"></i> مركز قيادة المنصة</h2>
            <p class="text-secondary mb-0 small">أهلاً بك أيها المدير. لديك التحكم الكامل في كافة مفاصل المنصة ومراقبة النشاط اللحظي.</p>
        </div>
        <div class="stats-pills d-flex gap-3">
            <div class="stat-pill bg-white p-3 rounded-4 border shadow-sm">
                <small class="text-muted d-block fw-bold mb-1">إجمالي المستخدمين</small>
                <span class="fw-bold fs-4 text-dark">{{ $users->count() }}</span>
            </div>
            <div class="stat-pill bg-white p-3 rounded-4 border shadow-sm border-warning">
                <small class="text-muted d-block fw-bold mb-1">بانتظار الموافقة</small>
                <span class="fw-bold fs-4 text-warning">{{ $pendingProjects->count() }}</span>
            </div>
            <div class="stat-pill bg-white p-3 rounded-4 border shadow-sm border-success">
                <small class="text-muted d-block fw-bold mb-1">المتصلون الآن</small>
                <span class="fw-bold fs-4 text-success">{{ $users->filter(fn($u) => $u->isOnline())->count() }}</span>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mb-4">
        <ul class="nav nav-pills gap-2 bg-white p-2 rounded-pill shadow-sm border" id="adminTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active rounded-pill px-5 fw-bold" id="freelancers-tab" data-bs-toggle="pill" data-bs-target="#freelancers" type="button">المستقلين</button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill px-5 fw-bold" id="clients-tab" data-bs-toggle="pill" data-bs-target="#clients" type="button">العملاء</button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill px-5 fw-bold position-relative" id="projects-tab" data-bs-toggle="pill" data-bs-target="#projects" type="button">
                    المشاريع المعلقة
                    @if($pendingProjects->count() > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow">
                            {{ $pendingProjects->count() }}
                        </span>
                    @endif
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="adminTabContent">

        <div class="tab-pane fade show active" id="freelancers" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">المستقل</th>
                                <th>التواجد</th>
                                <th>اكتمال الملف</th>
                                <th>آخر ظهور</th>
                                <th class="text-center">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users->where('role', 'freelancer') as $freelancer)
                            <tr class="{{ $freelancer->is_banned ? 'bg-light opacity-75' : '' }}">
                                <td class="px-4">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($freelancer->name) }}&background=random" class="rounded-circle me-3" width="45">
                                            @if($freelancer->isOnline())
                                                <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1" style="transform: translate(-10px, -5px);"></span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $freelancer->name }}</div>
                                            <small class="text-muted">{{ $freelancer->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($freelancer->isOnline())
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">متصل الآن</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">أوفلاين</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; width: 80px;">
                                            <div class="progress-bar bg-{{ $freelancer->is_profile_completed ? 'success' : 'warning' }}" style="width: {{ $freelancer->is_profile_completed ? '100%' : '40%' }}"></div>
                                        </div>
                                        <small class="text-muted fw-bold">{{ $freelancer->is_profile_completed ? '100%' : '40%' }}</small>
                                    </div>
                                </td>
                                <td><small class="text-muted fw-bold">{{ $freelancer->last_seen ? $freelancer->last_seen->diffForHumans() : 'لم يظهر أبداً' }}</small></td>
                                <td class="text-center">
                                    <form action="{{ route('admin.user.ban', $freelancer->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm {{ $freelancer->is_banned ? 'btn-success' : 'btn-outline-danger' }} rounded-pill px-3">
                                            <i class="fas {{ $freelancer->is_banned ? 'fa-unlock' : 'fa-ban' }} me-1"></i> {{ $freelancer->is_banned ? 'إلغاء الحظر' : 'حظر' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="clients" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">العميل</th>
                                <th>الحالة</th>
                                <th>آخر نشاط</th>
                                <th class="text-center">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users->where('role', 'client') as $client)
                            <tr class="{{ $client->is_banned ? 'bg-light opacity-75' : '' }}">
                                <td class="px-4 text-dark fw-bold">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($client->name) }}&background=0D6EFD&color=fff" class="rounded-circle me-3" width="45">
                                    {{ $client->name }}
                                </td>
                                <td>
                                    @if($client->is_banned)
                                        <span class="badge bg-danger rounded-pill">محظور</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill">نشط</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $client->last_seen ? $client->last_seen->format('Y-m-d H:i') : 'لا يوجد' }}</small></td>
                                <td class="text-center">
                                    <form action="{{ route('admin.user.ban', $client->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-light border rounded-pill px-4">إدارة الحساب</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="projects" role="tabpanel">
            <div class="alert alert-dark border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center">
                <i class="fas fa-shield-alt fa-2x me-3 text-warning"></i>
                <div>
                    <h6 class="fw-bold mb-1">نظام المراجعة الصارم</h6>
                    <small>أنت في وضع "الحارس". أي مشروع يظهر هنا هو مخفي تماماً عن المستقلين حتى تمنحه الضوء الأخضر.</small>
                </div>
            </div>

            <div class="row g-4">
                @forelse($pendingProjects as $project)
                <div class="col-md-6 col-xl-4">
                    <div class="project-mod-card bg-white rounded-4 shadow-sm border p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-bold">مراجعة أمنية</span>
                             <small class="text-muted fw-bold">
    <i class="far fa-clock me-1"></i>
    {{ $project->created_at ? $project->created_at->diffForHumans() : 'منذ قليل' }}
</small>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">{{ $project->title }}</h5>
                        <p class="text-secondary small mb-4 flex-grow-1">{{ Str::limit($project->description, 130) }}</p>

                        <div class="d-flex align-items-center mb-4 p-2 bg-light rounded-4">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($project->user->name) }}" class="rounded-circle me-2 border border-white" width="35">
                            <div>
                                <small class="text-muted d-block" style="font-size: 10px;">طلب بواسطة:</small>
                                <small class="fw-bold">{{ $project->user->name }}</small>
                            </div>
                            <div class="ms-auto pe-2">
                                <span class="fw-bold text-primary">{{ number_format($project->price) }} ج.م</span>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <form action="{{ route('admin.projects.approve', $project->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-success w-100 rounded-pill fw-bold py-2 shadow-sm"><i class="fas fa-check-circle me-1"></i> فسح</button>
                                </form>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-outline-danger w-100 rounded-pill fw-bold py-2" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $project->id }}">
                                    <i class="fas fa-trash-alt me-1"></i> حذف
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="deleteModal{{ $project->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow-lg">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="fw-bold text-danger">سبب الرفض والحذف</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.projects.delete', $project->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <div class="modal-body py-4">
                                        <p class="text-secondary small mb-3">سيتم إرسال الرسالة التالية للعميل لإخطاره بسبب حذف مشروعه:</p>
                                        <textarea name="notification_message" class="form-control border-0 bg-light p-3 rounded-4" rows="4" placeholder="مثال: المشروع مخالف لسياسة المحتوى الخاصة بنا..." required></textarea>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-danger rounded-pill px-4 shadow">تأكيد الحذف النهائي</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-check-double fa-4x text-light mb-3"></i>
                    <h5 class="text-muted">الرادار نظيف! لا توجد مشاريع تنتظر المراجعة.</h5>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<style>
    body { background-color: #f8fafc; }
    .nav-pills .nav-link { color: #64748b; background: #fff; border: 1px solid #e2e8f0; margin: 0 5px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .nav-pills .nav-link.active { background: #0f172a !important; color: white !important; transform: scale(1.05); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .project-mod-card { transition: all 0.4s ease; border: 1px solid #e2e8f0; }
    .project-mod-card:hover { transform: translateY(-10px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1) !important; border-color: #3b82f6; }
    .stat-pill { min-width: 140px; }
    .table thead th { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 1px; }
    .status-indicator { display: inline-block; width: 10px; height: 10px; border-radius: 50%; }
</style>
@endsection
