@extends('layouts.master')

@section('content')

<section class="about-section">

    <!-- Hero -->
    <div class="about-hero">
        <h1 class="brand-logo-text mb-4" style="font-family: 'Playfair Display', serif; -webkit-text-fill-color: initial; background: none; color: var(--text-main);">Worklyday</h1>
        <h2 class="fw-800">نبتكر الحلول الرقمية لمستقبلك</h2>
        <p>
            Worklyday هي شريكك التقني الأمثل لتنفيذ مشاريعك الرقمية. نحن متخصصون في تقديم حلول برمجية مبتكرة
            بجودة عالمية تضمن نمو وتطور أعمالك في العصر الرقمي.
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
                أن نكون المؤسسة الرائدة في تقديم الحلول التقنية المتكاملة وتطوير البرمجيات،
                من خلال تبني أحدث التكنولوجيات التي تضع الجودة والابتكار في المقام الأول.
            </p>
        </div>

        <div class="about-card">
            <div class="icon-wrapper">
                <i class="fa-solid fa-bullseye"></i>
            </div>
            <h3>رسالتنا</h3>
            <p>
                توفير بيئة تقنية احترافية تتيح لأصحاب الأعمال تنفيذ رؤيتهم البرمجية وتطوير تطبيقاتهم
                بأعلى معايير الدقة والكفاءة التقنية.
            </p>
        </div>

        <div class="about-card">
            <div class="icon-wrapper">
                <i class="fa-solid fa-gem"></i>
            </div>
            <h3>قيمنا</h3>
            <p>
                الالتزام التام — الأمان الرقمي — جودة الأداء — وسرعة التنفيذ.
                نحن نؤمن ببناء شراكات طويلة الأمد تعتمد على الثقة والاحترافية.
            </p>
        </div>

    </div>

    <!-- CTA -->
    <div class="about-cta">
        <h2>نفذ مشروعك التقني الآن مع فريق من الخبراء</h2>
        <div class="mt-4">
            <a href="/Services" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm" style="background: var(--primary-color); border:none;">
                اطلب خدمة الآن <i class="fas fa-arrow-left ms-2"></i>
            </a>
        </div>
    </div>

</section>

<style>
/* تنسيقات صفحة "عن الشركة" المتوافقة مع نظام Worklyday */
.about-section {
    font-family: 'Cairo', sans-serif;
    direction: rtl;
    padding: 40px 5%;
    color: var(--text-main);
}

.about-hero {
    text-align: center;
    margin-bottom: 70px;
}

.about-hero h2 {
    font-size: clamp(26px, 3.5vw, 38px);
    font-weight: 800;
    margin-bottom: 20px;
    color: var(--text-main);
}

.about-hero p {
    font-size: 18px;
    max-width: 850px;
    margin: auto;
    color: var(--text-muted);
    line-height: 1.8;
}

.about-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 80px;
}

.about-card {
    background: var(--card-bg);
    padding: 50px 35px;
    border-radius: 30px;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid var(--border-color);
}

.about-card .icon-wrapper {
    width: 85px;
    height: 85px;
    background: var(--nav-hover);
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px auto;
    transition: 0.4s;
}

.about-card i {
    font-size: 30px;
    color: var(--primary-color);
}

.about-card h3 {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 18px;
    color: var(--text-main);
}

.about-card p {
    color: var(--text-muted);
    font-size: 15.5px;
    line-height: 1.7;
}

.about-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-light);
}

.about-card:hover .icon-wrapper {
    background: var(--primary-color);
}

.about-card:hover i {
    color: #ffffff;
}

.about-cta {
    text-align: center;
    background: var(--card-bg);
    padding: 60px 40px;
    border-radius: 40px;
    border: 1px solid var(--border-color);
}

.about-cta h2 {
    font-size: 30px;
    font-weight: 800;
}
</style>

@endsection
