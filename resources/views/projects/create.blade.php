@extends('layouts.master')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<main class="container py-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <article class="card border-0 shadow-lg rounded-5 overflow-hidden luxury-card animate__animated animate__fadeIn">

                {{-- Header Section --}}
                <header class="card-header border-0 p-4 d-flex align-items-center justify-content-between {{ $type === 'premium' ? 'premium-gradient' : 'standard-gradient' }}">
                    <div class="d-flex align-items-center">
                        <div class="icon-badge shadow-sm" aria-hidden="true">
                            <i class="fas {{ $type === 'premium' ? 'fa-rocket' : 'fa-briefcase' }}"></i>
                        </div>
                        <div class="ms-3 text-right pr-3">
                            <h1 class="mb-0 fw-bold text-white h4">إضافة مشروع جديد</h1>
                            <p class="mb-0 text-white-50 small">املأ التفاصيل لجذب أفضل المستقلين</p>
                        </div>
                    </div>
                    @if($type === 'premium')
                        <div class="premium-badge shadow-sm animate__animated animate__pulse animate__infinite" role="status">
                            <i class="fas fa-crown me-1 text-warning"></i> مشروع مميز
                        </div>
                    @endif
                </header>

                <div class="card-body p-4 p-lg-5 bg-white">

                    @if($type === 'premium')
                        <section class="alert alert-premium d-flex align-items-center border-0 rounded-4 p-3 mb-4 shadow-sm" role="alert">
                            <div class="alert-icon-box text-warning" aria-hidden="true">
                                <i class="fas fa-gem fa-lg"></i>
                            </div>
                            <div class="ms-3 text-right pr-3">
                                <strong class="d-block mb-1">خيار ذكي!</strong>
                                <span class="small opacity-75">سيتم تمييز مشروعك باللون الذهبي وتثبيته في المقدمة لجذب النخبة.</span>
                            </div>
                        </section>
                    @endif

                    <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" id="projectForm">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">

                        {{-- Main Image Section --}}
                        <div class="mb-5">
                            <label for="image_url" class="form-label fw-bold text-dark h6 mb-3 text-right d-block">الصورة التوضيحية الرئيسية</label>
                            <div class="image-upload-wrapper">
                                <label for="image_url" class="image-drop-zone rounded-4 text-center p-4 w-100 mb-0 position-relative d-block" role="button" aria-label="اضغط لرفع صورة المشروع الرئيسية">
                                    <div class="upload-content" id="upload_placeholder">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary-soft mb-3" aria-hidden="true"></i>
                                        <h2 class="fw-bold text-dark h6">اسحب الصورة الرئيسية هنا أو اضغط للاختيار</h2>
                                        <p class="text-muted small mb-0">الحجم الأقصى 2MB (يفضل 1200x630)</p>
                                    </div>

                                    <div id="preview_info_container" class="d-none">
                                        <img id="image_preview" src="" class="img-fluid rounded-4 shadow-sm mb-2" style="max-height: 250px; width: 100%; object-fit: cover;" alt="معاينة صورة المشروع">
                                        <div class="d-flex justify-content-center gap-2 mb-2">
                                            <button type="button" class="btn btn-danger btn-sm rounded-pill px-4" onclick="resetImageInput(event)">
                                                حذف الصورة <i class="fas fa-trash-alt ms-1"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <input type="file" name="image_url" id="image_url" class="d-none" accept="image/*" onchange="previewImage(this)">
                                </label>
                            </div>
                            @error('image_url')
                                <div class="text-danger small mt-2 text-right" role="alert"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Project Title --}}
                        <div class="mb-4 text-right">
                            <label for="project_title" class="form-label fw-bold text-dark h6 mb-3">عنوان المشروع</label>
                            <div class="input-group-custom">
                                <i class="fas fa-pen-nib input-icon" aria-hidden="true"></i>
                                <input type="text" name="title" id="project_title"
                                       class="form-control luxury-input @error('title') is-invalid @enderror"
                                       placeholder="مثلاً: تصميم هوية بصرية لشركة عقارات ناشئة"
                                       value="{{ old('title') }}"
                                       required
                                       aria-required="true">
                            </div>
                            @error('title')
                                <div class="invalid-feedback d-block mt-2" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Project Description --}}
                        <div class="mb-4 text-right">
                            <label for="editor" class="form-label fw-bold text-dark h6 mb-3">وصف المشروع بالتفصيل</label>
                            <div class="editor-wrapper shadow-sm rounded-4 overflow-hidden">
                                <textarea name="description" id="editor" class="form-control" aria-label="وصف المشروع">{{ old('description') }}</textarea>
                            </div>
                            <div class="mt-2">
                                <span class="text-muted small">اشرح بوضوح ما تريده، المعايير الفنية، وما تتوقع الحصول عليه.</span>
                            </div>
                            @error('description')
                                <div class="text-danger small mt-2" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row text-right">
                            {{-- Budget --}}
                            <div class="col-md-6 mb-4">
                                <label for="project_price" class="form-label fw-bold text-dark h6 mb-3">الميزانية والعملة</label>
                                <div class="d-flex gap-2">
                                    <select name="currency" id="currency_selector" aria-label="اختر العملة" class="form-select luxury-input-select" onchange="updateChargeNotice(this.value)">
                                        <option value="EGP" {{ old('currency') == 'EGP' ? 'selected' : '' }}>ج.م</option>
                                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    </select>
                                    <div class="input-group-custom flex-grow-1">
                                        <i class="fas fa-wallet input-icon text-success" aria-hidden="true"></i>
                                        <input type="number" name="price" id="project_price"
                                               class="form-control luxury-input @error('price') is-invalid @enderror"
                                               placeholder="الميزانية" step="0.01"
                                               value="{{ old('price') }}"
                                               required
                                               aria-required="true">
                                    </div>
                                </div>
                                <div class="mt-2 small text-muted" id="charge_notice">
                                    <i class="fas fa-info-circle me-1"></i> ملاحظة: يجب شحن رصيدك بـ <span id="currency_type_name">الجنيه المصري</span> لتفعيل المشروع.
                                </div>
                                @error('price') <div class="text-danger small mt-2" role="alert">{{ $message }}</div> @enderror
                            </div>

                            {{-- Duration --}}
                            <div class="col-md-6 mb-4">
                                <label for="project_duration" class="form-label fw-bold text-dark h6 mb-3">مدة التنفيذ المتوقعة</label>
                                <div class="input-group-custom">
                                    <i class="fas fa-hourglass-half input-icon text-primary" aria-hidden="true"></i>
                                    <input type="text" name="duration" id="project_duration"
                                           class="form-control luxury-input @error('duration') is-invalid @enderror"
                                           placeholder="مثلاً: 10 أيام"
                                           value="{{ old('duration') }}"
                                           required
                                           aria-required="true">
                                </div>
                                @error('duration') <div class="text-danger small mt-2" role="alert">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-end mt-4 pt-4 border-top">
                            <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary px-5 py-3 rounded-pill fw-bold order-2 order-sm-1">إلغاء العملية</a>
                            <button type="submit" id="submitBtn" class="btn {{ $type === 'premium' ? 'btn-premium-action' : 'btn-primary-action' }} px-5 py-3 rounded-pill shadow-lg fw-bold order-1 order-sm-2">
                                <i class="fas {{ $type === 'premium' ? 'fa-paper-plane' : 'fa-check-circle' }} me-2"></i>
                                {{ $type === 'premium' ? 'نشر المشروع المميز فـوراً' : 'نشر مشروعي الآن' }}
                            </button>
                        </div>
                    </form>
                </div>
            </article>
        </div>
    </div>
