@extends('layouts.master')

@section('content')
<div class="container py-5 text-end" dir="rtl">
    {{-- عرض رسائل النجاح أو الخطأ بعد إرسال الفورم --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 text-center py-3 animate__animated animate__fadeIn">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="text-center mb-5">
        <h2 class="fw-bold text-dark">كيف يمكننا مساعدتك اليوم؟</h2>
        <p class="text-muted">فريقنا متاح دائماً للإجابة على استفساراتك وضمان تجربة مميزة لك.</p>
    </div>

    <div class="row g-4">
        {{-- 1. بطاقات المساعدة السريعة --}}
        <div class="col-md-4">
            <div class="glass-card p-4 text-center h-100 border-0 shadow-sm">
                <div class="icon-circle bg-primary-soft text-primary mx-auto mb-3">
                    <i class="fas fa-question-circle fa-2x"></i>
                </div>
                <h5 class="fw-bold">مركز الأسئلة</h5>
                <p class="text-muted small">إجابات سريعة على الأسئلة الشائعة حول الشحن، السحب، وقوانين المنصة.</p>
                <a href="#faqSection" class="btn btn-outline-primary btn-sm rounded-pill px-4">تصفح الأسئلة</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card p-4 text-center h-100 border-0 shadow-sm">
                <div class="icon-circle bg-success-soft text-success mx-auto mb-3">
                    <i class="fab fa-whatsapp fa-2x"></i>
                </div>
                <h5 class="fw-bold">تواصل فوري</h5>
                <p class="text-muted small">تحدث مع أحد ممثلي خدمة العملاء مباشرة عبر الواتساب (متاح 24/7).</p>
                <a href="https://wa.me/201556332042?text=السلام%20عليكم،%20أريد%20الاستفسار%20عن%20خدمات%20المنصة" target="_blank" class="btn btn-success btn-sm rounded-pill px-4">ابدأ الدردشة</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card p-4 text-center h-100 border-0 shadow-sm">
                <div class="icon-circle bg-info-soft text-info mx-auto mb-3">
                    <i class="fas fa-ticket-alt fa-2x"></i>
                </div>
                <h5 class="fw-bold">فتح تذكرة دعم</h5>
                <p class="text-muted small">إذا كانت لديك مشكلة تقنية، قم بفتح تذكرة وسيقوم القسم المختص بالرد عليك.</p>
                <a href="#ticketForm" class="btn btn-info text-white btn-sm rounded-pill px-4">فتح تذكرة</a>
            </div>
        </div>

        {{-- 2. قسم الأسئلة الشائعة (13 سؤال) --}}
        <div class="col-12 mt-5" id="faqSection">
            <div class="glass-card p-4 shadow-sm border-0">
                <h4 class="fw-bold mb-4 text-primary"><i class="fas fa-list-ol me-2"></i> الأسئلة الشائعة</h4>
                <div class="accordion accordion-flush" id="accordionFAQ">
                    @php
                        $faqs = [
                            ['q' => 'ما هي فكرة المنصة؟', 'a' => 'منصة تجمع بين أصحاب المشاريع والمستقلين لتنفيذ الأعمال عبر الإنترنت بكل أمان وضمان للحقوق.'],
                            ['q' => 'كيف يمكنني توثيق حسابي؟', 'a' => 'من خلال صفحة "توثيق الحساب"، ترفع صورة الهوية والبيانات المطلوبة وتنتظر مراجعة الإدارة.'],
                            ['q' => 'ما هي عمولة المنصة؟', 'a' => 'تقتطع المنصة عمولة بسيطة من قيمة المشروع لضمان استمرارية الخدمة وتأمين المعاملات.'],
                            ['q' => 'كيف أضمن حقي كمستقل؟', 'a' => 'بمجرد قبول العرض، يتم حجز مبلغ المشروع في المنصة ولا يُسلم للمستقل إلا بعد إتمام العمل.'],
                            ['q' => 'كيف أضمن حقي كصاحب مشروع؟', 'a' => 'المبلغ يظل في أمان لدينا ولا يتم تحويله للمستقل إلا بعد استلامك للمشروع وموافقتك عليه.'],
                            ['q' => 'ما هي وسائل الدفع المتاحة؟', 'a' => 'ندعم البطاقات البنكية، والمحافظ الإلكترونية (مثل فودافون كاش) عبر بوابة Paymob.'],
                            ['q' => 'كم تستغرق عملية سحب الأرباح؟', 'a' => 'تتم مراجعة طلبات السحب خلال 24 إلى 48 ساعة عمل.'],
                            ['q' => 'هل يمكنني تغيير تخصصي بعد التسجيل؟', 'a' => 'نعم، يمكنك تعديل بياناتك الشخصية وتخصصك من خلال إعدادات الملف الشخصي.'],
                            ['q' => 'ماذا أفعل إذا حدث خلاف مع الطرف الآخر؟', 'a' => 'يمكنك فتح نزاع، وسيقوم فريق الدعم بالتدخل كمحكم بناءً على المراسلات داخل المنصة.'],
                            ['q' => 'هل يمكنني التواصل مع العميل خارج المنصة؟', 'a' => 'يمنع التواصل الخارجي لضمان حقوقك، حيث أن المنصة لا تحمي المعاملات التي تتم خارجها.'],
                            ['q' => 'ما هو الحد الأدنى للسحب؟', 'a' => 'الحد الأدنى للسحب يختلف حسب وسيلة السحب المتبعة، ويظهر لك في صفحة المحفظة.'],
                            ['q' => 'كيف يمكنني زيادة فرصي في الحصول على مشاريع؟', 'a' => 'عن طريق إكمال ملفك الشخصي، توثيق الحساب، وإضافة أعمال سابقة قوية في معرض أعمالك.'],
                            ['q' => 'نسيت كلمة المرور، ماذا أفعل؟', 'a' => 'استخدم رابط "نسيت كلمة المرور" في صفحة تسجيل الدخول وسيصلك رابط استعادة عبر البريد.'],
                        ];
                    @endphp

                    @foreach($faqs as $index => $faq)
                    <div class="accordion-item border-0 mb-2 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                                {{ $faq['q'] }}
                            </button>
                        </h2>
                        <div id="collapse{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body text-secondary">
                                {{ $faq['a'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 3. فورم فتح التذكرة --}}
        <div class="col-lg-8 mx-auto mt-5" id="ticketForm">
            <div class="glass-card p-5 border-0 shadow-lg">
                <h4 class="fw-bold mb-4 d-flex align-items-center">
                    <i class="fas fa-pen-nib me-2 text-primary"></i> أرسل لنا تفاصيل مشكلتك
                </h4>
                <form action="{{ route('contact.send') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3 text-end">
                            <label class="form-label small fw-bold">نوع المشكلة</label>
                            <select name="type" class="form-select border-0 bg-light-soft py-2 shadow-sm rounded-3" required>
                                <option value="شحن/سحب">مشكلة في الشحن/السحب</option>
                                <option value="تقنية">مشكلة تقنية في الموقع</option>
                                <option value="تبليغ">تبليغ عن مستخدم/مشروع</option>
                                <option value="اقتراح">اقتراح لتطوير المنصة</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3 text-end">
                            <label class="form-label small fw-bold">عنوان الموضوع</label>
                            <input type="text" name="subject" class="form-control border-0 bg-light-soft py-2 shadow-sm rounded-3" placeholder="مثلاً: تعذر سحب الرصيد" required>
                        </div>
                        <div class="col-12 mb-3 text-end">
                            <label class="form-label small fw-bold">وصف المشكلة بالتفصيل</label>
                            <textarea name="message" class="form-control border-0 bg-light-soft shadow-sm rounded-3" rows="5" placeholder="اكتب كل التفاصيل التي قد تساعدنا..." required></textarea>
                        </div>
                        <div class="col-12 text-start">
                            <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow">إرسال التذكرة <i class="fas fa-paper-plane ms-1"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
    .bg-light-soft { background: #f8fafc; }

    .icon-circle {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .glass-card {
        background: #fff;
        border-radius: 25px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }

    .accordion-button:not(.collapsed) {
        background-color: #f0f7ff;
        color: #0d6efd;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }

    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        background: #fff;
    }

    .rounded-4 { border-radius: 1rem !important; }

    /* أنيميشن بسيط لظهور الرسالة */
    .animate__fadeIn {
        animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
