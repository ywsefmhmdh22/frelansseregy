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

                    <form method="POST" action="{{ route('profile.store') }}" enctype="multipart/form-data">
                        @csrf

                        <h5 class="mb-4 text-success border-bottom pb-2"><i class="fas fa-info-circle me-2"></i> البيانات الأساسية</h5>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">رقم الهاتف <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control bg-light border-0 shadow-sm" placeholder="مثلاً: 01012345678" required>
                                </div>
                                @error('phone') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">التخصص / المهارة <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-briefcase text-muted"></i></span>
                                    <input type="text" name="skills" value="{{ old('skills') }}" class="form-control bg-light border-0 shadow-sm" placeholder="مثلاً: مطور Laravel" required>
                                </div>
                                @error('skills') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">الدولة <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-globe-africa text-muted"></i></span>
                                    <input type="text" name="country" value="{{ old('country') }}" class="form-control bg-light border-0 shadow-sm" placeholder="مثلاً: مصر" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">المدينة <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                    <input type="text" name="city" value="{{ old('city') }}" class="form-control bg-light border-0 shadow-sm" placeholder="مثلاً: القاهرة" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">نبذة تعريفية عنك <span class="text-danger">*</span></label>
                            <textarea name="bio" rows="4" class="form-control bg-light border-0 shadow-sm" placeholder="اشرح خبراتك بوضوح..." required>{{ old('bio') }}</textarea>
                            @error('bio') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                        </div>

                        <h5 class="mb-4 text-primary border-bottom pb-2 mt-5"><i class="fas fa-id-card me-2"></i> توثيق الهوية الشخصية</h5>

                        <div class="mb-4">
                            <label class="form-label fw-bold">رقم البطاقة الشخصية / الهوية <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-fingerprint text-muted"></i></span>
                                <input type="text" name="id_number" value="{{ old('id_number') }}" class="form-control bg-light border-0 shadow-sm" placeholder="أدخل الرقم القومي أو رقم الهوية" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">صورة الهوية (الوجه الأمامي) <span class="text-danger">*</span></label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="id_image" class="form-control border-dashed p-3" accept="image/*" required>
                                    <small class="text-muted"><i class="fas fa-image me-1"></i> JPG, PNG (Max: 2MB)</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">صورة الهوية (الوجه الخلفي) <span class="text-danger">*</span></label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="id_image_back" class="form-control border-dashed p-3" accept="image/*" required>
                                    <small class="text-muted"><i class="fas fa-image me-1"></i> JPG, PNG (Max: 2MB)</small>
                                </div>
                            </div>
                        </div>

                        <div class="text-center pt-4">
                            <button type="submit" class="submit-btn w-100 py-3 fs-5 shadow-lg border-0 text-white fw-bold">
                                إرسال البيانات للتوثيق <i class="fas fa-check-circle ms-2"></i>
                            </button>
                            <p class="mt-4 text-muted small">
                                <i class="fas fa-lock me-1"></i> نضمن لك تشفير بياناتك وعدم مشاركتها مع أي طرف ثالث.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f8fafc; }

    .completion-card {
        border-radius: 2rem !important;
        transition: all 0.3s ease;
    }

    .form-control {
        border-radius: 10px !important;
        padding: 12px 15px;
    }

    .form-control:focus {
        background-color: #fff !important;
        border: 1px solid #10b981 !important;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.1) !important;
    }

    .input-group-text {
        border-radius: 0 10px 10px 0 !important; /* للـ RTL */
        font-size: 1.1rem;
    }

    .input-group > .form-control {
        border-radius: 10px 0 0 10px !important; /* للـ RTL */
    }

    .border-dashed {
        border: 2px dashed #e2e8f0 !important;
        background-color: #f8fafc;
    }

    .submit-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
    }

    /* تحسين العناوين الفرعية */
    h5 {
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }
</style>
@endsection
