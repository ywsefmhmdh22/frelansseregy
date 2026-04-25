@extends('layouts.master')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">

<style>
    :root {
        --bg-deep: #020617; /* أسود كوني */
        --accent-glow: #10b981; /* أخضر ماهر المميز */
        --text-bright: #f8fafc; /* أبيض ناصع للنصوص */
        --text-dim: #cbd5e1; /* رمادي لؤلؤي للقراءة الطويلة */
    }

    body {
        background-color: var(--bg-deep);
        color: var(--text-dim);
        font-family: 'Cairo', sans-serif;
    }

    /* هيرو سيكشن بتأثير الضوء */
    .article-hero {
        padding: 180px 0 100px;
        background: radial-gradient(circle at 50% 30%, rgba(16, 185, 129, 0.15) 0%, transparent 60%);
        text-align: center;
        position: relative;
    }

    .article-hero::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 150px;
        background: linear-gradient(to top, var(--bg-deep), transparent);
    }

    .article-title {
        font-weight: 900;
        font-size: clamp(2.8rem, 7vw, 5rem);
        color: #ffffff;
        line-height: 1.1;
        margin-bottom: 25px;
        text-shadow: 0 10px 30px rgba(0,0,0,0.5);
        letter-spacing: -1px;
    }

    .premium-badge {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid var(--accent-glow);
        color: var(--accent-glow);
        padding: 8px 24px;
        border-radius: 100px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
    }

    /* منطقة القراءة الجذابة */
    .article-body {
        max-width: 850px;
        margin: 0 auto;
        font-size: 1.35rem;
        line-height: 2.3;
        color: var(--text-dim);
        text-align: justify;
        position: relative;
        z-index: 2;
    }

    /* تمييز الفقرات الأولى */
    .article-body > p:first-of-type {
        font-size: 1.6rem;
        color: #fff;
        font-weight: 500;
        line-height: 1.8;
        border-right: 4px solid var(--accent-glow);
        padding-right: 25px;
        margin-bottom: 50px;
    }

    .glow-line {
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--accent-glow), transparent);
        margin: 60px 0;
        box-shadow: 0 0 15px var(--accent-glow);
    }

    /* الزرار "الخرافي" للعودة */
    .btn-return {
        background: transparent;
        color: #fff;
        border: 1px solid rgba(255,255,255,0.2);
        padding: 15px 45px;
        border-radius: 20px;
        font-weight: 700;
        transition: 0.4s;
        text-decoration: none;
        display: inline-block;
        margin-top: 50px;
        backdrop-filter: blur(10px);
    }

    .btn-return:hover {
        background: var(--accent-glow);
        border-color: var(--accent-glow);
        box-shadow: 0 0 30px rgba(16, 185, 129, 0.4);
        transform: scale(1.05);
        color: #fff;
    }

    /* تحسين شكل الـ Scrollbar */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: var(--bg-deep); }
    ::-webkit-scrollbar-thumb { background: var(--accent-glow); border-radius: 10px; }
</style>

<div class="article-hero">
    <div class="container animate__animated animate__fadeInDown">
        <span class="premium-badge">MÄHEER EXCLUSIVE</span>
        <h1 class="article-title mt-4">{{ $blog->title }}</h1>
        <div class="d-flex justify-content-center align-items-center gap-4 text-white-50">
            <span><i class="far fa-user text-success me-2"></i> {{ $blog->user->name }}</span>
            <span><i class="far fa-clock text-success me-2"></i> 25 دقيقة قراءة</span>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="article-body animate__animated animate__fadeInUp">
        <div class="glow-line"></div>

        {{-- المحتوى الضخم --}}
        <div class="main-content">
            {!! nl2br(e($blog->content)) !!}
        </div>

        <div class="glow-line"></div>

        <div class="text-center">
            <h3 class="text-white mb-4">انتهت الرحلة في هذا المقال، لكن الإبداع في ماهر لا ينتهي.</h3>
            <a href="{{ route('blog.index') }}" class="btn-return">
                <i class="fas fa-arrow-right me-2"></i> العودة للمدونة
            </a>
        </div>
    </div>
</div>
@endsection
