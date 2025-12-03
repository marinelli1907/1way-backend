<?php

namespace App\Traits;

trait InstallUpdateTrait
{
    public function setupScheduleTripSettingsData(){
        insertBusinessSetting(keyName: 'schedule_trip_status', settingType: SCHEDULE_TRIP_SETTINGS, value: '0');
        insertBusinessSetting(keyName: 'minimum_schedule_book_time', settingType: SCHEDULE_TRIP_SETTINGS, value: '1');
        insertBusinessSetting(keyName: 'minimum_schedule_book_time_type', settingType: SCHEDULE_TRIP_SETTINGS, value: 'day');
        insertBusinessSetting(keyName: 'advance_schedule_book_time', settingType: SCHEDULE_TRIP_SETTINGS, value: '1');
        insertBusinessSetting(keyName: 'advance_schedule_book_time_type', settingType: SCHEDULE_TRIP_SETTINGS, value: 'day');
        insertBusinessSetting(keyName: 'driver_request_notify_time', settingType: SCHEDULE_TRIP_SETTINGS, value: '1');
        insertBusinessSetting(keyName: 'driver_request_notify_time_type', settingType: SCHEDULE_TRIP_SETTINGS, value: 'day');
        insertBusinessSetting(keyName: 'increase_fare', settingType: SCHEDULE_TRIP_SETTINGS, value: 0);
        insertBusinessSetting(keyName: 'increase_fare_amount', settingType: SCHEDULE_TRIP_SETTINGS, value: '1');
    }

    public function setupVersion2Point7Data()
    {
        insertBusinessSetting(keyName: 'cash_in_hand_setup_status', settingType: DRIVER_SETTINGS, value: '0');
        insertBusinessSetting(keyName: 'max_amount_to_hold_cash', settingType: DRIVER_SETTINGS, value: '100');
        insertBusinessSetting(keyName: 'min_amount_to_pay', settingType: DRIVER_SETTINGS, value: '20');
        insertBusinessSetting(keyName: 'customer_wallet', settingType: CUSTOMER_SETTINGS, value: ['add_fund_status' => 0, 'min_deposit_limit' => 10]);
        insertBusinessSetting(keyName: 'do_not_charge_customer_return_fee', settingType: PARCEL_SETTINGS, value: '1');
    }
}
