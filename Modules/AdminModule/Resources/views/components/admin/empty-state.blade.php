{{-- Reusable empty state for tables. @include with ['message' => '...', 'icon' => 'bi-inbox', 'colspan' => 4] --}}
<tr><td colspan="{{ $colspan ?? 4 }}" class="text-center text-muted py-5">
    <i class="bi {{ $icon ?? 'bi-inbox' }} fs-1 d-block mb-2"></i>
    <div>{{ $message ?? 'No records yet. Data will appear here when available.' }}</div>
</td></tr>
