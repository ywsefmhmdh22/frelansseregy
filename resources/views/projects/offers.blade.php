@extends('layouts.master')

@section('content')
<div class="container py-5 text-end" dir="rtl">
    <h3>العروض المقدمة لمشروع: {{ $project->title }}</h3>
    @foreach($offers as $offer)
    <div class="glass-card p-3 mb-3 border">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold">{{ $offer->user->name }}</h6>
                <p class="small text-muted">{{ $offer->comment }}</p>
            </div>
            <div class="text-start">
                <span class="badge bg-soft-success text-success">{{ $offer->amount }} ج.م</span>
                <button class="btn btn-sm btn-success d-block mt-2">قبول العرض</button>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
