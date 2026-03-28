@extends('layouts.master')

@section('content')
<div class="container py-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="glass-card p-5 shadow-lg border-0 rounded-4">
                <div class="text-center mb-4">
                    <div class="icon-circle bg-success-light text-success mb-3" style="width: 80px; height: 80px; font-size: 40px; margin: 0 auto; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #e6f7f1;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <h3 class="fw-bold">استلام الطلب وتقييم الخدمة</h3>
                    <p class="text-muted">بمجرد تأكيد الاستلام، سيتم نقل الأرباح مباشرة لمحفظة المستقل</p>
                </div>

                <hr class="my-4 opacity-25">

                <form action="{{ route('orders.complete.post') }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    {{-- ملخص الخدمة --}}
                    <div class="bg-light p-3 rounded-3 mb-4 d-flex align-items-center">
                        <div class="ms-3">
                            <h6 class="mb-1 fw-bold">{{ $order->service->title }}</h6>
                            <span class="text-success fw-bold">{{ number_format($order->price, 2) }} ج.م</span>
                        </div>
                    </div>

                    {{-- النجوم --}}
                    <div class="mb-4 text-center">
                        <label class="fw-bold d-block mb-3">ما هو تقييمك لجودة العمل؟</label>
                        <div class="d-flex justify-content-center gap-2">
                            @for($i=1; $i<=5; $i++)
                                <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" class="btn-check" required>
                                <label class="btn btn-outline-warning rounded-circle star-btn" for="star{{ $i }}">
                                    {{ $i }} <i class="fas fa-star"></i>
                                </label>
                            @endfor
                        </div>
                    </div>

                    {{-- التعليق --}}
                    <div class="mb-4">
                        <label class="fw-bold mb-2">أضف تعليقك (سيظهر في الملف الشخصي للمستقل)</label>
                        <textarea name="comment" class="form-control rounded-4 p-3" rows="4" placeholder="كيف كانت جودة التواصل، الالتزام بالوقت، واحترافية العمل؟"></textarea>
                    </div>

                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-success btn-lg rounded-pill fw-bold py-3 shadow-sm">
                            <i class="fas fa-check-circle me-2"></i> تأكيد الاستلام النهائي
                        </button>
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-link text-muted">العودة لتفاصيل الطلب</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .star-btn { width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; font-weight: bold; transition: 0.3s; }
    .btn-check:checked + .star-btn { background-color: #ffc107; color: white; border-color: #ffc107; transform: translateY(-5px); box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4); }
    .glass-card { background: white; border: 1px solid rgba(0,0,0,0.05); }
</style>
@endsection
