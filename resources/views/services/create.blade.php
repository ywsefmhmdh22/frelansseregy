@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<div class="premium-creation-wrapper py-5 position-relative overflow-hidden" style="background: #0f172a; min-height: 100vh;">

    <div class="premium-bg-glow"></div>

    <div class="container position-relative" style="z-index: 5;">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">

                <div class="premium-glass-card border-0 shadow-lg rounded-5 overflow-hidden animate__animated animate__fadeInUp">

                    <div class="card-header p-4 border-bottom border-secondary border-opacity-25 bg-transparent">
                        <div class="d-flex justify-content-between align-items-center flex-row-reverse">
                            <h3 class="text-white fw-black mb-0 fs-4">
                                <i class="fas fa-crown text-gold ms-2"></i> إنشاء خدمة النخبة
                            </h3>
                            <a href="{{ route('freelancer.dashboard') }}" class="btn btn-outline-gold btn-sm rounded-pill px-4 fw-bold">
                                <i class="fas fa-arrow-left me-1"></i> لوحة التحكم
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        @if($errors->any())
                            <div class="alert alert-premium-danger border-0 rounded-4 shadow-sm mb-4 animate__animated animate__shakeX">
                                <ul class="mb-0 text-white small fw-bold text-end" dir="rtl">
                                    @foreach($errors->all() as $error)
                                        <li><i class="fas fa-exclamation-circle me-1 text-gold"></i> {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('services.store') }}" method="POST" enctype="multipart/form-data" id="serviceForm">
                            @csrf

                            <div class="row text-end" dir="rtl">

                                {{-- اختيار نوع الخدمة --}}
                                <div class="col-12 mb-5">
                                    <span class="form-label text-gold-light fw-bold fs-5 mb-3 d-block text-center">اختر نوع الإبداع</span>
                                    <div class="service-type-selector d-flex justify-content-center gap-3">
                                        <input type="radio" name="type" value="normal" id="type_normal" class="btn-check" {{ old('type', 'normal') == 'normal' ? 'checked' : '' }}>
                                        <label class="type-card" for="type_normal">
                                            <i class="fas fa-handshake fa-2x mb-2"></i>
                                            <span>خدمة عامة</span>
                                            <small>تواصل وتفاوض مع العميل</small>
                                        </label>

                                        <input type="radio" name="type" value="ready" id="type_ready" class="btn-check" {{ old('type') == 'ready' ? 'checked' : '' }}>
                                        <label class="type-card" for="type_ready">
                                            <i class="fas fa-bolt fa-2x mb-2"></i>
                                            <span>خدمة جاهزة</span>
                                            <small>بيع فوري وتحميل تلقائي</small>
                                        </label>
                                    </div>
                                </div>

                                {{-- عنوان الخدمة --}}
                                <div class="col-12 mb-4">
                                    <label class="form-label text-gold-light fw-bold fs-5 mb-3">ما هو عنوان إبداعك القادم؟</label>
                                    <div class="premium-input-group position-relative">
                                        <i class="fas fa-pen-fancy input-icon"></i>
                                        <input type="text" name="title" class="premium-input" placeholder="مثلاً: تصميم هوية بصرية فاخرة لشركتك" value="{{ old('title') }}" required>
                                    </div>
                                </div>

                                {{-- وصف الخدمة --}}
                                <div class="col-12 mb-4">
                                    <label class="form-label text-gold-light fw-bold fs-5 mb-3">وصف الخدمة (تفاصيل الامتياز)</label>
                                    <textarea name="description" class="premium-input" rows="5" placeholder="اكتب هنا تفاصيل الخدمة..." required>{{ old('description') }}</textarea>
                                </div>

                                {{-- السعر بالجنيه المصري --}}
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-gold-light fw-bold fs-5 mb-3">قيمة الخدمة (بالجنيه المصري ج.م)</label>
                                    <div class="premium-input-group price-group position-relative">
                                        <span class="currency-label" style="font-size: 0.9rem;">ج.م</span>
                                        <input type="number" name="price" class="premium-input pe-5" placeholder="1500" value="{{ old('price') }}" min="1" required>
                                    </div>
                                    <small class="text-white-50 mt-2 d-block">يرجى تحديد السعر بالعملة المحلية (الجنيه المصري).</small>
                                </div>

                                {{-- صورة الغلاف --}}
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-gold-light fw-bold fs-5 mb-3">غلاف الخدمة الفخم</label>
                                    <div class="custom-file-upload">
                                        <input type="file" name="image" id="service_image" class="file-hidden" accept="image/*" required onchange="previewImage(event)">
                                        <label for="service_image" class="file-label">
                                            <i class="fas fa-image mb-2 text-gold fa-2x"></i>
                                            <span id="file-status">اضغط لاختيار صورة مذهلة</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- قسم رفع الملف الجاهز (يظهر فقط عند اختيار خدمة جاهزة) --}}
                                <div class="col-12 mb-4 {{ old('type') == 'ready' ? '' : 'd-none' }}" id="ready_file_section">
                                    <div class="ready-upload-box p-4 rounded-4 text-center animate__animated animate__fadeIn">
                                        <label class="form-label text-gold-light fw-bold fs-5 mb-3">ارفع ملف الخدمة (ZIP, PDF, JPG)</label>
                                        <p class="text-white-50 small">سيتم إرسال هذا الملف تلقائياً للعميل فور الدفع</p>
                                        <div class="premium-input-group position-relative">
                                            <i class="fas fa-cloud-arrow-up input-icon"></i>
                                            <input type="file" name="ready_file" class="premium-input" id="ready_file_input" {{ old('type') == 'ready' ? 'required' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                {{-- معاينة الصورة --}}
                                <div class="col-12 mb-4 text-center">
                                    <div id="image_preview_container" class="d-none animate__animated animate__zoomIn">
                                        <div class="preview-wrapper border-gold p-1 rounded-4 shadow-lg d-inline-block">
                                            <img id="image_preview" src="#" alt="Preview" class="rounded-4" style="max-width: 100%; max-height: 280px;">
                                            <div class="preview-badge">معاينة الواجهة</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- زر النشر --}}
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-premium-submit w-100 py-3 rounded-pill fw-black">
                                        <i class="fas fa-paper-plane me-2"></i> نـشـر الـخدمـة فـي الـمـتـجر الآن
                                    </button>
                                    <p class="text-center text-white-50 small mt-3">
                                        <i class="fas fa-shield-halved text-gold me-1"></i> بمجرد النشر، ستكون خدمتك متاحة في سوق النخبة.
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('input[name="type"]').forEach((elem) => {
        elem.addEventListener("change", function(event) {
            let fileSection = document.getElementById('ready_file_section');
            let fileInput = document.getElementById('ready_file_input');
            if (event.target.value === 'ready') {
                fileSection.classList.remove('d-none');
                fileInput.setAttribute('required', 'required');
            } else {
                fileSection.classList.add('d-none');
                fileInput.removeAttribute('required');
                fileInput.value = "";
            }
        });
    });

    function previewImage(event) {
        var reader = new FileReader();
        var fileStatus = document.getElementById('file-status');
        reader.onload = function() {
            var output = document.getElementById('image_preview');
            var container = document.getElementById('image_preview_container');
            output.src = reader.result;
            container.classList.remove('d-none');
            fileStatus.innerText = "تم اختيار الصورة بنجاح ✅";
            fileStatus.style.color = "#fbbf24";
        };
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');

    :root {
        --bg-deep: #0f172a;
        --gold: #fbbf24;
        --gold-dark: #b45309;
        --glass: rgba(30, 41, 59, 0.7);
    }

    body { font-family: 'Cairo', sans-serif; background-color: var(--bg-deep); }
    .fw-black { font-weight: 900; }
    .text-gold { color: var(--gold); }
    .text-gold-light { color: #fcd34d; }
    .border-gold { border: 2px solid var(--gold) !important; }

    .premium-bg-glow {
        position: absolute;
        width: 100%; height: 100%;
        background: radial-gradient(circle at 50% 50%, rgba(251, 191, 36, 0.08) 0%, transparent 70%);
        z-index: 1;
        animation: pulse-glow 8s infinite alternate;
    }

    @keyframes pulse-glow {
        0% { transform: scale(1); opacity: 0.5; }
        100% { transform: scale(1.2); opacity: 0.8; }
    }

    .service-type-selector .type-card {
        flex: 1;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 25px;
        color: white;
        cursor: pointer;
        transition: 0.3s;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .btn-check:checked + .type-card {
        background: rgba(251, 191, 36, 0.1);
        border-color: var(--gold);
        box-shadow: 0 0 20px rgba(251, 191, 36, 0.2);
    }

    .type-card i { color: var(--gold); }
    .type-card span { font-weight: 700; display: block; margin-top: 10px; }
    .type-card small { font-size: 0.7rem; color: #94a3b8; }

    .premium-glass-card {
        background: var(--glass);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(251, 191, 36, 0.2) !important;
    }

    .premium-input {
        width: 100%;
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(251, 191, 36, 0.2);
        border-radius: 15px;
        padding: 15px 50px 15px 20px;
        color: white;
        transition: 0.3s;
    }

    .premium-input:focus {
        border-color: var(--gold);
        box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
        outline: none;
    }

    .input-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); z-index: 10; color: var(--gold); }
    .currency-label { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: var(--gold); font-weight: 900; font-size: 1.3rem; z-index: 10; }

    .ready-upload-box {
        background: rgba(251, 191, 36, 0.05);
        border: 2px dashed var(--gold);
    }

    .btn-premium-submit {
        background: linear-gradient(135deg, var(--gold), var(--gold-dark));
        color: #000;
        border: none;
        box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
    }

    .btn-premium-submit:hover {
        transform: translateY(-3px);
        filter: brightness(1.2);
    }

    .file-label {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        background: rgba(15, 23, 42, 0.8); border: 2px dashed rgba(251, 191, 36, 0.3);
        border-radius: 15px; padding: 20px; cursor: pointer; color: #cbd5e1;
    }

    .file-hidden { display: none; }

    .preview-wrapper { position: relative; }
    .preview-badge { position: absolute; bottom: 10px; right: 10px; background: var(--gold); color: black; padding: 2px 12px; border-radius: 50px; font-weight: 900; font-size: 0.75rem; }
    .alert-premium-danger { background: rgba(220, 38, 38, 0.2); border-right: 4px solid var(--gold) !important; }
</style>
@endsection

