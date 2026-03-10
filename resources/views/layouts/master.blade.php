 <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="FreelancerPro Platform">

    <title>FreelancerPro - منصة العمل الحر</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">

<style>
    /* التنسيقات الأساسية والخلفية المتدرجة */
    body {
        margin: 0;
        padding: 0;
        font-family: 'Cairo', sans-serif;
        background: linear-gradient(180deg, #d8ebd9 0%, #f4f7f4 100%);
        color: #1e293b;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        overflow-x: hidden;
        position: relative;
    }

    /* إعدادات مساحة الأكواد في الخلفية */
    #bg-canvas {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -1;
        pointer-events: none;
    }

    /* الهيدر المتجاوب */
    .top-header-area {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        position: sticky;
        top: 0;
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .navbar {
        padding: 15px 0;
    }

    /* اللوجو */
    .site-logo h1 {
        color: #10b981;
        font-weight: 800;
        margin: 0;
        font-size: 28px;
    }
    .site-logo span {
        color: #3b82f6;
    }

    /* تخصيص القوائم لتناسب الموبايل والشاشات الكبيرة */
    .nav-link {
        color: #334155 !important;
        font-weight: 600;
        transition: color 0.3s ease;
        padding: 10px 15px !important;
        font-size: 16px;
    }

    .nav-link:hover, .nav-link.active {
        color: #10b981 !important;
    }

    .navbar-toggler {
        border-color: rgba(16, 185, 129, 0.5);
    }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2816, 185, 129, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    /* تنسيق جرس الإشعارات */
    .notification-dropdown .dropdown-menu {
        width: 320px;
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 15px;
    }
    .notif-badge {
        font-size: 0.6rem;
        padding: 0.3em 0.5em;
    }

    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 15px;
        }
        .header-icons {
            justify-content: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
    }

    /* Hero Area */
    .hero-area {
        padding: 100px 0;
        position: relative;
    }

    .hero-text h1 {
        font-size: clamp(28px, 5vw, 48px);
        font-weight: 800;
        line-height: 1.5;
        color: #0f172a;
        margin-bottom: 25px;
    }

    /* الأزرار */
    .boxed-btn {
        background: linear-gradient(45deg, #10b981, #059669);
        border-radius: 30px;
        padding: 12px 30px;
        color: #fff;
        font-weight: bold;
        border: none;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        margin-bottom: 10px;
    }

    .bordered-btn {
        border: 2px solid #10b981;
        background: transparent;
        border-radius: 30px;
        padding: 10px 30px;
        color: #10b981;
        font-weight: bold;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
        margin-bottom: 10px;
    }

    /* منطقة المحتوى لـ Laravel */
    .main-background {
        min-height: 400px;
        padding: 40px 20px;
        background: rgba(255, 255, 255, 0.65);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        margin-bottom: 50px;
    }

    /* الفوتر */
    .copyright {
        background: rgba(255, 255, 255, 0.9);
        border-top: 1px solid #e2e8f0;
        color: #64748b;
        padding: 25px 0;
        margin-top: auto;
    }

    .social-icons ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .social-icons a {
        color: #64748b;
        transition: color 0.3s ease;
        font-size: 1.2rem;
    }

    .social-icons a:hover {
        color: #10b981;
    }

    .border-start-lg {
        border-left: 1px solid #e2e8f0;
    }

    @media (max-width: 991px) {
        .border-start-lg {
            border-left: none;
        }
    }
</style>
</head>
<body>

<canvas id="bg-canvas"></canvas>

<header class="top-header-area">
    <div class="container-fluid px-3 px-lg-5">
        <nav class="navbar navbar-expand-lg">
            <a class="navbar-brand site-logo m-0 p-0" href="/">
                <h1>Freelancer<span>Pro</span></h1>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainMenu">
                <ul class="navbar-nav mx-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="/about">من نحن</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">الصفحات</a>
                        <ul class="dropdown-menu text-end border-0 shadow-sm">
                            <li><a class="dropdown-item" href="/Projects">المشاريع</a></li>
                            <li><a class="dropdown-item" href="/Services">الخدمات</a></li>
                            <li><a class="dropdown-item" href="/Works">الأعمال</a></li>
                            <li><a class="dropdown-item" href="/contact">تواصل معنا</a></li>
                        </ul>
                    </li>

                    @guest
                        <li class="nav-item"><a class="nav-link" href="/register">تسجيل كمستقل</a></li>
                        <li class="nav-item"><a class="nav-link" href="/register">تسجيل كصاحب مشاريع</a></li>
                    @endguest

                    @auth
                        <li class="nav-item">
                            @if(auth()->user()->role === 'freelancer')
                                <a class="nav-link fw-bold" href="{{ route('freelancer.dashboard') }}">
                                    <i class="fas fa-user-circle"></i> ملف المستقل
                                </a>
                            @elseif(auth()->user()->role === 'client')
                                <a class="nav-link fw-bold" href="{{ route('client.dashboard') }}">
                                    <i class="fas fa-user-tie"></i> ملف العميل
                                </a>
                            @elseif(auth()->user()->role === 'admin')
                                <a class="nav-link fw-bold text-primary" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-user-shield"></i> لوحة الإدارة
                                </a>
                            @endif
                        </li>
                    @endauth

                    <li class="nav-item"><a class="nav-link" href="/top-rated">الأعلى تقييم</a></li>
                </ul>

                <div class="header-icons d-flex gap-4 align-items-center ms-lg-3 ps-lg-3 border-start-lg">
                    @auth
                        <div class="dropdown notification-dropdown">
                            <a class="text-dark fs-5 position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notif-badge">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu shadow border-0 text-end rounded-4 p-3 dropdown-menu-end">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">الإشعارات</h6>
                                @forelse(auth()->user()->notifications->take(5) as $notification)
                                    <li class="mb-3 small border-bottom pb-2">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="{{ $notification->data['icon'] ?? 'fas fa-info-circle text-primary' }} mt-1"></i>
                                            <div>
                                                <div class="fw-bold">{{ $notification->data['title'] }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $notification->data['project'] ?? '' }}</div>
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="text-center py-2 text-muted small">لا توجد إشعارات حالياً</li>
                                @endforelse
                            </ul>
                        </div>

                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link text-danger p-0 border-0" title="تسجيل الخروج">
                                <i class="fas fa-sign-out-alt fs-5"></i>
                            </button>
                        </form>
                    @endauth

                    <a class="text-dark fs-5" href="#"><i class="fas fa-shopping-cart"></i></a>
                    <a class="text-dark fs-5" href="#"><i class="fas fa-search"></i></a>
                </div>
            </div>
        </nav>
    </div>
