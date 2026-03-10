@extends('layouts.master')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card p-4 shadow-sm" style="background: rgba(255, 255, 255, 0.9); border-radius: 20px; border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(10px);">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-dark mb-0">مركز الإشعارات 🔔</h4>
                    <a href="{{ route('notifications.markAllRead') }}" class="btn btn-sm btn-soft-success rounded-pill px-3">تحديد الكل كمقروء</a>
                </div>

                <div class="notif-list">
                    @forelse(auth()->user()->notifications as $notif)
                        <div class="p-3 mb-3 border-bottom d-flex gap-3 align-items-start {{ $notif->read_at ? 'opacity-75' : 'bg-light rounded-3' }}">
                            <div class="icon-circle bg-primary text-white p-2 rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="{{ $notif->data['icon'] ?? 'fas fa-bell' }}"></i>
                            </div>
                            <div class="flex-grow-1 text-end">
                                <h6 class="mb-1 fw-bold text-dark">{{ $notif->data['title'] ?? 'تحديث جديد' }}</h6>
                                <p class="text-muted small mb-1">{{ $notif->data['message'] ?? '' }}</p>
                                <span class="extra-small text-muted">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fs-1 text-muted mb-3"></i>
                            <p class="text-muted">لا توجد إشعارات حتى الآن.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
