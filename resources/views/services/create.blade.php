@extends('layouts.master')

@section('content')
<div class="container py-5 animate__animated animate__fadeIn">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- بطاقة إضافة الخدمة --}}
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-primary p-4 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="text-white fw-bold mb-0">
                            <i class="fas fa-magic me-2"></i> إضافة خدمة جديدة للبيع
                        </h4>
                        <a href="{{ route('freelancer.dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold">
                            <i class="fas fa-arrow-right ms-1"></i> العودة للوحة التحكم
                        </a>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    {{-- رسائل الخطأ إن وجدت --}}
                    @if($errors->any())
                        <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('services.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row text-end" dir="rtl">
                            {{-- عنوان الخدمة --}}
                            <div class="col-12 mb-4">
                                <label class="form-label fw-bold text-dark fs-5">عنوان الخدمة</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-heading text-primary"></i></span>
                                    <input type="text" name="title" class="form-control form-control-lg border-0 bg-light rounded-start-3"
                                           placeholder="مثلاً: تصميم هوية بصرية كاملة لشركتك" value="{{ old('title') }}" required>
                                </div>
                                <small class="text-muted">اختر عنواناً جذاباً وواضحاً يصف ما ستقدمه بالضبط.</small>
                            </div>

                            {{-- وصف الخدمة --}}
                            <div class="col-12 mb-4">
                                <label class="form-label fw-bold text-dark fs-5">وصف الخدمة بالتفصيل</label>
                                <textarea name="description" class="form-control border-0 bg-light rounded-3" rows="6"
                                          placeholder="اكتب هنا تفاصيل الخدمة، ماذا سيتسلم العميل، وعدد التعديلات المتاحة..." required>{{ old('description') }}</textarea>
                            </div>

                            {{-- السعر وصورة الخدمة --}}
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-dark fs-5">سعر الخدمة (ج.م)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-tag text-success"></i></span>
                                    <input type="number" name="price" class="form-control form-control-lg border-0 bg-light rounded-start-3"
                                           placeholder="50" value="{{ old('price') }}" min="1" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-dark fs-5">غلاف الخدمة (Image)</label>
                                <input type="file" name="image" id="service_image" class="form-control form-control-lg border-0 bg-light rounded-3"
                                       accept="image/*" required onchange="previewImage(event)">
                            </div>

                            {{-- مكان معاينة الصورة --}}
                            <div class="col-12 mb-4 text-center">
                                <div id="image_preview_container" class="d-none border rounded-4 p-2 bg-light d-inline-block shadow-sm">
                                    <p class="small fw-bold text-secondary mb-2">معاينة الغلاف</p>
                                    <img id="image_preview" src="#" alt="Preview" class="rounded-3 shadow-sm" style="max-width: 100%; max-height: 250px;">
                                </div>
                            </div>

                            {{-- أزرار التحكم --}}
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow-sm py-3 mb-2">
                                    <i class="fas fa-rocket me-2"></i> نشر الخدمة الآن في المتجر
                                </button>
                                <p class="text-center text-muted small mt-2">
                                    بمجرد ضغطك على نشر، ستظهر الخدمة في صفحة الخدمات العامة للعملاء.
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- سكربت بسيط لمعاينة الصورة قبل الرفع --}}
<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('image_preview');
            var container = document.getElementById('image_preview_container');
            output.src = reader.result;
            container.classList.remove('d-none');
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

<style>
    .form-control:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        border: 1px solid #0d6efd !important;
    }
    .input-group-text {
        border-right: 0;
    }
    .fw-bold { font-weight: 700 !important; }
</style>
@endsection
