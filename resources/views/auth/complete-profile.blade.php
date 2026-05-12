@extends('layouts.master')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #10b981 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
    }

    body { background-color: #f8fafc; font-family: 'Cairo', sans-serif; }

    .completion-card {
        border-radius: 2rem;
        border: none;
        backdrop-filter: blur(10px);
        background: var(--glass-bg);
    }

    /* تخصيص Select2 ليتماشى مع التصميم */
    .select2-container--default .select2-selection--multiple {
        border: none !important;
        background-color: #f1f5f9 !important;
        border-radius: 12px !important;
        padding: 5px !important;
        min-height: 50px;
    }
    .select2-container--default .select2-selection__choice {
        background-color: #3b82f6 !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 4px 10px !important;
        font-size: 0.9rem;
    }
    .select2-container--default .select2-selection__choice__remove {
        color: white !important;
        margin-left: 5px !important;
    }

    .form-label { color: #475569; font-weight: 700; font-size: 0.95rem; margin-bottom: 0.5rem; }
    .form-control { border-radius: 12px; padding: 12px 15px; transition: all 0.3s; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); border-color: #3b82f6; }

    .border-dashed {
        border: 2px dashed #cbd5e1 !important;
        background: #f8fafc;
        transition: 0.3s;
        cursor: pointer;
    }
    .border-dashed:hover { border-color: #3b82f6 !important; background: #f1f7ff; }

    .submit-btn {
        background: var(--primary-gradient);
        border-radius: 15px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        letter-spacing: 0.5px;
    }
    .submit-btn:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 12px 24px rgba(59, 130, 246, 0.3); }

    .icon-pulse { animation: pulse 2s infinite; }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
</style>

<div class="container py-5" style="direction: rtl;">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="completion-card bg-white shadow-xl overflow-hidden animate__animated animate__fadeIn">
                <div class="top-accent-bar" style="height: 8px; background: var(--primary-gradient);"></div>

                <div class="p-4 p-md-5">
                    <div class="text-center mb-5">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 icon-pulse" style="width: 90px; height: 90px;">
                            <i class="fas fa-user-check fs-1"></i>
                        </div>
                        <h2 class="fw-extrabold text-dark">إكمال الملف المهني</h2>
                        <p class="text-muted fs-5">خطوة واحدة تفصلك عن الانضمام لنخبة المبرمجين</p>
                    </div>

                    {{-- التنبيهات --}}
                    @if(!auth()->user()->verification_status)
                        <div class="alert border-0 shadow-sm rounded-4 mb-4 p-4 text-center animate__animated animate__headShake" style="background: #eef2ff;">
                            <i class="fas fa-rocket fa-3x mb-3 text-primary"></i>
                            <h4 class="fw-bold text-primary">أهلاً بك يا بطل!</h4>
                            <p class="mb-0 text-secondary">حسابك قيد التجهيز. بانتظار إشارة الإدارة لتبدأ برفع إبداعاتك.</p>
                        </div>
                    @endif

                    @if(auth()->user()->verification_status == 'pending')
                        <div class="alert border-0 shadow-sm rounded-4 mb-4 p-4 text-center" style="background: #fffbeb;">
                            <div class="spinner-grow text-warning mb-3" role="status"></div>
                            <h4 class="fw-bold text-warning-emphasis">جاري المراجعة بعناية</h4>
                            <p class="mb-0">فريقنا يراجع بياناتك الآن لضمان جودة المنصة. سيصلك إشعار قريباً!</p>
                        </div>
                    @endif

                    @if(auth()->user()->verification_status == 'verified' || auth()->user()->verification_status == 'rejected' || !auth()->user()->verification_status)
                        <form id="uploadForm" method="POST" action="{{ route('profile.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="section-title d-flex align-items-center mb-4">
                                <span class="bg-primary text-white rounded-pill px-3 py-1 me-2 fw-bold small">1</span>
                                <h5 class="mb-0 fw-bold text-dark">الهوية والبيانات الشخصية</h5>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">رقم الهاتف النشط</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-phone-alt"></i></span>
                                        <input type="text" name="phone" value="{{ auth()->user()->phone }}" class="form-control bg-light border-0 shadow-sm" placeholder="01xxxxxxxxx" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">الدولة</label>
                                    <input type="text" name="country" value="{{ auth()->user()->country }}" class="form-control bg-light border-0 shadow-sm" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">التخصصات البرمجية (يمكنك اختيار أكثر من تخصص) <span class="text-danger">*</span></label>
                                <select id="specialization_select" name="skills[]" class="form-control select2-multiple" multiple="multiple" required>
                                    @php
                                        $userSkills = is_array(auth()->user()->skills) ? auth()->user()->skills : json_decode(auth()->user()->skills ?? '[]', true);
                                    @endphp
                                    <optgroup label="تطوير الويب (Backend)">
                                        <option value="PHP / Laravel" {{ in_array('PHP / Laravel', $userSkills) ? 'selected' : '' }}>PHP / Laravel</option>
                                        <option value="Node.js" {{ in_array('Node.js', $userSkills) ? 'selected' : '' }}>Node.js</option>
                                        <option value="Python / Django" {{ in_array('Python / Django', $userSkills) ? 'selected' : '' }}>Python / Django</option>
                                    </optgroup>
                                    <optgroup label="تطوير الويب (Frontend)">
                                        <option value="React.js" {{ in_array('React.js', $userSkills) ? 'selected' : '' }}>React.js</option>
                                        <option value="Vue.js" {{ in_array('Vue.js', $userSkills) ? 'selected' : '' }}>Vue.js</option>
                                        <option value="Tailwind CSS" {{ in_array('Tailwind CSS', $userSkills) ? 'selected' : '' }}>Tailwind CSS</option>
                                    </optgroup>
                                    <optgroup label="تطبيقات الموبايل">
                                        <option value="Flutter" {{ in_array('Flutter', $userSkills) ? 'selected' : '' }}>Flutter</option>
                                        <option value="React Native" {{ in_array('React Native', $userSkills) ? 'selected' : '' }}>React Native</option>
                                    </optgroup>
                                    {{-- يمكنك إضافة باقي التخصصات هنا --}}
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">نبذة احترافية عنك</label>
                                <textarea name="bio" rows="3" class="form-control bg-light border-0 shadow-sm" placeholder="احكِ لنا عن مشاريعك وخبراتك البرمجية..." required>{{ auth()->user()->bio }}</textarea>
                            </div>

                            <div class="section-title d-flex align-items-center mb-4 mt-5">
                                <span class="bg-primary text-white rounded-pill px-3 py-1 me-2 fw-bold small">2</span>
                                <h5 class="mb-0 fw-bold text-dark">رفع صور الهوية لتوثيق الحساب</h5>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">رقم البطاقة الشخصية / جواز السفر</label>
                                <input type="text" name="id_number" value="{{ auth()->user()->id_number }}" class="form-control bg-light border-0 shadow-sm" placeholder="أدخل الرقم القومي بدقة" required>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-6 mb-3">
                                    <div class="upload-area p-3 border-dashed rounded-4 text-center" onclick="document.getElementById('id_image').click()">
                                        @if(auth()->user()->id_image)
                                            <img src="{{ Storage::disk('s3')->url(auth()->user()->id_image) }}" class="img-fluid rounded-3 mb-2" style="max-height: 100px;">
                                        @else
                                            <i class="fas fa-id-card-alt fa-2x text-muted mb-2"></i>
                                        @endif
                                        <p class="small mb-0">وجه البطاقة (Front)</p>
                                        <input type="file" id="id_image" name="id_image" class="d-none" accept="image/*" {{ auth()->user()->id_image ? '' : 'required' }}>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="upload-area p-3 border-dashed rounded-4 text-center" onclick="document.getElementById('id_image_back').click()">
                                        @if(auth()->user()->id_image_back)
                                            <img src="{{ Storage::disk('s3')->url(auth()->user()->id_image_back) }}" class="img-fluid rounded-3 mb-2" style="max-height: 100px;">
                                        @else
                                            <i class="fas fa-id-card-alt fa-2x text-muted mb-2"></i>
                                        @endif
                                        <p class="small mb-0">ظهر البطاقة (Back)</p>
                                        <input type="file" id="id_image_back" name="id_image_back" class="d-none" accept="image/*" {{ auth()->user()->id_image_back ? '' : 'required' }}>
                                    </div>
                                </div>
                            </div>

                            {{-- شريط التقدم --}}
                            <div id="progressWrapper" class="d-none mb-4 animate__animated animate__fadeIn">
                                <div class="progress" style="height: 12px; border-radius: 50px;">
                                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%;"></div>
                                </div>
                                <p id="uploadStatus" class="text-center small mt-2 fw-bold text-primary">بدء عملية الرفع السحابي الآمن...</p>
                            </div>

                            <button type="submit" id="submitBtn" class="submit-btn w-100 py-3 fs-5 shadow border-0 text-white fw-bold">
                                حفظ البيانات وتوثيق الحساب <i class="fas fa-shield-alt ms-2"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
$(document).ready(function() {
    $('#specialization_select').select2({
        placeholder: "اختر تخصصاتك البرمجية",
        allowClear: true,
        width: '100%',
        dir: "rtl"
    });

    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#submitBtn');
        const formData = new FormData(this);
        const wrapper = $('#progressWrapper');
        const bar = $('#progressBar');
        const status = $('#uploadStatus');

        btn.prop('disabled', true).html('جاري معالجة البيانات سحابياً.. <i class="fas fa-circle-notch fa-spin ms-2"></i>');
        wrapper.removeClass('d-none');

        axios.post(this.action, formData, {
            onUploadProgress: (progressEvent) => {
                let percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                bar.css('width', percent + '%');
                status.text(`جاري الرفع إلى Laravel Cloud (${percent}%)`);
            }
        })
        .then(res => {
            status.html('<i class="fas fa-check-circle me-1"></i> تم الحفظ والرفع بنجاح!');
            btn.addClass('bg-success').html('تم بنجاح!');
            setTimeout(() => {
                window.location.href = res.data.redirect_to || '/client/dashboard';
            }, 1500);
        })
        .catch(err => {
            btn.prop('disabled', false).html('حاول مرة أخرى <i class="fas fa-redo ms-2"></i>');
            wrapper.addClass('d-none');
            alert(err.response?.data?.message || "حدث خطأ أثناء الرفع");
        });
    });
});
</script>
@endsection
