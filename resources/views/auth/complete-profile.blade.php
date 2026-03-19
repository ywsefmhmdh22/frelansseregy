@extends('layouts.master')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">
            <div class="completion-card bg-white shadow-lg rounded-5 overflow-hidden border-0">
                <div class="top-accent-bar" style="height: 8px; background: linear-gradient(90deg, #10b981, #3b82f6);"></div>

                <div class="p-5">
                    <div class="text-center mb-5">
                        <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-shield fs-1"></i>
                        </div>
                        <h2 class="fw-bold text-dark">توثيق الحساب والبيانات</h2>
                        <p class="text-secondary">يرجى تزويدنا بالبيانات المطلوبة لضمان أمان حسابك وتفعيل ميزات العمل الحر.</p>
                    </div>

                    {{-- 1. حالة المستخدم الجديد --}}
                    @if(!auth()->user()->verification_status)
                        <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 p-4 text-center" style="background: #f0f9ff;">
                            <i class="fas fa-clock fa-3x mb-3 text-info"></i>
                            <h4 class="fw-bold text-info-emphasis">مرحباً بك في منصتنا!</h4>
                            <p class="mb-0 text-secondary">تم إنشاء حسابك بنجاح. يرجى الانتظار حتى تقوم الإدارة بالموافقة على بدء عملية توثيق حسابك لتتمكن من رفع بياناتك الشخصية.</p>
                        </div>
                    @endif

                    {{-- 2. حالة طلبك قيد المراجعة --}}
                    @if(auth()->user()->verification_status == 'pending')
                        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center p-3" style="background: #fffbeb;">
                            <div class="spinner-border spinner-border-sm me-3 text-warning" role="status"></div>
                            <div class="text-warning-emphasis">
                                <strong>طلبك قيد المراجعة:</strong> بياناتك وصورك محفوظة لدينا، جاري مراجعتها من قبل الإدارة لتفعيل حسابك بالكامل.
                            </div>
                        </div>
                    @endif

                    {{-- 3. حالة الرفض --}}
                    @if(auth()->user()->verification_status == 'rejected')
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 p-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>تم رفض الطلب السابق:</strong> يرجى مراجعة البيانات المرفوعة والتأكد من وضوح صور الهوية ثم المحاولة مرة أخرى.
                        </div>
                    @endif

                    {{-- ظهور الفورم: تم تعديل الشرط هنا ليتوافق مع verified --}}
                    @if(auth()->user()->verification_status == 'verified' || auth()->user()->verification_status == 'rejected')
                        <form id="uploadForm" method="POST" action="{{ route('profile.store') }}" enctype="multipart/form-data">
                            @csrf

                            <h5 class="mb-4 text-success border-bottom pb-2"><i class="fas fa-info-circle me-2"></i> البيانات الأساسية</h5>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">رقم الهاتف <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-phone text-muted"></i></span>
                                        <input type="text" name="phone" value="{{ auth()->user()->phone ?? old('phone') }}" class="form-control bg-light border-0 shadow-sm" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">التخصص / المهارة <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-briefcase text-muted"></i></span>
                                        <input type="text" name="skills" value="{{ auth()->user()->skills ?? old('skills') }}" class="form-control bg-light border-0 shadow-sm" required>
                                    </div>
                                </div>
                            </div>

                            <div id="progressWrapper" class="d-none mb-4">
                                <div class="progress" style="height: 25px; border-radius: 10px;">
                                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;">0%</div>
                                </div>
                                <p id="uploadStatus" class="text-center small mt-2 fw-bold text-primary">جاري معالجة ورفع الملفات...</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">الدولة <span class="text-danger">*</span></label>
                                    <input type="text" name="country" value="{{ auth()->user()->country ?? old('country') }}" class="form-control bg-light border-0 shadow-sm" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">المدينة <span class="text-danger">*</span></label>
                                    <input type="text" name="city" value="{{ auth()->user()->city ?? old('city') }}" class="form-control bg-light border-0 shadow-sm" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">نبذة تعريفية <span class="text-danger">*</span></label>
                                <textarea name="bio" rows="4" class="form-control bg-light border-0 shadow-sm" placeholder="اكتب نبذة عن خبراتك (30 حرف على الأقل)..." required>{{ auth()->user()->bio ?? old('bio') }}</textarea>
                            </div>

                            <h5 class="mb-4 text-primary border-bottom pb-2 mt-5"><i class="fas fa-id-card me-2"></i> توثيق الهوية الشخصية</h5>

                            <div class="mb-4">
                                <label class="form-label fw-bold">رقم البطاقة / الهوية <span class="text-danger">*</span></label>
                                <input type="text" name="id_number" value="{{ auth()->user()->id_number ?? old('id_number') }}" class="form-control bg-light border-0 shadow-sm" placeholder="ادخل الرقم بالكامل" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">صورة الهوية (الأمامي)</label>
                                    <input type="file" name="id_image" class="form-control border-dashed p-3" accept="image/*" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">صورة الهوية (الخلفي)</label>
                                    <input type="file" name="id_image_back" class="form-control border-dashed p-3" accept="image/*" required>
                                </div>
                            </div>

                            <div class="text-center pt-4">
                                <button type="submit" id="submitBtn" class="submit-btn w-100 py-3 fs-5 shadow-lg border-0 text-white fw-bold">
                                    إرسال البيانات للمراجعة <i class="fas fa-check-circle ms-2"></i>
                                </button>
                            </div>
                        </form>
                    @endif

                    @if(auth()->user()->verification_status == 'pending')
                        <div class="text-center pt-4">
                            <div class="pending-container p-3 rounded-4" style="background: #fffbeb; border: 1px solid #fef3c7;">
                                <button type="button" class="btn-pending w-100 py-3 fs-5 shadow-sm border-0 fw-bold">
                                    طلبك قيد المراجعة حالياً <i class="fas fa-hourglass-half ms-2 rotate-icon"></i>
                                </button>
                                <p class="text-warning-emphasis small mt-2 mb-0 fw-bold">سيتم إشعارك فور تفعيل حسابك من قبل الإدارة</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.getElementById('uploadForm').onsubmit = function(e) {
    e.preventDefault();

    const btn = document.getElementById('submitBtn');
    if(btn.disabled) return;

    const formData = new FormData(this);
    const wrapper = document.getElementById('progressWrapper');
    const bar = document.getElementById('progressBar');
    const status = document.getElementById('uploadStatus');

    btn.disabled = true;
    btn.innerHTML = 'جاري الإرسال... <i class="fas fa-spinner fa-spin ms-2"></i>';
    btn.style.opacity = '0.7';

    wrapper.classList.remove('d-none');

    axios.post(this.action, formData, {
        onUploadProgress: (progressEvent) => {
            let percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            bar.style.width = percent + '%';
            bar.innerText = percent + '%';
            status.innerText = "جاري رفع الملفات (" + percent + "%)";
        }
    })
    .then(res => {
        status.innerHTML = "✅ " + res.data.message;
        btn.innerHTML = 'تم الإرسال بنجاح <i class="fas fa-check"></i>';
        btn.classList.replace('submit-btn', 'btn-success');

        // التحويل التلقائي للداشبورد بعد ثانيتين بناءً على رد السيرفر
        setTimeout(() => {
            if (res.data.redirect_to) {
                window.location.href = res.data.redirect_to;
            } else {
                location.reload();
            }
        }, 2000);
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = 'إرسال البيانات للمراجعة <i class="fas fa-check-circle ms-2"></i>';
        btn.style.opacity = '1';
        wrapper.classList.add('d-none');

        let errorMsg = "حدث خطأ أثناء الرفع، يرجى المحاولة مرة أخرى.";
        if(err.response && err.response.data.message) errorMsg = err.response.data.message;
        alert(errorMsg);
    });
};
</script>

<style>
    .border-dashed { border: 2px dashed #cbd5e1 !important; background: #f1f5f9; cursor: pointer; border-radius: 12px; }
    .progress-bar { transition: width 0.4s ease; font-weight: bold; }
    .submit-btn { background: linear-gradient(90deg, #10b981, #3b82f6); border-radius: 15px; transition: all 0.3s ease; }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3) !important; }
    .btn-pending { background: #f59e0b; color: white !important; border-radius: 15px; cursor: not-allowed; }
    .text-warning-emphasis { color: #92400e; }
    .rotate-icon { animation: rotateHour 2s infinite linear; }
    @keyframes rotateHour { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
@endsection
