@extends('adminmodule::layouts.master')

@section('title', translate('AI Logs'))

@section('content')
<div class="main-content">
    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="fs-22 fw-bold mb-0">
                <i class="bi bi-journal-text text-primary me-2"></i>{{ translate('AI Run Logs') }}
            </h2>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.ai.settings') }}">
                    <i class="bi bi-sliders me-1"></i>{{ translate('Settings') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.ai.logs') }}">
                    <i class="bi bi-journal-text me-1"></i>{{ translate('Logs') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.ai.tools') }}">
                    <i class="bi bi-tools me-1"></i>{{ translate('Tools') }}
                </a>
            </li>
        </ul>

        {{-- Filters --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body py-2">
                <form method="GET" class="d-flex gap-3 align-items-center flex-wrap">
                    <div>
                        <select name="tool" class="form-select form-select-sm">
                            <option value="">{{ translate('All Tools') }}</option>
                            @foreach($tools as $t)
                            <option value="{{ $t }}" {{ request('tool') === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">{{ translate('All Statuses') }}</option>
                            @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">{{ translate('Filter') }}</button>
                    <a href="{{ route('admin.ai.logs') }}" class="btn btn-sm btn-outline-secondary">{{ translate('Reset') }}</a>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ translate('ID') }}</th>
                                <th>{{ translate('Tool') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Duration') }}</th>
                                <th>{{ translate('Triggered') }}</th>
                                <th>{{ translate('Output') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="text-muted small">#{{ $log->id }}</td>
                                <td>
                                    <code class="text-primary">{{ $log->tool }}</code>
                                </td>
                                <td>
                                    @php
                                    $badgeMap = [
                                        'success' => 'bg-success',
                                        'failed'  => 'bg-danger',
                                        'running' => 'bg-info',
                                        'pending' => 'bg-secondary',
                                    ];
                                    @endphp
                                    <span class="badge {{ $badgeMap[$log->status] ?? 'bg-secondary' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $log->duration_ms ? $log->duration_ms . 'ms' : '—' }}
                                </td>
                                <td class="text-muted small">{{ $log->created_at->diffForHumans() }}</td>
                                <td>
                                    @if($log->output)
                                        <button class="btn btn-xs btn-outline-secondary"
                                                data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        {{-- Modal --}}
                                        <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ translate('AI Log') }} #{{ $log->id }} — {{ $log->tool }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h6>{{ translate('Input') }}</h6>
                                                        <pre class="bg-dark text-white p-3 rounded small">{{ json_encode($log->input, JSON_PRETTY_PRINT) }}</pre>
                                                        <h6 class="mt-3">{{ translate('Output') }}</h6>
                                                        <pre class="bg-dark text-white p-3 rounded small">{{ json_encode($log->output, JSON_PRETTY_PRINT) }}</pre>
                                                        @if($log->error)
                                                        <h6 class="mt-3 text-danger">{{ translate('Error') }}</h6>
                                                        <pre class="bg-danger text-white p-3 rounded small">{{ $log->error }}</pre>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($log->error)
                                        <span class="text-danger small">{{ Str::limit($log->error, 40) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">{{ translate('No AI logs yet.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
            <div class="card-footer">
                {{ $logs->withQueryString()->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
