@extends('layouts.master')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center py-5" style="min-height: 70vh;">
    <div class="auth-card shadow-lg rounded-4 bg-white p-5 w-100" style="max-width: 450px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark mb-2">تغيير كلمة المرور</h2>
            <p class="text-secondary">قم بإنشاء كلمة مرور قوية وجديدة</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label class="form-label fw-bold">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control bg-light" value="{{ request()->email }}" required readonly>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autofocus placeholder="••••••••">
                @error('password') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-success w-100 py-3 fs-5 border-0 shadow-sm" style="background-color: #10b981;">
                تحديث كلمة المرور
            </button>
        </form>
    </div>
</div>
@endsection
