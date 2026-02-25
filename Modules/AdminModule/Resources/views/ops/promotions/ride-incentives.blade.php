@section('title', 'Ride Incentives')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Ride Incentives', 'subtitle' => 'Coupons, discounts, and perks.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])

    {{-- Create Coupon Card --}}
    <div class="card oneway-card mb-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Create Coupon</h5>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#createCouponForm" aria-expanded="false">
                <i class="bi bi-plus-lg me-1"></i>Toggle Form
            </button>
        </div>
        <div class="collapse" id="createCouponForm">
            <div class="card-body pt-0">
                <form action="{{ route('admin.coupon.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Coupon Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Weekend Special" required maxlength="100" value="{{ old('name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Coupon Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. WEEKEND20" required maxlength="50" value="{{ old('code') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Discount Type <span class="text-danger">*</span></label>
                            <select name="amount_type" class="form-select" required>
                                <option value="percentage" {{ old('amount_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="amount" {{ old('amount_type') === 'amount' ? 'selected' : '' }}>Fixed Amount</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Discount Value <span class="text-danger">*</span></label>
                            <input type="number" name="discount" class="form-control" placeholder="e.g. 15" required min="0" step="0.01" value="{{ old('discount') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Min Trip Amount</label>
                            <input type="number" name="min_trip_amount" class="form-control" placeholder="0" min="0" step="0.01" value="{{ old('min_trip_amount') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Max Discount Cap</label>
                            <input type="number" name="max_coupon_amount" class="form-control" placeholder="0 = unlimited" min="0" step="0.01" value="{{ old('max_coupon_amount') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Usage Limit / User</label>
                            <input type="number" name="limit" class="form-control" placeholder="1" min="1" value="{{ old('limit', 1) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required value="{{ old('start_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required value="{{ old('end_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Coupon Category</label>
                            <select name="coupon_type" class="form-select">
                                <option value="default">Default</option>
                                <option value="first_ride">First Ride</option>
                                <option value="referral">Referral</option>
                                <option value="loyalty">Loyalty</option>
                                <option value="event">Event</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" class="form-control" rows="2" maxlength="500" placeholder="Optional note about this coupon...">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-plus-circle me-1"></i>Create Coupon
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-semibold mb-0">Coupons & Discounts</h5>
        <form method="GET" class="d-flex gap-2" style="max-width: 320px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or code…" value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i></button>
        </form>
    </div>

    {{-- Coupons Table --}}
    <div class="card oneway-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Discount</th>
                            <th>Min Trip</th>
                            <th>Max Cap</th>
                            <th>Limit</th>
                            <th>Dates</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr>
                            <td class="fw-medium">{{ $item->name ?? '—' }}</td>
                            <td><code>{{ $item->coupon_code ?? $item->coupon ?? '—' }}</code></td>
                            <td>
                                @if($item->amount_type === 'percentage')
                                    <span class="badge bg-info-subtle text-info">Percentage</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Fixed</span>
                                @endif
                            </td>
                            <td>
                                {{ $item->total_amount ?? 0 }}{{ $item->amount_type === 'percentage' ? '%' : '' }}
                            </td>
                            <td>{{ $item->min_trip_amount ?? '—' }}</td>
                            <td>{{ $item->max_coupon_amount ?: '∞' }}</td>
                            <td>{{ $item->limit ?? '—' }} <span class="text-muted small">(used: {{ (int)($item->total_used ?? 0) }})</span></td>
                            <td class="small">
                                {{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('M j') : '—' }}
                                –
                                {{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('M j, Y') : '—' }}
                            </td>
                            <td>
                                <form action="{{ route('admin.coupon.toggle-status') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <button type="submit" class="btn btn-sm {{ $item->is_active ? 'btn-success' : 'btn-outline-secondary' }}" title="Toggle status">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCouponModal{{ $loop->index }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <div class="modal fade" id="editCouponModal{{ $loop->index }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('admin.coupon.update', $item->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Coupon — {{ $item->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Code</label>
                                                    <input type="text" name="code" class="form-control text-uppercase" value="{{ $item->coupon_code ?? $item->coupon }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Discount Type</label>
                                                    <select name="amount_type" class="form-select" required>
                                                        <option value="percentage" {{ $item->amount_type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                        <option value="amount" {{ $item->amount_type === 'amount' ? 'selected' : '' }}>Fixed Amount</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Discount Value</label>
                                                    <input type="number" name="discount" class="form-control" value="{{ $item->total_amount }}" required min="0" step="0.01">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Category</label>
                                                    <select name="coupon_type" class="form-select">
                                                        <option value="default" {{ ($item->coupon_type ?? '') === 'default' ? 'selected' : '' }}>Default</option>
                                                        <option value="first_ride" {{ ($item->coupon_type ?? '') === 'first_ride' ? 'selected' : '' }}>First Ride</option>
                                                        <option value="referral" {{ ($item->coupon_type ?? '') === 'referral' ? 'selected' : '' }}>Referral</option>
                                                        <option value="loyalty" {{ ($item->coupon_type ?? '') === 'loyalty' ? 'selected' : '' }}>Loyalty</option>
                                                        <option value="event" {{ ($item->coupon_type ?? '') === 'event' ? 'selected' : '' }}>Event</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Min Trip Amount</label>
                                                    <input type="number" name="min_trip_amount" class="form-control" value="{{ $item->min_trip_amount }}" min="0" step="0.01">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Max Discount Cap</label>
                                                    <input type="number" name="max_coupon_amount" class="form-control" value="{{ $item->max_coupon_amount }}" min="0" step="0.01">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Limit / User</label>
                                                    <input type="number" name="limit" class="form-control" value="{{ $item->limit }}" min="1">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Start Date</label>
                                                    <input type="date" name="start_date" class="form-control" value="{{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('Y-m-d') : '' }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">End Date</label>
                                                    <input type="date" name="end_date" class="form-control" value="{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('Y-m-d') : '' }}" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="2">{{ $item->description }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        @include('adminmodule::components.admin.empty-state', ['colspan' => 10, 'message' => 'No coupons yet. Create one above.'])
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($items) && method_exists($items, 'links'))
            <div class="p-3">{{ $items->links() }}</div>
            @endif
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
