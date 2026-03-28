 <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="FreelancerPro Platform">

    <title>FreelancerPro - منصة العمل الحر</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">

<style>
    :root {
        --primary-color: #047857;
        --secondary-color: #d97706;
        --bg-soft: #f8fafc;
        --sidebar-width: 110px;
        --glass-bg: rgba(255, 255, 255, 0.8);
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: rgba(0,0,0,0.05);
        --nav-hover: #ecfdf5;
    }

    /* --- Dark Mode Variables --- */
    [data-theme="dark"] {
        --bg-soft: #0f172a;
        --glass-bg: rgba(30, 41, 59, 0.8);
        --card-bg: #1e293b;
        --text-main: #f1f5f9;
        --text-muted: #94a3b8;
        --border-color: rgba(255,255,255,0.1);
        --nav-hover: rgba(4, 120, 87, 0.2);
    }

    body {
        margin: 0;
        font-family: 'Cairo', sans-serif;
        background: var(--bg-soft);
        color: var(--text-main);
        min-height: 100vh;
        overflow-x: hidden;
        transition: background 0.3s, color 0.3s;
    }

    #bg-canvas {
        position: fixed;
        top: 0; left: 0;
        width: 100vw; height: 100vh;
        z-index: -1;
        opacity: 0.8;
    }

    /* --- Sidebar (Desktop) --- */
    .sidebar {
        width: var(--sidebar-width);
        background: var(--card-bg);
        height: 94vh;
        position: fixed;
        right: 20px;
        top: 3vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 30px 0;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        border-radius: 30px;
        z-index: 1040;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .sidebar-logo { font-size: 30px; color: var(--primary-color); margin-bottom: 30px; cursor: pointer; transition: transform 0.2s; }
    .sidebar-logo:hover { transform: scale(1.1); }

    .nav-item-custom { width: 100%; text-align: center; margin-bottom: 5px; }

    .nav-link-custom {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: var(--text-muted);
        font-size: 10px;
        font-weight: 700;
        padding: 12px 0;
        transition: 0.3s;
        border-radius: 18px;
        margin: 0 12px;
    }

    .nav-link-custom i { font-size: 22px; margin-bottom: 4px; }

    .nav-link-custom:hover, .nav-link-custom.active {
        color: var(--primary-color);
        background: var(--nav-hover);
    }

    .nav-link-special { color: var(--secondary-color); }

    /* --- Header & Wrapper --- */
    .top-header {
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        padding: 12px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 20px;
        margin-bottom: 30px;
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        position: relative;
        z-index: 1030;
    }

    .dropdown-menu {
        z-index: 2000 !important;
        background-color: var(--card-bg) !important;
        border: 1px solid var(--border-color) !important;
    }

    .dropdown-item { color: var(--text-main) !important; }

    .search-box {
        background: rgba(0,0,0,0.05);
        border-radius: 12px;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        width: 300px;
    }
    [data-theme="dark"] .search-box { background: rgba(255,255,255,0.05); }

    .search-box input { border: none; background: transparent; color: var(--text-main); margin-right: 10px; width: 100%; outline: none; }

    .main-wrapper {
        margin-right: calc(var(--sidebar-width) + 40px);
        padding: 20px;
        transition: all 0.3s ease;
    }

    .hero-section-card {
        background: linear-gradient(135deg, #047857 0%, #065f46 100%);
        border-radius: 30px;
        padding: 45px;
        color: white;
        margin-bottom: 40px;
        box-shadow: 0 20px 40px rgba(4, 120, 87, 0.2);
        position: relative;
        overflow: hidden;
    }

    .user-profile-img {
        width: 40px; height: 40px; border-radius: 10px; object-fit: cover;
        border: 2px solid white;
    }

    .floating-footer {
        background: var(--card-bg); padding: 20px 40px; border-radius: 20px;
        display: flex; justify-content: space-between; align-items: center;
        margin-top: 50px; border: 1px solid var(--border-color);
    }

    /* --- Responsive Fix for Mobile --- */
    @media (max-width: 992px) {
        .sidebar {
            width: 90%;
            height: 70px;
            bottom: 20px;
            top: auto;
            right: 5%;
            left: 5%;
            flex-direction: row;
            justify-content: space-around;
            padding: 0 10px;
            border-radius: 20px;
            box-shadow: 0 -5px 25px rgba(0,0,0,0.15);
            z-index: 2000;
        }
        .sidebar-logo, .desktop-theme-toggle { display: none !important; }
        .mobile-theme-item { display: block !important; }
        .nav-item-custom { margin-bottom: 0; width: auto; }
        .nav-link-custom { margin: 0; padding: 8px 5px; font-size: 10px; }
        .nav-link-custom i { font-size: 20px; margin-bottom: 2px; }
        .main-wrapper { margin-right: 0; padding-bottom: 100px; }
        .search-box { width: 140px; }
    }

    @media (min-width: 993px) {
        .mobile-theme-item { display: none !important; }
    }
</style>
</head>
<body>

<canvas id="bg-canvas"></canvas>

<aside class="sidebar shadow">
    <div class="sidebar-logo" id="adminGate"><i class="fas fa-rocket"></i></div>

    <div class="nav-item-custom">
        <a href="{{ route('works.index') }}" class="nav-link-custom {{ request()->routeIs('works.index') ? 'active' : '' }}">
            <i class="fas fa-briefcase"></i>
            <span>الأعمال</span>
        </a>
    </div>

    <div class="nav-item-custom">
        <a href="/top-rated" class="nav-link-custom nav-link-special">
            <i class="fas fa-star"></i>
            <span>النخبة</span>
        </a>
    </div>

    <div class="nav-item-custom">
        <a href="/Services" class="nav-link-custom">
            <i class="fas fa-shapes"></i>
            <span>الخدمات</span>
        </a>
    </div>

    @auth
    <div class="nav-item-custom">
        @php
            $dashboardUrl = auth()->user()->role === 'freelancer' ? route('freelancer.dashboard') : route('client.dashboard');
        @endphp
        <a href="{{ $dashboardUrl }}" class="nav-link-custom">
            <i class="fas fa-gauge-high"></i>
            <span>لوحه التحكم </span>
        </a>
    </div>
    @endauth

    <div class="nav-item-custom mobile-theme-item">
        <button class="nav-link-custom border-0 bg-transparent w-100 theme-toggle-btn">
            <i class="fas fa-moon theme-icon"></i>
            <span>المظهر</span>
        </button>
    </div>

    <div class="nav-item-custom mt-auto mb-3 desktop-theme-toggle">
        <button class="nav-link-custom border-0 bg-transparent w-100 theme-toggle-btn">
            <i class="fas fa-moon theme-icon"></i>
        </button>
    </div>
</aside>

<main class="main-wrapper">
    <header class="top-header">
        <div class="search-box">
            <i class="fas fa-search text-muted small"></i>
            <input type="text" placeholder="ابحث عن مشاريع...">
        </div>

        <div class="header-actions d-flex align-items-center gap-3">
            @auth
                <div class="dropdown">
                    <div class="position-relative cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-regular fa-bell fs-5 text-secondary"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-3 mt-3">
                        <li class="small text-muted p-2">لا توجد إشعارات جديدة</li>
                    </ul>
                </div>

                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=047857&color=fff" class="user-profile-img">
                        <i class="fas fa-chevron-down small text-muted d-none d-md-block"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3 rounded-4">
                        <li><a class="dropdown-item py-2" href="{{ route('profile.settings') }}"><i class="fas fa-user-gear me-2"></i> الإعدادات</a></li>
                        <li><hr class="dropdown-divider opacity-50"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger py-2"><i class="fas fa-sign-out-alt me-2"></i> خروج</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="/login" class="btn btn-sm text-secondary fw-bold">دخول</a>
                <a href="/register" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold" style="background: var(--primary-color); border:none;">انضم إلينا</a>
            @endauth
        </div>
    </header>

    <div class="container-fluid p-0">
        @if(request()->is('/'))
        <section class="hero-section-card shadow-lg">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-800 mb-3 display-5">عالم من المبدعين بين يديك</h1>
                    <p class="opacity-75 mb-4 fs-5">نفذ مشاريعك بأعلى جودة مع أفضل المستقلين في الوطن العربي.</p>
                    <div class="d-flex gap-3">
                        <a href="/projects" class="btn btn-light rounded-pill px-4 fw-bold" style="color: var(--primary-color);">ابدأ مشروعك</a>
                        <a href="/top-rated" class="btn btn-outline-light rounded-pill px-4 border-2">الأعلى تقييماً</a>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <div class="page-content">
            @yield('content')
        </div>
    </div>

    <footer class="floating-footer">
        <div class="small text-muted">&copy; 2026 FreelancerPro. صنع بكل حب.</div>
        <div class="d-flex gap-3 text-muted">
            <i class="fab fa-instagram cursor-pointer"></i>
            <i class="fab fa-linkedin-in cursor-pointer"></i>
            <i class="fab fa-x-twitter cursor-pointer"></i>
        </div>
    </footer>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const canvas = document.getElementById('bg-canvas');
    const ctx = canvas.getContext('2d');
    let width, height, particles = [];
    let mouse = { x: null, y: null };

    window.addEventListener('mousemove', (e) => { mouse.x = e.x; mouse.y = e.y; });

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
            this.vx = (Math.random() - 0.5) * 0.5;
            this.vy = (Math.random() - 0.5) * 0.5;
            this.size = Math.random() * 2 + 1;
        }
        update() {
            this.x += this.vx; this.y += this.vy;
            if(this.x < 0 || this.x > width || this.y < 0 || this.y > height) this.reset();
            let dx = mouse.x - this.x;
            let dy = mouse.y - this.y;
            let distance = Math.sqrt(dx*dx + dy*dy);
            if (distance < 100) { this.x -= dx/20; this.y -= dy/20; }
        }
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI*2);
            ctx.fillStyle = document.documentElement.getAttribute('data-theme') === 'dark' ? `rgba(4, 120, 87, 0.2)` : `rgba(4, 120, 87, 0.2)`;
            ctx.fill();
        }
    }

    for(let i=0; i<80; i++) particles.push(new Particle());

    function animate() {
        ctx.clearRect(0,0,width,height);
        particles.forEach((p, i) => {
            p.update(); p.draw();
            for(let j=i+1; j<particles.length; j++) {
                const p2 = particles[j];
                const dist = Math.hypot(p.x - p2.x, p.y - p2.y);
                if(dist < 120) {
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y); ctx.lineTo(p2.x, p2.y);
                    ctx.strokeStyle = document.documentElement.getAttribute('data-theme') === 'dark' ? `rgba(99, 102, 241, 0.05)` : `rgba(4, 120, 87, 0.05)`;
                    ctx.stroke();
                }
            }
        });
        requestAnimationFrame(animate);
    }
    animate();

    const themeToggles = document.querySelectorAll('.theme-toggle-btn');
    const themeIcons = document.querySelectorAll('.theme-icon');

    function updateIcons(theme) {
        themeIcons.forEach(icon => {
            if (theme === 'dark') {
                icon.classList.replace('fa-moon', 'fa-sun');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
            }
        });
    }

    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        updateIcons('dark');
    }

    themeToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                updateIcons('light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                updateIcons('dark');
            }
        });
    });

    // --- التعديل المطلوب: وظيفة الصاروخ السرية ---
    document.getElementById('adminGate').addEventListener('click', function() {
        const pass = prompt("أدخل كلمة مرور الإدارة للدخول:");
        if (pass === "01025450449") {
            window.location.href = "/admin/dashboard";
        } else if (pass !== null) {
            alert("عذراً، كلمة المرور خاطئة!");
        }
    });
</script>

</body>
</html>
