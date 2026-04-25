@extends('layouts.master')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">

<style>
    body {
        background: #020617; /* أسود أعمق يظهر الألوان */
        color: #f8fafc;
        font-family: 'Cairo', sans-serif;
    }

    .blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 40px;
        padding: 120px 0;
    }

    .glass-card {
        background: rgba(15, 23, 42, 0.8); /* خلفية كارت أغمق عشان النص الأبيض يبان */
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 35px;
        padding: 25px;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    /* تأثير التوهج عند الوقوف على الكارت */
    .glass-card:hover {
        transform: translateY(-15px);
        border-color: #10b981;
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);
    }

    .glass-card img {
        width: 100%;
        border-radius: 25px;
        height: 240px;
        object-fit: cover;
        filter: brightness(0.9);
        transition: 0.5s;
    }

    .glass-card:hover img {
        filter: brightness(1.1);
        transform: scale(1.02);
    }

    /* العناوين - أبيض صريح وبخط عريض */
    .glass-card h3 {
        color: #ffffff;
        font-weight: 900;
        font-size: 1.6rem;
        line-height: 1.4;
        margin-top: 20px;
    }

    /* النص الوصفي - خليناه أفتح عشان ميبقاش باهت */
    .post-excerpt {
        color: #94a3b8; /* رمادي فاتح مريح للعين */
        font-size: 1.05rem;
        line-height: 1.7;
        margin: 15px 0;
    }

    /* زر استكشف المزيد - Neon Style */
    .btn-maheer {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        padding: 14px 30px;
        border-radius: 20px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        font-weight: 700;
        letter-spacing: 0.5px;
        transition: 0.3s;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        border: none;
    }

    .btn-maheer:hover {
        color: #fff;
        transform: scale(1.05);
        box-shadow: 0 0 25px rgba(16, 185, 129, 0.6);
    }

    /* لمسة جمالية فوق الكارت */
    .category-tag {
        position: absolute;
        top: 40px;
        left: 40px;
        background: rgba(16, 185, 129, 0.9);
        color: white;
        padding: 5px 15px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: bold;
        backdrop-filter: blur(5px);
    }
</style>

<div class="container">
    <div class="blog-grid">
        @foreach($blogs as $item)
        <div class="glass-card">
            <span class="category-tag">{{ $item->category->name }}</span>
            <img src="{{ $item->image }}" alt="{{ $item->title }}">

            <h3>{{ $item->title }}</h3>

            <p class="post-excerpt">
                {{ Str::limit(strip_tags($item->content), 120) }}
            </p>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <span style="color: #64748b; font-size: 0.9rem;">
                    <i class="far fa-clock me-1 text-success"></i> 12 دقيقة قراءة
                </span>
                <a href="{{ route('blog.show', $item->id) }}" class="btn-maheer">
                    استكشف المزيد <i class="fas fa-chevron-left ms-2" style="font-size: 0.8rem;"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
