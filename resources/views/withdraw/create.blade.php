@extends('layouts.master')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

<style>
:root {
    --glass-bg: rgba(255, 255, 255, 0.96);
    /* ألوان خيالية جديدة */
    --primary-gradient: linear-gradient(135deg, #0f172a 0%, #334155 100%);
    --neon-blue: #3b82f6;
    --emerald-glow: #10b981;
    --royal-purple: #6366f1;
}

body {
    background: radial-gradient(circle at center, #f8fafc 0%, #e2e8f0 100%) !important;
    font-family: 'Cairo', sans-serif !important;
}

.withdraw-card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.6) !important;
    border-radius: 40px !important;
    box-shadow: 0 40px 80px -15px rgba(0,0,0,0.08) !important;
    overflow: hidden;
    position: relative;
    transition: all 0.4s ease;
}

/* شريط علوي ملون "بريميوم" */
.withdraw-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 8px;
    background: linear-gradient(90deg, #3b82f6, #6366f1, #10b981, #f59e0b, #ef4444);
    z-index: 10;
}

.digital-balance {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 30px;
    padding: 35px;
    color: white;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
}

.digital-balance::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
    top: -80px; left: -80px;
}

/* شبكة بوابات الدفع */
.method-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.method-item { cursor: pointer; position: relative; }
.method-item input { position: absolute; opacity: 0; }

.method-content {
    background: #ffffff;
    border: 2px solid #f1f5f9;
    padding: 20px 10px;
    border-radius: 25px;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex; flex-direction: column; align-items: center; gap: 10px;
}

.method-item input:checked + .method-content {
    border-color: var(--neon-blue);
    background: #f0f7ff;
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(59, 130, 246, 0.12);
}

.method-icon {
    width: 50px; height: 50px;
    border-radius: 15px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; color: white;
}

