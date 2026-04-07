@extends('layouts.master')

@section('content')
<div class="container py-5" style="direction: rtl;">
    <div class="mb-4 animate__animated animate__fadeIn">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-secondary rounded-pill px-3">
            <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
        </a>
    </div>

    <div class="row">
        {{-- قسم تفاصيل الحساب --}}
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 rounded-4 p-4 h-100" style="background: rgba(255, 255, 255, 0.05); color: #fff; border: 1px solid rgba(255, 255, 255, 0.1) !important;">
                <h5 class="fw-bold mb-4 border-bottom pb-2 border-secondary">
                    <i class="fas fa-info-circle text-info me-2"></i>تفاصيل الحساب
                </h5>
                <div class="user-info-list">
                    <p><strong>الاسم:</strong> {{ $user->name }}</p>
                    <p><strong>الهاتف:</strong> {{ $user->phone ?? 'غير مسجل' }}</p>
                    <p><strong>الموقع:</strong> {{ $user->city }}, {{ $user->country }}</p>
                    <p><strong>رقم الهوية:</strong> <span class="badge bg-dark text-warning">{{ $user->id_number }}</span></p>
                    <p class="mb-0"><strong>النبذة:</strong></p>
                    <p class="text-muted small italic">{{ $user->bio ?? 'لا يوجد نبذة تعريفية' }}</p>
                </div>
            </div>
        </div>

        {{-- قسم مراجعة الوثائق --}}
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 rounded-4 p-4 h-100" style="background: rgba(255, 255, 255, 0.05); color: #fff; border: 1px solid rgba(255, 255, 255, 0.1) !important;">
                <h5 class="fw-bold mb-4 border-bottom pb-2 text-primary border-secondary">
                    <i class="fas fa-id-card me-2"></i>مراجعة وثائق الهوية
                </h5>

                <div class="row g-3">
                    {{-- الوجه الأمامي --}}
                    <div class="col-6">
                        {{-- تم استبدال label بـ span أو إضافته بشكل صحيح برمجياً --}}
                        <span id="front-id-label" class="d-block mb-2 small text-muted">الوجه الأمامي للبطاقة</span>
                        <a href="{{ asset('storage/' . $user->id_image) }}" target="_blank" aria-labelledby="front-id-label">
                            <img src="{{ asset('storage/' . $user->id_image) }}"
                                 class="img-fluid rounded border border-secondary shadow-sm"
                                 alt="صورة الوجه الأمامي لبطاقة هوية {{ $user->name }}">
                        </a>
                    </div>

                    {{-- الوجه الخلفي --}}
                    <div class="col-6">
                        <span id="back-id-label" class="d-block mb-2 small text-muted">الوجه الخلفي للبطاقة</span>
                        <a href="{{ asset('storage/' . $user->id_image_back) }}" target="_blank" aria-labelledby="back-id-label">
                            <img src="{{ asset('storage/' . $user->id_image_back) }}"
                                 class="img-fluid rounded border border-secondary shadow-sm"
                                 alt="صورة الوجه الخلفي لبطاقة هوية {{ $user->name }}">
                        </a>
                    </div>
                </div>

                {{-- إجراءات التوثيق --}}
                <div class="mt-5 pt-3 border-top border-secondary">
                    @if($user->verification_status !== 'verified')
                        <form action="{{ route('admin.verify', $user->id) }}" method="POST" id="verifyForm">
                            @csrf
                            <button type="button" onclick="confirmVerification()" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow">
                                <i class="fas fa-check-double me-2"></i> توثيق هذا الحساب الآن
                            </button>
                        </form>
                    @else
                        <div class="alert alert-success text-center fw-bold rounded-4 border-0 py-3">
                            <i class="fas fa-check-circle me-2 fs-4"></i> هذا الحساب موثق بالفعل
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- إضافة لمسة تفاعلية للتوثيق --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmVerification() {
        Swal.fire({
            title: 'هل راجعت البيانات جيداً؟',
            text: "بتوثيقك لهذا الحساب، أنت تؤكد صحة هوية المستخدم",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، الحساب سليم',
            cancelButtonText: 'إلغاء',
            background: '#141923',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('verifyForm').submit();
            }
        })
    }
</script>
@endsection
