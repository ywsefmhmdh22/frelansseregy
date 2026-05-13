@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<div class="creative-studio-wrapper py-5" dir="rtl">
    <div class="container">

        {{-- تنبيهات الأخطاء بتصميم عصري --}}
        @if ($errors->any())
            <div class="alert alert-danger custom-alert animate__animated animate__headShake">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li><i class="fas fa-shield-alt me-2"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- الهيدر الاحترافي --}}
        <div class="studio-header text-center mb-5 animate__animated animate__fadeIn">
            <div class="main-badge mb-3">Portfolio Studio</div>
            <h1 class="display-3 fw-black text-white">حول أفكارك إلى <span class="text-gradient">واقع ملموس</span></h1>
            <p class="text-secondary-light fs-5">انضم إلى نخبة المبدعين وانشر أعمالك على Laravel Cloud</p>
        </div>

        <div class="row g-5">
            {{-- الجانب الأيمن: الفورم الذكي --}}
            <div class="col-lg-7 animate__animated animate__fadeInUp">
                <form action="{{ route('portfolio.store') }}" method="POST" enctype="multipart/form-data" id="projectForm" class="glass-container p-4 p-md-5">
                    @csrf

                    <div class="input-group-premium mb-4">
                        <label for="projectTitle" class="premium-label">عنوان التحفة الفنية</label>
                        <div class="input-wrapper">
                            <i class="fas fa-pen-nib icon-lead"></i>
                            <input type="text" name="title" id="projectTitle" class="premium-control"
                                   value="{{ old('title') }}" placeholder="ماذا سنسمي هذا الإبداع؟" required>
                        </div>
                    </div>

                    <div class="input-group-premium mb-4">
                        <label class="premium-label">الغلاف البصري (S3 Cloud Storage)</label>
                        <div class="cloud-upload-box" id="dropZone">
                            <input type="file" name="image" id="imageInput" accept="image/*" class="d-none">

                            <div id="uploadPlaceholder" class="text-center">
                                <div class="cloud-icon-wrapper mb-3">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <h6 class="text-white fw-bold">اسحب الصورة هنا للرفع السحابي</h6>
                                <span class="text-muted small">يدعم JPG, PNG بجودة عالية (Max 5MB)</span>
                            </div>

                            <div id="imagePreviewContainer" class="d-none w-100">
                                <div class="preview-overlay">
                                    <img id="imagePreview" src="" class="img-fluid rounded-3" alt="Preview">
                                    <button type="button" class="btn-remove-cloud" id="removeImg">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group-premium mb-4">
                        <label for="projectDesc" class="premium-label">قصة النجاح والأدوات</label>
                        <textarea name="description" id="projectDesc" class="premium-control" rows="4"
                                  placeholder="تحدث عن التقنيات المستخدمة والتحديات التي واجهتها..." required>{{ old('description') }}</textarea>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="char-counter"><span id="currentChar">0</span> / 500 حرف</span>
                            <span class="text-info small"><i class="fas fa-info-circle"></i> الوصف الجيد يجذب العملاء</span>
                        </div>
                    </div>

                    <div class="input-group-premium mb-4">
                        <label for="projectLink" class="premium-label">رابط العرض الحي (Live Demo)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-external-link-alt icon-lead"></i>
                            <input type="url" name="link" id="projectLink" class="premium-control"
                                   value="{{ old('link') }}" placeholder="https://your-work.com">
                        </div>
                    </div>

                    <button type="submit" class="btn-nebula w-100" id="submitBtn">
                        <span class="btn-content">
                            <i class="fas fa-rocket me-2"></i> نشر العمل في المعرض السحابي
                        </span>
                        <div class="loading-spinner d-none">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            جاري المعالجة والرفع...
                        </div>
                    </button>
                </form>
            </div>

            {{-- الجانب الأيسر: المعاينة الحية الفاخرة --}}
            <div class="col-lg-5 d-none d-lg-block">
                <div class="sticky-preview">
                    <div class="preview-card-header d-flex align-items-center justify-content-between mb-3">
                        <span class="badge-live"><i class="fas fa-circle blink me-1"></i> معاينة حية</span>
                        <div class="creativity-score-container">
                            <div class="score-track">
                                <div class="score-fill" id="scoreFill" style="width: 10%;"></div>
                            </div>
                            <span class="score-label">قوة الإبداع</span>
                        </div>
                    </div>

                    <div class="mockup-device">
                        <div class="device-screen">
                            <div class="portfolio-item-preview">
                                <div class="image-box">
                                    <img id="liveImg" src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=800" alt="Mockup">
                                    <div class="category-tag">Creative Design</div>
                                </div>
                                <div class="content-box p-4">
                                    <h4 id="liveTitle" class="text-dark fw-bold">عنوان المشروع يظهر هنا</h4>
                                    <p id="liveDesc" class="text-muted small">هنا سيظهر وصفك الإبداعي الذي سيبهر الزوار.. ابدأ بالكتابة الآن!</p>
                                    <div class="footer-meta d-flex justify-content-between align-items-center mt-3">
                                        <div class="user-info">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}" class="rounded-circle me-2" width="25">
                                            <span class="small fw-bold">{{ auth()->user()->name }}</span>
                                        </div>
                                        <div class="stats small text-muted">
                                            <i class="far fa-heart"></i> 0 <i class="far fa-eye ms-2"></i> 0
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap');

    :root {
        --space-dark: #0a0a12;
        --accent-primary: #6366f1;
        --accent-secondary: #a855f7;
        --gold: #fbbf24;
        --glass-white: rgba(255, 255, 255, 0.05);
    }

    body { font-family: 'Cairo', sans-serif; background: var(--space-dark); }

    .creative-studio-wrapper {
        background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.1) 0%, transparent 40%),
                    radial-gradient(circle at 90% 80%, rgba(168, 85, 247, 0.1) 0%, transparent 40%);
        min-height: 100vh;
    }

    /* هيدر الصفحة */
    .main-badge {
        display: inline-block; padding: 6px 16px; background: rgba(99, 102, 241, 0.2);
        color: var(--accent-primary); border-radius: 50px; font-weight: 700; font-size: 0.8rem;
        letter-spacing: 1px; border: 1px solid var(--accent-primary);
    }
    .text-gradient {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .text-secondary-light { color: #94a3b8; }

    /* الحاويات الزجاجية */
    .glass-container {
        background: var(--glass-white); backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 30px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    /* المدخلات البريميوم */
    .premium-label { color: #e2e8f0; font-weight: 700; margin-bottom: 10px; display: block; font-size: 0.95rem; }
    .input-wrapper { position: relative; }
    .icon-lead { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--accent-primary); }
    .premium-control {
        width: 100%; background: rgba(0, 0, 0, 0.3); border: 2px solid rgba(255, 255, 255, 0.05);
        border-radius: 15px; padding: 12px 45px 12px 15px; color: white; transition: 0.3s;
    }
    .premium-control:focus {
        border-color: var(--accent-primary); outline: none; background: rgba(0, 0, 0, 0.5);
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.2);
    }

    /* منطقة الرفع السحابي */
    .cloud-upload-box {
        border: 2px dashed rgba(99, 102, 241, 0.3); border-radius: 20px;
        padding: 30px; cursor: pointer; transition: 0.3s; background: rgba(99, 102, 241, 0.02);
    }
    .cloud-upload-box:hover { border-color: var(--gold); background: rgba(251, 191, 36, 0.05); }
    .cloud-icon-wrapper { font-size: 3rem; color: var(--accent-primary); animation: float 3s infinite ease-in-out; }

    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    .preview-overlay { position: relative; border-radius: 15px; overflow: hidden; }
    .btn-remove-cloud {
        position: absolute; top: 10px; left: 10px; background: rgba(239, 68, 68, 0.9);
        border: none; color: white; width: 35px; height: 35px; border-radius: 50%;
    }

    /* زر نيبولا (سديمي) */
    .btn-nebula {
        background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
        color: white; border: none; padding: 18px; border-radius: 15px; font-weight: 800;
        transition: 0.4s; position: relative; overflow: hidden;
    }
    .btn-nebula:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4); }

    /* المعاينة الحية */
    .sticky-preview { position: sticky; top: 20px; }
    .badge-live { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }
    .blink { animation: blinker 1.5s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }

    .creativity-score-container { width: 120px; }
    .score-track { background: rgba(255, 255, 255, 0.1); height: 6px; border-radius: 10px; overflow: hidden; }
    .score-fill { height: 100%; background: var(--gold); transition: 0.5s; }
    .score-label { font-size: 0.65rem; color: #94a3b8; display: block; margin-top: 4px; }

    .mockup-device {
        background: #fff; border-radius: 30px; padding: 10px;
        box-shadow: 0 50px 100px -20px rgba(0,0,0,0.5);
    }
    .portfolio-item-preview { background: #fff; border-radius: 20px; overflow: hidden; }
    .image-box { height: 220px; position: relative; background: #f1f5f9; }
    .image-box img { width: 100%; height: 100%; object-fit: cover; }
    .category-tag {
        position: absolute; bottom: 15px; right: 15px; background: rgba(255, 255, 255, 0.9);
        padding: 4px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; color: var(--accent-primary);
    }

    .custom-alert {
        background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);
        color: #f87171; border-radius: 15px; backdrop-filter: blur(10px);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('imageInput');
    const liveImg = document.getElementById('liveImg');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const scoreFill = document.getElementById('scoreFill');

    // تفعيل الرفع بالضغط أو السحب
    dropZone.addEventListener('click', () => imageInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#fbbf24';
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = 'rgba(99, 102, 241, 0.3)';
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        const files = e.dataTransfer.files;
        if (files.length) {
            imageInput.files = files;
            handleImagePreview(files[0]);
        }
    });

    imageInput.addEventListener('change', (e) => {
        if (e.target.files.length) handleImagePreview(e.target.files[0]);
    });

    function handleImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('imagePreview').src = e.target.result;
            liveImg.src = e.target.result;
            previewContainer.classList.remove('d-none');
            uploadPlaceholder.classList.add('d-none');
            updateCreativityScore();
        }
        reader.readAsDataURL(file);
    }

    // تحديث المحتوى الحي
    document.getElementById('projectTitle').addEventListener('input', (e) => {
        document.getElementById('liveTitle').innerText = e.target.value || "عنوان المشروع يظهر هنا";
        updateCreativityScore();
    });

    document.getElementById('projectDesc').addEventListener('input', (e) => {
        const val = e.target.value;
        document.getElementById('liveDesc').innerText = val || "هنا سيظهر وصفك الإبداعي...";
        document.getElementById('currentChar').innerText = val.length;
        updateCreativityScore();
    });

    // حذف الصورة
    document.getElementById('removeImg').addEventListener('click', (e) => {
        e.stopPropagation();
        imageInput.value = "";
        previewContainer.classList.add('d-none');
        uploadPlaceholder.classList.remove('d-none');
        liveImg.src = "https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=800";
        updateCreativityScore();
    });

    // حساب قوة الإبداع
    function updateCreativityScore() {
        let score = 5;
        const titleLen = document.getElementById('projectTitle').value.length;
        const descLen = document.getElementById('projectDesc').value.length;

        if(titleLen > 10) score += 20;
        if(descLen > 50) score += 25;
        if(descLen > 200) score += 20;
        if(imageInput.files.length > 0) score += 30;

        scoreFill.style.width = score + '%';
        if(score > 80) scoreFill.style.background = '#10b981';
        else if(score > 40) scoreFill.style.background = '#fbbf24';
        else scoreFill.style.background = '#ef4444';
    }

    // معالجة الإرسال مع Spinner
    document.getElementById('projectForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        const btnContent = btn.querySelector('.btn-content');
        const loader = btn.querySelector('.loading-spinner');

        btnContent.classList.add('d-none');
        loader.classList.remove('d-none');
        btn.disabled = true;
    });
});
</script>
@endsection
