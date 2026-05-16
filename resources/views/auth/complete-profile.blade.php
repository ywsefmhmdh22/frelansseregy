@extends('layouts.master')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #10b981 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
    }

    body { background-color: #f8fafc; font-family: 'Cairo', sans-serif; }

    .completion-card {
        border-radius: 2rem;
        border: none;
        backdrop-filter: blur(10px);
        background: var(--glass-bg);
    }

    /* تخصيص Select2 ليتماشى مع التصميم */
    .select2-container--default .select2-selection--multiple {
        border: none !important;
        background-color: #f1f5f9 !important;
        border-radius: 12px !important;
        padding: 5px !important;
        min-height: 50px;
    }
    .select2-container--default .select2-selection__choice {
        background-color: #3b82f6 !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 4px 10px !important;
        font-size: 0.9rem;
    }
    .select2-container--default .select2-selection__choice__remove {
        color: white !important;
        margin-left: 5px !important;
    }

    .form-label { color: #475569; font-weight: 700; font-size: 0.95rem; margin-bottom: 0.5rem; }
    .form-control { border-radius: 12px; padding: 12px 15px; transition: all 0.3s; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); border-color: #3b82f6; }

    .border-dashed {
        border: 2px dashed #cbd5e1 !important;
        background: #f8fafc;
        transition: 0.3s;
        cursor: pointer;
    }
    .border-dashed:hover { border-color: #3b82f6 !important; background: #f1f7ff; }

    .submit-btn {
        background: var(--primary-gradient);
        border-radius: 15px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        letter-spacing: 0.5px;
    }
    .submit-btn:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 12px 24px rgba(59, 130, 246, 0.3); }

    .icon-pulse { animation: pulse 2s infinite; }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
</style>

<div class="container py-5" style="direction: rtl;">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="completion-card bg-white shadow-xl overflow-hidden animate__animated animate__fadeIn">
                <div class="top-accent-bar" style="height: 8px; background: var(--primary-gradient);"></div>

                <div class="p-4 p-md-5">
                    <div class="text-center mb-5">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 icon-pulse" style="width: 90px; height: 90px;">
                            <i class="fas fa-user-check fs-1"></i>
                        </div>
                        <h2 class="fw-extrabold text-dark">إكمال الملف المهني</h2>
                        <p class="text-muted fs-5">خطوة واحدة تفصلك عن الانضمام لنخبة المبرمجين والمبدعين</p>
                    </div>

                    {{-- التنبيهات --}}
                    @if(!auth()->user()->verification_status)
                        <div class="alert border-0 shadow-sm rounded-4 mb-4 p-4 text-center animate__animated animate__headShake" style="background: #eef2ff;">
                            <i class="fas fa-rocket fa-3x mb-3 text-primary"></i>
                            <h4 class="fw-bold text-primary">أهلاً بك يا بطل!</h4>
                            <p class="mb-0 text-secondary">حسابك قيد التجهيز. بانتظار إشارة الإدارة لتبدأ برفع إبداعاتك.</p>
                        </div>
                    @endif

                    @if(auth()->user()->verification_status == 'pending')
                        <div class="alert border-0 shadow-sm rounded-4 mb-4 p-4 text-center" style="background: #fffbeb;">
                            <div class="spinner-grow text-warning mb-3" role="status"></div>
                            <h4 class="fw-bold text-warning-emphasis">جاري المراجعة بعناية</h4>
                            <p class="mb-0">فريقنا يراجع بياناتك الآن لضمان جودة المنصة. سيصلك إشعار قريباً!</p>
                        </div>
                    @endif

                    @if(auth()->user()->verification_status == 'verified' || auth()->user()->verification_status == 'rejected' || !auth()->user()->verification_status)
                        <form id="uploadForm" method="POST" action="{{ route('profile.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="section-title d-flex align-items-center mb-4">
                                <span class="bg-primary text-white rounded-pill px-3 py-1 me-2 fw-bold small">1</span>
                                <h5 class="mb-0 fw-bold text-dark">الهوية والبيانات الشخصية</h5>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">رقم الهاتف النشط</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-phone-alt"></i></span>
                                        <input type="text" name="phone" value="{{ auth()->user()->phone }}" class="form-control bg-light border-0 shadow-sm" placeholder="01xxxxxxxxx" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">الدولة</label>
                                    <input type="text" name="country" value="{{ auth()->user()->country }}" class="form-control bg-light border-0 shadow-sm" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">التخصصات والمهارات الاحترافية (يمكنك اختيار من القائمة أو كتابة تخصصك يدويًا والضغط على Enter) <span class="text-danger">*</span></label>
                                <select id="specialization_select" name="skills[]" class="form-control select2-multiple" multiple="multiple" required>
                                    @php
                                        $userSkills = is_array(auth()->user()->skills) ? auth()->user()->skills : json_decode(auth()->user()->skills ?? '[]', true);
                                    @endphp

                                    <!-- 1. تطوير الويب - الخلفية (Backend Development) -->
                                    <optgroup label="تطوير الويب - الخلفية (Backend Development)">
                                        <option value="PHP / Laravel" {{ in_array('PHP / Laravel', $userSkills) ? 'selected' : '' }}>PHP / Laravel</option>
                                        <option value="Node.js / Express" {{ in_array('Node.js / Express', $userSkills) ? 'selected' : '' }}>Node.js / Express</option>
                                        <option value="NestJS" {{ in_array('NestJS', $userSkills) ? 'selected' : '' }}>NestJS (TypeScript)</option>
                                        <option value="Python / Django" {{ in_array('Python / Django', $userSkills) ? 'selected' : '' }}>Python / Django</option>
                                        <option value="Python / FastAPI" {{ in_array('Python / FastAPI', $userSkills) ? 'selected' : '' }}>Python / FastAPI</option>
                                        <option value="ASP.NET Core" {{ in_array('ASP.NET Core', $userSkills) ? 'selected' : '' }}>ASP.NET Core (C#)</option>
                                        <option value="Java / Spring Boot" {{ in_array('Java / Spring Boot', $userSkills) ? 'selected' : '' }}>Java / Spring Boot</option>
                                        <option value="Ruby on Rails" {{ in_array('Ruby on Rails', $userSkills) ? 'selected' : '' }}>Ruby on Rails</option>
                                        <option value="Golang (Go)" {{ in_array('Golang (Go)', $userSkills) ? 'selected' : '' }}>Golang (Go)</option>
                                    </optgroup>

                                    <!-- 2. تطوير الويب - الواجهة الأمامية (Frontend Development) -->
                                    <optgroup label="تطوير الويب - الواجهة الأمامية (Frontend Development)">
                                        <option value="React.js" {{ in_array('React.js', $userSkills) ? 'selected' : '' }}>React.js</option>
                                        <option value="Next.js" {{ in_array('Next.js', $userSkills) ? 'selected' : '' }}>Next.js</option>
                                        <option value="Vue.js" {{ in_array('Vue.js', $userSkills) ? 'selected' : '' }}>Vue.js</option>
                                        <option value="Nuxt.js" {{ in_array('Nuxt.js', $userSkills) ? 'selected' : '' }}>Nuxt.js</option>
                                        <option value="Angular" {{ in_array('Angular', $userSkills) ? 'selected' : '' }}>Angular</option>
                                        <option value="Svelte" {{ in_array('Svelte', $userSkills) ? 'selected' : '' }}>Svelte</option>
                                        <option value="HTML5 / CSS3 / JavaScript" {{ in_array('HTML5 / CSS3 / JavaScript', $userSkills) ? 'selected' : '' }}>HTML5 / CSS3 / JavaScript</option>
                                        <option value="TypeScript" {{ in_array('TypeScript', $userSkills) ? 'selected' : '' }}>TypeScript</option>
                                        <option value="Tailwind CSS" {{ in_array('Tailwind CSS', $userSkills) ? 'selected' : '' }}>Tailwind CSS</option>
                                        <option value="Bootstrap" {{ in_array('Bootstrap', $userSkills) ? 'selected' : '' }}>Bootstrap</option>
                                    </optgroup>

                                    <!-- 3. تطبيقات الموبايل (Mobile App Development) -->
                                    <optgroup label="تطبيقات الموبايل (Mobile App Development)">
                                        <option value="Flutter" {{ in_array('Flutter', $userSkills) ? 'selected' : '' }}>Flutter (Dart)</option>
                                        <option value="React Native" {{ in_array('React Native', $userSkills) ? 'selected' : '' }}>React Native</option>
                                        <option value="iOS (Swift)" {{ in_array('iOS (Swift)', $userSkills) ? 'selected' : '' }}>iOS Native (Swift)</option>
                                        <option value="Android (Kotlin/Java)" {{ in_array('Android (Kotlin/Java)', $userSkills) ? 'selected' : '' }}>Android Native (Kotlin / Java)</option>
                                        <option value="Ionic / Capacitor" {{ in_array('Ionic / Capacitor', $userSkills) ? 'selected' : '' }}>Ionic / Capacitor</option>
                                    </optgroup>

                                    <!-- 4. البنية التحتية وقواعد البيانات (DevOps & Databases) -->
                                    <optgroup label="البنية التحتية وقواعد البيانات (DevOps & Databases)">
                                        <option value="MySQL / PostgreSQL" {{ in_array('MySQL / PostgreSQL', $userSkills) ? 'selected' : '' }}>MySQL / PostgreSQL</option>
                                        <option value="MongoDB / NoSQL" {{ in_array('MongoDB / NoSQL', $userSkills) ? 'selected' : '' }}>MongoDB / NoSQL</option>
                                        <option value="Redis / Memcached" {{ in_array('Redis / Memcached', $userSkills) ? 'selected' : '' }}>Redis / Memcached</option>
                                        <option value="Firebase / Supabase" {{ in_array('Firebase / Supabase', $userSkills) ? 'selected' : '' }}>Firebase / Supabase</option>
                                        <option value="Docker / Kubernetes" {{ in_array('Docker / Kubernetes', $userSkills) ? 'selected' : '' }}>Docker / Kubernetes</option>
                                        <option value="DevOps / CI-CD" {{ in_array('DevOps / CI-CD', $userSkills) ? 'selected' : '' }}>DevOps / CI-CD</option>
                                        <option value="Cloud (AWS / GCP / Azure)" {{ in_array('Cloud (AWS / GCP / Azure)', $userSkills) ? 'selected' : '' }}>Cloud (AWS / GCP / Azure)</option>
                                        <option value="Linux Administration" {{ in_array('Linux Administration', $userSkills) ? 'selected' : '' }}>Linux Administration (SysAdmin)</option>
                                    </optgroup>

                                    <!-- 5. برمجيات سطح المكتب والأنظمة المدمجة (Desktop & Embedded) -->
                                    <optgroup label="برمجة الأنظمة وسطح المكتب (Desktop & Embedded Systems)">
                                        <option value="C++ / Qt" {{ in_array('C++ / Qt', $userSkills) ? 'selected' : '' }}>C++ / Qt Applications</option>
                                        <option value="C# / WPF / WinForms" {{ in_array('C# / WPF / WinForms', $userSkills) ? 'selected' : '' }}>C# Desktop Apps</option>
                                        <option value="Electron.js" {{ in_array('Electron.js', $userSkills) ? 'selected' : '' }}>Electron.js (Cross-Platform)</option>
                                        <option value="Python (Flet/PyQt)" {{ in_array('Python (Flet/PyQt)', $userSkills) ? 'selected' : '' }}>Python GUI (Flet / PyQt)</option>
                                        <option value="Embedded Systems / C" {{ in_array('Embedded Systems / C', $userSkills) ? 'selected' : '' }}>Embedded Systems / C</option>
                                        <option value="Arduino / Raspberry Pi" {{ in_array('Arduino / Raspberry Pi', $userSkills) ? 'selected' : '' }}>إنترنت الأشياء (IoT / Arduino)</option>
                                    </optgroup>

                                    <!-- 6. الذكاء الاصطناعي وعلوم البيانات (AI & Data Science) -->
                                    <optgroup label="الذكاء الاصطناعي وتحليل البيانات (AI & Data Science)">
                                        <option value="Machine Learning" {{ in_array('Machine Learning', $userSkills) ? 'selected' : '' }}>تعلم الآلة (Machine Learning)</option>
                                        <option value="Data Analysis" {{ in_array('Data Analysis', $userSkills) ? 'selected' : '' }}>تحليل البيانات (Data Analysis)</option>
                                        <option value="Deep Learning & NLP" {{ in_array('Deep Learning & NLP', $userSkills) ? 'selected' : '' }}>الذكاء الاصطناعي ومعالجة اللغات (NLP)</option>
                                        <option value="Computer Vision" {{ in_array('Computer Vision', $userSkills) ? 'selected' : '' }}>رؤية الحاسوب (Computer Vision)</option>
                                        <option value="Python Scripting & Automation" {{ in_array('Python Scripting & Automation', $userSkills) ? 'selected' : '' }}>أتمتة المهام وبرمجة الإسكربتات</option>
                                        <option value="Web Scraping & Data Mining" {{ in_array('Web Scraping & Data Mining', $userSkills) ? 'selected' : '' }}>تجميع البيانات (Web Scraping)</option>
                                    </optgroup>

                                    <!-- 7. الأمن السيبراني واختبار الاختراق (Cybersecurity) -->
                                    <optgroup label="الأمن السيبراني والشبكات (Cybersecurity & Networks)">
                                        <option value="Penetration Testing" {{ in_array('Penetration Testing', $userSkills) ? 'selected' : '' }}>اختبار الاختراق (Penetration Testing)</option>
                                        <option value="Ethical Hacking" {{ in_array('Ethical Hacking', $userSkills) ? 'selected' : '' }}>الهكر الأخلاقي (Ethical Hacking)</option>
                                        <option value="Web/App Security Audit" {{ in_array('Web/App Security Audit', $userSkills) ? 'selected' : '' }}>فحص وتأمين الثغرات للموقع والتطبيق</option>
                                        <option value="Network Security" {{ in_array('Network Security', $userSkills) ? 'selected' : '' }}>أمن الشبكات والأنظمة</option>
                                        <option value="Reverse Engineering" {{ in_array('Reverse Engineering', $userSkills) ? 'selected' : '' }}>الهندسة العكسية (Reverse Engineering)</option>
                                    </optgroup>

                                    <!-- 8. تطوير الألعاب (Game Development) -->
                                    <optgroup label="تطوير وبرمجة الألعاب (Game Development)">
                                        <option value="Unity / C#" {{ in_array('Unity / C#', $userSkills) ? 'selected' : '' }}>Unity (C#)</option>
                                        <option value="Unreal Engine / C++" {{ in_array('Unreal Engine / C++', $userSkills) ? 'selected' : '' }}>Unreal Engine (C++)</option>
                                        <option value="Godot Engine" {{ in_array('Godot Engine', $userSkills) ? 'selected' : '' }}>Godot Engine</option>
                                    </optgroup>

                                    <!-- 9. تقنيات البلوكشين والويب 3 (Blockchain & Web3) -->
                                    <optgroup label="تقنيات البلوكشين والويب 3 (Blockchain & Web3)">
                                        <option value="Smart Contracts / Solidity" {{ in_array('Smart Contracts / Solidity', $userSkills) ? 'selected' : '' }}>العقود الذكية (Solidity / Ethereum)</option>
                                        <option value="Web3.js / Ethers.js" {{ in_array('Web3.js / Ethers.js', $userSkills) ? 'selected' : '' }}>ربط التطبيقات بالبلوكشين (Web3.js)</option>
                                    </optgroup>

                                    <!-- 10. اختبار الجودة والبرمجيات (Software Testing / QA) -->
                                    <optgroup label="فحص الجودة والتأكد من الكود (Software QA & Testing)">
                                        <option value="Manual Testing" {{ in_array('Manual Testing', $userSkills) ? 'selected' : '' }}>الفحص اليدوي (Manual Testing)</option>
                                        <option value="Automation Testing" {{ in_array('Automation Testing', $userSkills) ? 'selected' : '' }}>الفحص الآلي (Selenium / Cypress)</option>
                                        <option value="API Testing (Postman)" {{ in_array('API Testing (Postman)', $userSkills) ? 'selected' : '' }}>اختبار الـ APIs (Postman)</option>
                                    </optgroup>

                                    <!-- 11. التصميم الإبداعي وواجهات المستخدم (Design & UI/UX) -->
                                    <optgroup label="التصميم الإبداعي وواجهات المستخدم (Design & UI/UX)">
                                        <option value="UI/UX Design (Figma/Adobe XD)" {{ in_array('UI/UX Design (Figma/Adobe XD)', $userSkills) ? 'selected' : '' }}>تصميم واجهات المستخدم (UI/UX Design)</option>
                                        <option value="Graphic Design (Photoshop/Illustrator)" {{ in_array('Graphic Design (Photoshop/Illustrator)', $userSkills) ? 'selected' : '' }}>تصميم جرافيك (Graphic Design)</option>
                                        <option value="Motion Graphics & Video Editing" {{ in_array('Motion Graphics & Video Editing', $userSkills) ? 'selected' : '' }}>موشن جرافيك ومونتاج فيديو</option>
                                        <option value="Branding & Logo Design" {{ in_array('Branding & Logo Design', $userSkills) ? 'selected' : '' }}>تصميم الهويات والشعارات</option>
                                    </optgroup>

                                    <!-- 12. التصميم الهندسي والمعماري (Engineering & CAD) -->
                                    <optgroup label="التصميم الهندسي والمعماري (Engineering & CAD)">
                                        <option value="AutoCAD 2D/3D" {{ in_array('AutoCAD 2D/3D', $userSkills) ? 'selected' : '' }}>أوتوكاد (AutoCAD 2D/3D)</option>
                                        <option value="3D Modeling & Rendering" {{ in_array('3D Modeling & Rendering', $userSkills) ? 'selected' : '' }}>نمذجة ثلاثية الأبعاد وإظهار معماري</option>
                                        <option value="BIM / Revit" {{ in_array('BIM / Revit', $userSkills) ? 'selected' : '' }}>ريفت وتصميم هندسي (Revit)</option>
                                    </optgroup>

                                    <!-- 13. التسويق الرقمي والمبيعات (Digital Marketing & Sales) -->
                                    <optgroup label="التسويق الرقمي والمبيعات (Digital Marketing & Sales)">
                                        <option value="SEO (Search Engine Optimization)" {{ in_array('SEO (Search Engine Optimization)', $userSkills) ? 'selected' : '' }}>تحسين محركات البحث (SEO)</option>
                                        <option value="Social Media Management" {{ in_array('Social Media Management', $userSkills) ? 'selected' : '' }}>إدارة حملات السوشيال ميديا</option>
                                        <option value="Google & Facebook Ads" {{ in_array('Google & Facebook Ads', $userSkills) ? 'selected' : '' }}>إعلانات ممولة (Google, Meta, TikTok)</option>
                                        <option value="E-commerce Strategy" {{ in_array('E-commerce Strategy', $userSkills) ? 'selected' : '' }}>إدارة واستراتيجيات المتاجر الإلكترونية</option>
                                    </optgroup>

                                    <!-- 14. الكتابة والترجمة وصناعة المحتوى (Writing & Translation) -->
                                    <optgroup label="الكتابة والترجمة وصناعة المحتوى (Writing & Translation)">
                                        <option value="Content Writing" {{ in_array('Content Writing', $userSkills) ? 'selected' : '' }}>كتابة المحتوى والمقالات</option>
                                        <option value="Copywriting" {{ in_array('Copywriting', $userSkills) ? 'selected' : '' }}>كتابة الإعلانات والنصوص البيعية</option>
                                        <option value="Technical Writing" {{ in_array('Technical Writing', $userSkills) ? 'selected' : '' }}>الكتابة التقنية وإعداد الشروحات</option>
                                        <option value="Professional Translation" {{ in_array('Professional Translation', $userSkills) ? 'selected' : '' }}>الترجمة الاحترافية والتعريب</option>
                                    </optgroup>

                                    <!-- طباعة المهارات المخزنة مسبقاً والغير موجودة في اللستة الافتراضية (عشان تفضل مختارة) -->
                                    @foreach($userSkills as $skill)
                                        @if(!in_array($skill, [
                                            'PHP / Laravel', 'Node.js / Express', 'NestJS', 'Python / Django', 'Python / FastAPI', 'ASP.NET Core', 'Java / Spring Boot', 'Ruby on Rails', 'Golang (Go)',
                                            'React.js', 'Next.js', 'Vue.js', 'Nuxt.js', 'Angular', 'Svelte', 'HTML5 / CSS3 / JavaScript', 'TypeScript', 'Tailwind CSS', 'Bootstrap',
                                            'Flutter', 'React Native', 'iOS (Swift)', 'Android (Kotlin/Java)', 'Ionic / Capacitor',
                                            'MySQL / PostgreSQL', 'MongoDB / NoSQL', 'Redis / Memcached', 'Firebase / Supabase', 'Docker / Kubernetes', 'DevOps / CI-CD', 'Cloud (AWS / GCP / Azure)', 'Linux Administration',
                                            'C++ / Qt', 'C# / WPF / WinForms', 'Electron.js', 'Python (Flet/PyQt)', 'Embedded Systems / C', 'Arduino / Raspberry Pi',
                                            'Machine Learning', 'Data Analysis', 'Deep Learning & NLP', 'Computer Vision', 'Python Scripting & Automation', 'Web Scraping & Data Mining',
                                            'Penetration Testing', 'Ethical Hacking', 'Web/App Security Audit', 'Network Security', 'Reverse Engineering',
                                            'Unity / C#', 'Unreal Engine / C++', 'Godot Engine',
                                            'Smart Contracts / Solidity', 'Web3.js / Ethers.js',
                                            'Manual Testing', 'Automation Testing', 'API Testing (Postman)',
                                            'UI/UX Design (Figma/Adobe XD)', 'Graphic Design (Photoshop/Illustrator)', 'Motion Graphics & Video Editing', 'Branding & Logo Design',
                                            'AutoCAD 2D/3D', '3D Modeling & Rendering', 'BIM / Revit',
                                            'SEO (Search Engine Optimization)', 'Social Media Management', 'Google & Facebook Ads', 'E-commerce Strategy',
                                            'Content Writing', 'Copywriting', 'Technical Writing', 'Professional Translation'
                                        ]))
                                            <option value="{{ $skill }}" selected>{{ $skill }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">نبذة احترافية عنك</label>
                                <textarea name="bio" rows="3" class="form-control bg-light border-0 shadow-sm" placeholder="احكِ لنا عن مشاريعك وخبراتك البرمجية أو الإبداعية..." required>{{ auth()->user()->bio }}</textarea>
                            </div>

                            <div class="section-title d-flex align-items-center mb-4 mt-5">
                                <span class="bg-primary text-white rounded-pill px-3 py-1 me-2 fw-bold small">2</span>
                                <h5 class="mb-0 fw-bold text-dark">رفع صور الهوية لتوثيق الحساب</h5>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">رقم البطاقة الشخصية / جواز السفر</label>
                                <input type="text" name="id_number" value="{{ auth()->user()->id_number }}" class="form-control bg-light border-0 shadow-sm" placeholder="أدخل الرقم القومي بدقة" required>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-6 mb-3">
                                    <div class="upload-area p-3 border-dashed rounded-4 text-center" onclick="document.getElementById('id_image').click()">
                                        @if(auth()->user()->id_image)
                                            <img src="{{ Storage::disk('s3')->url(auth()->user()->id_image) }}" class="img-fluid rounded-3 mb-2" style="max-height: 100px;">
                                        @else
                                            <i class="fas fa-id-card-alt fa-2x text-muted mb-2"></i>
                                        @endif
                                        <p class="small mb-0">وجه البطاقة (Front)</p>
                                        <input type="file" id="id_image" name="id_image" class="d-none" accept="image/*" {{ auth()->user()->id_image ? '' : 'required' }}>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="upload-area p-3 border-dashed rounded-4 text-center" onclick="document.getElementById('id_image_back').click()">
                                        @if(auth()->user()->id_image_back)
                                            <img src="{{ Storage::disk('s3')->url(auth()->user()->id_image_back) }}" class="img-fluid rounded-3 mb-2" style="max-height: 100px;">
                                        @else
                                            <i class="fas fa-id-card-alt fa-2x text-muted mb-2"></i>
                                        @endif
                                        <p class="small mb-0">ظهر البطاقة (Back)</p>
                                        <input type="file" id="id_image_back" name="id_image_back" class="d-none" accept="image/*" {{ auth()->user()->id_image_back ? '' : 'required' }}>
                                    </div>
                                </div>
                            </div>

                            {{-- شريط التقدم --}}
                            <div id="progressWrapper" class="d-none mb-4 animate__animated animate__fadeIn">
                                <div class="progress" style="height: 12px; border-radius: 50px;">
                                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%;"></div>
                                </div>
                                <p id="uploadStatus" class="text-center small mt-2 fw-bold text-primary">بدء عملية الرفع السحابي الآمن...</p>
                            </div>

                            <button type="submit" id="submitBtn" class="submit-btn w-100 py-3 fs-5 shadow border-0 text-white fw-bold">
                                حفظ البيانات وتوثيق الحساب <i class="fas fa-shield-alt ms-2"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
$(document).ready(function() {
    $('#specialization_select').select2({
        placeholder: "اختر تخصصاتك المهنية والبرمجية أو اكتب مهارة جديدة",
        allowClear: true,
        width: '100%',
        dir: "rtl",
        tags: true, // هتا تسمح للفريلانسر يكتب أي مهارة يدوياً وتتحول لـ Tag عند الضغط على Enter
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') {
                return null;
            }
            return {
                id: term,
                text: term,
                newTag: true // علامة توضح أن التاج جديد ومكتوب يدوياً
            }
        }
    });

    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#submitBtn');
        const formData = new FormData(this);
        const wrapper = $('#progressWrapper');
        const bar = $('#progressBar');
        const status = $('#uploadStatus');

        btn.prop('disabled', true).html('جاري معالجة البيانات سحابياً.. <i class="fas fa-circle-notch fa-spin ms-2"></i>');
        wrapper.removeClass('d-none');

        axios.post(this.action, formData, {
            onUploadProgress: (progressEvent) => {
                let percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                bar.css('width', percent + '%');
                status.text(`جاري الرفع إلى الـ Cloud السحابي (${percent}%)`);
            }
        })
        .then(res => {
            status.html('<i class="fas fa-check-circle me-1"></i> تم الحفظ والرفع بنجاح!');
            btn.addClass('bg-success').html('تم بنجاح!');
            setTimeout(() => {
                window.location.href = res.data.redirect_to || '/client/dashboard';
            }, 1500);
        })
        .catch(err => {
            btn.prop('disabled', false).html('حاول مرة أخرى <i class="fas fa-redo ms-2"></i>');
            wrapper.addClass('d-none');
            alert(err.response?.data?.message || "حدث خطأ أثناء الرفع");
        });
    });
});
</script>
@endsection
