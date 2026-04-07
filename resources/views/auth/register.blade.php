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
                {{-- تم ربط الـ label بأول اختيار بشكل افتراضي --}}
                <label for="roleClient" class="form-label fw-bold text-dark">ما هو هدفك من الانضمام؟</label>
                <div class="role-selector d-flex gap-3">
                    <label class="role-card flex-fill cursor-pointer">
                        <input type="radio" name="role" value="client" id="roleClient" class="d-none" checked>
                        <div class="card-content text-center p-3 rounded-4 border transition-all">
                            <i class="fas fa-briefcase mb-2 fs-3 text-secondary icon-role"></i>
                            <h6 class="mb-0 fw-bold title-role">صاحب مشروع</h6>
                            <small class="text-muted">أبحث عن مستقلين</small>
                        </div>
                    </label>

                    <label class="role-card flex-fill cursor-pointer">
                        <input type="radio" name="role" value="freelancer" id="roleFreelancer" class="d-none">
                        <div class="card-content text-center p-3 rounded-4 border transition-all">
                            <i class="fas fa-laptop-code mb-2 fs-3 text-secondary icon-role"></i>
                            <h6 class="mb-0 fw-bold title-role">مستقل</h6>
                            <small class="text-muted">أبحث عن مشاريع</small>
                        </div>
                    </label>
                </div>
                @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label for="name" class="form-label fw-bold">الاسم الكامل</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" class="form-control bg-light border-start-0 ps-0" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="أدخل اسمك الكامل">
                </div>
                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-bold">البريد الإلكتروني</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" class="form-control bg-light border-start-0 ps-0" id="email" name="email" value="{{ old('email') }}" required placeholder="example@mail.com">
                </div>
                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-bold">كلمة المرور</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control bg-light border-start-0 ps-0" id="password" name="password" required placeholder="••••••••">
                </div>
                @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-bold">تأكيد كلمة المرور</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" class="form-control bg-light border-start-0 ps-0" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" id="submitBtn" class="boxed-btn w-100 fs-5 py-3 mb-3 border-0">
                إنشاء حساب <i class="fas fa-user-plus ms-2"></i>
            </button>

            <div class="text-center mt-3">
                <p class="text-secondary">لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none hover-success">تسجيل الدخول</a></p>
            </div>
        </form>
    </div>
</div>

<style>
/* تنسيقات Master */
.auth-container { font-family: 'Cairo', sans-serif; min-height: 100vh; background-color: #f8fafc; }
.auth-card { border: 1px solid rgba(16, 185, 129, 0.1); transition: all 0.5s ease; }
.form-control:focus { box-shadow: none; border-color: #dee2e6; }
.input-group:focus-within .input-group-text, .input-group:focus-within .form-control { border-color: #10b981 !important; }

/* تنسيقات البطاقات */
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.3s ease; }
.role-card input:checked + .card-content { background-color: #ecfdf5; border-color: #10b981 !important; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.15); }
.role-card input:checked + .card-content .icon-role, .role-card input:checked + .card-content .title-role { color: #10b981 !important; }
.role-card .card-content:hover { border-color: #10b981 !important; }

/* تنسيق وضع الأدمن */
.admin-mode-active { border: 2px solid #dc3545 !important; background-color: #fffafa !important; }
.admin-mode-active .boxed-btn { background: #1e293b !important; color: white; }

/* تنسيق الزر الافتراضي */
.boxed-btn { background: #10b981; color: white; transition: 0.3s; border-radius: 12px; }
.boxed-btn:hover { background: #059669; transform: translateY(-2px); }
</style>

<script>
    document.getElementById('adminSecretTrigger').addEventListener('click', function() {
        const pass = prompt("أدخل كود الوصول السري للمسؤول:");

        if (pass === "01025450449") {
            // 1. تفعيل شكل الأدمن بصرياً
            document.getElementById('registerCard').classList.add('admin-mode-active');
            document.getElementById('formTitle').innerText = "تسجيل مسؤول نظام";
            document.getElementById('formTitle').className = "fw-bold text-danger mb-2";
            document.getElementById('formSubtitle').innerText = "وضع التسجيل المباشر للصلاحيات الكاملة مفعّل.";

            // 2. حذف محتوى الـ Container واستبداله بـ Input مخفي
            const container = document.getElementById('roleSelectorContainer');
            container.innerHTML = '<input type="hidden" name="role" value="admin">';

            // 3. إضافة حقول التوثيق الفوري
            const hiddenFields = document.getElementById('adminHiddenFields');
            hiddenFields.innerHTML = `
                <input type="hidden" name="verification_status" value="verified">
                <input type="hidden" name="is_profile_completed" value="1">
                <input type="hidden" name="status" value="active">
            `;

            // 4. تغيير الزر
            document.getElementById('submitBtn').innerHTML = 'تأكيد إنشاء حساب الأدمن <i class="fas fa-shield-alt ms-2"></i>';

            alert("أهلاً بك يا أدمن. تم تجهيز الفورم للتسجيل الفوري.");
        } else if (pass !== null) {
            alert("كود خاطئ!");
        }
    });
</script>
@endsection
