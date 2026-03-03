@extends('adminmodule::layouts.master')
@section('title', 'Application: ' . $application->full_name)

@section('content')
<div class="main-content">
    <div class="container-fluid">

        <a href="{{ route('admin.driver-applications.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left me-1"></i>Back to Applications
        </a>

        <div class="row g-4">
            {{-- Left column: Details --}}
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">{{ $application->full_name }}</span>
                        @if($application->status === 'pending')
                            <span class="badge bg-warning text-dark fs-6">Pending</span>
                        @elseif($application->status === 'approved')
                            <span class="badge bg-success fs-6">Approved</span>
                        @else
                            <span class="badge bg-danger fs-6">Rejected</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold text-muted mb-3">Personal Information</h6>
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <small class="text-muted d-block">First Name</small>
                                <span class="fw-semibold">{{ $application->first_name }}</span>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Last Name</small>
                                <span class="fw-semibold">{{ $application->last_name }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Phone</small>
                                <span class="fw-semibold">{{ $application->phone }}</span>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Email</small>
                                <span class="fw-semibold">{{ $application->email }}</span>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <small class="text-muted d-block">City</small>
                                <span class="fw-semibold">{{ $application->city }}</span>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">State</small>
                                <span class="fw-semibold">{{ $application->state }}</span>
                            </div>
                        </div>

                        <hr>
                        <h6 class="fw-bold text-muted mb-3">Vehicle Information</h6>
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Make</small>
                                <span class="fw-semibold">{{ $application->vehicle_make ?? '—' }}</span>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Model</small>
                                <span class="fw-semibold">{{ $application->vehicle_model ?? '—' }}</span>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Year</small>
                                <span class="fw-semibold">{{ $application->vehicle_year ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Rideshare Insurance</small>
                                <span class="fw-semibold">
                                    @if(is_null($application->rideshare_insurance))
                                        —
                                    @elseif($application->rideshare_insurance)
                                        <span class="text-success"><i class="bi bi-check-circle me-1"></i>Yes</span>
                                    @else
                                        <span class="text-danger"><i class="bi bi-x-circle me-1"></i>No</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <hr>
                        <h6 class="fw-bold text-muted mb-3">Availability & Preferences</h6>
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Availability</small>
                                @if($application->availability && is_array($application->availability))
                                    @foreach($application->availability as $slot)
                                        <span class="badge bg-info text-dark me-1">{{ $slot }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Preferred Service Area</small>
                                <span class="fw-semibold">{{ $application->preferred_service_area ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Notes</small>
                            <span>{{ $application->notes ?? '—' }}</span>
                        </div>

                        <hr>
                        <h6 class="fw-bold text-muted mb-3">Meta</h6>
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Application ID</small>
                                <code style="font-size:.8rem">{{ $application->id }}</code>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Consent</small>
                                <span class="fw-semibold">{{ $application->consent ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Applied At</small>
                                <span class="fw-semibold">{{ $application->created_at->format('M j, Y g:ia') }}</span>
                            </div>
                        </div>
                        @if($application->reviewed_at)
                        <div class="row">
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Reviewed By</small>
                                <span class="fw-semibold">{{ $application->reviewer?->full_name ?? $application->reviewed_by }}</span>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Reviewed At</small>
                                <span class="fw-semibold">{{ $application->reviewed_at->format('M j, Y g:ia') }}</span>
                            </div>
                            @if($application->status === 'rejected' && $application->reject_reason)
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Reject Reason</small>
                                <span class="text-danger fw-semibold">{{ $application->reject_reason }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right column: Documents + Actions --}}
            <div class="col-lg-4">

                {{-- Documents --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-bold">
                        <i class="bi bi-file-earmark-image text-primary me-1"></i>Uploaded Documents
                    </div>
                    <div class="card-body">
                        @php
                            $hasDocs = $application->docs && is_array($application->docs) && count($application->getUploadedDocKeys()) > 0;
                        @endphp

                        @if($hasDocs)
                            <div class="row g-3">
                                @foreach($docLabels as $key => $label)
                                    @php $doc = $application->getDoc($key); @endphp
                                    @if($doc && !empty($doc['path']))
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center bg-light">
                                                <a href="{{ route('admin.driver-applications.serve-document', [$application->id, $key]) }}"
                                                   target="_blank"
                                                   title="View full size: {{ $label }}">
                                                    <img src="{{ route('admin.driver-applications.serve-document', [$application->id, $key]) }}"
                                                         alt="{{ $label }}"
                                                         class="img-fluid rounded mb-2"
                                                         style="max-height:160px; object-fit:contain; width:100%;"
                                                         loading="lazy">
                                                </a>
                                                <small class="d-block text-muted fw-semibold">{{ $label }}</small>
                                                <small class="text-muted">
                                                    {{ number_format(($doc['size'] ?? 0) / 1024, 0) }} KB
                                                </small>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @elseif($application->license_photo_path)
                            {{-- Legacy single license photo --}}
                            <div class="text-center">
                                <p class="text-muted small mb-2">Legacy upload (single license photo)</p>
                                <img src="{{ asset('storage/' . $application->license_photo_path) }}"
                                     alt="Driver License"
                                     class="img-fluid rounded mb-3"
                                     style="max-height:300px; object-fit:contain;">
                                <div>
                                    <a href="{{ route('admin.driver-applications.download-license', $application->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    {{ $application->license_photo_original_name }}
                                    ({{ number_format(($application->license_photo_size ?? 0) / 1024, 0) }} KB)
                                </small>
                            </div>
                        @else
                            <p class="text-muted py-3 text-center mb-0">No documents uploaded.</p>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                @if($application->status === 'pending')
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">
                        <i class="bi bi-check2-square text-primary me-1"></i>Actions
                    </div>
                    <div class="card-body d-grid gap-2">
                        <form action="{{ route('admin.driver-applications.approve', $application->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Approve this application?')">
                                <i class="bi bi-check-lg me-1"></i>Approve
                            </button>
                        </form>

                        <hr class="my-2">

                        <form action="{{ route('admin.driver-applications.reject', $application->id) }}" method="POST"
                              id="rejectForm">
                            @csrf
                            <div class="mb-2">
                                <label for="reject_reason" class="form-label fw-semibold small">
                                    Rejection Reason <span class="text-danger">*</span>
                                </label>
                                <textarea name="reject_reason" id="reject_reason"
                                          class="form-control form-control-sm @error('reject_reason') is-invalid @enderror"
                                          rows="3"
                                          placeholder="Explain why this application is being rejected..."
                                          required>{{ old('reject_reason') }}</textarea>
                                @error('reject_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('Reject this application?')">
                                <i class="bi bi-x-lg me-1"></i>Reject
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
