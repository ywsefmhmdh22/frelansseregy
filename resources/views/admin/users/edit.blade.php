@extends('layouts.master')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 animate__animated animate__fadeInUp">
            <div class="glass-card p-5 rounded-4 border border-info border-opacity-25 shadow-lg">
                <div class="text-center mb-4">
                    <h2 class="fw-black text-white tracking-tighter">تعديل ملف <span class="text-info">العميل</span></h2>
                    <p class="text-muted small">تعديل الصلاحيات والميزانية لـ {{ $user->name }}</p>
                </div>

                <form action="{{ route('admin.user.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="text-info small fw-bold mb-2 d-block">الاسم بالكامل</label>
                        <input type="text" name="name" class="form-control bg-black border-secondary text-white p-3" value="{{ $user->name }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="text-info small fw-bold mb-2 d-block">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control bg-black border-secondary text-white p-3" value="{{ $user->email }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="text-info small fw-bold mb-2 d-block">الرصيد الحالي (ج.م)</label>
                            <input type="number" name="balance" class="form-control bg-black border-secondary text-white p-3" value="{{ $user->balance }}" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="text-info small fw-bold mb-2 d-block">الرتبة / الدور</label>
                            <select name="role" class="form-select bg-black border-secondary text-white p-3">
                                <option value="freelancer" {{ $user->role == 'freelancer' ? 'selected' : '' }}>مستقل (Freelancer)</option>
                                <option value="client" {{ $user->role == 'client' ? 'selected' : '' }}>عميل (Client)</option>
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>مسؤول (Admin)</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-info flex-fill fw-bold py-3 shadow-glow-info">حفظ التغييرات</button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary flex-fill fw-bold py-3">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .glass-card { background: rgba(13, 17, 23, 0.9); backdrop-filter: blur(20px); }
    .form-control:focus, .form-select:focus { background: #000; border-color: #0ea5e9; color: #fff; box-shadow: 0 0 15px rgba(14, 165, 233, 0.2); }
    .shadow-glow-info { box-shadow: 0 0 20px rgba(14, 165, 233, 0.4); }
</style>
@endsection