</header>

<section class="hero-area">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 text-center">
                <div class="hero-text">
                    <h1>نفذ مشروعك بسهولة مع<br>أفضل المستقلين في الوطن العربي</h1>
                    <p class="lead text-muted mb-4 fs-6 fs-md-5">نوفر لك بيئة آمنة واحترافية لتحويل أفكارك إلى واقع، مع شبكة متكاملة من الخبراء.</p>
                    <div class="hero-btns mt-4 d-flex flex-wrap justify-content-center gap-2 gap-md-3">
                        <a href="/Projects" class="boxed-btn"><i class="fas fa-briefcase me-2"></i> تصفح المشاريع</a>
                        <a href="/contact" class="bordered-btn"><i class="fas fa-envelope me-2"></i> تواصل معنا</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="main-background shadow-sm">
        <div class="p-4 text-center">
            @yield('content')
        </div>
    </div>
</div>

<footer class="copyright">
    <div class="container">
        <div class="row align-items-center text-center text-lg-start">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <p class="m-0 text-dark">حقوق النشر &copy; 2026 - Youssef almajek</p>
            </div>
            <div class="col-lg-6">
                <div class="social-icons">
                    <ul>
                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                        <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
    // 1. إشعارات السيشن
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'تم بنجاح',
            text: "{{ session('success') }}",
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    @endif

    // 2. إعداد Pusher - تم تحديث المفاتيح من ملفك الـ .env
    var pusher = new Pusher('7b3a7562c0aea93ec1a1', {
        cluster: 'eu',
        forceTLS: true
    });

    var channel = pusher.subscribe('chat-channel');
    channel.bind('new-message', function(data) {
        Swal.fire({
            icon: 'info',
            title: 'رسالة جديدة من ' + (data.message.user_name || 'مستخدم'),
            html: data.message.content,
            toast: true,
            position: 'top-start',
            showConfirmButton: false,
            timer: 8000,
            timerProgressBar: true
        });

        let badge = document.querySelector('.notif-badge');
        if(badge) {
            let count = parseInt(badge.innerText) || 0;
            badge.innerText = count + 1;
        }
    });

    // 3. كود الأنيميشن للخلفية
    const canvas = document.getElementById('bg-canvas');
    const ctx = canvas.getContext('2d');
    let width, height;

    function resizeCanvas() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    const freelanceSkills = [
        "UI/UX Design", "Laravel", "React.js", "SEO", "Copywriting",
        "تصميم جرافيك", "Video Editing", "Mobile Apps", "ترجمة",
        "Digital Marketing", "WordPress", "Python", "Data Analysis",
        "Voice Over", "3D Animation", "برمجة ويب", "إدارة حسابات",
        "Flutter", "Node.js", "Branding"
    ];

    class SkillTag {
        constructor() {
            this.init();
            this.x = Math.random() * width;
            this.y = Math.random() * height;
        }
        init() {
            this.text = freelanceSkills[Math.floor(Math.random() * freelanceSkills.length)];
            this.fontSize = Math.random() * 4 + 14;
            ctx.font = `600 ${this.fontSize}px 'Cairo', sans-serif`;
            this.textWidth = ctx.measureText(this.text).width;
            this.padding = 15;
            this.width = this.textWidth + (this.padding * 2);
            this.height = this.fontSize + (this.padding * 2);
            this.x = Math.random() > 0.5 ? -this.width : width + this.width;
            this.y = Math.random() * height;
            this.speedX = (Math.random() - 0.5) * 0.8;
            this.speedY = (Math.random() - 0.5) * 0.8;
            const colors = [{ r: 16, g: 185, b: 129 }, { r: 59, g: 130, b: 246 }, { r: 5, g: 150, b: 105 }];
            this.color = colors[Math.floor(Math.random() * colors.length)];
            this.alpha = 0;
            this.maxAlpha = Math.random() * 0.4 + 0.2;
            this.fadingIn = true;
            this.fadingOut = false;
            this.lifeTime = Math.random() * 500 + 500;
        }
        update() {
            this.x += this.speedX; this.y += this.speedY;
            if (this.fadingIn) { this.alpha += 0.005; if (this.alpha >= this.maxAlpha) this.fadingIn = false; }
            else if (this.fadingOut) { this.alpha -= 0.005; if (this.alpha <= 0) this.init(); }
            else { this.lifeTime--; if (this.lifeTime <= 0) this.fadingOut = true; }
        }
        draw() {
            if (this.alpha <= 0) return;
            ctx.fillStyle = `rgba(255, 255, 255, ${this.alpha * 0.8})`;
            ctx.strokeStyle = `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, ${this.alpha})`;
            this.drawRoundRect(this.x, this.y, this.width, this.height, this.height / 2);
            ctx.fill(); ctx.stroke();
            ctx.fillStyle = `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, ${this.alpha * 1.5})`;
            ctx.font = `600 ${this.fontSize}px 'Cairo', sans-serif`;
            ctx.textAlign = "center"; ctx.textBaseline = "middle";
            ctx.fillText(this.text, this.x + this.width / 2, this.y + this.height / 2);
        }
        drawRoundRect(x, y, w, h, radius) {
            ctx.beginPath(); ctx.moveTo(x + radius, y); ctx.lineTo(x + w - radius, y);
            ctx.quadraticCurveTo(x + w, y, x + w, y + radius); ctx.lineTo(x + w, y + h - radius);
            ctx.quadraticCurveTo(x + w, y + h, x + w - radius, y + h); ctx.lineTo(x + radius, y + h);
            ctx.quadraticCurveTo(x, y + h, x, y + h - radius); ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y); ctx.closePath();
        }
    }

    const tagsCount = Math.min(Math.floor((width * height) / 45000), 25);
    const tags = Array.from({ length: tagsCount }, () => new SkillTag());

    function animate() {
        ctx.clearRect(0, 0, width, height);
        for (let i = 0; i < tags.length; i++) {
            for (let j = i + 1; j < tags.length; j++) {
                const tagA = tags[i];
                const tagB = tags[j];
                const cxA = tagA.x + tagA.width / 2;
                const cyA = tagA.y + tagA.height / 2;
                const cxB = tagB.x + tagB.width / 2;
                const cyB = tagB.y + tagB.height / 2;
                const dx = cxA - cxB;
                const dy = cyA - cyB;
                const distance = Math.sqrt(dx * dx + dy * dy);
                if (distance < 180) {
                    const lineAlpha = (1 - (distance / 180)) * Math.min(tagA.alpha, tagB.alpha);
                    ctx.beginPath(); ctx.moveTo(cxA, cyA); ctx.lineTo(cxB, cyB);
                    ctx.strokeStyle = `rgba(16, 185, 129, ${lineAlpha * 0.5})`;
                    ctx.lineWidth = 1; ctx.stroke();
                }
            }
        }
        tags.forEach(tag => { tag.update(); tag.draw(); });
        requestAnimationFrame(animate);
    }

    window.addEventListener('load', function() {
        animate();
    });
</script>
</body>
</html>
