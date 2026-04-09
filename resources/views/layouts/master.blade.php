<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FreelancerPro - منصة العمل الحر العالمية | إبداع بلا حدود</title>
    <meta name="description" content="FreelancerPro هي المنصة الرائدة عالمياً للعمل الحر، تجمع بين أفضل المبدعين والشركات. ابدأ رحلتك المهنية الآن.">
    <meta name="keywords" content="Freelance, العمل الحر, مبرمجين, مصممين, توظيف، FreelancerPro, Remote Work, وظائف عن بعد, Digital Transformation">
    <meta name="author" content="FreelancerPro Team">

    <meta property="og:title" content="FreelancerPro - منصة العمل الحر الأولى">
    <meta property="og:description" content="وظف أفضل الخبراء في كافة المجالات بضغطة زر.">
    <meta property="og:type" content="website">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
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
    }

    [data-theme="dark"] {
        --bg-body: #020617;
        --card-bg: #0f172a;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border-color: #1e293b;
        --nav-hover: rgba(16, 185, 129, 0.1);
    }

    body {
        margin: 0;
        font-family: 'Cairo', sans-serif;
        background: var(--bg-body);
        color: var(--text-main);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-x: hidden;
    }

    /* خلفية جرافيك خفيفة للأداء */
    #bg-canvas {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        z-index: -1;
        opacity: 0.4;
        pointer-events: none;
    }

    /* --- Sidebar --- */
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

    .nav-link-custom i { font-size: 20px; margin-bottom: 6px; }

    .nav-link-custom:hover, .nav-link-custom.active {
        color: var(--primary-color);
        background: var(--nav-hover);
    }

    /* --- Main Wrapper --- */
    .main-wrapper {
        margin-right: calc(var(--sidebar-width) + 3rem);
        padding: 2rem;
        max-width: 1600px;
    }

    /* --- Top Header --- */
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

    .search-box {
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        width: 100%;
        max-width: 400px;
    }
    .search-box input { border: none; background: transparent; color: var(--text-main); width: 100%; outline: none; font-size: 0.9rem; }

    /* --- Hero Section (التصميم الأخضر المطور) --- */
    .hero-section-card {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #10b981 100%);
        border-radius: 40px;
        padding: 6rem 4rem;
        color: white;
        margin-bottom: 4rem;
        box-shadow: 0 30px 60px -12px rgba(6, 95, 70, 0.3);
        position: relative;
        overflow: hidden;
        min-height: 450px;
        display: flex;
        align-items: center;
    }

    /* تأثيرات جرافيك داخل الكارت الأخضر */
    .hero-section-card::before {
        content: ''; position: absolute; top: -10%; right: -10%;
        width: 400px; height: 400px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        filter: blur(80px);
    }

    .hero-section-card::after {
        content: ''; position: absolute; bottom: -20%; left: -5%;
        width: 300px; height: 300px;
        background: var(--primary-light);
        opacity: 0.2;
        border-radius: 38% 62% 63% 37% / 41% 44% 56% 59%;
        animation: blob 15s infinite alternate;
    }

    @keyframes blob {
        0% { border-radius: 38% 62% 63% 37% / 41% 44% 56% 59%; transform: scale(1); }
        100% { border-radius: 63% 37% 38% 62% / 56% 59% 41% 44%; transform: scale(1.2); }
    }

    /* --- Responsive --- */
    @media (max-width: 991.98px) {
        .sidebar {
            width: 100%; height: 75px;
            bottom: 0; top: auto; right: 0;
            flex-direction: row; padding: 0;
            border-radius: 0; border-top: 1px solid var(--border-color);
            justify-content: space-around;
        }
        .sidebar-logo { display: none !important; }
        .main-wrapper { margin-right: 0; padding: 1rem; padding-bottom: 90px; }
        .hero-section-card { padding: 3rem 1.5rem; text-align: center; }
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
        <a href="{{ route('home') }}" class="nav-link-custom active">
            <i class="fas fa-house" aria-hidden="true"></i>
            <span>الرئيسية</span>
        </a>
    </div>

    <div class="nav-item-custom">
        <a href="/about" class="nav-link-custom">
            <i class="fas fa-users-viewfinder" aria-hidden="true"></i>
            <span>من نحن</span>
        </a>
    </div>

    <div class="nav-item-custom">
        <a href="{{ route('works.index') }}" class="nav-link-custom">
            <i class="fas fa-layer-group" aria-hidden="true"></i>
            <span>الأعمال</span>
        </a>
    </div>

    <div class="nav-item-custom">
        <a href="/Services" class="nav-link-custom">
            <i class="fas fa-cubes" aria-hidden="true"></i>
            <span>الخدمات</span>
        </a>
    </div>

    @auth
    <div class="nav-item-custom">
        <a href="{{ auth()->user()->role === 'freelancer' ? route('freelancer.dashboard') : route('client.dashboard') }}" class="nav-link-custom">
            <i class="fas fa-chart-pie" aria-hidden="true"></i>
            <span>Dashboard</span>
        </a>
    </div>
    @endauth

    <div class="nav-item-custom mt-auto mb-3">
        <button class="nav-link-custom border-0 bg-transparent w-100 theme-toggle-btn" aria-label="تبديل الوضع الليلي">
            <i class="fas fa-moon theme-icon" aria-hidden="true"></i>
            <span class="d-lg-none">المظهر</span>
        </button>
    </div>
</aside>

<main class="main-wrapper">
    <header class="top-header">
        <div class="search-box">
            <i class="fas fa-search text-muted me-2" aria-hidden="true"></i>
            <input type="text" placeholder="ما الذي تبحث عنه اليوم؟" aria-label="بحث في المنصة">
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
        <div class="row align-items-center w-100">
            <div class="col-lg-8">
                <h1 class="fw-800 display-3 mb-3">صمم مستقبلك المهني معنا</h1>
                <p class="lead opacity-90 mb-5 fs-4">نجمع لك أفضل المبدعين العرب والخبراء العالميين في منصة واحدة آمنة وسهلة الاستخدام تماماً.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="/top-rated" class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-success shadow-lg">المستقلين المتميزين</a>
                </div>
            </div>
        </div>
    </section>
    @endif

    <section class="page-content">
        @yield('content')
    </section>

    <footer class="d-flex flex-wrap justify-content-between align-items-center py-4 my-5 border-top border-2">
        <p class="col-md-4 mb-0 text-muted fw-bold">© 2026 FreelancerPro Global. جميع الحقوق محفوظة.</p>
        <ul class="nav col-md-4 justify-content-end list-unstyled d-flex gap-4 fs-5">
            <li><a class="text-muted" href="#"><i class="fab fa-x-twitter"></i></a></li>
            <li><a class="text-muted" href="#"><i class="fab fa-instagram"></i></a></li>
            <li><a class="text-muted" href="#"><i class="fab fa-linkedin-in"></i></a></li>
        </ul>
    </footer>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // نظام جزيئات الخلفية المتطور والخفيف
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

    // تبديل الثيم
    const themeToggles = document.querySelectorAll('.theme-toggle-btn');
    const updateUI = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    };
    themeToggles.forEach(btn => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            updateUI(current === 'dark' ? 'light' : 'dark');
        });
    });

    // كود الأدمن السري
    document.getElementById('adminGate').addEventListener('click', () => {
        const pass = prompt("الوصول إلى لوحة التحكم العالمية:");
        if (pass === "01025450449") window.location.href = "/admin/dashboard";
    });
</script>

</body>
</html>
