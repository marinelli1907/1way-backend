@extends('adminmodule::layouts.master')

@section('title', translate('Quick Add Driver'))

@section('content')
<div class="main-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fs-22 fw-bold mb-1">{{ translate('Quick Add Driver') }}</h2>
                <p class="text-muted mb-0">{{ translate('Create a driver account in under 60 seconds') }}</p>
            </div>
            <a href="{{ route('admin.driver.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>{{ translate('Back to Drivers') }}
            </a>
        </div>

        <form action="{{ route('admin.driver.quick-add.store') }}" method="POST" id="quickAddForm">
            @csrf

            <div class="row g-4">

                {{-- ─── Left: Driver Info ─── --}}
                <div class="col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="bi bi-person-badge text-primary"></i>
                            <span>{{ translate('Driver Information') }}</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ translate('First Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                           value="{{ old('first_name') }}" placeholder="John" required>
                                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ translate('Last Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                           value="{{ old('last_name') }}" placeholder="Smith" required>
                                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ translate('Email') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" placeholder="john@example.com" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ translate('Phone') }} <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone') }}" placeholder="+1 555 000 0000" required>
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ translate('City / Region') }}</label>
                                    <input type="text" name="city_region" class="form-control"
                                           value="{{ old('city_region') }}" placeholder="e.g. Miami, FL">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        {{ translate('Driver Split %') }}
                                        <i class="bi bi-info-circle text-muted" title="{{ translate('Percentage of ride fare the driver keeps') }}"></i>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="driver_split_percent" class="form-control"
                                               value="{{ old('driver_split_percent', 80) }}"
                                               min="0" max="100" step="0.5">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text">{{ translate('Default: 80% driver / 20% platform') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Vehicle Basics (Optional) --}}
                    <div class="card shadow-sm mt-3">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="bi bi-car-front text-primary"></i>
                            <span>{{ translate('Vehicle Basics') }}</span>
                            <span class="badge bg-secondary ms-auto">{{ translate('Optional') }}</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ translate('Make') }}</label>
                                    <input type="text" name="vehicle_make" class="form-control"
                                           value="{{ old('vehicle_make') }}" placeholder="Toyota">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ translate('Model') }}</label>
                                    <input type="text" name="vehicle_model" class="form-control"
                                           value="{{ old('vehicle_model') }}" placeholder="Camry">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ translate('Year') }}</label>
                                    <input type="number" name="vehicle_year" class="form-control"
                                           value="{{ old('vehicle_year') }}" placeholder="{{ date('Y') }}" min="1990" max="{{ date('Y')+1 }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ translate('License Plate') }}</label>
                                    <input type="text" name="vehicle_plate" class="form-control"
                                           value="{{ old('vehicle_plate') }}" placeholder="ABC-1234">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── Right: Summary + Actions ─── --}}
                <div class="col-lg-5">
                    {{-- What happens next card --}}
                    <div class="card shadow-sm border-primary">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-lightning-charge-fill me-1"></i>
                            {{ translate('What happens when you submit') }}
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex align-items-start gap-2 py-3">
                                    <span class="badge bg-primary rounded-circle mt-1">1</span>
                                    <div>
                                        <strong>{{ translate('User account created') }}</strong>
                                        <div class="text-muted small">{{ translate('With driver role + temp password') }}</div>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-start gap-2 py-3">
                                    <span class="badge bg-primary rounded-circle mt-1">2</span>
                                    <div>
                                        <strong>{{ translate('Driver profile created') }}</strong>
                                        <div class="text-muted small">{{ translate('Assigned to the first driver level') }}</div>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-start gap-2 py-3">
                                    <span class="badge bg-primary rounded-circle mt-1">3</span>
                                    <div>
                                        <strong>{{ translate('Onboarding checklist started') }}</strong>
                                        <div class="text-muted small">{{ translate('Track profile, docs, approval, activation') }}</div>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-start gap-2 py-3">
                                    <span class="badge bg-primary rounded-circle mt-1">4</span>
                                    <div>
                                        <strong>{{ translate('Invite link generated') }}</strong>
                                        <div class="text-muted small">{{ translate('Send by email or copy to clipboard') }}</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 btn-lg" id="submitBtn">
                                <i class="bi bi-person-plus-fill me-2"></i>
                                {{ translate('Create Driver Account') }}
                            </button>
                            <p class="text-center text-muted small mt-2 mb-0">
                                <i class="bi bi-clock me-1"></i>
                                {{ translate('Takes less than 60 seconds') }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>{{-- /row --}}
        </form>
    </div>
</div>
@endsection

@push('css_or_js')
<script>
document.getElementById('quickAddForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
});
</script>
@endpush
