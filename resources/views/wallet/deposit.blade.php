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
                                <input type="radio" class="btn-check" name="currency" id="curr_egp" value="EGP" checked onchange="calculateEstimate()">
                                <label class="btn btn-outline-secondary flex-fill rounded-3 py-2 fw-bold" for="curr_egp">
                                    <img src="https://flagcdn.com/w20/eg.png" class="me-2"> الجنيه المصري
                                </label>

                                <input type="radio" class="btn-check" name="currency" id="curr_usd" value="USD" onchange="calculateEstimate()">
                                <label class="btn btn-outline-secondary flex-fill rounded-3 py-2 fw-bold" for="curr_usd">
                                    <img src="https://flagcdn.com/w20/us.png" class="me-2"> الدولار الأمريكي
                                </label>
                            </div>
                        </div>

                        <div class="mb-4 text-center">
                            <label for="amount" class="form-label fw-bold text-dark">المبلغ المراد دفعه</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-white border-end-0 fw-bold text-primary" id="currency-symbol">EGP</span>
                                <input type="number" name="amount" id="amount" class="form-control border-start-0 text-center fw-bold" placeholder="0.00" min="5" step="0.01" required>
                            </div>

                            <div id="conversion-hint" class="mt-3 p-3 rounded-3 bg-light border animate__animated animate__fadeIn" style="display:none;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small">صافي الرصيد المضاف للمحفظة:</span>
                                    <span class="fw-bold text-success"><span id="usd-estimate">0.00</span> $</span>
                                </div>

                                <div id="egp-equivalent-box" class="text-start mt-2 pt-2 border-top" style="display: none;">
                                    <span class="text-muted" style="font-size: 12px;">
                                        <i class="fas fa-info-circle me-1 text-primary"></i> سيتم الخصم بما يعادل تقريباً: <strong id="egp-total-val">0.00</strong> EGP
                                    </span>
                                </div>

                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    تطبق رسوم الخدمة والتشغيل الإدارية ({{ ($platformFee ?? 0.11) * 100 }}%)
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
// جلب البيانات من الكنترولر أو استخدام قيم افتراضية
// ملاحظة: تأكد من إرسال هذه القيم من الـ Controller الخاص بك
const EXCHANGE_RATE = {{ $exchangeRate ?? 50.0 }};
const PLATFORM_FEE = {{ $platformFee ?? 0.11 }};

function calculateEstimate() {
    const amountInput = document.getElementById('amount');
    const usdEstimate = document.getElementById('usd-estimate');
    const hint = document.getElementById('conversion-hint');
    const isUsd = document.getElementById('curr_usd').checked;
    const egpBox = document.getElementById('egp-equivalent-box');
    const egpVal = document.getElementById('egp-total-val');
    const symbol = document.getElementById('currency-symbol');

    // تحديث رمز العملة في الـ Input
    symbol.innerText = isUsd ? 'USD' : 'EGP';

    let amount = parseFloat(amountInput.value);

    if(!isNaN(amount) && amount > 0) {
        hint.style.display = 'block';

        // 1. حساب صافي الدولار (المبلغ اللي هيدخل المحفظة فعلياً)
        let baseAmountInUsd = isUsd ? amount : (amount / EXCHANGE_RATE);
        let netAmount = baseAmountInUsd * (1 - PLATFORM_FEE);
        usdEstimate.innerText = netAmount.toFixed(2);

        // 2. حساب المعادل بالجنيه لو كان بيختار USD
        if(isUsd) {
            egpBox.style.display = 'block';
            let totalInEgp = amount * EXCHANGE_RATE;
            egpVal.innerText = totalInEgp.toLocaleString('en-US', {minimumFractionDigits: 2});
        } else {
            egpBox.style.display = 'none';
        }
    } else {
        hint.style.display = 'none';
    }
}

function toggleFields() {
    const isWallet = document.getElementById('method_wallet').checked;
    const phoneField = document.getElementById('phone_field');
    const phoneNumber = document.getElementById('phone_number');

    if(phoneField) {
        phoneField.style.display = isWallet ? 'block' : 'none';
        phoneNumber.required = isWallet;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const amountEl = document.getElementById('amount');
    if(amountEl) {
        amountEl.addEventListener('input', calculateEstimate);
    }
    calculateEstimate();
    toggleFields();
});
</script>
@endsection
