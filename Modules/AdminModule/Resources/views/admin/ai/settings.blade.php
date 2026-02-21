@extends('adminmodule::layouts.master')

@section('title', translate('AI Settings'))

@section('content')
<div class="main-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fs-22 fw-bold mb-1">
                    <i class="bi bi-cpu text-primary me-2"></i>{{ translate('AI Settings') }}
                </h2>
                <p class="text-muted mb-0">{{ translate('Manage AI features and API integrations') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.ai.logs') }}" class="btn btn-outline-primary">
                    <i class="bi bi-journal-text me-1"></i>{{ translate('View Logs') }}
                </a>
                <a href="{{ route('admin.ai.tools') }}" class="btn btn-primary">
                    <i class="bi bi-tools me-1"></i>{{ translate('AI Tools') }}
                </a>
            </div>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.ai.settings') }}">
                    <i class="bi bi-sliders me-1"></i>{{ translate('Settings') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.ai.logs') }}">
                    <i class="bi bi-journal-text me-1"></i>{{ translate('Logs') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.ai.tools') }}">
                    <i class="bi bi-tools me-1"></i>{{ translate('Tools') }}
                </a>
            </li>
        </ul>

        <form method="POST" action="{{ route('admin.ai.settings.save') }}">
            @csrf

            <div class="row g-4">

                {{-- Feature Toggles --}}
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header fw-bold">
                            <i class="bi bi-toggles text-primary me-2"></i>{{ translate('Feature Toggles') }}
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">{{ translate('All AI features are disabled by default. Enable only what is needed.') }}</p>

                            @php
                            $featureLabels = [
                                'ai_feature_pricing_suggestions' => ['label' => 'Pricing Suggestions', 'desc' => 'Suggest zone-based fare multipliers using ride history', 'icon' => 'bi-cash-coin'],
                                'ai_feature_demand_heatmap'      => ['label' => 'Demand Heat Map',     'desc' => 'Show predicted high-demand areas on the map',        'icon' => 'bi-geo-alt'],
                                'ai_feature_fraud_flagging'      => ['label' => 'Fraud Flagging',       'desc' => 'Flag suspicious ride patterns for review',            'icon' => 'bi-shield-exclamation'],
                                'ai_feature_eta_predictor'       => ['label' => 'ETA Predictor',        'desc' => 'Improve ETA estimates using historical data',         'icon' => 'bi-clock-history'],
                            ];
                            @endphp

                            <div class="d-flex flex-column gap-3">
                                @foreach($featureLabels as $key => $info)
                                <div class="d-flex align-items-start gap-3 p-3 border rounded">
                                    <i class="bi {{ $info['icon'] }} text-primary fs-5 mt-1"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ translate($info['label']) }}</div>
                                        <div class="text-muted small">{{ translate($info['desc']) }}</div>
                                    </div>
                                    <div class="form-check form-switch ms-2">
                                        <input class="form-check-input" type="checkbox"
                                               name="{{ $key }}" id="{{ $key }}"
                                               {{ $toggles[$key] ? 'checked' : '' }}>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- API Keys --}}
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header fw-bold">
                            <i class="bi bi-key text-primary me-2"></i>{{ translate('API Keys') }}
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle me-1"></i>
                                {{ translate('Keys are encrypted before storage. Leave blank to keep the current key.') }}
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    {{ translate('OpenAI API Key') }}
                                    @if($hasOpenAiKey)
                                        <span class="badge bg-success ms-1">{{ translate('Saved') }}</span>
                                    @endif
                                </label>
                                <input type="password" name="openai_api_key" class="form-control"
                                       placeholder="{{ $hasOpenAiKey ? '••••••••••••••••' : 'sk-...' }}"
                                       autocomplete="off">
                                <div class="form-text">{{ translate('Used for pricing suggestions and zone analysis.') }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    {{ translate('Anthropic API Key') }}
                                    @if($hasAnthropicKey)
                                        <span class="badge bg-success ms-1">{{ translate('Saved') }}</span>
                                    @endif
                                </label>
                                <input type="password" name="anthropic_api_key" class="form-control"
                                       placeholder="{{ $hasAnthropicKey ? '••••••••••••••••' : 'sk-ant-...' }}"
                                       autocomplete="off">
                                <div class="form-text">{{ translate('Alternative AI provider.') }}</div>
                            </div>

                            <div class="alert alert-warning small mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                {{ translate('Never paste API keys into CLAUDE.md or commit them to version control. Use this secure input only.') }}
                            </div>
                        </div>
                    </div>

                    {{-- Env reminder --}}
                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <h6 class="fw-bold">{{ translate('Prefer .env for API Keys') }}</h6>
                            <p class="text-muted small mb-2">{{ translate('You can also set keys in your .env file:') }}</p>
                            <pre class="bg-dark text-white p-3 rounded small mb-0">OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...</pre>
                        </div>
                    </div>
                </div>

            </div>{{-- /row --}}

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-2"></i>{{ translate('Save Settings') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
