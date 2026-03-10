 @extends('layouts.master')

@section('content')

<section class="services-section py-5 bg-light">
    <div class="container">
        {{-- عرض رسائل النجاح أو الخطأ --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 text-end" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 text-end" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="text-center mb-5 animate__animated animate__fadeInDown">
            <h2 class="fw-black text-dark position-relative d-inline-block">
                خدمات المبدعين
                <span class="position-absolute bottom-0 start-0 w-100 bg-primary" style="height: 4px; border-radius: 2px;"></span>
            </h2>
            <p class="text-secondary mt-3 fs-5">تصفح أفضل الخدمات التي يقدمها المستقلون لإنجاز أعمالك</p>
        </div>

        <div class="row g-4 justify-content-center">
            @forelse($allData as $service)
                <div class="col-md-6 col-lg-4 col-xl-3 animate__animated animate__fadeInUp">
                    <div class="service-card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white d-flex flex-column">
                        {{-- صورة الخدمة --}}
                        <div class="position-relative overflow-hidden" style="height: 180px;">
                            <img src="{{ asset($service->image) }}"
                                 alt="{{ $service->title }}"
                                 class="w-100 h-100 object-fit-cover transition-transform">
                            <div class="position-absolute top-0 end-0 p-3">
                                <span class="badge bg-primary text-white shadow-sm rounded-pill fw-bold px-3 py-2">
                                    {{ number_format($service->price, 0) }} ج.م
                                </span>
                            </div>
                        </div>

                        {{-- تفاصيل الخدمة --}}
                        <div class="card-body p-4 text-end d-flex flex-column" dir="rtl">
                            <h5 class="fw-bold mb-2 text-dark">{{ $service->title }}</h5>
                            <p class="text-muted small mb-3 line-clamp-2 flex-grow-1">
                                {{ Str::limit($service->description, 80) }}
                            </p>

                            <div class="d-flex align-items-center mb-3">
                                <img src="{{ $service->user->profile_image ? asset('storage/' . $service->user->profile_image) : 'https://ui-avatars.com/api/?name='.urlencode($service->user->name).'&background=10b981&color=fff' }}"
                                     class="rounded-circle me-2" style="width: 25px; height: 25px; object-fit: cover;">
                                <small class="fw-bold text-secondary mx-1">{{ $service->user->name }}</small>
                            </div>

                            <hr class="opacity-25 mt-0">

                            <div class="d-grid gap-2">
                                {{-- تم تغيير الزرار لرابط مباشر لصفحة الدفع وإلغاء المودال --}}
                                <a href="{{ route('services.checkout', $service->id) }}"
                                   class="btn btn-success rounded-pill fw-bold shadow-sm py-2">
                                    <i class="fas fa-shopping-cart ms-1"></i> شراء الآن
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-4x text-light mb-3"></i>
                    <h4 class="text-muted">لا توجد خدمات متاحة حالياً</h4>
                </div>
            @endforelse
        </div>
    </div>
</section>

<style>
    .fw-black { font-weight: 900; }
    .service-card { transition: all 0.3s ease; border: 1px solid rgba(0,0,0,0.05) !important; }
    .service-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .object-fit-cover { object-fit: cover; }
    .transition-transform { transition: transform 0.5s ease; }
    .service-card:hover .transition-transform { transform: scale(1.08); }

    /* منع أي تداخل قد يسببه الجافاسكريبت القديم */
    .service-card a { text-decoration: none; }
</style>

@endsection
