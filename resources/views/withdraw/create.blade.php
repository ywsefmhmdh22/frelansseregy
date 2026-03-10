@extends('layouts.master')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-dark text-end">طلب سحب أرباح</h5>
                </div>

                <form action="{{ route('withdraw.request') }}" method="POST">
                    @csrf
                    <div class="card-body text-end" dir="rtl">

                        {{-- عرض رسائل الخطأ إن وجدت --}}
                        @if(session('error'))
                            <div class="alert alert-danger py-2 small">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                            </div>
                        @endif

                        {{-- عرض الرصيد --}}
                        <div class="mb-4 text-center bg-light p-4 rounded-4">
                            <small class="text-muted d-block mb-1">الرصيد القابل للسحب</small>
                            <h3 class="text-success fw-bold mb-0">{{ number_format(Auth::user()->wallet->balance ?? 0, 2) }} ج.م</h3>
                        </div>

                        {{-- عملة السحب --}}
                        <div class="mb-3">
                            <label class="small fw-bold mb-2">عملة السحب</label>
                            <select name="currency" class="form-select rounded-3 shadow-none" required id="currencySelect">
                                <option value="EGP">الجنيه المصري (EGP)</option>
                                <option value="USD">الدولار الأمريكي (USD)</option>
                            </select>
                        </div>

                        {{-- طريقة السحب --}}
                        <div class="mb-3">
                            <label class="small fw-bold mb-2">طريقة السحب</label>
                            <select name="method" class="form-select rounded-3 shadow-none" required>
                                <option value="instapay">InstaPay (فوري)</option>
                                <option value="vodafone_cash">محفظة إلكترونية (فودافون كاش..الخ)</option>
                                <option value="bank">حساب بنكي</option>
                                <option value="paypal">PayPal</option>
                            </select>
                        </div>

                        {{-- المبلغ --}}
                        <div class="mb-3">
                            <label class="small fw-bold mb-2">المبلغ المطلوب سحبه</label>
                            <input type="number" name="amount" class="form-control rounded-3 shadow-none"
                                   max="{{ Auth::user()->wallet->balance }}" placeholder="0.00" step="0.01" required>
                        </div>

                        {{-- بيانات التحويل --}}
                        <div class="mb-3">
                            <label class="small fw-bold mb-2">بيانات التحويل</label>
                            <textarea name="details" class="form-control rounded-3 shadow-none" rows="4"
                                      placeholder="اكتب رقم المحفظة، عنوان InstaPay، أو تفاصيل الحساب البنكي هنا..." required></textarea>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 p-3">
                        <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-bold shadow-sm">تأكيد طلب السحب</button>
                        <a href="{{ route('home') }}" class="btn btn-link w-100 text-secondary text-decoration-none mt-2 small">إلغاء والعودة للرئيسية</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
