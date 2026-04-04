@extends('layouts.master')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --bg-dark: #0b0e14;
        --card-bg: rgba(20, 25, 35, 0.85);
        --neon-yellow: #f59e0b;
        --neon-blue: #00d2ff;
    }
    body { background-color: var(--bg-dark); color: #fff; font-family: 'Cairo', sans-serif; }

    .project-card {
        background: var(--card-bg);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        margin-bottom: 25px;
        transition: 0.3s;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .project-img-container {
        width: 100%;
        height: 180px;
        overflow: hidden;
        position: relative;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .project-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: 0.5s;
    }
    .project-card:hover .project-img { transform: scale(1.1); }

    .project-card:hover {
        border-color: var(--neon-yellow);
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.6);
    }

    .card-body-custom { padding: 20px; }

    .price-tag {
        color: #10b981;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .badge-review {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 10;
        background: rgba(245, 158, 11, 0.9);
        color: #000;
        font-weight: bold;
    }
</style>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-5 animate__animated animate__fadeIn">
        <div>
            <h2 class="fw-900 shadow-sm"><i class="fas fa-gavel text-warning me-2"></i>مراجعة المشاريع المعلقة</h2>
            <p class="text-muted">لديك {{ $projects->count() }} مشروع ينتظر مراجعتك يا هندسة</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="fas fa-arrow-right me-2"></i> العودة للرئيسية
        </a>
    </div>

    <div class="row">
        @forelse($projects as $project)
        <div class="col-md-6 col-xl-4 animate__animated animate__zoomIn">
            <div class="project-card">
                <div class="project-img-container">
                    <span class="badge badge-review rounded-pill px-3">قيد المراجعة</span>
                    @if($project->image_url)
                        <img src="{{ asset('storage/' . $project->image_url) }}" class="project-img" alt="Project Cover">
                    @else
                        <img src="https://via.placeholder.com/400x200?text=No+Cover+Image" class="project-img" alt="No Image">
                    @endif
                </div>

                <div class="card-body-custom">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small"><i class="far fa-clock me-1"></i> {{ $project->created_at->diffForHumans() }}</span>
                        <span class="text-info small fw-bold">{{ strtoupper($project->type) }}</span>
                    </div>

                    <h5 class="fw-bold text-white mb-2">{{ $project->title }}</h5>

                    <p class="text-muted small" style="height: 45px; overflow: hidden;">
                        {{ Str::limit(strip_tags($project->description), 85) }}
                    </p>

                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-calendar-alt text-muted me-2"></i>
                        <small class="text-muted">المدة المتوقعة: {{ $project->duration }} يوم</small>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.1)">

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="d-block text-muted">الميزانية</small>
                            <span class="price-tag">
                                {{ number_format($project->price, 2) }}
                                <small>{{ $project->currency }}</small>
                            </span>
                        </div>

                        <div class="d-flex gap-2">
                            <button onclick="approveProject({{ $project->id }})" class="btn btn-sm btn-success rounded-pill px-3">
                                <i class="fas fa-check"></i> قبول
                            </button>
                            <button onclick="rejectProject({{ $project->id }})" class="btn btn-sm btn-outline-danger rounded-pill">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="fas fa-coffee fs-1 text-muted mb-3"></i>
            <h4 class="text-muted">كل شيء تمام! لا توجد مشاريع لمراجعتها حالياً.</h4>
        </div>
        @endforelse
    </div>
</div>

<script>
    // 1. دالة القبول
    function approveProject(id) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "بموافقتك سيتم عرض المشروع في Freelancerig رسمياً",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'نعم، وافق على النشر',
            cancelButtonText: 'إلغاء',
            background: '#141923', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                sendAction(id, 'approve');
            }
        });
    }

    // 2. دالة الرفض (اللي كانت ناقصة)
    function rejectProject(id) {
        Swal.fire({
            title: 'هل تريد رفض المشروع؟',
            text: "سيتم تغيير حالة المشروع إلى مرفوض ولن يظهر للعامة",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'نعم، رفض المشروع',
            cancelButtonText: 'تراجع',
            background: '#141923', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                sendAction(id, 'reject');
            }
        });
    }

    // 3. دالة الإرسال الموحدة
    function sendAction(id, action) {
        // تأكد أن الرابط هنا يطابق الـ Routes في web.php
        fetch(`/admin/projects/${id}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if(data.success) {
                Swal.fire({
                    title: 'عاش يا وحش!',
                    text: data.message,
                    icon: 'success',
                    background: '#141923', color: '#fff'
                }).then(() => location.reload());
            } else {
                Swal.fire('خطأ!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('عطل فني', 'تأكد من أن الـ Routes معرفة بشكل صحيح في ملف web.php', 'error');
        });
    }
</script>
@endsection
