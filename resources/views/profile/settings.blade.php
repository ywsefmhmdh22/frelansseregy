@extends('layouts.master')

@section('content')
<div class="container py-5" dir="rtl">
    {{-- رسائل النجاح أو الخطأ --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        {{-- الجانب الأيمن: كارت المعلومات السريع --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center sticky-top" style="top: 100px;">
                <div class="position-relative d-inline-block mb-3">
                    {{-- إصلاح L25: إضافة alt لضمان إمكانية الوصول --}}
                    <img src="{{ auth()->user()->profile_image ? asset('storage/' . auth()->user()->profile_image) : asset('assets/default-avatar.png') }}"
                         class="rounded-circle shadow-sm border border-4 border-white"
                         style="width: 130px; height: 130px; object-fit: cover;"
                         id="profilePreview"
                         alt="صورة الملف الشخصي للمستخدم {{ auth()->user()->name }}">

                    {{-- فورم تغيير الصورة --}}
                    <form action="{{ route('profile.update_image') }}" method="POST" enctype="multipart/form-data" id="imageUploadForm">
                        @csrf
                        {{-- تم ربط الـ label بالـ input عبر id="profile_input" --}}
                        <label for="profile_input" class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 shadow" title="تغيير الصورة">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" name="profile_image" id="profile_input" class="d-none" onchange="document.getElementById('imageUploadForm').submit();">
                    </form>
                </div>

                <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                <p class="text-muted small mb-3">{{ auth()->user()->email }}</p>

                <div class="bg-light rounded-4 p-3 d-flex justify-content-around">
                    <div>
                        <small class="text-muted d-block">المحفظة</small>
                        <span class="fw-bold text-success">{{ number_format(auth()->user()->wallet->balance ?? 0) }} ج.م</span>
                    </div>
                    <div class="border-start"></div>
                    <div>
                        <small class="text-muted d-block">الحالة</small>
                        <span class="badge {{ auth()->user()->verification_status == 'verified' ? 'bg-success' : 'bg-warning' }} rounded-pill">
                            {{ auth()->user()->verification_status == 'verified' ? 'موثق' : 'تحت المراجعة' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- الجانب الأيسر: الإعدادات --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <ul class="nav nav-tabs border-0 custom-nav-tabs" id="settingsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#tab-personal" type="button" role="tab" aria-controls="tab-personal" aria-selected="true">البيانات الأساسية</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#tab-security" type="button" role="tab" aria-controls="tab-security" aria-selected="false">كلمة المرور</button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content" id="settingsTabContent">
                        {{-- تبويب الحساب --}}
                        <div class="tab-pane fade show active" id="tab-personal" role="tabpanel" aria-labelledby="personal-tab">
                            <form action="{{ route('profile.update.personal') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    {{-- إصلاح L34: ربط الـ label بالـ id لضمان إمكانية الوصول --}}
                                    <div class="col-md-6">
                                        <label for="user_name" class="form-label small fw-bold">الاسم</label>
                                        <input type="text" id="user_name" name="name" class="form-control rounded-3" value="{{ auth()->user()->name }}">
                                    </div>
                                    {{-- إصلاح L82: ربط الـ label بالـ id للبريد الإلكتروني --}}
                                    <div class="col-md-6">
                                        <label for="user_email" class="form-label small fw-bold">البريد الإلكتروني</label>
                                        <input type="email" id="user_email" name="email" class="form-control rounded-3" value="{{ auth()->user()->email }}">
                                    </div>
                                    {{-- إصلاح L106: ربط الـ label بـ textarea النبذة --}}
                                    <div class="col-12">
                                        <label for="user_bio" class="form-label small fw-bold">النبذة التعريفية (Bio)</label>
                                        <textarea id="user_bio" name="bio" class="form-control rounded-3" rows="4" placeholder="اكتب نبذة قصيرة عنك...">{{ auth()->user()->bio }}</textarea>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary px-4 rounded-pill">حفظ التعديلات</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- تبويب الأمان --}}
                        <div class="tab-pane fade" id="tab-security" role="tabpanel" aria-labelledby="security-tab">
                            <form action="{{ route('profile.update.password') }}" method="POST">
                                @csrf
                                <div class="col-md-8">
                                    {{-- إصلاح L110 والمشابهة: ربط كافة خانات كلمة المرور --}}
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label small fw-bold">كلمة المرور الحالية</label>
                                        <input type="password" id="current_password" name="current_password" class="form-control rounded-3">
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label small fw-bold">كلمة المرور الجديدة</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control rounded-3">
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password_confirmation" class="form-label small fw-bold">تأكيد كلمة المرور</label>
                                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control rounded-3">
                                    </div>
                                    <button type="submit" class="btn btn-dark px-4 rounded-pill mt-2">تحديث الأمان</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f8fafc; }
    .custom-nav-tabs .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        padding: 1rem 1.5rem;
        position: relative;
    }
    .custom-nav-tabs .nav-link.active {
        color: var(--bs-primary);
        background: none;
    }
    .custom-nav-tabs .nav-link.active::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--bs-primary);
        border-radius: 10px;
    }
    .form-control { border: 1px solid #e2e8f0; padding: 0.75rem 1rem; }
    .form-control:focus { border-color: var(--bs-primary); box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); }
</style>
@endsection
