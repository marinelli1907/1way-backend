@extends('adminmodule::layouts.master')
@section('title', 'Driver Applications')

@section('content')
<div class="main-content">
    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fs-22 fw-bold mb-1">Driver Applications / Onboarding</h2>
                <p class="text-muted mb-0">Review and manage incoming driver applications.</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Search name, email, phone, city..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Applications Table --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>City / State</th>
                                <th>Status</th>
                                <th>Applied</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $i => $app)
                            <tr>
                                <td class="text-muted" style="font-size:.8rem">{{ Str::limit($app->id, 8, '…') }}</td>
                                <td class="fw-semibold">{{ $app->first_name }} {{ $app->last_name }}</td>
                                <td>{{ $app->phone }}</td>
                                <td>{{ $app->email }}</td>
                                <td>{{ $app->city }}, {{ $app->state }}</td>
                                <td>
                                    @if($app->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($app->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $app->created_at->format('M j, Y g:ia') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.driver-applications.show', $app->id) }}"
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No driver applications yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($applications->hasPages())
            <div class="card-footer">{{ $applications->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
