 @extends('layouts.master')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center py-5" style="min-height: 70vh;">
    <div class="auth-card shadow-lg rounded-4 bg-white p-5 w-100" style="max-width: 450px;">

        <div class="text-center mb-4">
            <!-- أيقونة دائرية أنيقة -->
            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 70px; height: 70px;">
                <i class="fas fa-sign-in-alt fs-2"></i>
            </div>
            <h2 class="fw-bold text-dark mb-2">مرحباً بعودتك!</h2>
            <p class="text-secondary">قم بتسجيل الدخول لمتابعة أعمالك</p>
        </div>

        <!-- تم استخدام url بدلاً من route لتجنب أخطاء Route Not Found -->
        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <!-- البريد الإلكتروني -->
            <div class="mb-4">
                <label for="email" class="form-label fw-bold">البريد الإلكتروني</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" class="form-control bg-light border-start-0 ps-0" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="example@mail.com">
                </div>
                @error('email') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
            </div>

            <!-- كلمة المرور -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="password" class="form-label fw-bold mb-0">كلمة المرور</label>
                    <!-- تم تعديل الرابط ليكون مباشراً -->
                    <a href="{{ url('/forgot-password') }}" class="text-success text-decoration-none small">نسيت كلمة المرور؟</a>
                </div>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control bg-light border-start-0 ps-0" id="password" name="password" required placeholder="••••••••">
                </div>
                @error('password') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
            </div>

            <!-- تذكرني -->
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                <label class="form-check-label text-secondary" for="remember_me">تذكر بيانات الدخول</label>
            </div>

            <!-- زر الدخول -->
            <button type="submit" class="boxed-btn w-100 fs-5 py-3 mb-3 border-0 shadow-sm">
                دخول <i class="fas fa-arrow-left ms-2"></i>
            </button>

            <div class="text-center mt-4 pt-3 border-top">
                <!-- تم استخدام url هنا أيضاً -->
                <p class="text-secondary mb-0">ليس لديك حساب؟ <a href="{{ url('/register') }}" class="text-success fw-bold text-decoration-none hover-success">سجل الآن مجاناً</a></p>
            </div>
        </form>
    </div>
</div>

<style>
/* تنسيقات حقول الإدخال لتكون متناسقة مع الهوية */
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
.form-check-input:checked {
    background-color: #10b981;
    border-color: #10b981;
}
</style>
@endsection
