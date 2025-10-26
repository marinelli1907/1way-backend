@extends('Gateways::payment.layouts.master')

@push('script')
<script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')
<div style="max-width:420px;margin:60px auto;text-align:center;">
    <h3>Authorizing Your Ride Payment</h3>
    <p>Please do not refresh this page.</p>

    <form id="payment-form" style="margin-top:30px;">
        <div id="payment-element"></div>
        <button id="submit" type="submit" style="margin-top:20px;padding:8px 20px;">
            Authorize Payment
        </button>
        <p id="error-message" style="color:#c00;margin-top:10px;"></p>
        <p id="success-message" style="color:green;margin-top:10px;display:none;"></p>
    </form>
</div>

<script>
"use strict";

const stripe = Stripe("{{ $config->published_key }}");
const paymentId = "{{ $data->id }}";
const successUrl = "{{ url('/payment/stripe/authorized/success?payment_id='.$data->id) }}";
const intentUrl  = "{{ url('payment/stripe/intent') }}";
const csrfToken  = "{{ csrf_token() }}";

console.log("Stripe authorize starting for payment ID:", paymentId);

fetch(intentUrl, {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken
    },
    body: JSON.stringify({ payment_id: paymentId })
})
.then(res => res.json())
.then(async (data) => {
    console.log("Intent response:", data);

    if (!data.client_secret) {
        document.getElementById("error-message").textContent =
            "Failed to create PaymentIntent. Please try again.";
        return;
    }

    const clientSecret = data.client_secret;
    const elements = stripe.elements({ clientSecret });
    const paymentElement = elements.create("payment");
    paymentElement.mount("#payment-element");

    const form   = document.getElementById("payment-form");
    const button = document.getElementById("submit");
    const errMsg = document.getElementById("error-message");
    const okMsg  = document.getElementById("success-message");

    paymentElement.on("change", (event) => {
        if (event.error) {
            errMsg.textContent = event.error.message;
            button.disabled = true;
        } else {
            errMsg.textContent = "";
            button.disabled = false;
        }
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (button.disabled) return;

        button.disabled = true;
        button.textContent = "Authorizing...";
        errMsg.textContent = "";
        okMsg.style.display = "none";

        try {
            // ✅ REQUIRED BY STRIPE (deferred flow)
            const { error: submitError } = await elements.submit();
            if (submitError) {
                errMsg.textContent = submitError.message;
                button.disabled = false;
                button.textContent = "Authorize Payment";
                return;
            }

            // ✅ Now confirm the payment
            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                clientSecret,
                redirect: "if_required",
            });

            console.log("Confirm result:", paymentIntent, error);

            if (error) {
                errMsg.textContent = error.message || "Authorization failed.";
                button.disabled = false;
                button.textContent = "Authorize Payment";
                return;
            }

            if (paymentIntent && paymentIntent.status === "requires_capture") {
                okMsg.textContent = "Payment authorized successfully! Redirecting...";
                okMsg.style.display = "block";
                setTimeout(() => window.location.href = successUrl, 1000);
            } else if (paymentIntent && paymentIntent.status === "succeeded") {
                okMsg.textContent = "Payment completed successfully!";
                okMsg.style.display = "block";
                setTimeout(() => window.location.href = successUrl, 1000);
            } else {
                errMsg.textContent = "Unexpected payment status: " + paymentIntent.status;
                button.disabled = false;
                button.textContent = "Authorize Payment";
            }

        } catch (err) {
            console.error("Stripe error:", err);
            errMsg.textContent = "Something went wrong during authorization.";
            button.disabled = false;
            button.textContent = "Authorize Payment";
        }
    });
})
.catch((err) => {
    console.error("Fetch error:", err);
    document.getElementById("error-message").textContent =
        "Failed to initialize authorization. Please refresh and try again.";
});
</script>


@endsection
