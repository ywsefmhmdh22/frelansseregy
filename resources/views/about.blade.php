 @extends('layouts.master')

@section('content')

<section class="about-section">

    <!-- Hero -->
    <div class="about-hero">
        <h1>نحن نصنع مستقبل العمل الحر</h1>
        <p>
            منصة ذكية تربط بين أصحاب المشاريع وأفضل المستقلين
            في تجربة سريعة، آمنة، واحترافية بمعايير عالمية.
        </p>
    </div>

    <!-- Cards -->
    <div class="about-cards">

        <div class="about-card">
            <div class="icon-wrapper">
                <i class="fa-solid fa-eye"></i>
            </div>
            <h3>رؤيتنا</h3>
            <p>
                أن نكون المنصة العربية الأولى في مجال العمل الحر
                عبر تجربة تقنية متطورة تضع الجودة والسرعة أولاً.
            </p>
        </div>

        <div class="about-card">
            <div class="icon-wrapper">
                <i class="fa-solid fa-bullseye"></i>
            </div>
            <h3>رسالتنا</h3>
            <p>
                تمكين المستقلين من تحقيق دخل مستقر
                ومساعدة رواد الأعمال على تنفيذ مشاريعهم بكفاءة.
            </p>
        </div>

        <div class="about-card">
            <div class="icon-wrapper">
                <i class="fa-solid fa-gem"></i>
            </div>
            <h3>قيمنا</h3>
            <p>
                الشفافية — الأمان — الجودة — السرعة —
                تجربة مستخدم استثنائية.
            </p>
        </div>

    </div>

    <!-- CTA -->
    <div class="about-cta">
        <h2>ابدأ رحلتك معنا اليوم</h2>
        <!-- تم استخدام كلاس boxed-btn ليتطابق مع زرار الواجهة الرئيسية -->
        <a href="#" class="boxed-btn px-5 py-3 fs-5 mt-3">ابدأ الآن <i class="fas fa-arrow-left ms-2"></i></a>
    </div>

</section>

<style>
/* تنسيقات صفحة من نحن المتناسقة مع الثيم الفاتح والأخضر */
.about-section {
    font-family: 'Cairo', sans-serif;
    direction: rtl;
    padding: 20px 5%;
    /* تم إزالة الخلفية الداكنة لتندمج مع الواجهة الرئيسية بشفافية */
    color: #0f172a;
}

/* Hero */
.about-hero {
    text-align: center;
    margin-bottom: 70px;
}

.about-hero h1 {
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 900;
    margin-bottom: 20px;
    color: #0f172a; /* كحلي داكن أنيق */
    letter-spacing: -0.5px;
}

.about-hero p {
    font-size: 18px;
    max-width: 700px;
    margin: auto;
    color: #475569; /* رمادي مريح للعين */
    line-height: 1.7;
}

/* Cards */
.about-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-bottom: 80px;
}

.about-card {
    background: #ffffff;
    padding: 40px 30px;
    border-radius: 20px;
    text-align: center;
    transition: all 0.4s ease;
    border: 1px solid rgba(16, 185, 129, 0.1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
}

/* تصميم دائرة الأيقونات باللون الأخضر المميز للمنصة */
.about-card .icon-wrapper {
    width: 80px;
    height: 80px;
    background: #ecfdf5; /* أخضر فاتح جداً */
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px auto;
    transition: all 0.4s ease;
}

.about-card i {
    font-size: 32px;
    color: #10b981; /* أخضر المنصة الأساسي */
    transition: all 0.4s ease;
}

.about-card h3 {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 15px;
    color: #0f172a;
}

.about-card p {
    color: #64748b;
    font-size: 15px;
    line-height: 1.6;
    margin: 0;
}

/* تأثير التمرير (Hover) */
.about-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(16, 185, 129, 0.08);
    border-color: rgba(16, 185, 129, 0.3);
}

.about-card:hover .icon-wrapper {
    background: #10b981;
}

.about-card:hover i {
    color: #ffffff;
}

/* CTA */
.about-cta {
    text-align: center;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(59, 130, 246, 0.05));
    padding: 50px 30px;
    border-radius: 24px;
    border: 1px solid rgba(16, 185, 129, 0.1);
}

.about-cta h2 {
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 10px;
    color: #0f172a;
}
</style>

@endsection
