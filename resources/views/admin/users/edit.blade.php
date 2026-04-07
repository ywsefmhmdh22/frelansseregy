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
                        {{-- تم ربط الـ label عبر خاصية for --}}
                        <label for="full_name" class="text-info small fw-bold mb-2 d-block">الاسم بالكامل</label>
                        <input type="text"
                               id="full_name"
                               name="name"
                               class="form-control bg-black border-secondary text-white p-3"
                               value="{{ old('name', $user->name) }}"
                               required>
                    </div>

                    <div class="mb-4">
                        {{-- تم ربط الـ label عبر خاصية for --}}
                        <label for="user_email" class="text-info small fw-bold mb-2 d-block">البريد الإلكتروني</label>
                        <input type="email"
                               id="user_email"
                               name="email"
                               class="form-control bg-black border-secondary text-white p-3"
                               value="{{ old('email', $user->email) }}"
                               required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            {{-- تم ربط الـ label عبر خاصية for --}}
                            <label for="user_balance" class="text-info small fw-bold mb-2 d-block">الرصيد الحالي (ج.م)</label>
                            <input type="number"
                                   id="user_balance"
                                   name="balance"
                                   class="form-control bg-black border-secondary text-white p-3"
                                   value="{{ old('balance', $user->balance) }}"
                                   required>
                        </div>
                        <div class="col-md-6 mb-4">
                            {{-- تم ربط الـ label عبر خاصية for --}}
                            <label for="user_role" class="text-info small fw-bold mb-2 d-block">الرتبة / الدور</label>
                            <select id="user_role" name="role" class="form-select bg-black border-secondary text-white p-3">
                                <option value="freelancer" {{ old('role', $user->role) == 'freelancer' ? 'selected' : '' }}>مستقل (Freelancer)</option>
                                <option value="client" {{ old('role', $user->role) == 'client' ? 'selected' : '' }}>عميل (Client)</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>مسؤول (Admin)</option>
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
    .form-control, .form-select { transition: all 0.3s ease; }
    .form-control:focus, .form-select:focus {
        background: #000;
        border-color: #0ea5e9;
        color: #fff;
        box-shadow: 0 0 15px rgba(14, 165, 233, 0.2);
    }
    .shadow-glow-info {
        box-shadow: 0 0 20px rgba(14, 165, 233, 0.4);
        transition: 0.3s;
    }
    .shadow-glow-info:hover {
        box-shadow: 0 0 30px rgba(14, 165, 233, 0.6);
        transform: translateY(-2px);
    }
</style>

{{-- عرض تنبيهات الخطأ إن وجدت --}}
@if($errors->any())
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'error',
        title: 'عذراً.. توجد أخطاء',
        html: '<ul class="text-start">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
        background: '#0d1117',
        color: '#fff',
        confirmButtonColor: '#0ea5e9'
    });
</script>
@endif
@endsection
