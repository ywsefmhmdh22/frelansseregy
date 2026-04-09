@extends('layouts.master')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

<style>
:root {
    --glass-bg: rgba(255, 255, 255, 0.95);
    --primary-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    --accent-blue: #3b82f6;
    --success-green: #10b981;
    --input-border: #e2e8f0;
}

body {
    background: radial-gradient(circle at top right, #f8fafc, #e2e8f0) !important;
    font-family: 'Cairo', sans-serif !important;
}

/* بطاقة السحب "فوق الخيال" */
.withdraw-card {
    background: var(--glass-bg);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255,255,255,0.4) !important;
    border-radius: 40px !important;
    box-shadow: 0 50px 100px -20px rgba(0,0,0,0.15), 0 30px 60px -30px rgba(0,0,0,0.1) !important;
    overflow: hidden;
    position: relative;
}

.withdraw-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 6px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
}

/* صندوق الرصيد الرقمي */
.digital-balance {
    background: #1e293b;
    border-radius: 25px;
    padding: 30px;
    color: white;
    position: relative;
    overflow: hidden;
}

.digital-balance::after {
    content: '';
    position: absolute;
    width: 150px; height: 150px;
    background: rgba(59, 130, 246, 0.2);
    border-radius: 50%;
    top: -50px; left: -50px;
}

/* شبكة بوابات الدفع المطورة */
.method-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 15px;
}

.method-item { cursor: pointer; position: relative; }
.method-item input { position: absolute; opacity: 0; }

.method-content {
    background: #ffffff;
    border: 2px solid #f1f5f9;
    padding: 20px 15px;
    border-radius: 24px;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex; flex-direction: column; align-items: center; gap: 12px;
}

.method-item input:checked + .method-content {
    border-color: var(--accent-blue);
    background: #f0f7ff;
    transform: scale(1.05);
    box-shadow: 0 15px 30px rgba(59, 130, 246, 0.15);
}

