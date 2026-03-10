@extends('layouts.master')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient bg-primary text-white py-3 rounded-top-4">
                    <h4 class="mb-0 text-center fw-bold">
                        <i class="fas fa-wallet me-2"></i> شحن رصيد المحفظة
                    </h4>
                </div>

                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    <form action="{{ route('pay.initiate') }}" method="POST" id="payment-form">
                        @csrf

                        <div class="mb-4">
                            <label for="amount" class="form-label fw-bold text-dark">المبلغ المراد شحنه</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 text-success fw-bold">EGP</span>
                                <input type="number" name="amount" id="amount"
                                       class="form-control border-start-0 ps-0 text-center"
                                       placeholder="مثلاً: 100"
                                       min="5" required>
                            </div>
                            <div class="form-text mt-2 text-muted">
                                <i class="fas fa-info-circle me-1"></i> أقل مبلغ للشحن هو 5 جنيهات.
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                              <div class="mb-4">
    <label class="form-label fw-bold text-dark">اختر وسيلة الدفع</label>
    <div class="row g-3">
        <div class="col-6">
            <input type="radio" name="payment_method" id="method_card" value="card" checked onchange="toggleFields()">
            <label for="method_card">بطاقة بنكية</label>
        </div>
        <div class="col-6">
            <input type="radio" name="payment_method" id="method_wallet" value="wallet" onchange="toggleFields()">
            <label for="method_wallet">محفظة كاش</label>
        </div>
    </div>
</div>

                        <div id="phone_field" class="mb-4 animate__animated animate__fadeIn" style="display: none;">
                            <label for="phone_number" class="form-label fw-bold text-dark">رقم المحفظة الإلكترونية</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone-alt"></i></span>
                                <input type="text" name="phone_number" id="phone_number"
                                       class="form-control"
                                       placeholder="مثلاً: 01012345678">
                            </div>
                            <div class="form-text text-danger">تأكد من إدخال الرقم المسجل عليه المحفظة.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg py-3 fw-bold rounded-3 shadow-sm border-0 transition-transform">
                                <i class="fas fa-lock me-2"></i> تأكيد ودفع الآن
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer text-center bg-light py-3 rounded-bottom-4 border-0">
                    <div class="d-flex justify-content-center gap-3 opacity-50 mb-2">
                        <i class="fab fa-cc-visa fa-2x"></i>
                        <i class="fab fa-cc-mastercard fa-2x"></i>
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                    <p class="small mb-0 text-muted">الدفع مؤمن بواسطة <strong>Paymob</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* شوية لمسات جمالية */
    .btn-check:checked + .btn-outline-primary {
        background-color: #0d6efd;
        color: white;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }
    .transition-transform:active {
        transform: scale(0.98);
    }
    .card {
        transition: all 0.3s ease;
    }
    .form-control:focus {
        box-shadow: none;
        border-color: #0d6efd;
    }
</style>

<script>
function toggleFields() {
    const isWallet = document.getElementById('method_wallet').checked;
    const phoneField = document.getElementById('phone_field');
    const phoneInput = document.getElementById('phone_number');

    if (isWallet) {
        phoneField.style.display = 'block';
        phoneInput.setAttribute('required', 'required');
    } else {
        phoneField.style.display = 'none';
        phoneInput.removeAttribute('required');
    }
}
</script>
@stop
