@extends('layouts.master')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center py-5">
    <div class="auth-card shadow-lg rounded-4 bg-white p-5 w-100" style="max-width: 550px;">

        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark mb-2">إنشاء حساب جديد</h2>
            <p class="text-secondary">انضم إلى نخبة المستقلين وأصحاب المشاريع</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- اختيار نوع الحساب (مستقل أم صاحب مشروع) بتصميم بطاقات -->
            <div class="mb-4">
                <label class="form-label fw-bold text-dark">ما هو هدفك من الانضمام؟</label>
                <div class="role-selector d-flex gap-3">
                    <!-- كارت صاحب المشروع -->
                    <label class="role-card flex-fill cursor-pointer">
                        <input type="radio" name="role" value="client" class="d-none" checked>
                        <div class="card-content text-center p-3 rounded-4 border transition-all">
                            <i class="fas fa-briefcase mb-2 fs-3 text-secondary icon-role"></i>
                            <h6 class="mb-0 fw-bold title-role">صاحب مشروع</h6>
                            <small class="text-muted">أبحث عن مستقلين</small>
                        </div>
                    </label>

                    <!-- كارت المستقل -->
                    <label class="role-card flex-fill cursor-pointer">
                        <input type="radio" name="role" value="freelancer" class="d-none">
                        <div class="card-content text-center p-3 rounded-4 border transition-all">
                            <i class="fas fa-laptop-code mb-2 fs-3 text-secondary icon-role"></i>
                            <h6 class="mb-0 fw-bold title-role">مستقل</h6>
                            <small class="text-muted">أبحث عن مشاريع</small>
                        </div>
                    </label>
                </div>
                @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <!-- الاسم -->
            <div class="mb-3">
                <label for="name" class="form-label fw-bold">الاسم الكامل</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" class="form-control bg-light border-start-0 ps-0" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="أدخل اسمك الكامل">
                </div>
                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <!-- البريد الإلكتروني -->
            <div class="mb-3">
                <label for="email" class="form-label fw-bold">البريد الإلكتروني</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" class="form-control bg-light border-start-0 ps-0" id="email" name="email" value="{{ old('email') }}" required placeholder="example@mail.com">
                </div>
                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <!-- كلمة المرور -->
            <div class="mb-3">
                <label for="password" class="form-label fw-bold">كلمة المرور</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control bg-light border-start-0 ps-0" id="password" name="password" required placeholder="••••••••">
                </div>
                @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <!-- تأكيد كلمة المرور -->
            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-bold">تأكيد كلمة المرور</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control bg-light border-start-0 ps-0" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
                </div>
            </div>

            <!-- زر التسجيل -->
            <button type="submit" class="boxed-btn w-100 fs-5 py-3 mb-3 border-0">
                إنشاء حساب <i class="fas fa-user-plus ms-2"></i>
            </button>

            <div class="text-center mt-3">
                <p class="text-secondary">لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none hover-success">تسجيل الدخول</a></p>
            </div>
        </form>
    </div>
</div>

<style>
/* تنسيقات خاصة بصفحات المصادقة (Auth) */
.auth-container {
    font-family: 'Cairo', sans-serif;
}

.auth-card {
    border: 1px solid rgba(16, 185, 129, 0.1);
}

.form-control:focus {
    box-shadow: none;
    border-color: #dee2e6;
}
.input-group:focus-within .input-group-text,
.input-group:focus-within .form-control {
    border-color: #10b981 !important;
}

/* تنسيقات بطاقات اختيار نوع الحساب */
.cursor-pointer {
    cursor: pointer;
}
.transition-all {
    transition: all 0.3s ease;
}
.role-card input:checked + .card-content {
    background-color: #ecfdf5;
    border-color: #10b981 !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.15);
}
.role-card input:checked + .card-content .icon-role,
.role-card input:checked + .card-content .title-role {
    color: #10b981 !important;
}
.role-card .card-content:hover {
    border-color: #10b981 !important;
}
</style>
@endsection
