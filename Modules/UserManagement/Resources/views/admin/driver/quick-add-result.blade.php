@extends('adminmodule::layouts.master')

@section('title', translate('Driver Created'))

@section('content')
<div class="main-content">
    <div class="container-fluid">

        {{-- Success Header --}}
        <div class="text-center py-4">
            <div class="mb-3">
                <span style="font-size:3rem;">✅</span>
            </div>
            <h2 class="fw-bold">{{ translate('Driver Account Created!') }}</h2>
            <p class="text-muted">
                {{ $driver->first_name }} {{ $driver->last_name }} &middot;
                {{ $driver->email }} &middot;
                {{ $driver->phone }}
            </p>
        </div>

        <div class="row g-4 justify-content-center">

            {{-- Invite Link Card --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border border-primary">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-link-45deg me-1"></i>{{ translate('Driver Invite Link') }}
                    </div>
                    <div class="card-body">
                        @if($inviteUrl)
                            <p class="text-muted small mb-2">{{ translate('Share this link with the driver. It expires in 7 days.') }}</p>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="inviteLinkInput"
                                       value="{{ $inviteUrl }}" readonly>
                                <button class="btn btn-primary" type="button" onclick="copyInviteLink()">
                                    <i class="bi bi-clipboard" id="copyIcon"></i>
                                    <span id="copyText">{{ translate('Copy') }}</span>
                                </button>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="mailto:{{ $driver->email }}?subject={{ urlencode('Your 1Way Driver Invite') }}&body={{ urlencode('Hi '.$driver->first_name.',\n\nClick this link to activate your account:\n'.$inviteUrl) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-envelope me-1"></i>{{ translate('Send via Email') }}
                                </a>
                                <button class="btn btn-outline-secondary btn-sm" onclick="regenerateInvite('{{ $driver->id }}')">
                                    <i class="bi bi-arrow-clockwise me-1"></i>{{ translate('Regenerate Link') }}
                                </button>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                {{ translate('No active invite link. Click "Regenerate" to create one.') }}
                                <button class="btn btn-warning btn-sm ms-2" onclick="regenerateInvite('{{ $driver->id }}')">
                                    {{ translate('Regenerate') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Onboarding Checklist --}}
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-clipboard-check text-primary"></i>
                        {{ translate('Onboarding Checklist') }}
                        @php($onboarding = $driver->onboardingStatus)
                        @if($onboarding)
                            <span class="badge bg-primary ms-auto">{{ $onboarding->progressPercent() }}%</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($onboarding)
                            {{-- Progress Bar --}}
                            <div class="progress mb-3" style="height:8px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width:{{ $onboarding->progressPercent() }}%"></div>
                            </div>

                            {{-- Checklist Steps --}}
                            @php
                            $steps = [
                                ['key' => 'profile_complete', 'label' => translate('Profile Complete'),   'icon' => 'bi-person-fill'],
                                ['key' => 'docs_uploaded',    'label' => translate('Documents Uploaded'), 'icon' => 'bi-file-earmark-check'],
                                ['key' => 'approved',         'label' => translate('Account Approved'),   'icon' => 'bi-shield-check'],
                                ['key' => 'active',           'label' => translate('Activated & Live'),   'icon' => 'bi-play-circle'],
                            ];
                            @endphp

                            <ul class="list-group list-group-flush" id="onboardingList">
                                @foreach($steps as $step)
                                <li class="list-group-item d-flex align-items-center gap-3 px-0">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input onboarding-toggle"
                                               type="checkbox"
                                               data-key="{{ $step['key'] }}"
                                               data-driver="{{ $driver->id }}"
                                               id="step_{{ $step['key'] }}"
                                               {{ $onboarding->{$step['key']} ? 'checked' : '' }}>
                                    </div>
                                    <i class="bi {{ $step['icon'] }} text-primary"></i>
                                    <label for="step_{{ $step['key'] }}" class="mb-0 cursor-pointer">
                                        {{ $step['label'] }}
                                    </label>
                                    <span class="ms-auto badge {{ $onboarding->{$step['key']} ? 'bg-success' : 'bg-secondary' }} step-badge" id="badge_{{ $step['key'] }}">
                                        {{ $onboarding->{$step['key']} ? translate('Done') : translate('Pending') }}
                                    </span>
                                </li>
                                @endforeach
                            </ul>

                            <div class="mt-3">
                                <label class="form-label small">{{ translate('Notes') }}</label>
                                <textarea class="form-control form-control-sm" id="onboardingNotes"
                                          rows="2" placeholder="{{ translate('Optional notes...') }}">{{ $onboarding->notes }}</textarea>
                                <button class="btn btn-sm btn-outline-primary mt-2" onclick="saveNotes('{{ $driver->id }}')">
                                    {{ translate('Save Notes') }}
                                </button>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">{{ translate('Onboarding record not found.') }}</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>{{-- /row --}}

        {{-- Action Buttons --}}
        <div class="text-center mt-4 d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('admin.driver.show', $driver->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i>{{ translate('View Full Profile') }}
            </a>
            <a href="{{ route('admin.driver.quick-add.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>{{ translate('Add Another Driver') }}
            </a>
            <a href="{{ route('admin.driver.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list me-1"></i>{{ translate('Driver List') }}
            </a>
        </div>

    </div>
</div>
@endsection

@push('css_or_js')
<script>
function copyInviteLink() {
    const input = document.getElementById('inviteLinkInput');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        document.getElementById('copyIcon').className = 'bi bi-check2';
        document.getElementById('copyText').textContent = 'Copied!';
        setTimeout(() => {
            document.getElementById('copyIcon').className = 'bi bi-clipboard';
            document.getElementById('copyText').textContent = 'Copy';
        }, 2500);
    });
}

function regenerateInvite(driverId) {
    if (!confirm('{{ translate("Regenerate invite link? The old link will stop working.") }}')) return;
    fetch(`/admin/driver/quick-add/${driverId}/regenerate-invite`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('inviteLinkInput').value = data.invite_url;
        alert('{{ translate("New invite link ready!") }}');
    })
    .catch(() => alert('{{ translate("Error regenerating link.") }}'));
}

// Onboarding toggle
document.querySelectorAll('.onboarding-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const payload = {
            [this.dataset.key]: this.checked,
            _token: document.querySelector('meta[name="csrf-token"]').content
        };
        fetch(`/admin/driver/quick-add/${this.dataset.driver}/onboarding`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': payload._token },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('badge_' + this.dataset.key);
            if (this.checked) {
                badge.className = 'ms-auto badge bg-success step-badge';
                badge.textContent = '{{ translate("Done") }}';
            } else {
                badge.className = 'ms-auto badge bg-secondary step-badge';
                badge.textContent = '{{ translate("Pending") }}';
            }
            // update progress bar
            document.querySelector('.progress-bar').style.width = data.progress_percent + '%';
            document.querySelector('.badge.bg-primary.ms-auto').textContent = data.progress_percent + '%';
        });
    });
});

function saveNotes(driverId) {
    const notes = document.getElementById('onboardingNotes').value;
    fetch(`/admin/driver/quick-add/${driverId}/onboarding`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ notes })
    }).then(() => alert('{{ translate("Notes saved!") }}'));
}
</script>
@endpush
