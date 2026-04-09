@extends('layouts.master')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center py-5" style="min-height: 70vh;">
    <div class="auth-card shadow-lg rounded-4 bg-white p-5 w-100" style="max-width: 450px;">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 70px; height: 70px;">
                <i class="fas fa-key fs-2"></i>
            </div>
            <h2 class="fw-bold text-dark mb-2">نسيت كلمة المرور؟</h2>
            <p class="text-secondary">أدخل بريدك الإلكتروني وسنرسل لك رابطاً لتعيين كلمة مرور جديدة.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="form-label fw-bold">البريد الإلكتروني</label>
                <input type="email" class="form-control form-control-lg bg-light @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="example@mail.com">
                @error('email') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-success w-100 py-3 fs-5 border-0 shadow-sm" style="background-color: #10b981;">
                إرسال رابط الاستعادة
            </button>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-secondary text-decoration-none small">العودة لتسجيل الدخول</a>
            </div>
        </form>
    </div>
</div>
@endsection
