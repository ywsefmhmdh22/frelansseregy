@extends('layouts.master')

@section('content')
<div class="container py-5" dir="rtl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">إدارة مشاريعي</h2>
            <p class="text-muted small">هنا يمكنك متابعة حالة كافة مشاريعك وتعديلها.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="btn btn-success rounded-pill px-4 fw-bold">
            <i class="fas fa-plus me-1" aria-hidden="true"></i> إضافة مشروع جديد
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active rounded-pill" href="#">الكل</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-muted" href="#">قيد التنفيذ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-muted" href="#">مكتملة</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="row g-4">
        @forelse($myProjects as $project)
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden project-card-hover">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <span class="badge {{ $project->admin_status == 'approved' ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' }} rounded-pill px-3">
                                    {{ $project->admin_status == 'approved' ? 'منشور' : 'قيد المراجعة' }}
                                </span>
                                <span class="text-muted small">
                                    <i class="far fa-calendar-alt me-1" aria-hidden="true"></i>
                                    {{ $project->created_at->format('Y/m/d') }}
                                </span>
                            </div>
                            <h5 class="fw-bold mb-2">{{ $project->title }}</h5>
                            <p class="text-muted small mb-0">{{ Str::limit($project->description, 150) }}</p>
                        </div>
                        <div class="col-md-3 text-md-center my-3 my-md-0">
                            <div class="d-inline-block text-center px-4 border-start border-end">
                                <h5 class="fw-bold mb-0">{{ $project->offers_count }}</h5>
                                <small class="text-muted">عرض مقدم</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('projects.offers', $project->id) }}" class="btn btn-primary rounded-pill btn-sm">
                                    <i class="fas fa-eye me-1" aria-hidden="true"></i> عرض العروض
                                </a>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-outline-secondary rounded-pill btn-sm flex-grow-1" aria-label="تعديل مشروع: {{ $project->title }}">
                                        <i class="fas fa-edit" aria-hidden="true"></i> تعديل
                                    </a>
                                    <button class="btn btn-outline-danger rounded-pill btn-sm flex-grow-1" aria-label="حذف مشروع: {{ $project->title }}">
                                        <i class="fas fa-trash" aria-hidden="true"></i> حذف
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="bg-white p-5 rounded-4 shadow-sm">
                <img src="https://illustrations.popsy.co/flat/searching.svg"
                     width="200"
                     alt="رسم توضيحي يدل على البحث وعدم وجود نتائج"
                     class="mb-3">
                <h5 class="text-muted">لم تقم بإضافة أي مشاريع بعد.</h5>
                <a href="{{ route('projects.create') }}" class="btn btn-success mt-3 rounded-pill px-4">ابدأ مشروعك الأول الآن</a>
            </div>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-5">
        {{ $myProjects->links() }}
    </div>
</div>

<style>
    .project-card-hover { transition: all 0.3s ease; border-right: 4px solid transparent !important; }
    .project-card-hover:hover { transform: scale(1.01); border-right-color: #10b981 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
    .bg-soft-success { background: #dcfce7; }
    .bg-soft-warning { background: #fef9c3; }
    .nav-pills .nav-link.active { background-color: #10b981; }
</style>
@endsection
