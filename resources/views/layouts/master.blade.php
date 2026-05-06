<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Worklyday - للحلول التقنية المتكاملة وتطوير البرمجيات</title>
    <meta name="description" content="Worklyday هي شريكك التقني الأمثل لتنفيذ مشاريعك الرقمية. متخصصون في تطوير المواقع، تطبيقات الموبايل، والحلول البرمجية المبتكرة بجودة عالمية.">
    <meta name="keywords" content="برمجة، تطوير تطبيقات، تطوير مواقع، حلول تقنية، Worklyday, Software House, Digital Solutions">
    <meta name="author" content="Worklyday Team">

    <meta property="og:title" content="Worklyday - للحلول التقنية المتكاملة">
    <meta property="og:description" content="نفذ مشروعك التقني الآن مع فريق من الخبراء والمتخصصين.">
    <meta property="og:type" content="website">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">

<style>
    :root {
        --primary-color: #065f46;
        --primary-light: #10b981;
        --secondary-color: #b45309;
        --bg-body: #f1f5f9;
        --card-bg: #ffffff;
        --text-main: #0f172a;
        --text-muted: #475569;
        --sidebar-width: 100px;
        --border-color: #e2e8f0;
        --nav-hover: #f0fdf4;
        --logo-gradient: linear-gradient(135deg, #065f46 0%, #10b981 100%);
    }

    [data-theme="dark"] {
        --bg-body: #020617;
        --card-bg: #0f172a;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border-color: #1e293b;
        --nav-hover: rgba(16, 185, 129, 0.1);
        --logo-gradient: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    }

    body {
        margin: 0;
        font-family: 'Cairo', sans-serif;
        background: var(--bg-body);
        color: var(--text-main);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-x: hidden;
    }

    #bg-canvas {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        z-index: -1;
        opacity: 0.4;
        pointer-events: none;
    }

    .brand-logo-text {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem;
        font-weight: 900;
        background: var(--logo-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -1px;
        position: relative;
        text-decoration: none;
        transition: 0.4s;
        display: inline-block;
        filter: drop-shadow(0 2px 10px rgba(16, 185, 129, 0.2));
    }
    .brand-logo-text:hover {
        transform: scale(1.03) translateY(-2px);
        filter: drop-shadow(0 5px 15px rgba(16, 185, 129, 0.4));
    }
    .brand-logo-text::after {
        content: '.';
        color: var(--secondary-color);
        -webkit-text-fill-color: var(--secondary-color);
    }

    .sidebar {
        width: var(--sidebar-width);
        background: var(--card-bg);
        height: 94vh;
        position: fixed;
        right: 1.5rem;
        top: 3vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem 0;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        border-radius: 24px;
        z-index: 1050;
        border: 1px solid var(--border-color);
        transition: transform 0.3s ease;
    }

    .sidebar-logo {
        width: 50px; height: 50px;
        background: var(--primary-color);
        color: white;
        border-radius: 15px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; margin-bottom: 2.5rem;
        cursor: pointer; transition: 0.3s;
    }
    .sidebar-logo:hover { transform: rotate(15deg); background: var(--primary-light); }

    .nav-item-custom { width: 100%; margin-bottom: 0.5rem; }
    .nav-item-home { z-index: 10; }

    .nav-link-custom {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 700;
        padding: 1rem 0;
        transition: 0.2s;
        border-radius: 16px;
        margin: 0 0.75rem;
    }

    .nav-item-home .nav-link-custom {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transform: scale(1.1);
    }

    .nav-link-custom i { font-size: 20px; margin-bottom: 6px; }

    .nav-link-custom:hover, .nav-link-custom.active {
        color: var(--primary-color);
        background: var(--nav-hover);
    }

    .main-wrapper {
        margin-right: calc(var(--sidebar-width) + 3rem);
        padding: 2rem;
        max-width: 1600px;
    }

    .top-header {
        background: var(--card-bg);
        padding: 0.75rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 20px;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .header-logo {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .theme-toggle-header {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.3s;
    }
    .theme-toggle-header:hover {
        background: var(--nav-hover);
        color: var(--primary-color);
    }

    .blog-nav-btn {
        background: var(--nav-hover);
        border: 1px solid var(--primary-light);
        color: var(--primary-color);
        border-radius: 12px;
        padding: 0.5rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        text-decoration: none;
        transition: 0.3s;
        margin-right: 30px;
    }
    .blog-nav-btn:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-2px);
    }

    .hero-section-card {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #10b981 100%);
        border-radius: 40px;
        padding: 7rem 4rem;
        color: white;
        margin-bottom: 4rem;
        box-shadow: 0 30px 60px -12px rgba(6, 95, 70, 0.3);
        position: relative;
        overflow: hidden;
    }

    .hero-content { position: relative; z-index: 2; text-align: center; }

    .policy-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 25px;
        padding: 2rem;
        height: 100%;
        transition: 0.4s;
        position: relative;
        overflow: hidden;
    }
    .policy-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border-color: var(--primary-light);
    }
    .policy-icon {
        width: 60px;
        height: 60px;
        background: var(--nav-hover);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }

    @media (max-width: 991.98px) {
        .sidebar {
            width: 100%; height: 75px;
            bottom: 0; top: auto; right: 0;
            flex-direction: row; padding: 0;
            border-radius: 0; border-top: 1px solid var(--border-color);
            justify-content: space-between;
            align-items: center;
            overflow: visible;
        }
        .nav-item-custom { width: auto; margin-bottom: 0; flex: 1; display: flex; justify-content: center; }
        .nav-item-home { position: relative; transform: translateY(-20px); z-index: 100; }
        .nav-item-home .nav-link-custom {
            background: var(--card-bg); border-radius: 50%; width: 65px; height: 65px;
            display: flex; justify-content: center; align-items: center;
            border: 2px solid var(--primary-color); box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .nav-item-home .nav-link-custom span { display: none; }
        .sidebar-logo { display: none !important; }
        .main-wrapper { margin-right: 0; padding: 1rem; padding-bottom: 90px; }
        .hero-section-card { padding: 5rem 1.5rem; }
        .blog-nav-btn { display: none; }
        .brand-logo-text { font-size: 1.7rem; }
    }
</style>
</head>
<body>

<canvas id="bg-canvas" aria-hidden="true"></canvas>

<aside class="sidebar" role="navigation" aria-label="القائمة الرئيسية">
    <div class="sidebar-logo" id="adminGate" tabindex="0" title="بوابة الإدارة">
        <i class="fas fa-bolt" aria-hidden="true"></i>
    </div>

    <div class="nav-item-custom">
        <a href="/top-rated" class="nav-link-custom">
            <i class="fas fa-award text-warning" aria-hidden="true"></i>
            <span>المميزون</span>
        </a>
    </div>

    <div class="nav-item-custom">
        <a href="/about" class="nav-link-custom">
            <i class="fas fa-users-viewfinder" aria-hidden="true"></i>
            <span>عن الشركة</span>
        </a>
    </div>

    <div class="nav-item-custom">
        <a href="/Services" class="nav-link-custom">
            <i class="fas fa-cubes" aria-hidden="true"></i>
            <span>خدماتنا</span>
        </a>
    </div>

    <div class="nav-item-custom nav-item-home">
        <a href="{{ route('home') }}" class="nav-link-custom active">
            <i class="fas fa-house" aria-hidden="true"></i>
            <span>الرئيسية</span>
        </a>
    </div>

    <div class="nav-item-custom">
        <a href="{{ route('works.index') }}" class="nav-link-custom">
            <i class="fas fa-layer-group" aria-hidden="true"></i>
            <span>أعمالنا</span>
        </a>
    </div>

    <div class="nav-item-custom">
        <button class="nav-link-custom border-0 bg-transparent w-100" data-bs-toggle="modal" data-bs-target="#supportModal" aria-label="الدعم الفني">
            <i class="fas fa-headset text-warning" aria-hidden="true"></i>
            <span>تواصل معنا</span>
        </button>
    </div>

    <div class="nav-item-custom">
        @auth
            <a href="{{ auth()->user()->role === 'freelancer' ? route('freelancer.dashboard') : route('client.dashboard') }}" class="nav-link-custom">
                <i class="fas fa-chart-pie" aria-hidden="true"></i>
                <span>حسابي</span>
            </a>
        @else
            <a href="/login" class="nav-link-custom">
                <i class="fas fa-user-lock" aria-hidden="true"></i>
                <span>دخول</span>
            </a>
        @endauth
    </div>
</aside>

<main class="main-wrapper">
    <header class="top-header">
        <div class="d-flex align-items-center flex-grow-1">
            <div class="header-logo">
                <a href="/" class="brand-logo-text">Worklyday</a>
                <button class="theme-toggle-header theme-toggle-btn" aria-label="تبديل الوضع الليلي">
                    <i class="fas fa-moon theme-icon" aria-hidden="true"></i>
                </button>
            </div>
            <a href="{{ route('blog.index') }}" class="blog-nav-btn d-none d-md-flex">
                <i class="fas fa-feather-pointed"></i>
                <span>المدونة التقنية</span>
            </a>
        </div>

        <div class="header-actions d-flex align-items-center gap-3">
            @auth
                <div class="dropdown">
                    <button class="btn p-0 d-flex align-items-center gap-2 border-0" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=065f46&color=fff" class="rounded-circle shadow-sm" width="38" height="38">
                        <i class="fas fa-chevron-down small text-muted d-none d-md-block"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-3">
                        <li><a class="dropdown-item rounded-2" href="{{ route('profile.settings') }}"><i class="fas fa-cog me-2"></i> الإعدادات</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger rounded-2 w-100 text-start"><i class="fas fa-power-off me-2"></i> خروج</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="/login" class="btn text-muted fw-bold px-3">دخول</a>
                <a href="/register" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" style="background: var(--primary-color); border:none;">ابدأ الآن</a>
            @endauth
        </div>
    </header>

    @if(request()->is('/'))
    <section class="hero-section-card">
        <div class="hero-content">
            <h1 class="fw-800 display-4 mb-3">نبتكر الحلول الرقمية لمستقبلك</h1>
            <p class="lead opacity-90 mb-5 fs-5">فريقنا المتخصص يقدم لك أفضل الخدمات البرمجية وتطوير التطبيقات بجودة تضمن نجاح أعمالك.</p>
            <div class="d-flex justify-content-center flex-wrap gap-3">
                <a href="/Services" class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-success shadow-lg">اطلب خدمة الآن</a>
                <a href="{{ route('works.index') }}" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold">شاهد أعمالنا</a>
            </div>
        </div>
    </section>
    @endif

    <section class="page-content">
        @yield('content')
    </section>

    <section class="policies-section py-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="policy-card">
                    <div class="policy-icon"><i class="fas fa-user-shield"></i></div>
                    <h5 class="fw-bold">سياسة الخصوصية</h5>
                    <p class="text-muted small">تلتزم المنصة بعدم استخدام بيانات العملاء لأي أغراض خارج نطاق الخدمة، ونتبع أحدث طرق الحماية العالمية لضمان أمان معلوماتك تماماً.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="policy-card">
                    <div class="policy-icon"><i class="fas fa-rotate-left"></i></div>
                    <h5 class="fw-bold">سياسة الاسترجاع</h5>
                    <p class="text-muted small">في حال عدم استلام الخدمة في الوقت المقدر وطلب الاسترجاع، يتم الموافقة فوراً واسترداد كامل أموالك خلال 24 ساعة فقط دون أي تعقيدات.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="policy-card">
                    <div class="policy-icon"><i class="fas fa-server"></i></div>
                    <h5 class="fw-bold">سياسة التسليم</h5>
                    <p class="text-muted small">يتم تسليم الخدمات ورفعها على السيرفر الخاص بالمنصة بعد إجراء مراجعة نهائية شاملة والتأكد من رضاء العميل التام عن جودة الخدمة المقدمة.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="d-flex flex-wrap justify-content-between align-items-center py-4 my-5 border-top border-2">
        <p class="col-md-8 mb-0 text-muted fw-bold">
            © 2026 Worklyday Solutions. All Rights Reserved.
            <span class="ms-3 text-primary"><i class="fas fa-location-dot me-1"></i> Alexandria, New Borg El Arab, El Gehaz St.</span>
        </p>
        <ul class="nav col-md-4 justify-content-end list-unstyled d-flex gap-4 fs-5">
            <li><a class="text-muted" href="#"><i class="fab fa-x-twitter"></i></a></li>
            <li><a class="text-muted" href="#"><i class="fab fa-instagram"></i></a></li>
            <li><a class="text-muted" href="#"><i class="fab fa-linkedin-in"></i></a></li>
        </ul>
    </footer>
</main>

<div class="modal fade" id="supportModal" tabindex="-1" aria-labelledby="supportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="supportModalLabel">الدعم الفني والتواصل</h5>
                <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4">يسعدنا مساعدتك في أي وقت، اختر الوسيلة المناسبة لك:</p>
                <a href="https://wa.me/201556332042" target="_blank" class="d-flex align-items-center p-3 mb-3 border rounded-3 text-decoration-none text-dark shadow-sm">
                   <i class="fab fa-whatsapp fa-2x text-success me-3"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">واتساب (WhatsApp)</h6>
                        <p class="mb-0 text-muted small">رد سريع ومباشر على استفساراتكم</p>
                    </div>
                </a>
                <a href="mailto:ywsfmhmdh22@gmail.com" class="d-flex align-items-center p-3 border rounded-3 text-decoration-none text-dark shadow-sm">
                    <i class="far fa-envelope fa-2x text-danger me-3"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">البريد الإلكتروني (Gmail)</h6>
                        <p class="mb-0 text-muted small">ywsfmhmdh22@gmail.com</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const canvas = document.getElementById('bg-canvas');
    const ctx = canvas.getContext('2d');
    let width, height, particles = [];

    function resize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resize);
    resize();

    class Particle {
        constructor() { this.reset(); }
        reset() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.vx = (Math.random() - 0.5) * 0.4;
            this.vy = (Math.random() - 0.5) * 0.4;
            this.size = Math.random() * 2 + 0.5;
        }
        update() {
            this.x += this.vx; this.y += this.vy;
            if(this.x < 0 || this.x > width || this.y < 0 || this.y > height) this.reset();
        }
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI*2);
            ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--primary-light') + '22';
            ctx.fill();
        }
    }
    for(let i=0; i<50; i++) particles.push(new Particle());
    function animate() {
        ctx.clearRect(0,0,width,height);
        particles.forEach(p => { p.update(); p.draw(); });
        requestAnimationFrame(animate);
    }
    animate();

    const themeToggles = document.querySelectorAll('.theme-toggle-btn');
    const themeIcons = document.querySelectorAll('.theme-icon');
    const updateUI = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        themeIcons.forEach(icon => {
            if(theme === 'dark') icon.classList.replace('fa-moon', 'fa-sun');
            else icon.classList.replace('fa-sun', 'fa-moon');
        });
    };
    themeToggles.forEach(btn => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            updateUI(current === 'dark' ? 'light' : 'dark');
        });
    });
    updateUI(localStorage.getItem('theme') || 'light');
</script>

</body>
</html>
