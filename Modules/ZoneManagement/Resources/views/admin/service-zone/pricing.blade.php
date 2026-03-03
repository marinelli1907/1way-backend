@extends('adminmodule::layouts.master')
@section('title', 'Zone Pricing — ' . $zone->name)

@section('content')
<div class="main-content">
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="fs-22 fw-bold mb-1"><i class="bi bi-currency-dollar text-success me-2"></i>Zone Pricing: {{ $zone->name }}</h2>
            <p class="text-muted mb-0">Override pricing knobs for this zone. Leave blank to use defaults.</p>
        </div>
        <a href="{{ route('admin.service-zone.index') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="saveAlert" class="alert d-none"></div>

                    <div class="row g-3">
                        @php
                        $fields = [
                            ['key' => 'base_fee_cents', 'label' => 'Base Fee (cents)', 'help' => '0–2000'],
                            ['key' => 'booking_fee_cents', 'label' => 'Booking Fee (cents)', 'help' => '0–2000'],
                            ['key' => 'per_mile_cents', 'label' => 'Per Mile (cents)', 'help' => '50–500'],
                            ['key' => 'per_minute_cents', 'label' => 'Per Minute (cents)', 'help' => '10–200'],
                            ['key' => 'min_fare_cents', 'label' => 'Min Fare (cents)', 'help' => '0–5000'],
                            ['key' => 'max_fare_cents', 'label' => 'Max Fare (cents)', 'help' => '500–25000'],
                            ['key' => 'surge_cap_multiplier', 'label' => 'Surge Cap Multiplier', 'help' => '1.0–2.5', 'step' => '0.1'],
                            ['key' => 'event_surge_multiplier', 'label' => 'Event Surge Multiplier', 'help' => '1.0–2.5', 'step' => '0.1'],
                            ['key' => 'airport_fee_cents', 'label' => 'Airport Fee (cents)', 'help' => '0–5000'],
                            ['key' => 'driver_split_percent', 'label' => 'Driver Split %', 'help' => '50–95'],
                        ];
                        @endphp

                        @foreach($fields as $f)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ $f['label'] }}</label>
                            <input type="number"
                                   class="form-control"
                                   id="field_{{ $f['key'] }}"
                                   value="{{ $rules[$f['key']] ?? '' }}"
                                   placeholder="Default: {{ $defaults[$f['key']] }}"
                                   step="{{ $f['step'] ?? '1' }}">
                            <div class="form-text">{{ $f['help'] }} — default {{ $defaults[$f['key']] }}</div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary" id="btnSave" onclick="savePricing()">
                            <i class="bi bi-check-lg me-1"></i>Save Pricing
                        </button>
                        <button class="btn btn-outline-secondary" onclick="resetDefaults()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to Defaults
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header fw-bold"><i class="bi bi-info-circle text-primary me-2"></i>Quick Reference</div>
                <div class="card-body small">
                    <p><strong>Formula:</strong></p>
                    <code>base_fee + booking_fee + (miles × per_mile) + (minutes × per_minute) + airport_fee</code>
                    <p class="mt-2">Then clamped to <code>[min_fare, max_fare]</code> and surge capped at <code>surge_cap_multiplier</code>.</p>
                    <hr>
                    <p><strong>Zone:</strong> {{ $zone->name }}</p>
                    <p><strong>Active:</strong> {{ $zone->is_active ? 'Yes' : 'No' }}</p>
                    <p><strong>Priority:</strong> {{ $zone->priority }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('script')
<script>
var fieldKeys = @json(array_column($fields, 'key'));

function savePricing() {
    var btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    var alertBox = document.getElementById('saveAlert');
    alertBox.className = 'alert d-none';

    var payload = {};
    fieldKeys.forEach(function(key) {
        var val = document.getElementById('field_' + key).value;
        if (val !== '') payload[key] = parseFloat(val);
    });

    fetch("{{ route('admin.service-zone.pricing.update', $zone->id) }}", {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    }).then(function(r) {
        var ct = r.headers.get('content-type') || '';
        if (ct.indexOf('application/json') === -1) {
            return r.text().then(function() { throw new Error('Server error (HTTP ' + r.status + ')'); });
        }
        return r.json();
    }).then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Pricing';
        if (data.success) {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = data.message || 'Saved!';
        } else {
            var msgs = data.message || '';
            if (data.errors) msgs += ' ' + Object.values(data.errors).flat().join(', ');
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = msgs || 'Save failed.';
        }
    }).catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Pricing';
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = err.message || 'Network error.';
    });
}

function resetDefaults() {
    var defaults = @json($defaults);
    fieldKeys.forEach(function(key) {
        document.getElementById('field_' + key).value = '';
        document.getElementById('field_' + key).placeholder = 'Default: ' + defaults[key];
    });
}
</script>
@endpush
