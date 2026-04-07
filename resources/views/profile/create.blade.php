@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="creative-studio-wrapper py-5" dir="rtl">
    <div class="container">

        {{-- رسائل الأخطاء في حال فشل الـ Validation --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-lg mb-4 animate__animated animate__shakeX" style="border-radius: 20px; background: rgba(220, 38, 38, 0.2); color: white; backdrop-filter: blur(10px);">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li><i class="fas fa-exclamation-triangle me-2"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- رأس الصفحة الملهم --}}
        <div class="studio-header text-center mb-5 animate__animated animate__fadeInDown">
            <div class="icon-orb mb-3 mx-auto">
                <i class="fas fa-magic"></i>
            </div>
            <h1 class="display-4 fw-black text-white">استوديو <span class="text-gold">الإبداع</span></h1>
            <p class="text-light opacity-75 fs-5">حان الوقت لتبهر العالم.. أضف لمستك الفنية الآن.</p>
        </div>

        <div class="row g-5">
            {{-- النموذج (الجانب الأيمن) --}}
            <div class="col-lg-7 animate__animated animate__fadeInRight">
                <form action="{{ route('portfolio.store') }}" method="POST" enctype="multipart/form-data" id="projectForm" class="glass-form p-4 p-md-5">
                    @csrf

                    {{-- عنوان المشروع - تم إضافة for --}}
                    <div class="form-group-custom mb-4">
                        <label for="projectTitle" class="label-premium"><i class="fas fa-heading me-2"></i> ما هو عنوان تحفتك الفنية؟</label>
                        <input type="text" name="title" id="projectTitle" class="input-premium"
                               value="{{ old('title') }}" placeholder="مثلاً: تصميم تطبيق توصيل عصري" required>
                    </div>

                    {{-- منطقة رفع الصور - تم إضافة for للـ label --}}
                    <div class="form-group-custom mb-4">
                        <label for="imageInput" class="label-premium"><i class="fas fa-image me-2"></i> الغلاف البصري (الصورة هي الواجهة)</label>
                        <div class="upload-zone" id="dropZone">
                            <input type="file" name="image" id="imageInput" accept="image/*" class="d-none">
                            <div class="upload-content text-center py-4">
                                <div class="upload-icon-anim mb-3">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <h5 class="text-white">اسحب الصورة هنا أو اضغط للاختيار</h5>
                                <p class="text-light opacity-50 small">يفضل مقاس 1200x800 بكسل (بحد أقصى 5 ميجا)</p>
                            </div>
                            <div id="imagePreviewContainer" class="d-none">
                                {{-- تم إضافة alt لمعاينة الصورة --}}
                                <img id="imagePreview" src="" class="img-fluid rounded-4" alt="معاينة العمل الفني المرفوع">
                                <button type="button" class="btn-remove-img" id="removeImg" aria-label="حذف الصورة"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>

                    {{-- وصف المشروع - تم إضافة for --}}
                    <div class="form-group-custom mb-4">
                        <label for="projectDesc" class="label-premium"><i class="fas fa-quote-right me-2"></i> احكِ لنا قصة هذا العمل</label>
                        <textarea name="description" id="projectDesc" class="input-premium" rows="5"
                                  placeholder="ما هي المشكلة التي حللتها؟ وما هي الأدوات التي استخدمتها؟" required>{{ old('description') }}</textarea>
                        <div class="char-count text-start small opacity-50 text-white mt-1">0 / 500 حرف</div>
                    </div>

                    {{-- رابط العمل - تم إضافة for --}}
                    <div class="form-group-custom mb-4">
                        <label for="projectLink" class="label-premium"><i class="fas fa-link me-2"></i> رابط المعاينة المباشرة (اختياري)</label>
                        <input type="url" name="link" id="projectLink" class="input-premium"
                               value="{{ old('link') }}" placeholder="https://www.behance.net/your-work">
                    </div>

                    {{-- زر الإرسال --}}
                    <div class="submit-section mt-5">
                        <button type="submit" class="btn-submit-luxury w-100" id="submitBtn">
                            <span class="btn-text">انشر عملك في المتحف الآن</span>
                            <span class="btn-shine"></span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- المعاينة الحية (الجانب الأيسر) --}}
            <div class="col-lg-5 animate__animated animate__fadeInLeft d-none d-lg-block">
                <div class="preview-sticky-box">
                    <div class="preview-header mb-3 d-flex align-items-center justify-content-between">
                        <h5 class="text-gold fw-bold m-0"><i class="fas fa-eye me-2"></i> معاينة مباشرة</h5>
                        <div class="creative-meter" id="creativityMeter">
                            <div class="meter-bar" style="width: 20%"></div>
                            <span class="meter-text">طاقة الإبداع</span>
                        </div>
                    </div>

                    {{-- بطاقة المحاكاة --}}
                    <div class="elite-card-mockup shadow-2xl">
                        <div class="mockup-img-container">
                            {{-- تم إضافة alt للمحاكاة الحية --}}
                            <img id="liveImg" src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=800" class="img-fluid" alt="صورة محاكاة لغلاف المشروع">
                            <div class="mockup-overlay"></div>
                        </div>
                        <div class="mockup-body p-4 bg-white">
                            <h3 id="liveTitle" class="fw-black mb-2 text-dark">{{ old('title') ?? 'عنوان مشروعك يظهر هنا' }}</h3>
                            <p id="liveDesc" class="text-muted small mb-3">{{ old('description') ?? 'وصفك الجذاب سيظهر في هذه المنطقة ليشد انتباه العملاء...' }}</p>
                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <div class="mock-stars"><i class="fas fa-star text-warning"></i> 5.0</div>
                                <div class="mock-tag px-3 py-1 rounded-pill bg-light small fw-bold">إبداع</div>
                            </div>
                        </div>
                    </div>

                    <div class="inspiration-tip mt-4 p-3 rounded-4 bg-glass-dark text-white text-center">
                        <i class="fas fa-lightbulb text-gold mb-2 d-block fs-4"></i>
                        <small class="opacity-75">نصيحة: المشاريع التي تحتوي على وصف مفصل تزيد فرص توظيفك بنسبة 70%!</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');

    .creative-studio-wrapper {
        min-height: 100vh;
        background: radial-gradient(circle at top right, #1e1b4b, #0f172a);
        font-family: 'Cairo', sans-serif;
    }

    .fw-black { font-weight: 900; }
    .text-gold { color: #fbbf24; }

    .icon-orb {
        width: 80px; height: 80px; background: linear-gradient(135deg, #6366f1, #a855f7);
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: white; box-shadow: 0 0 30px rgba(99, 102, 241, 0.5);
    }

    .glass-form {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 40px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .label-premium { color: rgba(255,255,255,0.9); font-weight: 700; margin-bottom: 12px; display: block; font-size: 1.1rem; }

    .input-premium {
        width: 100%; background: rgba(0, 0, 0, 0.2); border: 2px solid rgba(255,255,255,0.05);
        border-radius: 15px; padding: 15px 20px; color: white; transition: all 0.3s ease;
    }
    .input-premium:focus {
        background: rgba(0, 0, 0, 0.4); border-color: #6366f1; outline: none;
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.3);
    }

    .upload-zone {
        border: 2px dashed rgba(255,255,255,0.2); border-radius: 25px;
        padding: 40px; cursor: pointer; transition: all 0.3s ease; position: relative;
    }
    .upload-zone:hover { background: rgba(255, 255, 255, 0.05); border-color: #fbbf24; }
    .upload-icon-anim { font-size: 3rem; color: #fbbf24; animation: bounce 2s infinite; }
    @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    .btn-remove-img {
        position: absolute; top: 15px; left: 15px; background: #ef4444; color: white;
        border: none; width: 40px; height: 40px; border-radius: 50%; display: flex;
        align-items: center; justify-content: center; transition: 0.3s;
    }

    .btn-submit-luxury {
        position: relative; overflow: hidden; background: linear-gradient(135deg, #6366f1, #a855f7);
        color: white; border: none; padding: 20px; border-radius: 20px; font-weight: 900;
        font-size: 1.2rem; transition: all 0.4s ease;
    }
    .btn-submit-luxury:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4); }
    .btn-shine {
        position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
        background: linear-gradient(to right, transparent, rgba(255,255,255,0.3), transparent);
        transform: skewX(-25deg); transition: 0.75s;
    }
    .btn-submit-luxury:hover .btn-shine { left: 125%; }

    .preview-sticky-box { position: sticky; top: 40px; }
    .elite-card-mockup {
        border-radius: 30px; overflow: hidden; transform: rotate(-2deg);
        transition: transform 0.5s ease;
    }
    .elite-card-mockup:hover { transform: rotate(0deg) scale(1.03); }
    .mockup-img-container { height: 250px; position: relative; overflow: hidden; background: #222; }
    .mockup-img-container img { width: 100%; height: 100%; object-fit: cover; }
    .mock-tag { color: #6366f1; }

    .creative-meter { width: 150px; position: relative; }
    .meter-bar { height: 8px; background: #fbbf24; border-radius: 10px; transition: 1s ease; }
    .meter-text { font-size: 0.7rem; color: white; opacity: 0.7; display: block; margin-top: 4px; }

    .bg-glass-dark { background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(10px); }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.getElementById('dropZone');
        const imageInput = document.getElementById('imageInput');
        const liveImg = document.getElementById('liveImg');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const uploadContent = document.querySelector('.upload-content');
        const meterBar = document.querySelector('.meter-bar');

        dropZone.addEventListener('click', () => imageInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#fbbf24';
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.style.borderColor = 'rgba(255,255,255,0.2)';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length) {
                imageInput.files = files;
                handleImage(files[0]);
            }
        });

        imageInput.addEventListener('change', (e) => {
            if (e.target.files.length) handleImage(e.target.files[0]);
        });

        function handleImage(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('imagePreview').src = e.target.result;
                liveImg.src = e.target.result;
                previewContainer.classList.remove('d-none');
                uploadContent.classList.add('d-none');
                updateMeter();
            }
            reader.readAsDataURL(file);
        }

        document.getElementById('projectTitle').addEventListener('input', (e) => {
            const val = e.target.value;
            document.getElementById('liveTitle').innerText = val || "عنوان مشروعك يظهر هنا";
            updateMeter();
        });

        document.getElementById('projectDesc').addEventListener('input', (e) => {
            const val = e.target.value;
            document.getElementById('liveDesc').innerText = val || "وصفك الجذاب سيظهر في هذه المنطقة...";
            document.querySelector('.char-count').innerText = `${val.length} / 500 حرف`;
            updateMeter();
        });

        document.getElementById('removeImg').addEventListener('click', (e) => {
            e.stopPropagation();
            imageInput.value = "";
            previewContainer.classList.add('d-none');
            uploadContent.classList.remove('d-none');
            liveImg.src = "https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=800";
            updateMeter();
        });

        function updateMeter() {
            let score = 0;
            if(document.getElementById('projectTitle').value.length > 5) score += 20;
            if(document.getElementById('projectDesc').value.length > 20) score += 30;
            if(document.getElementById('projectDesc').value.length > 100) score += 20;
            if(imageInput.files.length > 0) score += 30;

            meterBar.style.width = score + '%';
            if(score > 80) meterBar.style.background = '#10b981';
            else if(score > 50) meterBar.style.background = '#fbbf24';
            else meterBar.style.background = '#ef4444';
        }

        updateMeter();

        document.getElementById('projectForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري النشر...';
            btn.disabled = true;
        });
    });
</script>
@endsection
