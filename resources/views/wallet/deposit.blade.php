 @extends('layouts.master')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white py-4 text-center border-0">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-university me-2 text-warning"></i> شحن رصيد المحفظة</h4>
                    <p class="small opacity-75 mb-0 mt-1">سيتم إضافة الرصيد إلى حسابك بالدولار الأمريكي (USD)</p>
                </div>

                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger border-0 shadow-sm small">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('pay.initiate') }}" method="POST" id="payment-form">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">عملة الدفع الحالية</label>
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="currency" id="curr_egp" value="EGP" checked onchange="updateCurrencyLabel()">
                                <label class="btn btn-outline-secondary flex-fill rounded-3 py-2 fw-bold" for="curr_egp">
                                    <img src="https://flagcdn.com/w20/eg.png" class="me-2"> الجنيه المصري
                                </label>

                                <input type="radio" class="btn-check" name="currency" id="curr_usd" value="USD" onchange="updateCurrencyLabel()">
                                <label class="btn btn-outline-secondary flex-fill rounded-3 py-2 fw-bold" for="curr_usd">
                                    <img src="https://flagcdn.com/w20/us.png" class="me-2"> الدولار الأمريكي
                                </label>
                            </div>
                        </div>

                        <div class="mb-4 text-center">
                            <label for="amount" class="form-label fw-bold text-dark">المبلغ المراد دفعه</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-white border-end-0 fw-bold text-primary" id="currency-symbol">EGP</span>
                                <input type="number" name="amount" id="amount" class="form-control border-start-0 text-center fw-bold" placeholder="0.00" min="5" required>
                            </div>

                            <div id="conversion-hint" class="mt-3 p-3 rounded-3 bg-light border animate__animated animate__fadeIn" style="display:none;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small">صافي الرصيد المضاف للمحفظة:</span>
                                    <span class="fw-bold text-success"><span id="usd-estimate">0.00</span> $</span>
                                </div>

                                <div id="egp-equivalent-box" class="text-start mt-2 pt-2 border-top" style="display: none;">
                                    <span class="text-muted" style="font-size: 12px;">
                                        <i class="fas fa-info-circle me-1 text-primary"></i> سيتم الخصم بما يعادل: <strong id="egp-total-val">0.00</strong> EGP
                                    </span>
                                </div>

                                <div class="text-muted mt-1" style="font-size: 11px;">
                                     تطبق رسوم الخدمة والتشغيل الإدارية
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-10">

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">وسيلة الدفع</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="payment_method" id="method_card" value="card" checked onchange="toggleFields()">
                                    <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="method_card">
                                        <i class="fas fa-credit-card d-block mb-1"></i> فيزا / ماستر
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="payment_method" id="method_wallet" value="wallet" onchange="toggleFields()">
                                    <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="method_wallet">
                                        <i class="fas fa-mobile-alt d-block mb-1"></i> محفظة كاش
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="phone_field" class="mb-4" style="display: none;">
                            <label for="phone_number" class="form-label fw-bold small">رقم محفظة فودافون/اتصالات كاش</label>
                            <input type="text" name="phone_number" id="phone_number" class="form-control form-control-lg rounded-3" placeholder="01xxxxxxxxx">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold rounded-4 shadow-sm border-0 mt-2">
                            تأكيد الدفع <i class="fas fa-chevron-left ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// الإعدادات الأساسية (يجب أن تتطابق مع الـ Controller)
const EXCHANGE_RATE = 50.0;
const PLATFORM_FEE = 0.11; // تم رفع العمولة لـ 11%

function updateCurrencyLabel() {
    const isUsd = document.getElementById('curr_usd').checked;
    const symbol = document.getElementById('currency-symbol');

    symbol.innerText = isUsd ? 'USD' : 'EGP';
    calculateEstimate();
}

function calculateEstimate() {
    const amount = document.getElementById('amount').value;
    const usdEstimate = document.getElementById('usd-estimate');
    const hint = document.getElementById('conversion-hint');
    const isUsd = document.getElementById('curr_usd').checked;
    const egpBox = document.getElementById('egp-equivalent-box');
    const egpVal = document.getElementById('egp-total-val');

    if(amount > 0 && amount !== "") {
        hint.style.display = 'block';

        // 1. حساب صافي الدولار للمحفظة بعد خصم الـ 11%
        let baseAmountInUsd = isUsd ? parseFloat(amount) : (parseFloat(amount) / EXCHANGE_RATE);
        let netAmount = baseAmountInUsd * (1 - PLATFORM_FEE);
        usdEstimate.innerText = netAmount.toFixed(2);

        // 2. حساب المبلغ المعادل بالجنيه (اللي هيشوفه في صفحة Paymob)
        if(isUsd) {
            egpBox.style.display = 'block';
            let totalInEgp = parseFloat(amount) * EXCHANGE_RATE;
            egpVal.innerText = totalInEgp.toLocaleString('en-US', {minimumFractionDigits: 2});
        } else {
            egpBox.style.display = 'none';
        }

    } else {
        hint.style.display = 'none';
    }
}

document.getElementById('amount').addEventListener('input', calculateEstimate);

function toggleFields() {
    const isWallet = document.getElementById('method_wallet').checked;
    document.getElementById('phone_field').style.display = isWallet ? 'block' : 'none';
    document.getElementById('phone_number').required = isWallet;
}
</script>

<style>
    .bg-dark { background: #1a1a1a !important; }
    .btn-check:checked + .btn-outline-primary { background-color: #0d6efd; color: #fff; border-color: #0d6efd; }
    .btn-check:checked + .btn-outline-secondary { background-color: #f8f9fa; color: #333; border-color: #333; }
    .input-group-text { border: 2px solid #dee2e6; border-left: 0; }
    .form-control { border: 2px solid #dee2e6; font-size: 1.2rem; }
    .form-control:focus { border-color: #0d6efd; box-shadow: none; }
    .bg-light { background-color: #f8f9fa !important; }
    .animate__animated { animation-duration: 0.4s; }
</style>
@stop
