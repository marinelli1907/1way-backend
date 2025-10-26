<?php

namespace Modules\TripManagement\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Gateways\Library\Payer;
use Modules\Gateways\Traits\Payment;
use Illuminate\Http\RedirectResponse;
use Modules\Gateways\Library\Receiver;
use Illuminate\Support\Facades\Validator;
use App\Events\DriverPaymentReceivedEvent;
use Illuminate\Contracts\Foundation\Application;
use App\Events\CustomerTripPaymentSuccessfulEvent;
use Modules\Gateways\Library\Payment as PaymentInfo;
use Modules\UserManagement\Lib\LevelUpdateCheckerTrait;
use Modules\BusinessManagement\Entities\BusinessSetting;
use Modules\UserManagement\Lib\LevelHistoryManagerTrait;
use Modules\TransactionManagement\Traits\TransactionTrait;
use Modules\TripManagement\Interfaces\TripRequestInterfaces;

class PaymentController extends Controller
{
    use TransactionTrait, Payment, LevelHistoryManagerTrait, LevelUpdateCheckerTrait;

    public function __construct(
        private TripRequestInterfaces $trip
    )
    {
    }

    /**
     * @param Request $request
     * @return Application|JsonResponse|RedirectResponse|Redirector
     */
    public function digitalPaymentOld(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_request_id' => 'required',
            'payment_method' => 'required|in:ssl_commerz,stripe,paypal,razor_pay,paystack,senang_pay,paymob_accept,flutterwave,paytm,paytabs,liqpay,mercadopago,bkash,fatoorah,xendit,amazon_pay,iyzi_pay,hyper_pay,foloosi,ccavenue,pvit,moncash,thawani,tap,viva_wallet,hubtel,maxicash,esewa,swish,momo,payfast,worldpay,sixcash,ssl_commerz,stripe,paypal,razor_pay,paystack,senang_pay,paymob_accept,flutterwave,paytm,paytabs,liqpay,mercadopago,bkash,fatoorah,xendit,amazon_pay,iyzi_pay,hyper_pay,foloosi,ccavenue,pvit,moncash,thawani,tap,viva_wallet,hubtel,maxicash,esewa,swish,momo,payfast,worldpay,sixcash'
        ]);
        if ($validator->fails()) {

            return response()->json(responseFormatter(constant: DEFAULT_400, errors: errorProcessor($validator)), 400);
        }
        $trip = $this->trip->getBy(column: 'id', value: $request->trip_request_id, attributes: ['relations' => ['customer.userAccount', 'fee', 'time', 'driver']]);
        if (!$trip) {
            return response()->json(responseFormatter(TRIP_REQUEST_404), 403);
        }
        if ($trip->payment_status == PAID) {

            return response()->json(responseFormatter(DEFAULT_PAID_200));
        }

        $attributes = [
            'column' => 'id',
            'payment_method' => $request->payment_method,
        ];
        $tips = $request->tips;
        $feeAttributes['tips'] = $tips;
        $attributes['tips'] = $tips;
        $trip->fee()->update($feeAttributes);
        $trip = $this->trip->update($attributes, $request->trip_request_id);
        $paymentAmount = $trip->paid_fare + $tips;
        $customer = $trip->customer;
        $payer = new Payer(
            name: $customer?->first_name,
            email: $customer->email,
            phone: $customer->phone,
            address: '');
        $additionalData = [
            'business_name' => BusinessSetting::where(['key_name' => 'business_name'])->first()?->value,
            'business_logo' => asset('storage/app/public/business') . '/' . BusinessSetting::where(['key_name' => 'header_logo'])->first()?->value,
        ];
