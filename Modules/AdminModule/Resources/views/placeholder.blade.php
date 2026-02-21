@section('title', $title ?? 'Coming Soon')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold">{{ $title ?? 'Coming Soon' }}</h3>
            <div class="text-muted">This section is under construction</div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card oneway-card text-center p-5">
                <div class="mb-4" style="font-size:4rem; color:var(--bs-primary, #CC0000);">
                    <i class="bi bi-tools"></i>
                </div>
                <h4 class="fw-bold mb-2">Coming Soon</h4>
                <p class="text-muted mb-4">
                    <strong>{{ $title ?? 'This feature' }}</strong> is being built.<br>
                    Check back in a future release.
                </p>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
