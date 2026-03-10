@extends('layouts.master')

@section('content')
<div class="container py-5 text-end" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 text-center">طلب سحب أرباح 💸</h4>

                    <div class="alert alert-info small border-0 rounded-3">
                        رصيدك القابل للسحب حالياً هو:
                        <strong class="text-success">{{ Auth::user()->wallet->balance }}</strong>
                    </div>

                    <form action="{{ route('wallet.process_withdraw') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">المبلغ المراد سحبه</label>
                            <input type="number" name="amount" class="form-control rounded-pill" max="{{ Auth::user()->wallet->balance }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">وسيلة السحب</label>
                            <select name="method" class="form-select rounded-pill" required>
                                <option value="vodafone_cash">فودافون كاش</option>
                                <option value="instapay">انستا باي (InstaPay)</option>
                                <option value="paypal">بايبال (PayPal)</option>
                                <option value="bank">تحويل بنكي</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">بيانات السحب (الرقم أو الإيميل)</label>
                            <input type="text" name="account_details" class="form-control rounded-pill" placeholder="مثلاً: 010xxxxxxxx" required>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 rounded-pill py-2 fw-bold shadow-sm">
                            إرسال طلب السحب للمراجعة
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
