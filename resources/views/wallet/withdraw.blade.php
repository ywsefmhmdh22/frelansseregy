<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب سحب أرباح - FreelancerPro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800&display=swap" rel="stylesheet">

    <style>
        /* --- Global Theme & Reset --- */
        body {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
            min-height: 100vh;
            font-family: 'Cairo', sans-serif !important;
            margin: 0; padding: 0;
            display: flex; align-items: center; justify-content: center;
        }

        /* --- Withdraw Card --- */
        .withdraw-card {
            border: none !important;
            border-radius: 35px !important;
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.12) !important;
            background: #fff;
            width: 100%; max-width: 600px;
            margin: 2rem; overflow: hidden;
        }

        .withdraw-icon-wrapper {
            width: 70px; height: 70px;
            background: #eff6ff;
            color: #2563eb;
            display: flex; align-items: center; justify-content: center;
            border-radius: 22px;
            font-size: 2rem;
            margin: 2rem auto 1.5rem;
        }

        /* --- Balance Widget --- */
        .balance-widget {
            background: #f0fdf4 !important;
            border: 1px solid #dcfce7 !important;
            border-radius: 18px !important;
            padding: 1rem !important;
            display: flex !important;
            align-items: center !important;
            margin-bottom: 2rem;
        }
        .wallet-icon {
            color: #16a34a !important;
            background: #fff !important;
            width: 45px; height: 45px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 12px !important;
            margin-right: 15px !important;
        }

        /* --- Inputs --- */
        .input-group-custom { position: relative; display: flex; align-items: center; }
        .input-icon { position: absolute; right: 20px; color: #94a3b8; z-index: 5; }
        .currency-tag { position: absolute; left: 20px; font-weight: bold; color: #64748b; }

        .luxury-input {
            padding: 16px 55px 16px 60px !important;
            border-radius: 18px !important;
            border: 2px solid #e2e8f0 !important;
            background-color: #f8fafc !important;
            transition: all 0.3s ease;
            font-weight: 600 !important;
        }
        .luxury-input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.1) !important;
            background-color: #fff !important;
        }

        /* --- Method Selection Grid --- */
        .method-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }

        .method-item { cursor: pointer; position: relative; width: 100%; }
        .method-item input { position: absolute; opacity: 0; cursor: pointer; }

        .method-content {
            border: 2px solid #f1f5f9;
            padding: 20px 10px;
            border-radius: 20px;
            text-align: center;
            transition: 0.3s ease;
            background: #fff;
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            width: 100%;
        }

        .method-item input:checked + .method-content {
            border-color: #2563eb;
            background-color: #eff6ff;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.1);
        }

        .method-logo-bg {
            width: 45px; height: 45px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: #fff;
        }
        .vodafone { background: #e60000; }
        .instapay { background: #612d8a; }

        .method-name { font-size: 0.9rem; font-weight: 700; color: #334155; }

        /* --- Button --- */
        .btn-primary-withdraw {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: none; color: white; padding: 18px !important;
            transition: 0.3s ease;
            border-radius: 50px !important;
            margin-top: 2rem;
        }
        .btn-primary-withdraw:hover {
            transform: translateY(-3px);
            filter: brightness(1.2);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        @media (max-width: 480px) {
            .method-grid { grid-template-columns: 1fr; }
            .withdraw-card { margin: 1rem; }
        }
    </style>
</head>
<body>

    <article class="card border-0 shadow-lg rounded-5 overflow-hidden withdraw-card animate__animated animate__fadeIn">

        {{-- Header Section --}}
        <header class="card-header border-0 p-4 text-center bg-white pt-5">
            <div class="withdraw-icon-wrapper mb-3 shadow-sm mx-auto animate__animated animate__bounceIn" aria-hidden="true">
                <i class="fas fa-money-bill-transfer text-primary"></i>
            </div>
            <h1 class="fw-bold text-dark h4 mb-2">طلب سحب أرباح</h1>
            <p class="text-muted small">قم بسحب أرباحك بسهولة عبر وسيلتك المفضلة</p>
        </header>

        <div class="card-body p-4 p-lg-5 pt-2">

            {{-- Balance Display --}}
            <section class="balance-widget mb-4 shadow-sm" aria-label="رصيدك الحالي">
                <div class="d-flex align-items-center">
                    <div class="wallet-icon me-3 ml-3" aria-hidden="true">
                        <i class="fas fa-wallet fa-lg"></i>
                    </div>
                    <div class="text-right pr-3">
                        <span class="d-block text-muted small">رصيدك القابل للسحب</span>
                        <h2 class="h5 fw-bold mb-0 text-dark">{{ Auth::user()->wallet->balance }} <span class="small text-primary">ج.م</span></h2>
                    </div>
                </div>
            </section>

            <form action="{{ route('wallet.process_withdraw') }}" method="POST" id="withdrawForm">
                @csrf

                {{-- Amount Input --}}
                <div class="mb-4 text-right">
                    <label for="withdraw_amount" class="form-label fw-bold text-dark h6 mb-3">المبلغ المراد سحبه</label>
                    <div class="input-group-custom">
                        <span class="currency-tag" aria-hidden="true">ج.م</span>
                        <input type="number" name="amount" id="withdraw_amount"
                               class="form-control luxury-input"
                               max="{{ Auth::user()->wallet->balance }}"
                               placeholder="أدخل المبلغ هنا.."
                               required
                               aria-required="true">
                    </div>
                    <small class="text-muted mt-2 d-block px-2">الحد الأدنى للسحب هو 50 ج.م</small>
                </div>

                {{-- Method Selection (Radio Cards) --}}
                <div class="mb-4 text-right">
                    <label class="form-label fw-bold text-dark h6 mb-3 d-block">اختر وسيلة السحب</label>
                    <div class="method-grid">

                        <label class="method-item" role="radio" aria-checked="false">
                            <input type="radio" name="method" value="vodafone_cash" required>
                            <div class="method-content shadow-sm">
                                <div class="method-logo-bg vodafone">
                                    <i class="fas fa-mobile-screen-button"></i>
                                </div>
                                <span class="method-name">فودافون كاش</span>
                            </div>
                        </label>

                        <label class="method-item" role="radio" aria-checked="false">
                            <input type="radio" name="method" value="instapay">
                            <div class="method-content shadow-sm">
                                <div class="method-logo-bg instapay">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <span class="method-name">انستا باي</span>
                            </div>
                        </label>

                    </div>
                </div>

                {{-- Account Details --}}
                <div class="mb-5 text-right">
                    <label for="account_details" class="form-label fw-bold text-dark h6 mb-3">بيانات الحساب</label>
                    <div class="input-group-custom">
                        <i class="fas fa-id-card input-icon" aria-hidden="true"></i>
                        <input type="text" name="account_details" id="account_details"
                               class="form-control luxury-input"
                               placeholder="الرقم، البريد الإلكتروني، أو IBAN"
                               required
                               aria-required="true">
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary-withdraw w-100 rounded-pill py-3 fw-bold shadow-lg">
                    إرسال طلب السحب للمراجعة <i class="fas fa-paper-plane ms-2 mr-2"></i>
                </button>
            </form>
        </div>
    </article>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.getElementById('withdrawForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const amount = document.getElementById('withdraw_amount').value;
        const methodInput = document.querySelector('input[name="method"]:checked');
        const methodLabel = methodInput ? methodInput.parentElement.querySelector('.method-name').innerText : '';

        if(!methodInput) {
            Swal.fire({
                icon: 'warning',
                title: 'برجاء اختيار وسيلة السحب',
                confirmButtonText: 'فهمت',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        Swal.fire({
            title: 'تأكيد طلب السحب',
            html: `<p>هل أنت متأكد من سحب مبلغ <strong>${amount} ج.م</strong>؟</p>
                   <p class="text-muted small">الوسيلة المختارة: ${methodLabel}</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، أرسل الطلب',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#1e293b',
            cancelButtonColor: '#64748b',
            customClass: { popup: 'rounded-5' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'جاري الإرسال..',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                this.submit();
            }
        });
    });

    // تحديث ARIA labels لبطاقات الراديو عند النقر
    document.querySelectorAll('.method-item').forEach(item => {
        item.addEventListener('click', () => {
            document.querySelectorAll('.method-item').forEach(i => i.setAttribute('aria-checked', 'false'));
            item.setAttribute('aria-checked', 'true');
        });
    });
    </script>

</body>
</html>
