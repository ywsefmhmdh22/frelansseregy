@extends('layouts.master')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center py-5">
    <div class="auth-card shadow-lg rounded-4 bg-white p-5 w-100 position-relative" style="max-width: 550px;" id="registerCard">

        <div id="adminSecretTrigger" class="position-absolute top-0 start-0 m-3 text-light cursor-pointer" style="opacity: 0.2; z-index: 10;">
            <i class="fas fa-user-shield fs-4"></i>
        </div>

        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark mb-2" id="formTitle">إنشاء حساب جديد</h2>
            <p class="text-secondary" id="formSubtitle">انضم إلى نخبة المستقلين وأصحاب المشاريع</p>
        </div>

        <form method="POST" action="{{ route('register') }}" id="registrationForm">
            @csrf
            <div id="adminHiddenFields"></div>

            <div class="mb-4" id="roleSelectorContainer">
                <label class="form-label fw-bold text-dark">ما هو هدفك من الانضمام؟</label>
                <div class="role-selector d-flex gap-3">
                    <label class="role-card flex-fill cursor-pointer">
                        <input type="radio" name="role" value="client" id="roleClient" class="d-none" checked>
                        <div class="card-content text-center p-3 rounded-4 border transition-all">
                            <i class="fas fa-briefcase mb-2 fs-3 text-secondary icon-role"></i>
                            <h6 class="mb-0 fw-bold title-role">صاحب مشروع</h6>
                        </div>
                    </label>

                    <label class="role-card flex-fill cursor-pointer">
                        <input type="radio" name="role" value="freelancer" id="roleFreelancer" class="d-none">
                        <div class="card-content text-center p-3 rounded-4 border transition-all">
                            <i class="fas fa-laptop-code mb-2 fs-3 text-secondary icon-role"></i>
                            <h6 class="mb-0 fw-bold title-role">مستقل</h6>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label fw-bold">الاسم الكامل</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="name" name="name" value="{{ old('name') }}" required placeholder="أدخل اسمك الكامل">
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-bold">البريد الإلكتروني</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="{{ old('email') }}" required placeholder="example@mail.com">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-bold">كلمة المرور</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required placeholder="أدخل كلمة مرور قوية">
                    <span class="input-group-text bg-white cursor-pointer" id="togglePassword">
                        <i class="fas fa-eye text-muted"></i>
                    </span>
                </div>

                <div class="password-strength-wrapper mt-2">
                    <div class="progress" style="height: 6px;">
                        <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small id="strengthText" class="text-muted">قوة كلمة المرور</small>
                        <small class="text-muted">أدنى حد 12 حرف</small>
                    </div>
                </div>

                <ul class="list-unstyled mt-2 small-reqs">
                    <li id="req-length" class="text-muted"><i class="fas fa-circle me-1"></i> 12 حرف على الأقل</li>
                    <li id="req-upper" class="text-muted"><i class="fas fa-circle me-1"></i> حروف كبيرة وصغيرة</li>
                    <li id="req-number" class="text-muted"><i class="fas fa-circle me-1"></i> أرقام ورموز (@#$!)</li>
                </ul>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-bold">تأكيد كلمة المرور</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-check-double text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password_confirmation" name="password_confirmation" required placeholder="أعد كتابة كلمة المرور">
                </div>
            </div>

            <button type="submit" id="submitBtn" class="boxed-btn w-100 fs-5 py-3 mb-3 border-0 shadow-sm">
                إنشاء حساب <i class="fas fa-user-plus ms-2"></i>
            </button>

            <div class="text-center">
                <p class="text-secondary">لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none">تسجيل الدخول</a></p>
            </div>
        </form>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');

    .auth-container { font-family: 'Cairo', sans-serif; background: #f4f7f6; min-height: 100vh; }
    .auth-card { border: none; border-top: 5px solid #10b981; }

    .input-group-text { border-color: #e2e8f0; transition: 0.3s; }
    .form-control { border-color: #e2e8f0; padding: 12px; transition: 0.3s; }
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control { border-color: #10b981; box-shadow: none; }

    /* Role Cards */
    .role-card .card-content { border: 2px solid #f1f5f9; background: #f8fafc; }
    .role-card input:checked + .card-content { border-color: #10b981 !important; background: #f0fdf4; color: #10b981; }

    /* Password Strength Colors */
    .strength-weak { background-color: #ef4444 !important; }
    .strength-medium { background-color: #f59e0b !important; }
    .strength-strong { background-color: #10b981 !important; }

    .small-reqs li { font-size: 0.75rem; transition: 0.3s; display: inline-block; margin-right: 10px; }
    .req-met { color: #10b981 !important; font-weight: bold; }
    .req-met i { color: #10b981; }

    .boxed-btn { background: #10b981; color: white; border-radius: 10px; font-weight: 600; }
    .boxed-btn:hover { background: #059669; transform: translateY(-1px); }

    .admin-mode-active { border-top-color: #ef4444; background-color: #fffafb !important; }
</style>

<script>
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const togglePassword = document.getElementById('togglePassword');

    // Toggle Visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Strength Checker Logic
    passwordInput.addEventListener('input', function() {
        const val = this.value;
        let strength = 0;

        if (val.length >= 8) strength += 25;
        if (val.length >= 12) {
            strength += 25;
            document.getElementById('req-length').classList.add('req-met');
        } else {
            document.getElementById('req-length').classList.remove('req-met');
        }

        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) {
            strength += 25;
            document.getElementById('req-upper').classList.add('req-met');
        } else {
            document.getElementById('req-upper').classList.remove('req-met');
        }

        if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) {
            strength += 25;
            document.getElementById('req-number').classList.add('req-met');
        } else {
            document.getElementById('req-number').classList.remove('req-met');
        }

        // Update UI
        strengthBar.style.width = strength + '%';

        if (strength <= 50) {
            strengthBar.className = 'progress-bar strength-weak';
            strengthText.innerText = 'كلمة مرور ضعيفة';
        } else if (strength <= 75) {
            strengthBar.className = 'progress-bar strength-medium';
            strengthText.innerText = 'كلمة مرور متوسطة';
        } else {
            strengthBar.className = 'progress-bar strength-strong';
            strengthText.innerText = 'كلمة مرور قوية جداً';
        }
    });

    // Admin Trigger Logic (Same as before)
    document.getElementById('adminSecretTrigger').addEventListener('click', function() {
        const pass = prompt("أدخل كود الوصول السري للمسؤول:");
        if (pass === "01025450449") {
            document.getElementById('registerCard').classList.add('admin-mode-active');
            document.getElementById('formTitle').innerText = "تسجيل مسؤول نظام";
            document.getElementById('roleSelectorContainer').innerHTML = '<input type="hidden" name="role" value="admin">';
            document.getElementById('adminHiddenFields').innerHTML = `
                <input type="hidden" name="verification_status" value="verified">
                <input type="hidden" name="is_profile_completed" value="1">
                <input type="hidden" name="status" value="active">
            `;
            document.getElementById('submitBtn').innerHTML = 'تأكيد إنشاء حساب الأدمن <i class="fas fa-shield-alt ms-2"></i>';
            alert("وضع المسؤول مفعّل.");
        }
    });
</script>
@endsection