.method-icon {
    width: 50px; height: 50px;
    border-radius: 15px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: white;
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

/* ألوان البرندات */
.bg-vodafone { background: #e60000; }
.bg-instapay { background: #612d8a; }
.bg-paypal { background: #003087; }
.bg-bank { background: #475569; }
.bg-visa { background: #1a1f71; }

/* الحقول الاحترافية */
.luxury-input-group {
    position: relative;
}

.luxury-input-group i {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    transition: 0.3s;
}

.luxury-input {
    width: 100%;
    padding: 18px 55px 18px 25px !important;
    border-radius: 20px !important;
    border: 2px solid #e2e8f0 !important;
    background: #f8fafc !important;
    font-weight: 700 !important;
    transition: all 0.3s ease;
}

.luxury-input:focus {
    border-color: var(--accent-blue) !important;
    background: #fff !important;
    box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.1) !important;
}

.luxury-input:focus + i { color: var(--accent-blue); }

/* زر الإرسال النهائي */
.btn-submit-luxury {
    background: var(--primary-gradient);
    color: white;
    padding: 20px !important;
    border-radius: 25px !important;
    border: none;
    font-weight: 900;
    letter-spacing: 0.5px;
    transition: all 0.4s;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
}

.btn-submit-luxury:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(15, 23, 42, 0.3);
    filter: brightness(1.2);
}

@media (max-width: 576px) {
    .method-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<main class="container py-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10">

            <article class="card withdraw-card animate__animated animate__fadeInUp">

                <header class="p-5 text-center bg-transparent">
                    <div class="mb-4">
                        <span class="badge rounded-pill bg-soft-primary px-4 py-2 text-primary fw-bold" style="background: #eff6ff;">
                            بوابة سحب آمنة 100%
                        </span>
                    </div>
                    <h1 class="fw-black text-dark mb-2" style="font-size: 2rem;">سحب الأرباح</h1>
                    <p class="text-muted">اختر بوابتك المفضلة وسيتم تحويل أموالك فوراً</p>
                </header>

                <div class="card-body px-5 pb-5 pt-0">

                    <section class="digital-balance mb-5 animate__animated animate__zoomIn">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="d-block opacity-75 small fw-bold">إجمالي الرصيد المتاح</span>
                                <h2 class="display-6 fw-black mt-1 mb-0">
                                    {{ number_format(Auth::user()->wallet->balance, 2) }}
                                    <small style="font-size: 1.2rem; opacity: 0.8;">ج.م</small>
                                </h2>
                            </div>
                            <div class="icon-circle shadow-lg" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-wallet fa-2x text-info"></i>
                            </div>
                        </div>
                    </section>

                    <form action="{{ route('wallet.process_withdraw') }}" method="POST" id="withdrawForm">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark h6 mb-3 px-2">كم تود أن تسحب؟</label>
                            <div class="luxury-input-group">
                                <i class="fas fa-coins fa-lg"></i>
                                <input type="number" name="amount" id="withdraw_amount"
                                       class="form-control luxury-input"
                                       max="{{ Auth::user()->wallet->balance }}"
                                       placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark h6 mb-3 px-2">وسيلة السحب المتاحة</label>
                            <div class="method-grid">

                                <label class="method-item" title="سحب عبر فودافون كاش">
                                    <input type="radio" name="method" value="vodafone_cash" required>
                                    <div class="method-content">
                                        <div class="method-icon bg-vodafone"><i class="fas fa-mobile-alt"></i></div>
                                        <span class="method-name fw-bold small text-dark">فودافون كاش</span>
                                    </div>
                                </label>

                                <label class="method-item" title="سحب عبر انستا باي">
                                    <input type="radio" name="method" value="instapay">
                                    <div class="method-content">
                                        <div class="method-icon bg-instapay"><i class="fas fa-bolt"></i></div>
                                        <span class="method-name fw-bold small text-dark">انستا باي</span>
                                    </div>
                                </label>

                                <label class="method-item" title="سحب عبر PayPal">
                                    <input type="radio" name="method" value="paypal">
                                    <div class="method-content">
                                        <div class="method-icon bg-paypal"><i class="fab fa-paypal"></i></div>
                                        <span class="method-name fw-bold small text-dark">بايبال</span>
                                    </div>
                                </label>

                                <label class="method-item" title="تحويل بنكي مباشر">
                                    <input type="radio" name="method" value="bank_transfer">
                                    <div class="method-content">
                                        <div class="method-icon bg-bank"><i class="fas fa-university"></i></div>
                                        <span class="method-name fw-bold small text-dark">تحويل بنكي</span>
                                    </div>
                                </label>

                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark h6 mb-3 px-2">تفاصيل الحساب المستلم</label>
                            <div class="luxury-input-group">
                                <i class="fas fa-fingerprint fa-lg"></i>
                                <input type="text" name="account_details"
                                       class="form-control luxury-input"
                                       placeholder="رقم المحفظة، IBAN، أو بريد بايبال" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-submit-luxury w-100 shadow-lg">
                            <span class="me-2">تأكيد عملية السحب</span>
                            <i class="fas fa-shield-check"></i>
                        </button>

                    </form>
                </div>
            </article>

            <div class="text-center mt-4 animate__animated animate__fadeIn">
                <p class="text-muted small">
                    <i class="fas fa-lock me-1"></i> جميع المعاملات مشفرة ومحمية بمعايير SSL العالمية
                </p>
            </div>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('withdrawForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const amount = document.getElementById('withdraw_amount').value;
    const method = document.querySelector('input[name="method"]:checked');

    if(!method) {
        Swal.fire({ icon: 'error', title: 'عذراً', text: 'يرجى اختيار وسيلة سحب أولاً' });
        return;
    }

    Swal.fire({
        title: 'هل تريد المتابعة؟',
        html: `أنت على وشك سحب مبلغ <b class="text-primary h4">${amount} ج.م</b>`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'نعم، متأكد',
        cancelButtonText: 'رجوع',
        confirmButtonColor: '#0f172a',
        borderRadius: '25px',
        customClass: { popup: 'rounded-5' }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'جاري المعالجة',
                text: 'يرجى عدم إغلاق الصفحة..',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            this.submit();
        }
    });
});
</script>

@endsection
