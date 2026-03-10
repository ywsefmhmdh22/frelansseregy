@extends('layouts.master')

@section('content')
<div class="container py-5 animate__animated animate__fadeIn">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2 class="fw-bold text-dark mb-4 text-end" dir="rtl">تأكيد عملية الشراء</h2>

            <div class="row g-4" dir="rtl">
                {{-- تفاصيل الفاتورة --}}
                <div class="col-md-4 order-md-2">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                        <h5 class="fw-bold mb-4 border-bottom pb-2">ملخص الفاتورة</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">سعر الخدمة:</span>
                            <span class="fw-bold">{{ number_format($service->price, 2) }} ج.م</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">رسوم المنصة:</span>
                            <span class="text-success fw-bold">0.00 ج.م</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fs-5 fw-bold">الإجمالي:</span>
                            <span class="fs-5 fw-bold text-primary">{{ number_format($service->price, 2) }} ج.م</span>
                        </div>

                        <form action="{{ route('orders.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                                <i class="fas fa-lock me-2"></i> تأكيد الدفع والطلب
                            </button>
                        </form>
                        <p class="text-center text-muted small mt-3">
                            <i class="fas fa-shield-alt"></i> دفع آمن بنسبة 100%
                        </p>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-light">
                        <div class="d-flex align-items-center">
                            <div class="ms-3">
                                <h6 class="mb-1 fw-bold">رصيدك الحالي:</h6>
                                <span class="fs-5 text-success fw-bold">{{ number_format(Auth::user()->wallet->balance, 2) }} ج.م</span>
                            </div>
                            <i class="fas fa-wallet fa-2x text-primary opacity-25 ms-auto"></i>
                        </div>
                    </div>
                </div>

                {{-- تفاصيل الخدمة --}}
                <div class="col-md-8 order-md-1">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="row g-0">
                            <div class="col-md-5">
                                <img src="{{ asset($service->image) }}" class="img-fluid h-100 object-fit-cover" alt="{{ $service->title }}">
                            </div>
                            <div class="col-md-7">
                                <div class="card-body p-4 text-end">
                                    <div class="mb-2">
                                        <span class="badge bg-soft-primary text-primary rounded-pill px-3">مراجعة الخدمة</span>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-3">{{ $service->title }}</h4>
                                    <p class="text-muted mb-4">{{ Str::limit($service->description, 200) }}</p>

                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <img src="{{ $service->user->profile_image ? asset('storage/' . $service->user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($service->user->name) }}"
                                             class="rounded-circle ms-3" width="40" height="40">
                                        <div class="text-end">
                                            <p class="mb-0 fw-bold">{{ $service->user->name }}</p>
                                            <small class="text-muted">بائع موثوق</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 text-end">
                        <h6 class="fw-bold"><i class="fas fa-info-circle text-primary ms-2"></i> ملاحظات قبل الشراء:</h6>
                        <ul class="text-muted small">
                            <li>سيتم حجز المبلغ في الموقع ولن يصل للبائع إلا بعد تأكيد استلامك للخدمة.</li>
                            <li>تواصل مع البائع عبر الرسائل لمتابعة تنفيذ طلبك.</li>
                            <li>يمكنك إلغاء الطلب في حال تأخر البائع عن موعد التسليم المتفق عليه.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .object-fit-cover { object-fit: cover; }
</style>
@endsection