</main>

<style>
/* --- Global & Typography --- */
:root {
    --primary-blue: #2563eb;
    --premium-gold: #facc15;
    --text-dark: #1e293b;
    --bg-light: #f8fafc;
}

body { background-color: #f1f5f9; }

/* --- Luxury Card Design --- */
.luxury-card {
    border: none;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1) !important;
    transition: transform 0.3s ease;
}

.standard-gradient { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); }
.premium-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }

.icon-badge {
    width: 50px; height: 50px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(5px);
    border-radius: 15px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 1.2rem;
}

.premium-badge {
    background: var(--premium-gold);
    color: #000; padding: 8px 18px;
    border-radius: 50px; font-size: 14px; font-weight: 800;
    box-shadow: 0 4px 15px rgba(250, 204, 21, 0.3);
}

/* --- Input Styling --- */
.input-group-custom { position: relative; display: flex; align-items: center; }
.input-icon { position: absolute; right: 20px; color: #64748b; z-index: 5; }

.luxury-input {
    padding: 16px 55px 16px 20px !important;
    border-radius: 16px !important;
    border: 2px solid #e2e8f0 !important;
    background-color: var(--bg-light) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
}

.luxury-input:focus {
    background-color: #fff !important;
    border-color: var(--primary-blue) !important;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1) !important;
}

