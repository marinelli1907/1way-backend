@extends('adminmodule::layouts.master')

@section('title', translate('Trip Settings'))

@section('content')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fs-22 mb-4 text-capitalize">{{translate('business_management')}}</h2>
            <div class="col-12 mb-3">
                <div class="">
                    @include('businessmanagement::admin.business-setup.partials._business-setup-inline')
                </div>
            </div>
            <div class="card mb-3 text-capitalize">
                <form action="{{route('admin.business.setup.trip-fare.store')."?type=".TRIP_SETTINGS}}" id="trips_form"
                      method="POST">
                    @csrf

                    <div class="card-header">
                        <h5 class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-fill-gear"></i>
                            {{ translate('trips_settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3 pt-3 align-items-end">
                            <div class="col-lg-4 col-sm-6">
                                <label class="mb-4 d-flex align-items-center fw-medium gap-2">
                                    {{ translate('add_route_between_pickup_&_destination') }}
                                    <i class="bi bi-info-circle-fill text-primary cursor-pointer"
                                       data-bs-toggle="tooltip"
                                       title="{{ translate('If Yes, customers can add routes between pickup and destination') }}">
                                    </i>
                                </label>
                                <div class="d-flex align-items-center form-control mb-4 cmn_focus rounded">
                                    <div class="flex-grow-1">
                                        <input required type="radio" id="add_intermediate_points1"
                                               name="add_intermediate_points" tabindex="1"
                                               value="1" {{($settings->firstWhere('key_name', 'add_intermediate_points')->value?? 0) == 1 ? 'checked' : ''}}>
                                        <label for="add_intermediate_points1" class="media gap-2 align-items-center">
                                            <i class="tio-agenda-view-outlined text-muted"></i>
                                            <span class="media-body">{{ translate('yes') }}</span>
                                        </label>
                                    </div>

                                    <div class="flex-grow-1">
                                        <input required type="radio" id="add_intermediate_points"
                                               name="add_intermediate_points" tabindex="2"
                                               value="0" {{($settings->firstWhere('key_name', 'add_intermediate_points')->value?? 0) == 0 ? 'checked' : ''}}>
                                        <label for="add_intermediate_points" class="media gap-2 align-items-center">
                                            <i class="tio-table text-muted"></i>
                                            <span class="media-body">{{ translate('no') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="mb-4 text">
                                    <label for="trip_request_active_time"
                                           class="mb-4">{{ translate('trip_request_active_time_for_customer') }}</label>
                                    <div class="floating-form-group ">
                                        <label for="" class="floating-form-label">
                                            {{ translate('searching_active__time_for_(Min)') }}
                                        </label>
                                        <div class="input-group_tooltip">
                                            <input required type="number" class="form-control" placeholder="Ex: 5"
                                                   id="trip_request_active_time" name="trip_request_active_time"
                                                   value="{{$settings->firstWhere('key_name', 'trip_request_active_time')?->value}}" tabindex="2">
                                            <i class="bi bi-info-circle-fill text-primary tooltip-icon"
                                               data-bs-toggle="tooltip"
                                               data-bs-title="{{translate('Customers’ trip requests will be visible to drivers for the time (in minutes) you have set here') . '. '. translate('When the time is over, the requests get removed automatically.')}}"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <label class="mb-4 d-flex align-items-center fw-medium gap-2">
                                    {{ translate('Trip_OTP') }}
                                    <i class="bi bi-info-circle-fill text-primary cursor-pointer"
                                       data-bs-toggle="tooltip"
                                       title="{{ translate('When this option is enabled, for starting the trip, the driver will need to get an OTP from the customer') }}">
                                    </i>
                                </label>
                                <div class="form-control gap-2 align-items-center d-flex justify-content-between mb-4 rounded cmn_focus">
                                    <div class="d-flex align-items-center fw-medium gap-2 text-capitalize">
                                        {{ translate('Driver OTP Confirmation for Trip') }}
                                    </div>
                                    <div class="position-relative">
                                        <label class="switcher">
                                            <input type="checkbox" name="driver_otp_confirmation_for_trip"
                                                   class="switcher_input" tabindex="3"
                                                {{ $settings->where('key_name', 'driver_otp_confirmation_for_trip')->first()->value ?? 0 == 1 ? 'checked' : '' }}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 flex-wrap justify-content-end">
                            <button class="btn btn-secondary text-uppercase cmn_focus"
                                    type="reset" tabindex="4">{{ translate('Reset') }}</button>
                            <button type="submit"
                                    class="btn btn-primary text-uppercase cmn_focus" tabindex="5">{{ translate('submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card mb-3 text-capitalize">
                <form action="{{ route('admin.business.setup.schedule-trip.store')}}"
                      id="schedule_trip_form" method="POST">
                    @csrf
                    <div class="collapsible-card-body">
                        <div class="card-header d-flex align-items-center justify-content-between gap-2">
                            <div class="w-0 flex-grow-1">
                                <h5 class="mb-2">{{ translate('Schedule_trip') }}</h5>
                                <div class="fs-12">
                                    {{ translate('enable_customers_to_book_scheduled_trips_and_complete_the_setup_below_for_scheduled_trip_management.') }}
                                </div>
                            </div>
                            <a href="javascript:"
                               class="text-info fw-semibold fs-12 text-nowrap d-flex view-btn">
                                <span class="text-underline">{{ translate('View') }}</span>
                                @if(($settings->firstWhere('key_name', 'schedule_trip_status')->value ?? 0) == 1 )
                                    <span><i class="tio-arrow-upward"></i> </span>
                                @else
                                    <span><i class="tio-arrow-downward"></i> </span>
                                @endif
                            </a>
                            <label class="switcher rounded cmn_focus">
                                <input class="switcher_input collapsible-card-switcher update-business-setting"
                                       id="scheduleTripStatus" type="checkbox"
                                       name="schedule_trip_status" tabindex="6"
                                       data-name="schedule_trip_status" data-type="{{ SCHEDULE_TRIP_SETTINGS }}"
                                       data-url="{{ route('admin.business.setup.update-business-setting') }}"
                                       data-icon=" {{ dynamicAsset('public/assets/admin-module/img/parcel_return.png') }}"
                                       data-title="{{ translate('Are you sure?') }}"
                                       data-sub-title="{{ ($settings->firstWhere('key_name', 'schedule_trip_status')->value ?? 0) == 1 ? translate('Do you want to turn OFF Parcel Return Time & Fee for driver? When it’s off the driver don’t need to pay return fee for delay. ') : translate('Do you want to turn ON Parcel Return Time & Fee for driver? When it’s ON, the driver need to pay parcel return delay fee. ') }}"
                                       data-confirm-btn="{{ ($settings->firstWhere('key_name', 'schedule_trip_status')->value ?? 0) == 1 ? translate('Turn Off') : translate('Turn On') }}"
                                    {{ ($settings->firstWhere('key_name', 'schedule_trip_status')->value ?? 0) == 1 ? 'checked' : '' }}>
                                <span class="switcher_control"></span>
                            </label>
                        </div>
                        <div class="card-body collapsible-card-content">
                            <div class="row gy-3">
                                <div class="col-sm-6 mb-2 mb-md-4">
                                    <label for="minimumScheduleBook"
                                           class="form-label">{{ translate('minimum_schedule_book') }} <i
                                            class="bi bi-info-circle-fill text-primary cursor-pointer"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="{{ translate('this_time_is_the_minimum_advance_booking_time_for_scheduled_trips,_customer_cant_book_a_scheduled_trip_before_this.') }}"></i></label>
                                    <div class="input-group input--group">
                                        <input type="number" name="minimum_schedule_book_time" id="minimumScheduleBook"
                                               step="1" min="1" max="99999999" class="form-control"
                                               value="{{ $settings->firstWhere('key_name', 'minimum_schedule_book_time')?->value }}"
                                               placeholder="Ex : 40" required tabindex="7">
                                        <select id="minimumScheduleBookTimeType" class="form-select" name="minimum_schedule_book_time_type" tabindex="8">
                                            <option value="day"
                                                {{ $settings->firstWhere('key_name', 'minimum_schedule_book_time_type')?->value == 'day' ? 'selected' : '' }}>
                                                {{ translate('Day') }}</option>
                                            <option value="hour"
                                                {{ $settings->firstWhere('key_name', 'minimum_schedule_book_time_type')?->value == 'hour' ? 'selected' : '' }}>
                                                {{ translate('Hour') }}</option>
                                            <option value="minute"
                                                {{ $settings->firstWhere('key_name', 'minimum_schedule_book_time_type')?->value == 'minute' ? 'selected' : '' }}>
                                                {{ translate('Minute') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-2 mb-md-4">
                                    <label for="advanceScheduleBook"
                                           class="form-label">{{ translate('advance_schedule_book') }} <i
                                            class="bi bi-info-circle-fill text-primary cursor-pointer"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="{{ translate('this_time_is_the_maximum_advance_booking_time_for_scheduled_trips,_customer_can_not_book_a_scheduled_trip_if_the_time_is_over.') }}"></i></label>
                                    <div class="input-group input--group">
                                        <input type="number" name="advance_schedule_book_time" id="advanceScheduleBook"
                                               step="1" min="1" max="99999999" class="form-control"
                                               value="{{ $settings->firstWhere('key_name', 'advance_schedule_book_time')?->value }}"
                                               placeholder="Ex : 60" required tabindex="9">
                                        <select id="advanceScheduleBookTimeType" class="form-select" name="advance_schedule_book_time_type" tabindex="10">
                                            <option value="day"
                                                {{ $settings->firstWhere('key_name', 'advance_schedule_book_time_type')?->value == 'day' ? 'selected' : '' }}>
                                                {{ translate('Day') }}</option>
                                            <option value="hour"
                                                {{ $settings->firstWhere('key_name', 'advance_schedule_book_time_type')?->value == 'hour' ? 'selected' : '' }}>
                                                {{ translate('Hour') }}</option>
                                            <option value="minute"
                                                {{ $settings->firstWhere('key_name', 'advance_schedule_book_time_type')?->value == 'minute' ? 'selected' : '' }}>
                                                {{ translate('Minute') }}</option>
                                        </select>
                                    </div>
                                    <p id="time_conflicts_text_for_advance_schedule_book" class="text-danger text-end mt-2">{{ translate('your_input_time_conflicts_with_Minimum_Schedule_Book.') }}</p>
                                </div>
                                <div class="col-sm-6 mb-2 mb-md-4">
                                    <label for="driverRequestNotify"
                                           class="form-label">{{ translate('driver_request_notify') }} <i
                                            class="bi bi-info-circle-fill text-primary cursor-pointer"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="{{ translate('set_the_time_before_the_pickup_when_the_driver_should_be_notified_about_this_scheduled_trip.') }}"></i></label>
                                    <div class="input-group input--group">
                                        <input type="number" name="driver_request_notify_time" id="driverRequestNotify"
                                               step="1" min="1" max="99999999" class="form-control"
                                               value="{{ $settings->firstWhere('key_name', 'driver_request_notify_time')?->value }}"
                                               placeholder="Ex : 60" required tabindex="11">
                                        <select id="driverRequestNotifyTimeType" class="form-select" name="driver_request_notify_time_type" tabindex="12">
                                            <option value="day"
                                                {{ $settings->firstWhere('key_name', 'driver_request_notify_time_type')?->value == 'day' ? 'selected' : '' }}>
                                                {{ translate('Day') }}</option>
                                            <option value="hour"
                                                {{ $settings->firstWhere('key_name', 'driver_request_notify_time_type')?->value == 'hour' ? 'selected' : '' }}>
                                                {{ translate('Hour') }}</option>
                                            <option value="minute"
                                                {{ $settings->firstWhere('key_name', 'driver_request_notify_time_type')?->value == 'minute' ? 'selected' : '' }}>
                                                {{ translate('Minute') }}</option>
                                        </select>
                                    </div>
                                    <p id="time_conflicts_text_for_driver_request_notify" class="text-danger text-end mt-2">{{ translate('your_input_time_conflicts_with_Minimum_Schedule_Book.') }}</p>
                                </div>
                                <div class="col-sm-6 mb-2 mb-md-4" id="increaseFareWrapper">
                                    <label class="form-label" for="increaseFare">
                                        {{ translate('Increase_Fare') }}
                                        <i class="bi bi-info-circle-fill text-primary cursor-pointer"
                                           data-bs-toggle="tooltip"
                                           title="{{ translate('if_you_want_to_charge_more_for_scheduled_trips_than_regular_trips,_turn_on_the_switch.') }}">
                                        </i>
                                    </label>
                                    <div class="form-control gap-2 cmn_focus rounded align-items-center d-flex justify-content-between">
                                        <div class="d-flex align-items-center fw-medium gap-2 text-capitalize">
                                            {{ translate('Increase_fare_rate') . '?'}}
                                        </div>
                                        <div class="position-relative">
                                            <label class="switcher">
                                                <input type="checkbox" name="increase_fare"
                                                       class="switcher_input" tabindex="13"
                                                    {{ $settings->where('key_name', 'increase_fare')->first()->value ?? 0 == 1 ? 'checked' : '' }}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-2 mb-md-4 cmn_focus rounded {{ $settings->where('key_name', 'increase_fare')->first()->value ?? 0 == 1 ? '' : 'visually-hidden' }}" id="increaseFareAmountWrapper">
                                    <label for="IncreaseFareAmount" class="form-label">
                                        {{ translate('Increase_Fare_amount_(%)') }}
                                        <i class="bi bi-info-circle-fill text-primary cursor-pointer" data-bs-toggle="tooltip"
                                           data-bs-title="{{ translate('type_a_percentage_to_increase_the_fare_for_scheduled_trips._Leave_it_empty_to_keep_the_regular_fare') }}"></i></label>
                                    <input type="number" name="increase_fare_amount" min="1" max="100" step="1" id="IncreaseFareAmount" class="form-control" placeholder="Ex : 10"
                                        value="{{ $settings->firstWhere('key_name', 'increase_fare_amount')?->value }}" tabindex="14"
                                    >
                                </div>
                            </div>
                            <div class="d-flex gap-3 flex-wrap justify-content-end">
                                <button class="btn btn-secondary text-uppercase cmn_focus"
                                        type="reset" tabindex="15">{{ translate('Reset') }}</button>
                                <button type="submit"
                                        class="btn btn-primary text-uppercase cmn_focus" tabindex="16">{{ translate('submit') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>


            <div class="card mb-3 text-capitalize">
                <div class="card-header">
                    <h5 class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-fill-gear"></i>
                        {{ translate('trips_cancellation_messages') }}
                        <i class="bi bi-info-circle-fill text-primary cursor-pointer"
                           data-bs-toggle="tooltip"
                           title="{{ translate('changes_may_take_some_hours_in_app') }}"></i>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.business.setup.trip-fare.cancellation_reason.store') }}"
                          method="post">
                        @csrf
                        <div class="row gy-3 pt-3 align-items-start">
                            <div class="col-sm-6 col-md-6">
                                <label for="title" class="mb-3 d-flex align-items-center fw-medium gap-2">
                                    {{ translate('trip_cancellation_reason') }}
                                    <small>({{translate('Max 255 character')}})</small>
                                    <i class="bi bi-info-circle-fill text-primary cursor-pointer"
                                       data-bs-toggle="tooltip"
                                       title="{{ translate('Driver & Customer cancel trip confirmation reason') }}">
                                    </i>
                                </label>
                                <div class="character-count">
                                    <input id="title" name="title" type="text"
                                           placeholder="{{translate('Ex : vehicle problem')}}"
                                           class="form-control character-count-field"
                                           maxlength="255" data-max-character="255" required tabindex="17">
                                    <span>{{translate('0/255')}}</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label for="cancellationType" class="mb-3 d-flex align-items-center fw-medium gap-2">
                                    {{ translate('cancellation_type') }}
                                </label>
                                <select class="js-select cmn_focus" id="cancellationType" tabindex="18" name="cancellation_type"
                                        required>
                                    <option value="" disabled
                                            selected>{{translate('select_cancellation_type')}}</option>
                                    @foreach(CANCELLATION_TYPE as $key=> $item)
                                        <option value="{{$key}}">{{translate($item)}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label for="userType" class="mb-3 d-flex align-items-center fw-medium gap-2">
                                    {{ translate('user_type') }}
                                </label>
                                <select class="js-select cmn_focus" tabindex="19" id="userType" name="user_type" required>
                                    <option value="" disabled selected>{{translate('select_user_type')}}</option>
                                    <option value="driver">{{translate('driver')}}</option>
                                    <option value="customer">{{translate('customer')}}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-3 flex-wrap justify-content-end">
                                    <button class="btn btn-secondary text-uppercase cmn_focus"
                                            type="reset" tabindex="20">{{ translate('Reset') }}</button>
                                    <button type="submit"
                                            class="btn btn-primary text-uppercase cmn_focus" tabindex="21">{{ translate('submit') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


            <div class="card">
                <div class="card-header border-0 d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <h5 class="d-flex align-items-center gap-2 m-0">
                        <i class="bi bi-person-fill-gear"></i>
                        {{ translate('trip_cancellation_reason_list') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <thead class="table-light align-middle">
                            <tr>
                                <th class="sl">{{translate('SL')}}</th>
                                <th class="text-capitalize">{{translate('Reason')}}</th>
                                <th class="text-capitalize">{{translate('cancellation_type')}}</th>
                                <th class="text-capitalize">{{translate('user_type')}}</th>
                                <th class="text-capitalize">{{translate('Status')}}</th>
                                <th class="text-center action">{{translate('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($cancellationReasons as $key => $cancellationReason)
                                <tr>
                                    <td class="sl">{{ $key + $cancellationReasons->firstItem() }}</td>
                                    <td>
                                        {{$cancellationReason->title}}
                                    </td>
                                    <td>
                                        {{ CANCELLATION_TYPE[$cancellationReason->cancellation_type] }}
                                    </td>
                                    <td>
                                        {{ $cancellationReason->user_type == 'driver' ? translate('driver') : translate('customer') }}
                                        {{$cancellationReason->status}}
                                    </td>
                                    <td class="text-center">
                                        <label class="switcher mx-auto">
                                            <input class="switcher_input status-change"
                                                   data-url="{{ route('admin.business.setup.trip-fare.cancellation_reason.status') }}"
                                                   id="{{ $cancellationReason->id }}"
                                                   type="checkbox"
                                                   name="status" {{ $cancellationReason->is_active == 1 ? "checked": ""  }} >
                                            <span class="switcher_control"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2 align-items-center">
                                            <button class="btn btn-outline-primary btn-action editData"
                                                    data-id="{{$cancellationReason->id}}">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <button data-id="delete-{{ $cancellationReason?->id }}"
                                                    data-message="{{ translate('want_to_delete_this_cancellation_reason?') }}"
                                                    type="button"
                                                    class="btn btn-outline-danger btn-action form-alert">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                            <form
                                                action="{{ route('admin.business.setup.trip-fare.cancellation_reason.delete', ['id' => $cancellationReason?->id]) }}"
                                                id="delete-{{ $cancellationReason?->id }}" method="post">
                                                @csrf
                                                @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div
                                            class="d-flex flex-column justify-content-center align-items-center gap-2 py-3">
                                            <img
                                                src="{{ dynamicAsset('public/assets/admin-module/img/empty-icons/no-data-found.svg') }}"
                                                alt="" width="100">
                                            <p class="text-center">{{translate('no_data_available')}}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3">
        {{ $cancellationReasons->links() }}
    </div>

    <div class="modal fade" id="editDataModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <!-- End Main Content -->
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";


        let permission = false;
        @can('business_edit')
            permission = true;
        @endcan

        $('#trips_form').on('submit', function (e) {
            if (!permission) {
                toastr.error('{{ translate('you_do_not_have_enough_permission_to_update_this_settings') }}');
                e.preventDefault();
            }
        });
        $(document).ready(function () {
            $('#time_conflicts_text_for_driver_request_notify, #time_conflicts_text_for_advance_schedule_book').hide();
            $('.editData').click(function () {
                let id = $(this).data('id');
                let url = "{{ route('admin.business.setup.trip-fare.cancellation_reason.edit', ':id') }}";
                url = url.replace(':id', id);
                $.get({
                    url: url,
                    success: function (data) {
                        $('#editDataModal .modal-content').html(data);
                        $('#updateForm').removeClass('d-none');
                        $('#editDataModal').modal('show');
                        $('.character-count-field').on('keyup change', function () {
                            initialCharacterCount($(this));
                        });
                        $('.character-count-field').each(function () {
                            initialCharacterCount($(this));
                        });
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    }
                });
            });

            // Set up event listener to handle selection change
            $('.js-select').select2();
            let select = $('.js-select-2').select2({
                placeholder: $(this).data('placeholder')
            });
            select.on('select2:select', function (e) {
                let select = $(this);
                if (e.params.data.id === 'all') {
                    select.find('option').prop('selected', false);
                    select.val(['all']).trigger('change');
                } else {
                    let selectedValues = select.val().filter(item => item !== 'all');
                    select.find('option[value="all"]').prop('selected', false);
                    select.val(selectedValues).trigger('change');
                }
            });

            select.on('select2:unselect', function (e) {
                let select = $(this);
                select.find('option[value="all"]').prop('selected', false);
            });

            // show input conflicts text
            $('#driverRequestNotify, #driverRequestNotifyTimeType, #minimumScheduleBook, #minimumScheduleBookTimeType, #advanceScheduleBook, #advanceScheduleBookTimeType').on('input', function () {
                console.log('Input changed');
                showTimeConflictsText();
            });

            function convertToSeconds(value, type) {
                const timeMultipliers = {
                    day: 86400,
                    hour: 3600,
                    minute: 60,
                };

                return value * (timeMultipliers[type] || 0);
            }

            function showTimeConflictsText() {
                const getSeconds = (id, typeId) => convertToSeconds(parseInt($(id).val()), $(typeId).val()) || 0;

                const notifyTime = getSeconds('#driverRequestNotify', '#driverRequestNotifyTimeType');
                const minSchedule = getSeconds('#minimumScheduleBook', '#minimumScheduleBookTimeType');
                const advanceSchedule = getSeconds('#advanceScheduleBook', '#advanceScheduleBookTimeType');

                let hasConflict = false;

                if (notifyTime > minSchedule) {
                    $('#time_conflicts_text_for_driver_request_notify').show();
                    hasConflict = true;
                } else {
                    $('#time_conflicts_text_for_driver_request_notify').hide();
                }

                if (advanceSchedule <= minSchedule) {
                    $('#time_conflicts_text_for_advance_schedule_book').show();
                    hasConflict = true;
                } else {
                    $('#time_conflicts_text_for_advance_schedule_book').hide();
                }

                $('#schedule_trip_form button[type="submit"]').attr('disabled', hasConflict);
            }
            // show input conflicts text ends

            // Handle increase fare toggle
            $('#increaseFareWrapper').on('change', 'input[name="increase_fare"]', function () {
                if ($(this).is(':checked')) {
                    $('#increaseFareAmountWrapper').removeClass('visually-hidden');
                } else {
                    $('#increaseFareAmountWrapper').addClass('visually-hidden');
                }
            });

        });

    </script>
@endpush
