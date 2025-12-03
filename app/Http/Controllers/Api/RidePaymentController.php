<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\TripManagement\Entities\TripRequest;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class RidePaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function hold(Request $request, TripRequest $tripRequest)
    {
        $request->validate([
            'amount'          => 'required|integer',
            'payment_method'  => 'required|string',
            'customer_id'     => 'nullable|string',
        ]);

        $intent = PaymentIntent::create([
            'amount'             => $request->amount,
            'currency'           => config('services.stripe.currency', 'usd'),
            'capture_method'     => 'manual',
            'payment_method'     => $request->payment_method,
            'customer'           => $request->customer_id,
            'confirm'            => true,
            'metadata'           => [
                'trip_request_id' => $tripRequest->id,
                'user_id'         => $request->user()->id,
            ],
        ]);

        $tripRequest->payment_intent_id = $intent->id;
        $tripRequest->save();

        return [
            'payment_intent_id' => $intent->id,
            'client_secret'     => $intent->client_secret,
            'status'            => $intent->status,
        ];
    }

    public function capture(Request $request, TripRequest $tripRequest)
    {
        $id = $tripRequest->payment_intent_id;
        $intent = PaymentIntent::retrieve($id);
        $intent->capture();
        return ['status' => $intent->status];
    }

    public function cancel(Request $request, TripRequest $tripRequest)
    {
        $id = $tripRequest->payment_intent_id;
        $intent = PaymentIntent::retrieve($id);
        $intent->cancel();
        return ['status' => $intent->status];
    }
}