.luxury-input-select {
    width: 110px; border-radius: 16px !important;
    border: 2px solid #e2e8f0; background: var(--bg-light);
    font-weight: bold; padding: 0 15px;
}

/* --- Image Upload Zone --- */
.image-drop-zone {
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    transition: all 0.3s ease;
    border-radius: 20px !important;
}
.image-drop-zone:hover {
    border-color: var(--primary-blue);
    background: #f0f7ff;
}

/* --- Buttons --- */
.btn-primary-action {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border: none; color: white; transition: all 0.3s ease;
}
.btn-premium-action {
    background: linear-gradient(135deg, #facc15 0%, #ca8a04 100%);
    border: none; color: #000; transition: all 0.3s ease;
}
.btn-primary-action:hover, .btn-premium-action:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

/* --- Accessibility Focus --- */
button:focus, a:focus, input:focus {
    outline: 3px solid rgba(37, 99, 235, 0.5);
    outline-offset: 2px;
}

/* CKEditor Adjustments */
.ck-editor__editable {
    min-height: 250px !important;
    border-radius: 0 0 16px 16px !important;
    border: 1px solid #e2e8f0 !important;
}

/* SweetAlert Custom Luxe */
.swal2-popup-luxury {
    border-radius: 25px !important;
    font-family: inherit;
}
</style>

<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// 1. CKEditor Initialization
let projectEditor;
ClassicEditor
    .create(document.querySelector('#editor'), {
        language: 'ar',
        content: { direction: 'rtl' },
        toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo' ]
    })
    .then(editor => { projectEditor = editor; })
    .catch(error => { console.error(error); });

// 2. Form Submission Handling
document.getElementById('projectForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const description = projectEditor.getData();
    if (!description || description.trim() === '') {
        Swal.fire({
            icon: 'error',
            title: 'الوصف مطلوب',
            text: 'يرجى كتابة تفاصيل المشروع لجذب المستقلين.',
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#2563eb',
            customClass: { popup: 'swal2-popup-luxury' }
        });
        return;
    }

    // Success Confetti Effect
    const duration = 2 * 1000;
    const end = Date.now() + duration;

    (function frame() {
        confetti({ particleCount: 3, angle: 60, spread: 55, origin: { x: 0 } });
        confetti({ particleCount: 3, angle: 120, spread: 55, origin: { x: 1 } });
        if (Date.now() < end) requestAnimationFrame(frame);
    }());

    // Stylish Success Dialog
    Swal.fire({
        title: 'تم الإرسال بنجاح! 🚀',
        html: '<p class="text-muted">مشروعك قيد المراجعة حالياً وسينشر قريباً.</p>',
        icon: 'success',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        customClass: { popup: 'swal2-popup-luxury' },
        willClose: () => {
            document.getElementById('projectForm').submit();
        }
    });
});

// 3. Image Preview Logic
function previewImage(input) {
    const preview = document.getElementById('image_preview');
    const placeholder = document.getElementById('upload_placeholder');
    const container = document.getElementById('preview_info_container');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            placeholder.classList.add('d-none');
            container.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function resetImageInput(e) {
    if(e) e.preventDefault();
    const input = document.getElementById('image_url');
    const placeholder = document.getElementById('upload_placeholder');
    const container = document.getElementById('preview_info_container');
    input.value = '';
    placeholder.classList.remove('d-none');
    container.classList.add('d-none');
}

// 4. Currency Switcher Label Update
function updateChargeNotice(val) {
    const names = {'EGP': 'الجنيه المصري', 'USD': 'الدولار الأمريكي', 'EUR': 'اليورو'};
    document.getElementById('currency_type_name').innerText = names[val] || val;
}
</script>

@endsection
