@extends('layouts.master')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<main class="creative-studio-wrapper py-5" dir="rtl">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <article class="card border-0 shadow-2xl rounded-5 overflow-hidden luxury-project-card animate__animated animate__zoomIn">

                    {{-- Header Section: تصميم سينمائي متدرج --}}
                    <header class="card-header border-0 p-5 d-flex align-items-center justify-content-between {{ $type === 'premium' ? 'premium-header' : 'standard-header' }}">
                        <div class="d-flex align-items-center">
                            <div class="icon-orb-premium shadow-lg animate__animated animate__bounceIn">
                                <i class="fas {{ $type === 'premium' ? 'fa-rocket' : 'fa-lightbulb' }}"></i>
                            </div>
                            <div class="ms-4 text-right pr-4">
                                <h1 class="mb-1 fw-black text-white h2">إطلاق مشروع جديد</h1>
                                <p class="mb-0 text-white-50 fs-6">اصنع مستقبلك الآن عبر التخزين السحابي الآمن</p>
                            </div>
                        </div>
                        @if($type === 'premium')
                            <div class="premium-glow-badge shadow-lg animate__animated animate__pulse animate__infinite">
                                <i class="fas fa-crown me-2"></i> مشروع VIP
                            </div>
                        @endif
                    </header>

                    <div class="card-body p-4 p-lg-5 bg-white">

                        @if($type === 'premium')
                            <div class="premium-alert-banner d-flex align-items-center mb-5 p-4 rounded-4 shadow-sm animate__animated animate__fadeInRight">
                                <div class="alert-icon-gold">
                                    <i class="fas fa-gem fa-2x"></i>
                                </div>
                                <div class="ms-3 pr-3 text-right">
                                    <h5 class="fw-bold mb-1" style="color: #ca8a04;">أنت الآن في منطقة النخبة!</h5>
                                    <p class="mb-0 small text-dark opacity-75">سيتم رفع بياناتك مباشرة لـ Laravel Cloud لضمان أعلى مستويات الأداء.</p>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" id="projectForm">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">

                            {{-- قسم رفع الصور السحابي المتطور --}}
                            <div class="mb-5">
                                <label for="image_url" class="premium-form-label mb-3">الصورة التوضيحية الرئيسية (Cloud Storage)</label>
                                <div class="cloud-image-uploader">
                                    <label for="image_url" class="cloud-drop-zone rounded-5 text-center p-5 position-relative d-block transition-all" id="drop_zone">
                                        <div class="upload-state" id="upload_placeholder">
                                            <div class="cloud-upload-icon mb-3">
                                                <i class="fas fa-cloud-arrow-up"></i>
                                            </div>
                                            <h3 class="fw-bold text-dark h5">اسحب الصورة هنا للرفع الفوري</h3>
                                            <p class="text-muted small">الحد الأقصى 5MB (سيرفع لـ Laravel Cloud)</p>
                                        </div>

                                        <div id="preview_info_container" class="d-none animate__animated animate__fadeIn">
                                            <div class="preview-frame position-relative">
                                                <img id="image_preview" src="" class="img-fluid rounded-4 shadow-lg mb-3" style="max-height: 300px; width: 100%; object-fit: cover;">
                                                <button type="button" class="btn-delete-float" onclick="resetImageInput(event)">
                                                    <i class="fas fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <input type="file" name="image_url" id="image_url" class="d-none" accept="image/*" onchange="previewImage(this)">
                                    </label>
                                </div>
                                @error('image_url')
                                    <div class="error-msg mt-2 animate__animated animate__shakeX"><i class="fas fa-circle-exclamation me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- عنوان المشروع --}}
                            <div class="mb-4 text-right">
                                <label for="project_title" class="premium-form-label mb-3">عنوان المشروع</label>
                                <div class="premium-input-group">
                                    <i class="fas fa-pen-fancy input-lead-icon"></i>
                                    <input type="text" name="title" id="project_title"
                                           class="form-control premium-input @error('title') is-invalid @enderror"
                                           placeholder="مثلاً: تطوير منصة تجارة إلكترونية متكاملة"
                                           value="{{ old('title') }}" required>
                                </div>
                                @error('title')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- الوصف بالتفصيل --}}
                            <div class="mb-4 text-right">
                                <label for="editor" class="premium-form-label mb-3">وصف المشروع بالتفصيل</label>
                                <div class="ck-editor-luxury shadow-sm rounded-4 overflow-hidden border">
                                    <textarea name="description" id="editor" class="form-control">{{ old('description') }}</textarea>
                                </div>
                                @error('description')
                                    <div class="error-msg mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row text-right">
                                {{-- الميزانية --}}
                                <div class="col-md-6 mb-4">
                                    <label for="project_price" class="premium-form-label mb-3">الميزانية </label>
                                    <div class="d-flex gap-2">
                                        <select name="currency" id="currency_selector" class="form-select premium-select" onchange="updateChargeNotice(this.value)">

                                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>

                                        </select>
                                        <div class="premium-input-group flex-grow-1">
                                            <i class="fas fa-wallet input-lead-icon text-success"></i>
                                            <input type="number" name="price" id="project_price"
                                                   class="form-control premium-input @error('price') is-invalid @enderror"
                                                   placeholder="الميزانية" step="0.01" value="{{ old('price') }}" required>
                                        </div>
                                    </div>
                                    <div class="charge-hint mt-3 p-2 rounded-3 bg-light" id="charge_notice">
                                        <i class="fas fa-info-circle me-1"></i> يجب شحن رصيدك بـ <span id="currency_type_name" class="fw-bold">الجنيه المصري</span> لتفعيل المشروع.
                                    </div>
                                    @error('price') <div class="error-msg mt-2">{{ $message }}</div> @enderror
                                </div>

                                {{-- المدة --}}
                                <div class="col-md-6 mb-4">
                                    <label for="project_duration" class="premium-form-label mb-3">مدة التنفيذ المتوقعة</label>
                                    <div class="premium-input-group">
                                        <i class="fas fa-hourglass-half input-lead-icon text-primary"></i>
                                        <input type="text" name="duration" id="project_duration"
                                               class="form-control premium-input @error('duration') is-invalid @enderror"
                                               placeholder="مثلاً: 15 يوم" value="{{ old('duration') }}" required>
                                    </div>
                                    @error('duration') <div class="error-msg mt-2">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- أزرار التحكم الفاخرة --}}
                            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-end mt-5 pt-4 border-top">
                                <a href="{{ route('client.dashboard') }}" class="btn btn-cancel-premium px-5 py-3 order-2 order-sm-1">إلغاء وإغلاق</a>
                                <button type="submit" id="submitBtn" class="btn {{ $type === 'premium' ? 'btn-publish-premium' : 'btn-publish-standard' }} px-5 py-3 order-1 order-sm-2 shadow-lg">
                                    <span class="btn-text">
                                        <i class="fas {{ $type === 'premium' ? 'fa-paper-plane' : 'fa-check-circle' }} me-2"></i>
                                        {{ $type === 'premium' ? 'نشر المشروع المميز فوراً' : 'نشر مشروعي الآن' }}
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </div>
        </div>
    </div>
</main>

<style>
/* --- التصميم العالمي Neo-Luxury --- */
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap');