/* ألوان الشركات المحسنة */
.bg-vodafone { background: linear-gradient(45deg, #e60000, #ff3333); }
.bg-orange { background: linear-gradient(45deg, #ff6600, #ff9933); }
.bg-etisalat { background: linear-gradient(45deg, #719917, #99cc33); }
.bg-we { background: linear-gradient(45deg, #4c2483, #7b4eb3); }
.bg-instapay { background: linear-gradient(45deg, #612d8a, #8e44ad); }
.bg-paypal { background: linear-gradient(45deg, #003087, #0070ba); }

.luxury-input-group { position: relative; }
.luxury-input-group i {
    position: absolute;
    right: 22px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    transition: 0.3s;
    font-size: 1.2rem;
}

.luxury-input {
    width: 100%;
    height: 65px !important;
    padding: 0 60px 0 25px !important;
    border-radius: 22px !important;
    border: 2px solid #e2e8f0 !important;
    background: #f8fafc !important;
    font-weight: 700 !important;
    font-size: 1.1rem !important;
    transition: all 0.3s ease;
}

.luxury-input:focus {
    border-color: var(--neon-blue) !important;
    background: #fff !important;
    box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.1) !important;
}

.btn-submit-luxury {
    background: var(--primary-gradient);
    color: white;
    height: 70px;
    border-radius: 25px !important;
    border: none;
    font-weight: 900;
    font-size: 1.2rem;
    transition: all 0.4s;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-submit-luxury:hover {
    transform: translateY(-5px);
    filter: brightness(1.2);
    box-shadow: 0 25px 50px rgba(15, 23, 42, 0.35);
}

.warning-box {
    background: #fff7ed;
    border: 1px dashed #fb923c;
    border-radius: 18px;
    padding: 15px;
    font-size: 0.9rem;
    color: #9a3412;
    display: none;
}

@media (max-width: 576px) {
    .method-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<main class="container py-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10">

            <article class="card withdraw-card animate__animated animate__fadeIn">
                <header class="p-5 text-center bg-transparent">
                    <div class="mb-4">
                        <span class="badge rounded-pill px-4 py-2 text-primary fw-bold animate__animated animate__pulse animate__infinite" style="background: #eff6ff;">
                            <i class="fas fa-shield-check me-1"></i> بوابة سحب آمنة 100%
                        </span>
                    </div>
                    <h1 class="fw-black text-dark mb-2" style="font-size: 2rem;">سحب الأرباح</h1>
                    <p class="text-muted">نظام السحب الذكي - أمان وسرعة في التنفيذ</p>
                </header>

                <div class="card-body px-5 pb-5 pt-0">
                    {{-- عرض الرصيد --}}
                    <section class="digital-balance mb-5 animate__animated animate__zoomIn">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="d-block opacity-75 small fw-bold text-uppercase" style="letter-spacing: 1px;">الرصيد القابل للسحب</span>
                                <h2 class="display-5 fw-black mt-1 mb-0">
                                    <span style="color: var(--emerald-glow)">$</span>{{ number_format(Auth::user()->wallet->balance, 2) }}
                                </h2>
                            </div>
                            <div class="icon-circle shadow-lg" style="width: 65px; height: 65px; background: rgba(255,255,255,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-wallet fa-2x text-info"></i>
                            </div>
                        </div>
                    </section>

                    <form action="{{ route('wallet.process_withdraw') }}" method="POST" id="withdrawForm">
                        @csrf
                        <input type="hidden" name="currency" value="USD">

                        {{-- مبلغ السحب --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-3 px-2">مبلغ السحب المطلوب ($)</label>
                            <div class="luxury-input-group">
                                <i class="fas fa-coins"></i>
                                <input type="number" name="amount" id="withdraw_amount"
                                       class="form-control luxury-input"
                                       max="{{ Auth::user()->wallet->balance }}"
                                       placeholder="0.00" required step="0.01">
                            </div>
                        </div>

                        {{-- وسائل السحب --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-3 px-2">اختر بوابة الدفع</label>
                            <div class="method-grid">
                                <label class="method-item">
                                    <input type="radio" name="method" value="vodafone_cash" data-type="wallet" required>
                                    <div class="method-content">
                                        <div class="method-icon bg-vodafone"><i class="fas fa-mobile-screen"></i></div>
                                        <span class="fw-bold small">فودافون كاش</span>
                                    </div>
                                </label>
                                <label class="method-item">
                                    <input type="radio" name="method" value="etisalat_cash" data-type="wallet">
                                    <div class="method-content">
                                        <div class="method-icon bg-etisalat"><i class="fas fa-mobile-screen"></i></div>
                                        <span class="fw-bold small">اتصالات كاش</span>
                                    </div>
                                </label>
                                <label class="method-item">
                                    <input type="radio" name="method" value="orange_cash" data-type="wallet">
                                    <div class="method-content">
                                        <div class="method-icon bg-orange"><i class="fas fa-mobile-screen"></i></div>
                                        <span class="fw-bold small">أورانج كاش</span>
                                    </div>
                                </label>
                                <label class="method-item">
                                    <input type="radio" name="method" value="we_cash" data-type="wallet">
                                    <div class="method-content">
                                        <div class="method-icon bg-we"><i class="fas fa-mobile-screen"></i></div>
                                        <span class="fw-bold small">وي كاش</span>
                                    </div>
                                </label>
                                <label class="method-item">
                                    <input type="radio" name="method" value="instapay" data-type="instapay">
                                    <div class="method-content">
                                        <div class="method-icon bg-instapay"><i class="fas fa-bolt"></i></div>
                                        <span class="fw-bold small">انستا باي</span>
                                    </div>
                                </label>
                                <label class="method-item">
                                    <input type="radio" name="method" value="paypal" data-type="paypal">
                                    <div class="method-content">
                                        <div class="method-icon bg-paypal"><i class="fab fa-paypal"></i></div>
                                        <span class="fw-bold small">بايبال</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- تفاصيل الحساب المتغيرة --}}
                        <div class="mb-4" id="details_section">
                            <label class="form-label fw-bold text-dark mb-3 px-2" id="details_label">تفاصيل الاستلام</label>
                            <div class="luxury-input-group">
                                <i class="fas fa-id-card" id="details_icon"></i>
                                <input type="text" name="details" id="details_input"
                                       class="form-control luxury-input"
                                       placeholder="اختر وسيلة الدفع أولاً" required>
                            </div>
                            <div class="warning-box mt-3 animate__animated animate__fadeIn" id="warning_box">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span>تنبيه: تأكد من صحة البيانات بدقة، نحن غير مسؤولين عن أي خطأ في الرقم المدخل.</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-submit-luxury w-100 shadow-lg mt-3">
                            تأكيد طلب السحب الآن <i class="fas fa-arrow-left"></i>
                        </button>
                    </form>
                </div>
            </article>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const methodInputs = document.querySelectorAll('input[name="method"]');
const detailsLabel = document.getElementById('details_label');
const detailsInput = document.getElementById('details_input');
const detailsIcon = document.getElementById('details_icon');
const warningBox = document.getElementById('warning_box');

methodInputs.forEach(input => {
    input.addEventListener('change', function() {
        const type = this.getAttribute('data-type');
        const methodName = this.parentElement.querySelector('.fw-bold').innerText;

        warningBox.style.display = 'block';

        if(type === 'wallet') {
            detailsLabel.innerText = `رقم محفظة ${methodName}`;
            detailsInput.placeholder = "01xxxxxxxxx";
            detailsIcon.className = "fas fa-mobile-alt";
        } else if(type === 'instapay') {
            detailsLabel.innerText = "عنوان الدفع على انستا باي (IPA)";
            detailsInput.placeholder = "username@instapay";
            detailsIcon.className = "fas fa-at";
        } else if(type === 'paypal') {
            detailsLabel.innerText = "بريد حساب بايبال (PayPal Email)";
            detailsInput.placeholder = "example@email.com";
            detailsIcon.className = "fab fa-paypal";
        }
    });
});

document.getElementById('withdrawForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const amount = document.getElementById('withdraw_amount').value;
    const details = detailsInput.value;

    Swal.fire({
        title: 'تأكيد عملية السحب',
        html: `سيتم طلب سحب <b class="text-success">$${amount}</b> <br> إلى الحساب: <b class="text-primary">${details}</b>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'تأكيد وإرسال',
        cancelButtonText: 'تعديل البيانات',
        confirmButtonColor: '#0f172a',
        cancelButtonColor: '#94a3b8',
        customClass: { popup: 'rounded-5' }
    }).then((result) => {
        if (result.isConfirmed) {
            // رسالة الإبهار الجرافيكية النهائية
            Swal.fire({
                title: 'تم استلام طلبك بنجاح!',
                html: `
                    <div class="animate__animated animate__zoomIn">
                        <p style="font-size: 1.1rem; color: #475569;">جاري الآن مراجعة بياناتك وتحويل الأموال.<br>المدة المتوقعة: <b>3 أيام عمل</b>.</p>
                        <hr>
                        <h5 style="color: #6366f1; font-weight: 900;">شكراً لثقتكم بمنصة مهيير</h5>
                        <i class="fas fa-check-circle fa-3x text-success mt-2"></i>
                    </div>
                `,
                icon: 'success',
                timer: 4500,
                showConfirmButton: false,
                allowOutsideClick: false,
                willClose: () => {
                    // الإرسال الفعلي للكنترولر (WalletController)
                    document.getElementById('withdrawForm').submit();
                }
            });
        }
    });
});
</script>

@endsection
