@extends('layouts.master')

@section('content')

<div class="projects-wrapper py-5">
    <div class="container">
        <div class="projects-header text-center mb-5">
            <div class="header-badge mb-3">استكشف الفرص</div>
            <h1 class="display-5 fw-bold text-dark">المشاريع <span class="text-gradient">المتاحة</span></h1>
            <p class="text-muted mx-auto" style="max-width: 600px;">انضم إلى آلاف المستقلين المبدعين وابدأ رحلة نجاحك اليوم من خلال التقديم على أفضل المشاريع الموثقة والآمنة ماليًا.</p>
        </div>

        <div class="projects-grid">
            @foreach($allData as $item)
            {{-- فحص حالة المشروع لتطبيق التنسيق المناسب --}}
            @php
                $isCompleted = ($item->status === 'completed');
                $offersCount = $item->proposals_count ?? ($item->proposals ? $item->proposals->count() : 0);

                // جلب السعر ومعدل الصرف القادم من السيرفر (لو مش متاح بنحط fallback ذكي)
                $egpPrice = $item->price;
                $conversionRate = $rate ?? 50.0;
                $usdPrice = round($egpPrice / $conversionRate, 2);
            @endphp

            <article class="project-card-v2 {{ $isCompleted ? 'card-completed-premium' : '' }}">
                {{-- رابط الكارت بالكامل مدعم للـ SEO --}}
                <a href="{{ route('projects.show', $item->id) }}" class="stretched-link" aria-label="عرض تفاصيل مشروع: {{ $item->title }}"></a>

                {{-- ختم المنصة العالمي للمشاريع المكتملة --}}
                @if($isCompleted)
                <div class="global-seal">
                    <div class="seal-inner">
                        <i class="fas fa-check-double"></i>
                        <span>COMPLETED</span>
                    </div>
                </div>
                @endif

                <div class="card-top-section">
                    <div class="image-container">
                        <img src="{{ $item->full_image_url }}" alt="{{ $item->title }}" loading="lazy">
                        <div class="category-tag">{{ $item->type ?? 'عام' }}</div>
                    </div>
                </div>

                <div class="card-body-section p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                        <h3 class="project-title-v2">{{ $item->title }}</h3>
                        <div class="status-indicator {{ $isCompleted ? 'status-completed' : '' }}">
                            <span class="dot"></span> {{ $isCompleted ? 'مكتمل' : 'مفتوح' }}
                        </div>
                    </div>

                    <p class="project-description-v2">
                        {{ Str::limit($item->description, 110) }}
                    </p>

                    <div class="skills-tags mb-4">
                        <span class="skill-pill">تطوير</span>
                        <span class="skill-pill">إبداع</span>
                        <span class="skill-pill">+5</span>
                    </div>

                    <div class="card-footer-v2 pt-3">
                        <div class="footer-left-info d-flex justify-content-between align-items-end w-100">

                            {{-- عرض السعر المزدوج الفخم (جنيه وموازيه بالدولار) --}}
                            <div class="budget-info">
                                <span class="label">الميزانية التقديرية</span>
                                <div class="price-dual-wrapper d-flex flex-column text-end">
                                    <div class="amount-wrapper egp-main">
                                        <span class="amount-value fw-extrabold">{{ number_format($egpPrice) }}</span>
                                        <span class="currency-symbol ms-1">ج.م</span>
                                    </div>
                                    <div class="amount-wrapper usd-secondary">
                                        <span class="usd-value"><i class="fas fa-calculator extra-small opacity-50 me-1"></i> ما يوازي: {{ number_format($usdPrice, 2) }} $</span>
                                    </div>
                                </div>
                            </div>

                            {{-- معلومات عدد العروض المضافة --}}
                            <div class="offers-stats-info px-2">
                                <span class="label text-start">العروض</span>
                                <div class="stats-wrapper justify-content-end">
                                    <i class="fas fa-paper-plane text-success me-1"></i>
                                    <span class="stats-value">{{ $offersCount }}</span>
                                    <span class="stats-text ms-1">عروض</span>
                                </div>
                            </div>

                            <div class="view-details-circle flex-shrink-0 shadow-sm">
                                <i class="fas fa-arrow-left"></i>
                            </div>

                        </div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&display=swap');

    :root {
        --primary-color: #10b981;
        --secondary-color: #3b82f6;
        --dark-navy: #0f172a;
        --soft-gray: #f8fafc;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --premium-gold: linear-gradient(135deg, #bf953f, #fcf6ba, #b38728, #fbf5b7, #aa771c);
    }

    body {
        background-color: #f4f6f9;
        font-family: 'Cairo', sans-serif;
    }

    .text-gradient {
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-badge {
        display: inline-block;
        padding: 6px 16px;
        background: rgba(16, 185, 129, 0.1);
        color: var(--primary-color);
        border-radius: 50px;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    /* Grid Layout المستجيب بذكاء */
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(clamp(290px, 30vw, 360px), 1fr));
        gap: 25px;
        direction: rtl;
    }

    /* كارت النسخة الثانية المحسن */
    .project-card-v2 {
        background: #ffffff;
        border-radius: 24px;
        overflow: hidden;
        position: relative;
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: flex;
        flex-direction: column;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    }

    /* كارت البريميوم المكتمل الفخم */
    .card-completed-premium {
        background: #0b1329;
        border: 1.5px solid #bf953f;
        box-shadow: 0 15px 35px rgba(11, 19, 41, 0.2);
    }

    .card-completed-premium .project-title-v2 {
        color: #fcf6ba !important;
    }

    .card-completed-premium .project-description-v2 {
        color: #94a3b8 !important;
    }

    .card-completed-premium .amount-value,
    .card-completed-premium .stats-value {
        color: #ffffff !important;
    }

    .card-completed-premium .price-tag-modern .currency {
        color: #bf953f !important;
    }

    .global-seal {
        position: absolute;
        top: 18px;
        left: -38px;
        background: var(--premium-gold);
        transform: rotate(-45deg);
        width: 145px;
        text-align: center;
        z-index: 10;
        box-shadow: 0 4px 10px rgba(0,0,0,0.4);
        padding: 4px 0;
    }

    .seal-inner span {
        font-size: 9px;
        font-weight: 900;
        color: #000;
        letter-spacing: 1px;
    }

    .project-card-v2:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .card-top-section .image-container {
        position: relative;
        height: 180px;
        overflow: hidden;
    }

    .image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .project-card-v2:hover .image-container img {
        transform: scale(1.08);
    }

    .category-tag {
        position: absolute;
        bottom: 12px;
        right: 12px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(5px);
        padding: 4px 12px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
        color: var(--dark-navy);
        z-index: 2;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .project-title-v2 {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--dark-navy);
        margin: 0;
        line-height: 1.4;
    }

    .status-indicator {
        font-size: 11px;
        font-weight: 800;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        gap: 5px;
        background: #ecfdf5;
        padding: 4px 10px;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .status-completed {
        background: rgba(191, 149, 63, 0.15) !important;
        color: #fcf6ba !important;
    }

    .status-indicator .dot {
        width: 6px;
        height: 6px;
        background-color: currentColor;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        70% { transform: scale(1); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .project-description-v2 {
        color: var(--text-muted);
        font-size: 13.5px;
        line-height: 1.6;
        margin-top: 12px;
        height: 44px;
        overflow: hidden;
    }

    .skill-pill {
        display: inline-block;
        background: #f1f5f9;
        color: #475569;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        margin-left: 5px;
    }

    /* الفوتر الجديد للكارت */
    .card-footer-v2 {
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    .card-completed-premium .card-footer-v2 {
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .label {
        display: block;
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 6px;
        font-weight: 700;
    }

    .offers-stats-info .stats-wrapper {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stats-value {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--dark-navy);
    }

    .stats-text {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
    }

    .price-dual-wrapper {
        gap: 2px;
    }

    .amount-wrapper.egp-main {
        display: flex;
        align-items: baseline;
        color: var(--dark-navy);
    }

    .currency-symbol {
        font-size: 12px;
        font-weight: 800;
        color: var(--primary-color);
    }

    .card-completed-premium .currency-symbol {
        color: #bf953f;
    }

    .amount-value {
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: -0.5px;
    }

    .amount-wrapper.usd-secondary {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
    }

    .view-details-circle {
        width: 40px;
        height: 40px;
        background: var(--soft-gray);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .project-card-v2:hover .view-details-circle {
        background: var(--primary-color);
        color: #ffffff;
        transform: rotate(-45deg);
    }

    .card-completed-premium .view-details-circle {
        background: rgba(255,255,255,0.05);
        color: #bf953f;
    }

    .card-completed-premium:hover .view-details-circle {
        background: #bf953f;
        color: #000000;
    }

    /* التجاوب السلس مع الموبايل */
    @media (max-width: 768px) {
        .projects-grid {
            grid-template-columns: 1fr;
        }
        .checkout-wrapper {
            padding: 1rem;
        }
    }
</style>
@endsection