:root {
    --standard-blue: #2563eb;
    --premium-gold: #fbbf24;
    --dark-slate: #0f172a;
    --soft-bg: #f8fafc;
}

body { background-color: #f1f5f9; font-family: 'Cairo', sans-serif; }
.fw-black { font-weight: 900; }

.luxury-project-card {
    background: #fff;
    box-shadow: 0 40px 100px -20px rgba(0,0,0,0.15) !important;
    border-radius: 45px !important;
}

/* التدرج اللوني للهيدر */
.standard-header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
.premium-header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-bottom: 4px solid var(--premium-gold); }

.icon-orb-premium {
    width: 75px; height: 75px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);
    border-radius: 24px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 2rem;
    border: 1px solid rgba(255,255,255,0.3);
}

.premium-glow-badge {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #000; padding: 12px 30px;
    border-radius: 50px; font-weight: 900;
    font-size: 0.9rem; box-shadow: 0 10px 25px rgba(250, 204, 21, 0.4);
}

/* حقول الإدخال */
.premium-form-label {
    color: #1e293b; font-weight: 800; font-size: 1.05rem;
    display: block; position: relative; padding-right: 15px;
}
.premium-form-label::before {
    content: ''; position: absolute; right: 0; top: 0; bottom: 0; width: 5px;
    background: var(--standard-blue); border-radius: 10px;
}

.premium-input-group { position: relative; width: 100%; }
.input-lead-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 1.3rem; color: #64748b; z-index: 5; }

