@extends('adminmodule::layouts.master')

@section('title', translate('Waitlist_Users'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('assets/admin-module/plugins/dataTables/jquery.dataTables.min.css') }}">
@endpush

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fs-22 mt-4 text-capitalize">{{ translate('waitlist_users') }}</h2>

            <div class="row g-4">
                <div class="col-12">
                    <div class="d-flex flex-wrap justify-content-between align-items-center my-3 gap-3">
                        <form class="search-form search-form_style-two">
                            <div class="input-group search-form__input_group">
                                <span class="search-form__icon">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="search" name="search" value="{{ request()->get('search') }}"
                                    class="theme-input-style search-form__input"
                                    placeholder="{{ translate('Search_Here_by_Name_Email_or_Phone') }}">
                            </div>
                            <button type="submit" class="btn btn-primary search-submit"
                                data-url="{{ url()->full() }}">{{ translate('search') }}</button>
                        </form>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted text-capitalize">{{ translate('total_waitlist_users') }} : </span>
                            <span class="text-primary fs-16 fw-bold"
                                id="total_record_count">{{ $waitlistUsers->total() }}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive mt-3">
                                <table class="table table-borderless align-middle table-hover">
                                    <thead class="table-light align-middle text-capitalize">
                                        <tr>
                                            <th class="sl">{{ translate('SL') }}</th>
                                            <th class="name">{{ translate('Full Name') }}</th>
                                            <th class="email">{{ translate('Email') }}</th>
                                            <th class="phone">{{ translate('Phone') }}</th>
                                            <th class="phone">{{ translate('Address') }}</th>
                                            <th class="phone">{{ translate('Vehicle') }}</th>
                                            <th class="phone">{{ translate('Year') }}</th>
                                            <th class="phone">{{ translate('Make') }}</th>
                                            <th class="phone">{{ translate('Model') }}</th>
                                            <th class="phone">{{ translate('Role') }}</th>
                                            <th class="created">{{ translate('Created_At') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($waitlistUsers as $key => $user)
                                            <tr>
                                                <td class="sl">{{ $key + $waitlistUsers->firstItem() }}</td>
                                                <td class="name">{{ $user->fullName }}</td>
                                                <td class="email">{{ $user->email }}</td>
                                                <td class="phone">{{ $user->phone }}</td>
                                                <td class="address">{{ $user->address }}</td>
                                                <td class="vehicle">{{ $user->vehicle }}</td>
                                                <td class="year">{{ $user->year }}</td>
                                                <td class="make">{{ $user->make }}</td>
                                                <td class="model">{{ $user->model }}</td>
                                                <td class="role">{{ $user->role }}</td>
                                                <td class="created">{{ \Carbon\Carbon::parse($user->createdAt)->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <div class="d-flex flex-column justify-content-center align-items-center gap-2 py-3">
                                                        <img src="{{ asset('assets/admin-module/img/empty-icons/no-data-found.svg') }}" alt="" width="100">
                                                        <p class="text-center">{{ translate('no_data_available') }}</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end">
                                {{ $waitlistUsers->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
