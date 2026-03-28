@extends('layouts.master')

@section('content')
<div class="container py-5" dir="rtl">

    {{-- عرض رسائل النجاح أو الخطأ --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">
        {{-- Side Actions --}}
        <div class="col-lg-4">
            <div class="glass-card p-4 sticky-top" style="top: 20px;">
                <h5 class="fw-bold mb-4">بطاقة الطلب #{{ $order->id }}</h5>
                <div class="d-flex align-items-center mb-4">
                    <img src="{{ asset('storage/' . ($order->seller->profile_image ?? 'default.png')) }}" class="rounded-circle me-3" width="50" height="50">
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold">{{ $order->seller->name }}</h6>
                        <small class="text-muted">بائع الخدمة</small>
                    </div>
                </div>

                <div class="order-stats bg-light-soft p-3 rounded-4 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>قيمة الخدمة:</span>
                        <span class="fw-bold text-success">{{ number_format($order->price, 2) }} ج.م</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>تاريخ البدء:</span>
                        <span class="fw-bold">{{ $order->created_at->format('Y/m/d') }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-grid gap-2">
                    @if($order->status == 'delivered')
                        {{-- الزرار الآن يوجه لصفحة التقييم بدلاً من المودال --}}
                        <a href="{{ route('orders.complete.view', $order->id) }}" class="btn btn-success rounded-pill py-2 text-white">
                            <i class="fas fa-check-double me-2"></i> تأكيد الاستلام والتقييم
                        </a>
                    @endif

                    <a href="{{ route('messages.chat', $order->seller_id) }}" class="btn btn-white shadow-sm rounded-pill py-2 border">
                        <i class="far fa-comments me-2"></i> مراسلة المستقل
                    </a>
                </div>
            </div>
        </div>

        {{-- Main Details --}}
        <div class="col-lg-8">
            {{-- Status Bar --}}
            <div class="glass-card p-4 mb-4 text-center">
                <div class="status-steps d-flex justify-content-between position-relative">
                    <div class="step {{ in_array($order->status, ['pending', 'processing', 'delivered', 'completed']) ? 'active' : '' }}">
                        <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
                        <p class="small mt-2">طلب جديد</p>
                    </div>
                    <div class="step {{ in_array($order->status, ['processing', 'delivered', 'completed']) ? 'active' : '' }}">
                        <div class="step-icon"><i class="fas fa-cog fa-spin"></i></div>
                        <p class="small mt-2">قيد التنفيذ</p>
                    </div>
                    <div class="step {{ in_array($order->status, ['delivered', 'completed']) ? 'active' : '' }}">
                        <div class="step-icon"><i class="fas fa-truck-loading"></i></div>
                        <p class="small mt-2">تم التسليم</p>
                    </div>
                    <div class="step {{ $order->status == 'completed' ? 'active' : '' }}">
                        <div class="step-icon"><i class="fas fa-star text-warning"></i></div>
                        <p class="small mt-2">مكتمل</p>
                    </div>
                </div>
            </div>

            {{-- Service Delivery Content --}}
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-3">تفاصيل الخدمة المطلوبة</h5>
                <h6 class="text-primary fw-bold">{{ $order->service->title }}</h6>
                <p class="text-muted mt-3">{{ $order->service->description }}</p>
            </div>

            {{-- Work Delivery Area --}}
            @if($order->status == 'delivered' || $order->status == 'completed')
            <div class="glass-card p-4 border-success border-dashed">
                <h5 class="fw-bold mb-3 text-success"><i class="fas fa-gift me-2"></i> الرسالة المسلمة من المستقل</h5>
                <div class="p-3 bg-light rounded-3">
                    <p class="mb-0">{{ $order->delivery_msg ?? 'لا توجد رسالة مرفقة.' }}</p>
                </div>
            </div>
            @endif

            @if($order->status == 'completed')
            <div class="glass-card p-4 mt-4 border-warning shadow-sm">
                <h5 class="fw-bold mb-3 text-warning"><i class="fas fa-star me-2"></i> تقييمك للخدمة</h5>
                <div class="d-flex align-items-center mb-2 gap-1">
                    @for($i=1; $i<=5; $i++)
                        <i class="fas fa-star {{ $i <= $order->rating ? 'text-warning' : 'text-muted opacity-50' }}"></i>
                    @endfor
                </div>
                <p class="fst-italic text-muted mt-2">"{{ $order->comment ?? 'بدون تعليق' }}"</p>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
/* تجميل شريط الحالة */
.status-steps .step { flex: 1; position: relative; z-index: 1; }
.step-icon { width: 40px; height: 40px; background: #e2e8f0; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #64748b; }
.step.active .step-icon { background: #10b981; color: white; box-shadow: 0 0 15px rgba(16, 185, 129, 0.4); }
.status-steps::before { content: ""; position: absolute; top: 20px; left: 0; right: 0; height: 2px; background: #e2e8f0; z-index: 0; }

.border-dashed { border-style: dashed !important; border-width: 2px !important; }
.bg-light-soft { background-color: rgba(248, 249, 250, 0.8); }
.glass-card { background: white; border-radius: 15px; border: 1px solid rgba(0,0,0,0.05); }
</style>
@endsection