.premium-input {
    padding: 18px 65px 18px 20px !important;
    border-radius: 22px !important;
    border: 2px solid #e2e8f0 !important;
    background: var(--soft-bg) !important;
    transition: all 0.4s ease;
    font-weight: 600;
}
.premium-input:focus {
    border-color: var(--standard-blue) !important;
    background: #fff !important;
    box-shadow: 0 15px 30px rgba(37, 99, 235, 0.1) !important;
}

.premium-select {
    border-radius: 22px !important; border: 2px solid #e2e8f0 !important;
    width: 120px; font-weight: 800; background: var(--soft-bg);
}

/* منطقة الرفع السحابي */
.cloud-drop-zone {
    border: 3px dashed #cbd5e1;
    background: var(--soft-bg);
    border-radius: 35px !important;
    cursor: pointer; transition: 0.4s;
}
.cloud-drop-zone:hover {
    border-color: var(--standard-blue);
    background: #eff6ff; transform: scale(1.01);
}
.cloud-upload-icon { font-size: 4rem; color: var(--standard-blue); animation: bounceSoft 2s infinite; }

@keyframes bounceSoft { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }

.btn-delete-float {
    position: absolute; top: 20px; left: 20px;
    background: #ef4444; color: #fff; border: none;
    width: 50px; height: 50px; border-radius: 50%;
    box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3); transition: 0.3s;
}
.btn-delete-float:hover { transform: rotate(90deg) scale(1.1); }

/* الأزرار */
.btn-publish-standard {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #fff; border: none; border-radius: 50px;
    font-weight: 800; font-size: 1.1rem; transition: 0.4s;
}
.btn-publish-premium {
    background: linear-gradient(135deg, #fbbf24 0%, #ca8a04 100%);
    color: #000; border: none; border-radius: 50px;
    font-weight: 900; font-size: 1.1rem; transition: 0.4s;
}
.btn-publish-standard:hover, .btn-publish-premium:hover { transform: translateY(-4px); filter: brightness(1.1); }

.btn-cancel-premium { background: #f1f5f9; color: #64748b; border-radius: 50px; font-weight: 700; transition: 0.3s; }
.btn-cancel-premium:hover { background: #e2e8f0; }

.error-msg { color: #ef4444; font-weight: 800; font-size: 0.85rem; }

/* CKEditor Custom */
.ck-editor__editable { min-height: 280px !important; border-radius: 0 0 22px 22px !important; }
</style>

<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// 1. تهيئة المحرر الاحترافي
let projectEditor;
ClassicEditor
    .create(document.querySelector('#editor'), {
        language: 'ar',
        content: { direction: 'rtl' },
        toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo' ]
    })
    .then(editor => { projectEditor = editor; })
    .catch(error => { console.error(error); });

// 2. معاينة الصورة بحجم 5MB
function previewImage(input) {
    const preview = document.getElementById('image_preview');
    const placeholder = document.getElementById('upload_placeholder');
    const container = document.getElementById('preview_info_container');

    if (input.files && input.files[0]) {
        // التحقق من الحجم (5 ميجا)
        if (input.files[0].size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: 'warning',
                title: 'حجم الصورة كبير جداً',
                text: 'يرجى رفع صورة لا تتخطى 5 ميجابايت لضمان سرعة الرفع السحابي.',
                confirmButtonColor: '#2563eb'
            });
            input.value = '';
            return;
        }

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

// 3. معالجة الإرسال السحابي
document.getElementById('projectForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const description = projectEditor.getData();
    if (!description || description.trim() === '') {
        Swal.fire({
            icon: 'error',
            title: 'الوصف مطلوب',
            text: 'التفاصيل هي ما تجذب النخبة من المستقلين، لا تتركها فارغة.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    // تأثير الاحتفال Confetti
    const duration = 2 * 1000;
    const end = Date.now() + duration;
    (function frame() {
        confetti({ particleCount: 3, angle: 60, spread: 55, origin: { x: 0 }, colors: ['#2563eb', '#fbbf24'] });
        confetti({ particleCount: 3, angle: 120, spread: 55, origin: { x: 1 }, colors: ['#2563eb', '#fbbf24'] });
        if (Date.now() < end) requestAnimationFrame(frame);
    }());

    // حوار الرفع السحابي
    Swal.fire({
        title: 'جاري النشر ... 🚀',
        html: 'نحن نقوم الآن برفع ملفاتك   وتجهيز مشروعك.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
            // الرفع الفعلي
            document.getElementById('projectForm').submit();
        }
    });
});

function updateChargeNotice(val) {
    const names = { 'USD': 'الدولار الأمريكي'};
    document.getElementById('currency_type_name').innerText = names[val] || val;
}
</script>

@endsection