//hook is look for a autoloaded function to perform action after payment
        $paymentInfo = new PaymentInfo(
            hook: 'tripRequestUpdate',
            currencyCode: businessConfig('currency_code')?->value ?? 'USD',
            paymentMethod: $request->payment_method,
            paymentPlatform: 'mono',
            payerId: $customer->id,
            receiverId: '100',
            additionalData: $additionalData,
            paymentAmount: $paymentAmount,
            externalRedirectLink: null,
            attribute: 'order',
            attributeId: $request->trip_request_id
        );
        $receiverInfo = new Receiver('receiver_name', 'example.png');
        $redirectLink = $this->generate_link($payer, $paymentInfo, $receiverInfo);

        return redirect($redirectLink);
    }

    public function digitalPayment(Request $request)
    {
        // print('ok');
        $validator = Validator::make($request->all(), [
            'trip_request_id' => 'required',
            'payment_method' => 'required|in:ssl_commerz,stripe,paypal,razor_pay,paystack,senang_pay,paymob_accept,flutterwave,paytm,paytabs,liqpay,mercadopago,bkash,fatoorah,xendit,amazon_pay,iyzi_pay,hyper_pay,foloosi,ccavenue,pvit,moncash,thawani,tap,viva_wallet,hubtel,maxicash,esewa,swish,momo,payfast,worldpay,sixcash'
        ]);
        // print_r($validator);
        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, errorProcessor($validator)), 400);
        }

        $trip = $this->trip->getBy('id', $request->trip_request_id, ['relations' => ['customer.userAccount', 'fee', 'time', 'driver']]);
        if (!$trip) return response()->json(responseFormatter(TRIP_REQUEST_404), 403);
        if ($trip->payment_status == PAID) return response()->json(responseFormatter(DEFAULT_PAID_200));
        // dd($trip);
        // Update tips in trip and fee table
        $tips = $request->tips ?? 0;
        $trip->fee()->update(['tips' => $tips]);
        $trip = $this->trip->update([
            'column' => 'id',
            'payment_method' => $request->payment_method,
            'tips' => $tips,
            'payment_status' => 'hold',
        ], $request->trip_request_id);
        // dd($trip->estimated_fare);
        $paymentAmount = $trip->estimated_fare + $tips;
        // dd($paymentAmount);
        $customer = $trip->customer;

        $payer = new Payer(
            name: $customer?->first_name,
            email: $customer->email,
            phone: $customer->phone,
            address: ''
        );

        $additionalData = [
            'business_name' => BusinessSetting::where(['key_name' => 'business_name'])->first()?->value,
            'business_logo' => asset('storage/app/public/business') . '/' . BusinessSetting::where(['key_name' => 'header_logo'])->first()?->value,
        ];

        // Create the PaymentInfo instance (used by generate_link)
        $paymentInfo = new PaymentInfo(
            hook: 'tripRequestUpdate', // callback after payment
            currencyCode: businessConfig('currency_code')?->value ?? 'USD',
            paymentMethod: $request->payment_method,
            paymentPlatform: 'mono',
            payerId: $customer->id,
            receiverId: '100',
            additionalData: $additionalData,
            paymentAmount: $paymentAmount,
            externalRedirectLink: null,
            attribute: 'trip',
            attributeId: $request->trip_request_id
        );

        // dd($paymentInfo);
        $receiverInfo = new Receiver('receiver_name', 'example.png');

        /* 🔹 NEW LOGIC STARTS HERE 🔹 */
        if ($request->payment_method === 'stripe') {
            // For Stripe manual authorization — redirect to authorize view
            // dd($request->payment_method);
            $redirectLink = $this->generate_stripe_authorize_link($payer, $paymentInfo, $receiverInfo);
        } else {
            // For other gateways — fallback to your existing generate_link
            $redirectLink = $this->generate_link($payer, $paymentInfo, $receiverInfo);
        }
        /* 🔹 NEW LOGIC ENDS HERE 🔹 */

        return redirect($redirectLink);
        // return response()->json(responseFormatter(DEFAULT_UPDATE_200,$redirectLink));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_request_id' => 'required',
            'payment_method' => 'required|in:wallet,cash'
        ]);
        if ($validator->fails()) {

            return response()->json(responseFormatter(constant: DEFAULT_400, errors: errorProcessor($validator)), 400);
        }
        $trip = $this->trip->getBy(column: 'id', value: $request->trip_request_id, attributes: [
            'relations' => ['customer.userAccount', 'driver', 'fee']]);
        if (!$trip) {
            return response()->json(responseFormatter(TRIP_REQUEST_404), 403);
        }
        if ($trip->payment_status == PAID) {

            return response()->json(responseFormatter(DEFAULT_PAID_200));
        }

        $tips = 0;
        DB::beginTransaction();
        if (!is_null($request->tips) && $request->payment_method == 'wallet') {
            $tips = $request->tips;
        }
        $attributes = [
            'column' => 'id',
            'tips' => $tips,
            'payment_method' => $request->payment_method,
            'paid_fare' => $trip->paid_fare + $tips,
            'payment_status' => PAID
        ];
        $feeAttributes['tips'] = $tips;
        $trip->fee()->update($feeAttributes);
        $trip = $this->trip->update($attributes, $request->trip_request_id);
        $trip->tips = 0;
        $trip->save();
        if ($request->payment_method == 'wallet') {
            if ($trip->customer->userAccount->wallet_balance < ($trip->paid_fare)) {

                return response()->json(responseFormatter(INSUFFICIENT_FUND_403), 403);
            }
            $method = '_with_wallet_balance';
            $this->walletTransaction($trip);
        } // driver only make cash payment
        elseif ($request->payment_method == 'cash') {
            $method = '_by_cash';
            $this->cashTransaction($trip);
        }

        $this->customerLevelUpdateChecker($trip->customer);
        DB::commit();

        $push = getNotification('payment_successful');
        sendDeviceNotification(
            fcm_token: auth('api')->user()->user_type == 'customer' ? $trip->driver->fcm_token : $trip->customer->fcm_token,
            title: translate($push['title']),
            description: translate(textVariableDataFormat(value: $push['description'],paidAmount: $trip->paid_fare,methodName: $request->payment_method)),
            status: $push['status'],
            ride_request_id: $trip->id,
            type: $trip->type,
            action: 'payment_successful',
            user_id: $trip->driver->id
        );
        try {
            checkPusherConnection(DriverPaymentReceivedEvent::broadcast($trip));
        }catch(Exception $exception){

        }
        try {
            checkPusherConnection(CustomerTripPaymentSuccessfulEvent::broadcast($trip));
        }catch(Exception $exception){

        }

        return response()->json(responseFormatter(DEFAULT_UPDATE_200));
    }

}
