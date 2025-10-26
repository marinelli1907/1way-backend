<?php

namespace Modules\Gateways\Http\Controllers;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Modules\Gateways\Traits\Processor;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Entities\PaymentRequest;
use Illuminate\Contracts\Foundation\Application;

class StripePaymentController extends Controller
{
    use Processor;

    private mixed $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->paymentConfig('stripe', PAYMENT_CONFIG);
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }
        $this->payment = $payment;
    }

    public function index(Request $request): View|Factory|JsonResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_400, null, $this->errorProcessor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_204), 200);
        }
        $config = $this->config_values;

        return view('Gateways::payment.stripe', compact('data', 'config'));
    }

    public function payment_process_3d(Request $request): JsonResponse
    {
        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payment_amount = $data['payment_amount'];

        Stripe::setApiKey($this->config_values->api_key);
        header('Content-Type: application/json');
        $currency_code = $data->currency_code;

        if ($data['additional_data'] != null) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
            $business_logo = $business->business_logo ?? url('/');
        } else {
            $business_name = "my_business";
            $business_logo = url('/');
        }

        $currencies_not_supported_cents = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency_code ?? 'usd',
                    'unit_amount' => in_array($currency_code, $currencies_not_supported_cents) ? (int)$payment_amount : ($payment_amount * 100),
                    'product_data' => [
                        'name' => $business_name,
                        'images' => [$business_logo],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/') . '/payment/stripe/success?session_id={CHECKOUT_SESSION_ID}&payment_id=' . $data->id,
            'cancel_url' => url()->previous(),
        ]);

        return response()->json(['id' => $checkout_session->id]);
    }

    public function success(Request $request): \Illuminate\Foundation\Application|JsonResponse|Redirector|Application|RedirectResponse
    {
        Stripe::setApiKey($this->config_values->api_key);
        $session = Session::retrieve($request->get('session_id'));

        if ($session->payment_status == PAID && $session->status == 'complete') {

            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'stripe',
                'is_paid' => 1,
                'transaction_id' => $session->payment_intent,
            ]);

            $data = $this->payment::where(['id' => $request['payment_id']])->first();

            if (isset($data) && function_exists($data->hook)) {
                call_user_func($data->hook, $data);
            }

            return $this->paymentResponse($data, 'success');
        }
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        if (isset($payment_data) && function_exists($payment_data->hook)) {
            call_user_func($payment_data->hook, $payment_data);
        }
        return $this->paymentResponse($payment_data, 'fail');
    }

    /* -----------------------------
     * 1️⃣ Show Stripe Authorize View
     * ----------------------------- */
    public function authorizeView(Request $request)
    {
        $validator = Validator::make($request->all(), ['payment_id' => 'required|uuid']);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $this->payment::where('id', $request['payment_id'])
            ->where('is_paid', 0)->first();
        if (!$data) return response()->json(['message' => 'Invalid or already paid'], 404);

        $config = $this->config_values;
        // dd($config);
        return view('Gateways::payment.stripe_authorize', compact('data', 'config'));
    }

    /* ------------------------------------
     * 2️⃣ Create manual-capture PaymentIntent
     * ------------------------------------ */
    public function createManualIntent(Request $request)
    {
        $validator = Validator::make($request->all(), ['payment_id' => 'required|uuid']);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment = $this->payment::find($request->payment_id);
        if (!$payment || $payment->is_paid) {
            return response()->json(['message' => 'Invalid or already paid'], 400);
        }
        
        \Stripe\Stripe::setApiKey($this->config_values->api_key);

        // 1️⃣ Get payer (customer) from your database
        $user = \App\Models\User::find($payment->payer_id);
        if (!$user) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        // 2️⃣ Check if user already has a Stripe customer ID, otherwise create one
        if (empty($user->stripe_customer_id)) {
            $customer = \Stripe\Customer::create([
                'name'  => $user->first_name ?? null . ' ' . $user->last_name ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
                'description' => "Customer for user ID {$user->id}",
            ]);

            $user->stripe_customer_id = $customer->id;
            $user->save();
        } else {
            $customer = \Stripe\Customer::retrieve($user->stripe_customer_id);
        }

        $amount   = $this->toMinor($payment->payment_amount, $payment->currency_code);
        $currency = strtolower($payment->currency_code);

        // 🔹 Create PaymentIntent for client-side confirmation
        $intent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'customer'             => $customer->id, // ✅ linked to created/loaded customer
            'capture_method' => 'manual',
            // 'automatic_payment_methods' => ['enabled' => true],
            'payment_method_types' => ['card'], // only allow cards
            'setup_future_usage' => 'off_session', // ✅ key line (save for reuse)
            // 'confirmation_method' => 'automatic',  // frontend will confirm
            'description' => "Authorization hold for {$payment->attribute} #{$payment->attribute_id}",
            'metadata' => [
                'payment_request_id' => $payment->id,
                'payer_id'           => $payment->payer_id,
                'attribute'          => $payment->attribute,
                'attribute_id'       => $payment->attribute_id,
            ],
        ]);

        // Save details for later capture
        $payment->update([
            'stripe_payment_intent_id' => $intent->id,
            'stripe_status'            => $intent->status,
            'is_authorized' => 1,
        ]);

        return response()->json([
            'client_secret'      => $intent->client_secret,
            'payment_intent_id'  => $intent->id,
            'status'             => $intent->status,
        ]);
    }


    /* -------------------------------------
     * 3️⃣ Called when auth succeeds (hold ok)
     * ------------------------------------- */
    public function authorizedSuccess(Request $request)
    {
        try {
            \Stripe\Stripe::setApiKey($this->config_values->api_key);

            $paymentId = $request->get('payment_id');
            $payment = \Modules\Gateways\Entities\PaymentRequest::find($paymentId);

            if (!$payment) {
                return redirect('/payment-fail')->with('message', 'Payment record not found');
            }

            $intentId = $payment->stripe_payment_intent_id;
            if (!$intentId) {
                return redirect('/payment-fail')->with('message', 'Missing payment intent ID');
            }

            $intent = \Stripe\PaymentIntent::retrieve($intentId);
            $user   = \App\Models\User::find($payment->payer_id);
            // dd($intent,$user);
            // ✅ STEP 1 — Ensure Stripe Customer exists
            if ($user && empty($user->stripe_customer_id)) {
                $customer = \Stripe\Customer::create([
                    'name'  => trim($user->first_name . ' ' . $user->last_name),
                    'email' => $user->email,
                ]);
                $user->stripe_customer_id = $customer->id;
                $user->save();
            }

            // ✅ STEP 2 — Attach PaymentMethod to Customer
            if ($user && $intent->payment_method) {
                try {
                    $paymentMethod = \Stripe\PaymentMethod::retrieve($intent->payment_method);

                    // Attach if not already attached
                    if ($paymentMethod->customer !== $user->stripe_customer_id) {
                        $paymentMethod->attach(['customer' => $user->stripe_customer_id]);
                    }

                    // ✅ Update default PaymentMethod
                    \Stripe\Customer::update($user->stripe_customer_id, [
                        'invoice_settings' => [
                            'default_payment_method' => $intent->payment_method,
                        ],
                    ]);

                    Log::info('Saving stripe_payment_method_id', [
                        'user_id' => $user->id,
                        'method' => $intent->payment_method,
                        'model_class' => get_class($user),
                    ]);
                    // ✅ Save method in DB for later use
                    $user->stripe_payment_method_id = $intent->payment_method;
                    $user->save();

                } catch (\Exception $e) {
                    Log::warning('Stripe PaymentMethod attach failed', [
                        'message' => $e->getMessage(),
                        'user_id' => $user->id ?? null,
                        'payment_method' => $intent->payment_method,
                    ]);
                }
            }

            // ✅ STEP 3 — Update payment table
            if (in_array($intent->status, ['requires_capture', 'succeeded'])) {
                $payment->update([
                    'is_paid'        => 1,
                    'transaction_id' => $intent->id,
                    'stripe_status'  => $intent->status,
                ]);

                if (isset($payment->hook) && function_exists($payment->hook)) {
                    try {
                        call_user_func($payment->hook, $payment);
                    } catch (\Throwable $e) {
                        Log::error('Payment hook failed', [
                            'hook' => $payment->hook,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
                return response()->json([
                    'status'  => 'succeeded',
                    'message' => 'Payment authorized successfully!',
                    'payment_id' => $payment->id ?? null,
                    'capture_url' => url('/payment/stripe/capture'),
                ]);
                return redirect('/payment-success')->with('message', 'Payment authorized successfully!');
            }

            return redirect('/payment-fail')->with('message', 'Payment failed');

        } catch (\Exception $e) {
            Log::error('Stripe authorizedSuccess error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ]);

            return redirect('/payment-fail')->with('message', 'Payment failed');
        }
    }




    /* -----------------------------------
     * 4️⃣ Cancel authorization (no driver)
     * ----------------------------------- */
    public function cancelAuthorization(Request $request)
    {
        $request->validate(['payment_id' => 'required|uuid']);
        $payment = $this->payment::find($request->payment_id);
        if (!$payment || !$payment->stripe_payment_intent_id)
            return response()->json(['message' => 'Invalid request'], 400);

        Stripe::setApiKey($this->config_values->api_key);
        $intent = PaymentIntent::retrieve($payment->stripe_payment_intent_id);
        $intent->cancel();

        $payment->update([
            'stripe_status' => 'canceled',
            'is_authorized' => false,
        ]);

        return response()->json(['status' => 'voided']);
    }

    public function capturePayment(Request $request)
    {
        $request->validate([
            'payment_id'   => 'required|uuid',
            'final_amount' => 'required|numeric',
        ]);

        $payment = \Modules\Gateways\Entities\PaymentRequest::find($request->payment_id);
        if (!$payment || !$payment->stripe_payment_intent_id) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        \Stripe\Stripe::setApiKey($this->config_values->api_key);
        $intent = \Stripe\PaymentIntent::retrieve($payment->stripe_payment_intent_id);
        $user   = \App\Models\User::find($payment->payer_id);

        $authorized = (int) $intent->amount; // hold in cents
        $final = $this->toMinor($request->final_amount, $payment->currency_code); // final fare in cents

        try {
            $alreadyCaptured = false;

            // 🧩 Case 1: Already captured or canceled
            if (in_array($intent->status, ['succeeded', 'canceled'])) {
                $alreadyCaptured = true;
            } 
            else {
                // ✅ Ensure the intent has a customer
                if (!$intent->customer && $user && $user->stripe_customer_id) {
                    $intent = \Stripe\PaymentIntent::update($intent->id, [
                        'customer' => $user->stripe_customer_id,
                    ]);
                }

                // ✅ Ensure payment method is attached properly
                if ($intent->payment_method && $user && $user->stripe_customer_id) {
                    $pm = \Stripe\PaymentMethod::retrieve($intent->payment_method);
                    if ($pm && $pm->customer !== $user->stripe_customer_id) {
                        try {
                            $pm->attach(['customer' => $user->stripe_customer_id]);
                        } catch (\Exception $attachErr) {
                            Log::warning('PaymentMethod attach failed during capture', [
                                'message' => $attachErr->getMessage(),
                                'intent' => $intent->id,
                            ]);
                        }
                    }
                }

                // ✅ Case 2: Capture ≤ Authorized (normal or slightly reduced fare)
                if ($final <= $authorized) {
                    $intent->capture(['amount_to_capture' => $final]);
                }
                // ✅ Case 3: Capture + Additional Charges (final > hold)
                else {
                    // 1️⃣ Capture original hold amount first
                    $intent->capture(['amount_to_capture' => $authorized]);

                    // 2️⃣ Safely reuse same payment method (now attached)
                    $originalPaymentMethod = $intent->payment_method;
                    $customerId = $intent->customer ?? $user?->stripe_customer_id;

                    if (!$originalPaymentMethod) {
                        return response()->json([
                            'message' => 'No payment method found for extra charge.'
                        ], 400);
                    }

                    // 3️⃣ Create new PaymentIntent for extras (off-session)
                    $extraAmount = $final - $authorized;

                    \Stripe\PaymentIntent::create([
                        'amount'         => $extraAmount,
                        'currency'       => strtolower($payment->currency_code),
                        'customer'       => $customerId,
                        'payment_method' => $originalPaymentMethod,
                        'confirm'        => true,
                        'off_session'    => true,
                        'capture_method' => 'automatic',
                        'description'    => "Additional fare for {$payment->attribute} #{$payment->attribute_id}",
                        'automatic_payment_methods' => [
                            'enabled' => true,
                            'allow_redirects' => 'never',
                        ],
                    ]);
                }
            }

            // ✅ Always update DB
            $payment->update([
                'stripe_status' => 'succeeded',
                'is_captured'   => true,
                'is_paid'       => 1,
            ]);

            // ✅ Trigger post-capture hook
            if (isset($payment->hook) && function_exists($payment->hook)) {
                try {
                    call_user_func($payment->hook, $payment);
                } catch (\Throwable $e) {
                    Log::error('Stripe capture hook error', [
                        'hook'    => $payment->hook,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'status'  => $alreadyCaptured ? 'already_captured' : 'captured',
                'message' => $alreadyCaptured
                    ? 'Payment was already captured — synced.'
                    : 'Payment successfully captured with additional charges handled.',
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe capture API error', ['message' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Stripe capture general error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ]);
            return response()->json(['message' => 'Failed to capture payment'], 500);
        }
    }








    private function toMinor($amount, $currency)
    {
        $noMinor = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
        return in_array(strtoupper($currency), $noMinor)
            ? (int) $amount
            : (int) round($amount * 100);
    }
}
