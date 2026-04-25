@extends('layouts.master')

@section('content')

<div class="projects-wrapper py-5">
    <div class="container">
        <div class="projects-header text-center mb-5">
            <div class="header-badge mb-3">استكشف الفرص</div>
            <h1 class="display-5 fw-bold text-dark">المشاريع <span class="text-gradient">المتاحة</span></h1>
            <p class="text-muted mx-auto" style="max-width: 600px;">انضم إلى آلاف المستقلين المبدعين وابدأ رحلة نجاحك اليوم من خلال التقديم على أفضل المشاريع الموثقة.</p>
        </div>

        <div class="projects-grid">
            @foreach($allData as $item)
            {{-- فحص حالة المشروع لتطبيق التنسيق المناسب --}}
            @php
                $isCompleted = ($item->status === 'completed');

                /* التعديل الرئيسي هنا:
                   استخدام proposals_count (في حال استخدمت withCount في الكنترولر)
                   أو proposals->count() كبديل
                */
                $offersCount = $item->proposals_count ?? ($item->proposals ? $item->proposals->count() : 0);
            @endphp

            <div class="project-card-v2 {{ $isCompleted ? 'card-completed-premium' : '' }}">
                {{-- رابط الكارت بالكامل --}}
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
                        {{-- استخدام الـ Attribute المحسن من الموديل --}}
                        <img src="{{ $item->full_image_url }}" alt="{{ $item->title }}">
                        <div class="category-tag">{{ $item->type ?? 'عام' }}</div>
                    </div>
                </div>

                <div class="card-body-section p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
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

                    <div class="card-footer-v2">
                        <div class="footer-left-info d-flex gap-4">
                            {{-- معلومات الميزانية --}}
                            <div class="budget-info">
                                <span class="label">الميزانية</span>
                                <div class="amount-wrapper">
                                    <span class="currency-symbol">{{ $item->currency }}</span>
                                    <span class="amount-value">{{ number_format($item->price) }}</span>
                                </div>
                            </div>

                            {{-- معلومات عدد العروض المضافة --}}
                            <div class="offers-stats-info">
                                <span class="label">العروض</span>
                                <div class="stats-wrapper">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    <span class="stats-value">{{ $offersCount }}</span>
                                    <span class="stats-text">عروض</span>
                                </div>
                            </div>
                        </div>

                        <div class="view-details-circle">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap');

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
        background-color: #f3f4f6;
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

    /* Grid Layout */
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 30px;
        direction: rtl;
    }

    /* Card Design V2 */
    .project-card-v2 {
        background: #ffffff;
        border-radius: 24px;
        overflow: hidden;
        position: relative;
        border: 1px solid rgba(0,0,0,0.03);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .card-completed-premium {
        background: #0a0f1c;
        border: 1.5px solid #bf953f;
    }

    .card-completed-premium .project-title-v2 {
        color: #fcf6ba !important;
    }

    .card-completed-premium .project-description-v2,
    .card-completed-premium .amount-value,
    .card-completed-premium .stats-value {
        color: #ffffff !important;
    }

    .global-seal {
        position: absolute;
        top: 20px;
        left: -35px;
        background: var(--premium-gold);
        transform: rotate(-45deg);
        width: 150px;
        text-align: center;
        z-index: 10;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        padding: 5px 0;
    }

    .seal-inner span {
        font-size: 10px;
        font-weight: 900;
        color: #000;
        letter-spacing: 1px;
    }

    .project-card-v2:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
    }

    .card-top-section .image-container {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .project-card-v2:hover .image-container img {
        transform: scale(1.1);
    }

    .category-tag {
        position: absolute;
        bottom: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        padding: 5px 15px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        color: var(--dark-navy);
        z-index: 2;
    }

    .project-title-v2 {
        font-size: 20px;
        font-weight: 800;
        color: var(--dark-navy);
        margin: 0;
        line-height: 1.4;
    }

    .status-indicator {
        font-size: 12px;
        font-weight: 700;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        gap: 6px;
        background: #ecfdf5;
        padding: 4px 10px;
        border-radius: 8px;
    }

    .status-completed {
        background: rgba(191, 149, 63, 0.2) !important;
        color: #fcf6ba !important;
    }

    .status-indicator .dot {
        width: 8px;
        height: 8px;
        background-color: currentColor;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .project-description-v2 {
        color: var(--text-muted);
        font-size: 14px;
        line-height: 1.6;
        margin-top: 15px;
        height: 45px;
        overflow: hidden;
    }

    .skill-pill {
        display: inline-block;
        background: #f1f5f9;
        color: #64748b;
        padding: 3px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 6px;
    }

    /* Footer Section */
    .card-footer-v2 {
        border-top: 1px solid #f1f5f9;
        padding-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    .label {
        display: block;
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 4px;
        font-weight: 600;
    }

    /* Offers Info Style */
    .offers-stats-info .stats-wrapper {
        display: flex;
        align-items: center;
        gap: 4px;
        color: var(--primary-color);
    }

    .stats-value {
        font-size: 20px;
        font-weight: 800;
        color: var(--dark-navy);
    }

    .stats-text {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
    }

    .amount-wrapper {
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .currency-symbol {
        font-size: 14px;
        font-weight: 700;
        color: #bf953f;
    }

    .amount-value {
        font-size: 24px;
        font-weight: 800;
        color: var(--dark-navy);
        letter-spacing: -0.5px;
    }

    .view-details-circle {
        width: 45px;
        height: 45px;
        background: var(--soft-gray);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #bf953f;
        transition: all 0.3s ease;
    }

    .project-card-v2:hover .view-details-circle {
        background: #bf953f;
        color: black;
        transform: rotate(-45deg);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .projects-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
