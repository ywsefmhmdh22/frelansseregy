@extends('layouts.master')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    body { background: #f8fafc; font-family: 'Cairo', sans-serif; }
    .main-container { background: #ffffff; border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); color: #334155; }
    .text-gold { color: #b45309; }
    .bg-light-soft { background: #f1f5f9; }
    .premium-glass-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }

    /* نظام الخطوات */
    .status-steps-container::before { content: ""; position: absolute; top: 20px; left: 0; right: 0; height: 2px; background: #e2e8f0; z-index: 0; }
    .step-item { position: relative; z-index: 1; flex: 1; }
    .step-circle { width: 40px; height: 40px; background: #fff; border: 2px solid #e2e8f0; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #94a3b8; }
    .step-item.active .step-circle { background: #fbbf24; color: #fff; border-color: #fbbf24; box-shadow: 0 0 15px rgba(251, 191, 36, 0.3); }
    .step-item.active .small-text { color: #b45309; font-weight: bold; }
    .small-text { font-size: 0.75rem; color: #64748b; margin-top: 8px; }

    /* الأزرار */
    .btn-gold-action { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #ffffff !important; border: none; transition: all 0.3s ease; text-align: center; }
    .btn-gold-action:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(245, 158, 11, 0.4); }
    .btn-outline-custom { border: 1px solid #e2e8f0; color: #64748b; }

    /* صندوق التسليم الأخضر */
    .delivery-box { background: #f0fdf4; border: 2px dashed #22c55e; border-radius: 20px; padding: 25px; position: relative; }
</style>

<div class="container py-5" dir="rtl">
    <div class="main-container p-4 p-md-5">

        {{-- رسالة نجاح العملية للفريلانسر بعد الضغط --}}
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 animate__animated animate__fadeInDown">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="row g-4">
            {{-- الجانب الأيمن --}}
            <div class="col-lg-4">
                <div class="premium-glass-card p-4 sticky-top animate__animated animate__fadeInRight" style="top: 20px;">
                    <h5 class="fw-bold text-dark mb-4 text-center">إدارة الطلب #{{ $order->id }}</h5>

                    <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-light-soft">
                        @php
                            $isSeller = (auth()->id() == $order->seller_id);
                            $otherUser = $isSeller ? $order->buyer : $order->seller;
                            $userName = $otherUser->name ?? 'مستخدم';
                            $userImage = ($otherUser && $otherUser->profile_image) ? asset('storage/' . $otherUser->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($userName).'&background=fbbf24&color=fff';
                        @endphp
                        <img src="{{ $userImage }}" class="rounded-circle border border-2 border-white shadow-sm" width="60" height="60">
                        <div class="ms-3 text-end mr-3">
                            <h6 class="mb-0 fw-bold text-dark">{{ $userName }}</h6>
                            <small class="text-muted">{{ $isSeller ? 'العميل (المشتري)' : 'المستقل (البائع)' }}</small>
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        {{-- حالة الفريلانسر: بيظهرله زرار يفتح المودال عشان يسلم --}}
                        @if(auth()->id() == $order->seller_id && in_array($order->status, ['pending', 'processing']))
                            <button type="button" class="btn btn-gold-action rounded-pill py-3 fw-bold" data-bs-toggle="modal" data-bs-target="#deliverOrderModal">
                                <i class="fas fa-paper-plane me-2"></i> تسليم الخدمة الآن
                            </button>
                        @endif

                        {{-- حالة العميل: لما الفريلانسر يسلم (delivered) يظهر الزرار اللي بيوديه لصفحة الكمبليت --}}
                        @if(auth()->id() == $order->buyer_id && $order->status == 'delivered')
                            <a href="{{ route('orders.complete.view', $order->id) }}" class="btn btn-success rounded-pill py-3 fw-bold text-white shadow animate__animated animate__pulse animate__infinite">
                                <i class="fas fa-check-double me-2"></i> مراجعة واستلام الطلب
                            </a>
                        @endif

                        <a href="{{ route('messages.chat', $otherUser->id ?? 0) }}" class="btn btn-outline-custom rounded-pill py-2 text-center">
                            <i class="far fa-comments me-2"></i> مراسلة الطرف الآخر
                        </a>
                    </div>
                </div>
            </div>

            {{-- الجانب الأيسر --}}
            <div class="col-lg-8">

                {{-- شريط تتبع الحالة --}}
                <div class="premium-glass-card p-4 mb-4 text-center">
                    <div class="status-steps-container d-flex justify-content-between position-relative">
                        @php
                            $steps = [
                                'processing' => ['icon' => 'fa-cog', 'label' => 'قيد التنفيذ'],
                                'delivered' => ['icon' => 'fa-truck-loading', 'label' => 'تم التسليم'],
                                'completed' => ['icon' => 'fa-crown', 'label' => 'مكتمل']
                            ];
                            $currentStatusIndex = array_search($order->status, array_keys($steps));
                        @endphp
                        @foreach($steps as $key => $step)
                            <div class="step-item {{ array_search($key, array_keys($steps)) <= $currentStatusIndex ? 'active' : '' }}">
                                <div class="step-circle"><i class="fas {{ $step['icon'] }}"></i></div>
                                <p class="small-text mt-2 mb-0">{{ $step['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- الصندوق الذي يظهر للعميل عند التسليم --}}
                @if($order->status == 'delivered')
                    <div class="delivery-box mb-4 animate__animated animate__bounceIn">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-info-circle text-success fs-4 ml-2 me-2"></i>
                            <h5 class="fw-bold text-success mb-0">المستقل أرسل طلب تسليم الخدمة</h5>
                        </div>
                        <div class="bg-white p-3 rounded-4 mb-3 border">
                            <p class="mb-0 text-muted">رسالة المستقل:</p>
                            <p class="fw-bold text-dark">{{ $order->delivery_msg }}</p>
                        </div>

                        @if(auth()->id() == $order->buyer_id)
                            <div class="text-center">
                                <p class="small mb-2">بضغطك على الزر أدناه سيتم توجيهك لصفحة إنهاء الطلب والتقييم</p>
                                <a href="{{ route('orders.complete.view', $order->id) }}" class="btn btn-success rounded-pill px-5 fw-bold">
                                    الانتقال لإنهاء واستلام الطلب
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="premium-glass-card p-4">
                    <h5 class="fw-bold text-dark mb-3">تفاصيل الخدمة</h5>
                    <div class="p-3 bg-light-soft rounded-4">
                        <h6 class="text-gold fw-bold mb-2">{{ $order->service->title }}</h6>
                        <p class="text-muted mb-0" style="line-height: 1.8;">{{ $order->service->description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- مودال التسليم للفريلانسر --}}
<div class="modal fade" id="deliverOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-5 border-0 shadow">
            <div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4 text-end">
                <h4 class="fw-bold text-center mb-4">إرسال طلب تسليم</h4>
                <form action="{{ route('orders.deliver', $order->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">رسالة للعميل (أو رابط العمل):</label>
                        <textarea name="delivery_msg" class="form-control rounded-4 p-3" rows="4" placeholder="اكتب هنا تفاصيل ما قمت به للعميل..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-gold-action w-100 py-3 rounded-pill fw-bold fs-5 shadow-sm">إرسال الطلب الآن</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
