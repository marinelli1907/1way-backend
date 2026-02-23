{{-- Reusable filters row: date range, status, search. Pass optional slot for extra filters. --}}
<div class="card oneway-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2"><input type="date" name="from" value="{{ request('from', now()->subDays(30)->toDateString()) }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><input type="date" name="to" value="{{ request('to', now()->toDateString()) }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">All status</option><option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option></select></div>
            <div class="col-md-4"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="form-control form-control-sm"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary w-100">Filter</button></div>
        </form>
    </div>
</div>
