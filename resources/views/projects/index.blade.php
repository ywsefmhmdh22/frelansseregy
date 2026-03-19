@extends('layouts.master')

@section('content')
<div class="container py-5">
    <h3 class="mb-4">قائمة مشاريعي</h3>
    <div class="glass-card">
        <table class="table">
            <thead>
                <tr>
                    <th>المشروع</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                    <th>الإجراء</th>
                </tr>
            </thead>
            <tbody>
                @foreach($myProjects as $project)
                <tr>
                    <td>{{ $project->title }}</td>
                    <td>{{ $project->created_at->format('Y-m-d') }}</td>
                    <td><span class="badge bg-success">{{ $project->status }}</span></td>
                    <td><a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-primary">عرض</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $myProjects->links() }} {{-- للتقليب بين الصفحات --}}
    </div>
</div>
@endsection
