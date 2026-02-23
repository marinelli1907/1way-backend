{{-- Reusable admin page header: breadcrumbs, title, subtitle, optional actions. Use @include with title, subtitle, showExport. --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">{{ $title ?? 'Section' }}</li>
        </ol></nav>
        <h3 class="mb-0 fw-bold">{{ $title ?? 'Section' }}</h3>
        <div class="text-muted small">{{ $subtitle ?? 'View and manage data for this section.' }}</div>
    </div>
    @if($showExport ?? true)
    <div>
        <button class="btn btn-sm btn-outline-secondary" disabled title="Export available in a future release."><i class="bi bi-download"></i> Export CSV</button>
    </div>
    @endif
</div>
