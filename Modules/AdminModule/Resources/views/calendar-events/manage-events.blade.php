@section('title', 'Create / Manage Events')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Create / Manage Events</h3>
            <div class="text-muted small">Set up events, assign venues, and link ride logistics</div>
        </div>
    </div>

    <div class="row g-4">

        {{-- CREATE FORM (scaffolded) --}}
        <div class="col-md-5">
            <div class="card oneway-card p-4">
                <h6 class="fw-semibold mb-3">New Event</h6>

                <div class="alert alert-warning d-flex gap-2 align-items-start mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>
                        <strong>Events table not yet created.</strong><br>
                        This form is scaffolded and ready. Run the events migration to activate saving.
                    </div>
                </div>

                <form>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Event Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" placeholder="e.g. Summerfest 2026" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Venue / Location</label>
                        <input type="text" class="form-control form-control-sm" placeholder="e.g. Miller Park" disabled>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label small fw-semibold">Start Date</label>
                            <input type="datetime-local" class="form-control form-control-sm" disabled>
                        </div>
                        <div class="col">
                            <label class="form-label small fw-semibold">End Date</label>
                            <input type="datetime-local" class="form-control form-control-sm" disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Expected Attendees</label>
                        <input type="number" class="form-control form-control-sm" placeholder="0" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Notes</label>
                        <textarea class="form-control form-control-sm" rows="3" placeholder="Additional details..." disabled></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" disabled>Save Event (coming soon)</button>
                </form>
            </div>
        </div>

        {{-- EXISTING EVENTS LIST --}}
        <div class="col-md-7">
            <div class="card oneway-card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-semibold mb-0">All Events</h6>
                    <span class="badge bg-light text-muted">0 events</span>
                </div>

                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-plus fs-2 d-block mb-2"></i>
                    No events yet. Create your first event using the form.
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
