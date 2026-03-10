@extends('layouts.master')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم</a>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4 p-4">
                <h5 class="fw-bold mb-4 border-bottom pb-2">تفاصيل الحساب</h5>
                <p><strong>الاسم:</strong> {{ $user->name }}</p>
                <p><strong>الهاتف:</strong> {{ $user->phone ?? 'غير مسجل' }}</p>
                <p><strong>الموقع:</strong> {{ $user->city }}, {{ $user->country }}</p>
                <p><strong>رقم الهوية:</strong> {{ $user->id_number }}</p>
                <p><strong>النبذة:</strong> {{ $user->bio }}</p>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm border-0 rounded-4 p-4">
                <h5 class="fw-bold mb-4 border-bottom pb-2 text-primary">مراجعة وثائق الهوية</h5>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="d-block mb-2 small text-muted">الوجه الأمامي</label>
                        <a href="{{ asset('storage/' . $user->id_image) }}" target="_blank">
                            <img src="{{ asset('storage/' . $user->id_image) }}" class="img-fluid rounded border shadow-sm" alt="الوجه الأمامي">
                        </a>
                    </div>
                    <div class="col-6">
                        <label class="d-block mb-2 small text-muted">الوجه الخلفي</label>
                        <a href="{{ asset('storage/' . $user->id_image_back) }}" target="_blank">
                            <img src="{{ asset('storage/' . $user->id_image_back) }}" class="img-fluid rounded border shadow-sm" alt="الوجه الخلفي">
                        </a>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-top">
                    @if($user->verification_status !== 'verified')
                        <form action="{{ route('admin.verify', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                                <i class="fas fa-check-double me-2"></i> توثيق هذا الحساب الآن
                            </button>
                        </form>
                    @else
                        <div class="alert alert-success text-center fw-bold">
                            <i class="fas fa-check-circle me-2"></i> هذا الحساب موثق بالفعل
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
