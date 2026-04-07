@extends('layouts.master')

@section('content')

<div class="container py-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card border-0 shadow-lg rounded-5 overflow-hidden luxury-card">

                <div class="card-header border-0 p-4 d-flex align-items-center justify-content-between {{ $type === 'premium' ? 'premium-gradient' : 'standard-gradient' }}">
                    <div class="d-flex align-items-center">
                        <div class="icon-badge me-3 shadow-sm">
                            <i class="fas {{ $type === 'premium' ? 'fa-rocket' : 'fa-briefcase' }}"></i>
                        </div>
                        <div class="ms-3 text-right">
                            <h4 class="mb-0 fw-bold text-white">إضافة مشروع جديد</h4>
                            <p class="mb-0 text-white-50 small">املأ التفاصيل لجذب أفضل المستقلين</p>
                        </div>
                    </div>
                    @if($type === 'premium')
                        <div class="premium-badge shadow-sm">
                            <i class="fas fa-crown me-1 text-warning"></i> مشروع مميز
                        </div>
                    @endif
                </div>

                <div class="card-body p-4 p-lg-5 bg-white">

                    @if($type === 'premium')
                        <div class="alert alert-premium d-flex align-items-center border-0 rounded-4 p-3 mb-4 shadow-sm">
                            <div class="alert-icon-box me-3 text-warning">
                                <i class="fas fa-gem fa-lg"></i>
                            </div>
                            <div class="ms-3 text-right">
                                <strong class="d-block mb-1">خيار ذكي!</strong>
                                <span class="small opacity-75">سيتم تمييز مشروعك باللون الذهبي وتثبيته في المقدمة لجذب النخبة.</span>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" id="projectForm">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">

                        {{-- قسم الصورة الرئيسية --}}
                        <div class="mb-5">
                            {{-- تم إضافة id للربط --}}
                            <label id="main_image_label" class="form-label fw-bold text-dark h6 mb-3 text-right d-block">الصورة التوضيحية الرئيسية</label>
                            <div class="image-upload-wrapper">
                                <label for="image_url" class="image-drop-zone rounded-4 text-center p-4 w-100 mb-0 position-relative">
                                    <div class="upload-content" id="upload_placeholder">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary-soft mb-3"></i>
                                        <h6 class="fw-bold text-dark">اسحب الصورة الرئيسية هنا أو اضغط للاختيار</h6>
                                        <p class="text-muted small mb-0">الحجم الأقصى 2MB (يفضل 1200x630)</p>
                                    </div>

                                    <div id="preview_info_container" class="d-none">
                                        {{-- إصلاح L56: إضافة alt attribute للمعاينة --}}
                                        <img id="image_preview" src="" class="img-fluid rounded-4 shadow-sm mb-2" style="max-height: 250px; width: 100%; object-fit: cover;" alt="معاينة صورة المشروع المختارة">
                                        <div class="d-flex justify-content-center gap-2 mb-2">
                                            <button type="button" class="badge bg-danger border-0 rounded-pill px-3 py-2" onclick="resetImageInput(event)">حذف الصورة <i class="fas fa-trash-alt ms-1"></i></button>
                                        </div>
                                    </div>

                                    <input type="file" name="image_url" id="image_url" class="d-none" accept="image/*" onchange="previewImage(this)">
                                </label>
                            </div>
                            @error('image_url')
                                <div class="text-danger small mt-2 text-right"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        {{-- عنوان المشروع --}}
                        <div class="mb-4 text-right">
                            {{-- إصلاح L46: ربط الـ label بـ id الخانة --}}
                            <label for="project_title" class="form-label fw-bold text-dark h6 mb-3">عنوان المشروع</label>
                            <div class="input-group-custom">
                                <i class="fas fa-pen-nib input-icon"></i>
                                <input type="text" name="title" id="project_title"
                                       class="form-control luxury-input @error('title') is-invalid @enderror"
                                       placeholder="مثلاً: تصميم هوية بصرية لشركة عقارات ناشئة"
                                       value="{{ old('title') }}">
                            </div>
                            @error('title')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- وصف المشروع --}}
                        <div class="mb-4 text-right">
                            {{-- إصلاح: ربط الـ label بالـ textarea --}}
                            <label for="editor" class="form-label fw-bold text-dark h6 mb-3">وصف المشروع بالتفصيل</label>
                            <div class="editor-wrapper shadow-sm rounded-4 overflow-hidden">
                                <textarea name="description" id="editor" class="form-control">{{ old('description') }}</textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-2 flex-row-reverse">
                                <span class="text-muted small">اشرح بوضوح ما تريده، المعايير الفنية، وما تتوقع الحصول عليه.</span>
                            </div>
                            @error('description')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- المرفقات --}}
                        <div class="mb-5 text-right">
                            {{-- إصلاح L71: ربط الـ label بـ id المرفقات --}}
                            <label for="attachments_input" class="form-label fw-bold text-dark h6 mb-3">إرفاق ملفات توضيحية إضافية (PDF, ZIP, الصور المرجعية)</label>
                            <div class="input-group-custom">
                                <i class="fas fa-paperclip input-icon"></i>
                                <input type="file" id="attachments_input" name="attachments[]" class="form-control luxury-input" multiple onchange="handleFiles(this)">
                            </div>
                            <small class="text-muted mt-2 d-block">يمكنك اختيار ملفات متعددة. ستظهر القائمة أدناه لإدارتها.</small>

                            <div id="files_list_container" class="mt-3 d-none">
                                <div class="p-3 rounded-4 bg-light border border-dashed">
                                    <h6 class="small fw-bold mb-3"><i class="fas fa-list-ul me-2"></i> الملفات المختارة:</h6>
                                    <div id="files_preview_wrapper" class="d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row text-right">
                            {{-- الميزانية --}}
                            <div class="col-md-6 mb-4">
                                <label for="project_price" class="form-label fw-bold text-dark h6 mb-3">الميزانية والعملة</label>
                                <div class="d-flex gap-2">
                                    <select name="currency" aria-label="اختر العملة" class="form-select luxury-input-select" style="width: 100px; border-radius: 15px !important; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold;" onchange="updateChargeNotice(this.value)">
                                        <option value="EGP" {{ old('currency') == 'EGP' ? 'selected' : '' }}>ج.م</option>
                                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    </select>
                                    <div class="input-group-custom flex-grow-1">
                                        <i class="fas fa-wallet input-icon text-success"></i>
                                        <input type="number" name="price" id="project_price"
                                               class="form-control luxury-input @error('price') is-invalid @enderror"
                                               placeholder="الميزانية"
                                               step="0.01"
                                               value="{{ old('price') }}">
                                    </div>
                                </div>
                                <div class="mt-2 small text-muted" id="charge_notice">
                                    <i class="fas fa-info-circle me-1"></i> ملاحظة: يجب شحن رصيدك بـ <span id="currency_type_name">الجنيه المصري</span> لتفعيل المشروع.
                                </div>
                                @error('price') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>

                            {{-- مدة التنفيذ --}}
                            <div class="col-md-6 mb-4">
                                <label for="project_duration" class="form-label fw-bold text-dark h6 mb-3">مدة التنفيذ المتوقعة</label>
                                <div class="input-group-custom">
                                    <i class="fas fa-hourglass-half input-icon text-primary"></i>
                                    <input type="text" name="duration" id="project_duration"
                                           class="form-control luxury-input @error('duration') is-invalid @enderror"
                                           placeholder="مثلاً: 10 أيام"
                                           value="{{ old('duration') }}">
                                </div>
                                @error('duration') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-end mt-4 pt-4 border-top">
                            <a href="{{ route('client.dashboard') }}"
                               class="btn btn-outline-light text-dark px-5 py-3 rounded-pill fw-bold order-2 order-sm-1">
                                إلغاء العملية
                            </a>
                            <button type="submit" id="submitBtn"
                                    class="btn {{ $type === 'premium' ? 'btn-premium-action' : 'btn-primary-action' }} px-5 py-3 rounded-pill shadow-lg fw-bold order-1 order-sm-2">
                                <i class="fas {{ $type === 'premium' ? 'fa-paper-plane' : 'fa-check-circle' }} me-2"></i>
                                {{ $type === 'premium' ? 'نشر المشروع المميز فـوراً' : 'نشر مشروعي الآن' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Luxury Design System */
.luxury-card { border: none; box-shadow: 0 30px 60px rgba(0,0,0,0.08) !important; }
.standard-gradient { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); }
.premium-gradient { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }

.icon-badge {
    width: 45px; height: 45px; background: rgba(255,255,255,0.2);
    border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;
}

.premium-badge {
    background: #facc15; color: #000; padding: 6px 15px;
    border-radius: 50px; font-size: 13px; font-weight: 800;
}

.input-group-custom { position: relative; display: flex; align-items: center; }
.input-icon { position: absolute; right: 18px; color: #94a3b8; z-index: 5; font-size: 1.1rem; }

.luxury-input {
    padding: 16px 50px 16px 20px !important; border-radius: 15px !important;
    border: 1px solid #e2e8f0 !important; background-color: #f8fafc !important;
    transition: all 0.3s ease; font-weight: 500; text-align: right;
}

.luxury-input:focus {
    background-color: #fff !important; border-color: #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
}

.image-drop-zone { border: 2px dashed #cbd5e1; cursor: pointer; transition: all 0.3s ease; background: #f8fafc; }
.image-drop-zone:hover { border-color: #3b82f6; background: #eff6ff; }
.text-primary-soft { color: #bfdbfe; }

.btn-primary-action { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border: none; color: white; }
.btn-premium-action { background: linear-gradient(135deg, #facc15 0%, #eab308 100%); border: none; color: #000; }

.file-item-badge { transition: all 0.2s ease; border: 1px solid #e2e8f0 !important; background: white; }
.file-item-badge:hover { background-color: #f1f5f9 !important; }
.border-dashed { border: 2px dashed #e2e8f0 !important; }

.ck-editor__editable { min-height: 250px !important; text-align: right !important; direction: rtl !important; border-radius: 0 0 15px 15px !important; background: #f8fafc !important; }
.ck-toolbar { border-radius: 15px 15px 0 0 !important; border: 1px solid #e2e8f0 !important; }
</style>

<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>

<script>
// 1. Initialize CKEditor 5
ClassicEditor
    .create(document.querySelector('#editor'), {
        language: 'ar',
        content: { direction: 'rtl' }
    })
    .catch(error => { console.error(error); });

// 2. Main Image Preview
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

// 3. Multi-Attachments Logic
let selectedFiles = [];

function handleFiles(input) {
    const files = Array.from(input.files);
    files.forEach(file => {
        if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
            selectedFiles.push(file);
        }
    });

    renderFiles();
    input.value = '';
}

function renderFiles() {
    const wrapper = document.getElementById('files_preview_wrapper');
    const container = document.getElementById('files_list_container');

    wrapper.innerHTML = '';

    if (selectedFiles.length > 0) {
        container.classList.remove('d-none');
    } else {
        container.classList.add('d-none');
    }

    selectedFiles.forEach((file, index) => {
        const fileBox = document.createElement('div');
        fileBox.className = 'file-item-badge d-flex align-items-center rounded-pill px-3 py-2 shadow-sm';
        fileBox.innerHTML = `
            <i class="fas fa-file-alt text-primary me-2 ml-2"></i>
            <span class="small text-truncate" style="max-width: 150px;">${file.name}</span>
            <button type="button" class="btn-close ms-2" aria-label="Remove file" style="font-size: 0.7rem; margin-right: 10px;" onclick="removeFile(${index})"></button>
        `;
        wrapper.appendChild(fileBox);
    });

    syncInputFiles();
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    renderFiles();
}

function syncInputFiles() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    document.getElementById('attachments_input').files = dataTransfer.files;
}

// 4. Update Currency Text
function updateChargeNotice(val) {
    const names = {'EGP': 'الجنيه المصري', 'USD': 'الدولار الأمريكي', 'EUR': 'اليورو'};
    document.getElementById('currency_type_name').innerText = names[val] || val;
}
</script>

@endsection
