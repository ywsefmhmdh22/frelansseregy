@extends('layouts.master')

@section('content')
<style>
    /* خلفية فخمة بتدرج سينمائي وعمق بصري */
    body {
        background: radial-gradient(circle at 0% 0%, #1a2a6c 0%, #b21f1f 0%, #050914 100%);
        background-attachment: fixed;
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
        font-family: 'Cairo', sans-serif;
    }

    #particles-js {
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: 1;
    }

    .review-container {
        position: relative;
        z-index: 10;
        padding-top: 60px;
        padding-bottom: 80px;
    }

    /* كارت التقييم الرئيسي بتصميم زجاجي ملكي */
    .glass-review-card {
        background: rgba(10, 15, 28, 0.7);
        backdrop-filter: blur(40px);
        -webkit-backdrop-filter: blur(40px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 50px;
        box-shadow: 0 50px 120px rgba(0, 0, 0, 0.8), inset 0 0 20px rgba(255, 255, 255, 0.02);
        color: white;
        position: relative;
        overflow: hidden;
    }

    .glass-review-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: linear-gradient(135deg, rgba(0, 210, 255, 0.1), transparent);
        border-radius: 0 50px 0 100%;
    }

    /* كروت معايير التقييم - تصميم عصري جداً */
    .rating-criterion {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.01));
        padding: 35px 25px;
        border-radius: 35px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .rating-criterion:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: #00d2ff;
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 25px 60px rgba(0, 210, 255, 0.15);
    }

    .criterion-icon {
        width: 50px;
        height: 50px;
        background: rgba(0, 210, 255, 0.1);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #00d2ff;
        margin-bottom: 20px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    /* نجوم التقييم - ألوان كريستالية */
    .star-rating-v2 {
        display: flex;
        flex-direction: row-reverse;
        gap: 10px;
    }
    .star-rating-v2 input { display: none; }
    .star-rating-v2 label {
        font-size: 2.4rem;
        color: rgba(255, 255, 255, 0.05);
        cursor: pointer;
        transition: all 0.4s ease;
    }
    .star-rating-v2 label:hover,
    .star-rating-v2 label:hover ~ label,
    .star-rating-v2 input:checked ~ label {
        color: #ffaa00; /* تدرج ذهبي برتقالي */
        text-shadow: 0 0 25px rgba(255, 170, 0, 0.8), 0 0 50px rgba(255, 170, 0, 0.3);
        transform: rotate(-10deg) scale(1.15);
    }

    /* هوية المستقل */
    .freelancer-hero {
        background: linear-gradient(135deg, rgba(0, 210, 255, 0.05) 0%, rgba(157, 78, 221, 0.05) 100%);
        border-radius: 35px;
        padding: 30px;
        margin-bottom: 50px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        text-align: center;
    }

    .avatar-wrapper {
        position: relative;
        margin-bottom: 15px;
    }

    .avatar-glow {
        width: 100px;
        height: 100px;
        border-radius: 35px;
        border: 2px solid rgba(0, 210, 255, 0.3);
        padding: 5px;
        background: rgba(0, 0, 0, 0.3);
        object-fit: cover;
    }

    .online-indicator {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 15px;
        height: 15px;
        background: #2ecc71;
        border: 3px solid #0a0f1c;
        border-radius: 50%;
        box-shadow: 0 0 10px #2ecc71;
    }

    /* الأزرار الفخمة */
    .btn-submit-premium {
        background: linear-gradient(90deg, #00d2ff, #9d4edd, #00d2ff);
        background-size: 200% auto;
        border: none;
        color: white;
        padding: 22px;
        border-radius: 25px;
        font-weight: 800;
        font-size: 1.3rem;
        transition: 0.5s;
        box-shadow: 0 15px 40px rgba(157, 78, 221, 0.3);
    }

    .btn-submit-premium:hover {
        background-position: right center;
        transform: translateY(-5px);
        box-shadow: 0 20px 50px rgba(0, 210, 255, 0.5);
    }

    .form-control-premium {
        background: rgba(0, 0, 0, 0.4) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #fff !important;
        border-radius: 25px !important;
        padding: 25px !important;
        font-size: 1.1rem;
    }

    .form-control-premium:focus {
        border-color: #9d4edd !important;
        box-shadow: 0 0 30px rgba(157, 78, 221, 0.15) !important;
    }

    /* صندوق معلومات النقاط */
    .points-info-box {
        background: rgba(255, 170, 0, 0.03);
        border: 1px solid rgba(255, 170, 0, 0.15);
        border-radius: 30px;
        padding: 25px;
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 40px;
    }

    .badge-points {
        background: linear-gradient(135deg, #ffaa00, #ff7b00);
        color: white;
        font-weight: 900;
        width: 65px;
        height: 65px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 22px;
        font-size: 1.5rem;
        box-shadow: 0 10px 25px rgba(255, 123, 0, 0.4);
    }

    .section-title {
        color: #ffffff;
        font-weight: 700;
        margin-bottom: 10px;
        font-size: 1.15rem;
        letter-spacing: -0.5px;
    }

    .label-accent {
        color: rgba(255, 255, 255, 0.5);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .hover-white:hover {
        color: #ffffff !important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>

<div id="particles-js"></div>

<div class="container review-container">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-11">
            <div class="glass-review-card p-4 p-md-5">

                <div class="text-center mb-5">
                    <p class="label-accent mb-2">Final Certification</p>
                    <h1 class="fw-bold text-white mb-2" style="font-size: 2.8rem; letter-spacing: -1px;">استلام المشروع والتقييم</h1>
                    <div style="width: 60px; height: 4px; background: #00d2ff; margin: 20px auto; border-radius: 10px;"></div>
                </div>

                <div class="freelancer-hero">
                    <div class="avatar-wrapper">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($project->freelancer->name ?? 'F') }}&background=0a0f1c&color=00d2ff&size=256" class="avatar-glow" alt="Freelancer Avatar">
                        <div class="online-indicator"></div>
                    </div>
                    <h3 class="text-white fw-bold mb-1">{{ $project->freelancer->name }}</h3>
                    <p class="text-white-50 mb-0">خبير تقني موثق لدى المنصة</p>
                </div>

                <form action="{{ route('projects.complete', $project->id) }}" method="POST">
                    @csrf

                    <h5 class="text-white-50 mb-4 text-center small text-uppercase fw-bold" style="letter-spacing: 3px;">Global Performance Metrics</h5>

                    <div class="row g-4 mb-5">
                        <!-- دقة العمل -->
                        <div class="col-md-6 col-xl-3">
                            <div class="rating-criterion">
                                <div class="criterion-icon"><i class="fas fa-gem"></i></div>
                                <label class="section-title">جودة العمل</label>
                                <div class="star-rating-v2">
                                    <input type="radio" id="quality-5" name="rating_quality" value="5" required/><label for="quality-5">★</label>
                                    <input type="radio" id="quality-4" name="rating_quality" value="4"/><label for="quality-4">★</label>
                                    <input type="radio" id="quality-3" name="rating_quality" value="3"/><label for="quality-3">★</label>
                                    <input type="radio" id="quality-2" name="rating_quality" value="2"/><label for="quality-2">★</label>
                                    <input type="radio" id="quality-1" name="rating_quality" value="1"/><label for="quality-1">★</label>
                                </div>
                            </div>
                        </div>

                        <!-- وقت التسليم -->
                        <div class="col-md-6 col-xl-3">
                            <div class="rating-criterion">
                                <div class="criterion-icon"><i class="fas fa-bolt"></i></div>
                                <label class="section-title">سرعة التنفيذ</label>
                                <div class="star-rating-v2">
                                    <input type="radio" id="time-5" name="rating_time" value="5" required/><label for="time-5">★</label>
                                    <input type="radio" id="time-4" name="rating_time" value="4"/><label for="time-4">★</label>
                                    <input type="radio" id="time-3" name="rating_time" value="3"/><label for="time-3">★</label>
                                    <input type="radio" id="time-2" name="rating_time" value="2"/><label for="time-2">★</label>
                                    <input type="radio" id="time-1" name="rating_time" value="1"/><label for="time-1">★</label>
                                </div>
                            </div>
                        </div>

                        <!-- حسن التعامل -->
                        <div class="col-md-6 col-xl-3">
                            <div class="rating-criterion">
                                <div class="criterion-icon"><i class="fas fa-crown"></i></div>
                                <label class="section-title">الاحترافية</label>
                                <div class="star-rating-v2">
                                    <input type="radio" id="behavior-5" name="rating_behavior" value="5" required/><label for="behavior-5">★</label>
                                    <input type="radio" id="behavior-4" name="rating_behavior" value="4"/><label for="behavior-4">★</label>
                                    <input type="radio" id="behavior-3" name="rating_behavior" value="3"/><label for="behavior-3">★</label>
                                    <input type="radio" id="behavior-2" name="rating_behavior" value="2"/><label for="behavior-2">★</label>
                                    <input type="radio" id="behavior-1" name="rating_behavior" value="1"/><label for="behavior-1">★</label>
                                </div>
                            </div>
                        </div>

                        <!-- التواصل -->
                        <div class="col-md-6 col-xl-3">
                            <div class="rating-criterion">
                                <div class="criterion-icon"><i class="fas fa-satellite-dish"></i></div>
                                <label class="section-title">التواصل</label>
                                <div class="star-rating-v2">
                                    <input type="radio" id="comm-5" name="rating_communication" value="5" required/><label for="comm-5">★</label>
                                    <input type="radio" id="comm-4" name="rating_communication" value="4"/><label for="comm-4">★</label>
                                    <input type="radio" id="comm-3" name="rating_communication" value="3"/><label for="comm-3">★</label>
                                    <input type="radio" id="comm-2" name="rating_communication" value="2"/><label for="comm-2">★</label>
                                    <input type="radio" id="comm-1" name="rating_communication" value="1"/><label for="comm-1">★</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="section-title">شهادتك في حق المستقل</label>
                        <textarea name="review_comment" class="form-control form-control-premium" rows="5" placeholder="أخبر العالم عن تجربتك المميزة مع هذا المستقل..." required></textarea>
                    </div>

                    <div class="points-info-box shadow-lg">
                        <div class="badge-points">+8</div>
                        <div>
                            <h5 class="text-white fw-bold mb-1">نقاط المستقل المثالي (Premium Rewards)</h5>
                            <p class="mb-0 text-white-50 small">بتقييمك الإيجابي، تساهم في منح المستقل 8 نقاط تميز ترفع ترتيبه عالمياً وتفتح له ميزات حصرية.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit-premium w-100 mb-4">
                        إتمام الصفقة وتحويل المستحقات <i class="fas fa-paper-plane ms-2"></i>
                    </button>

                    <p class="text-center text-white-50 small mb-0">
                        <i class="fas fa-shield-alt text-success me-1"></i> حماية كاملة للمستحقات المالية - نظام دفع ذكي ومؤمن
                    </p>
                </form>

            </div>

            <div class="text-center mt-5">
                <a href="{{ route('projects.show', $project->id) }}" class="text-white-50 text-decoration-none hover-white transition-all">
                    <i class="fas fa-chevron-right me-2"></i> العودة لتفاصيل المشروع
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        particlesJS("particles-js", {
            "particles": {
                "number": { "value": 50, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": ["#00d2ff", "#9d4edd", "#ffaa00"] },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.2, "random": true },
                "size": { "value": 2, "random": true },
                "line_linked": { "enable": true, "distance": 150, "color": "#ffffff", "opacity": 0.05, "width": 1 },
                "move": { "enable": true, "speed": 0.8, "direction": "none", "random": true, "straight": false, "out_mode": "out", "bounce": false }
            },
            "interactivity": {
                "events": {
                    "onhover": { "enable": true, "mode": "bubble" },
                    "onclick": { "enable": true, "mode": "push" }
                },
                "modes": {
                    "bubble": { "distance": 150, "size": 4, "duration": 2, "opacity": 1, "speed": 3 }
                }
            },
            "retina_detect": true
        });
    });
</script>
@endsection
