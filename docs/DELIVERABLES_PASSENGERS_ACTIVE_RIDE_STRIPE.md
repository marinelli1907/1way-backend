# Deliverables: Passengers/Pets/Scheduled + Active Ride + Stripe

## Backend code changes (file list)

### Migration
- `Modules/TripManagement/Database/Migrations/2026_03_05_000001_add_passengers_pets_scheduled_to_trip_requests_table.php` — adds `passengers_count` (unsigned tinyint nullable), `pets_count` (unsigned tinyint nullable), `scheduled_at` (timestamp nullable) to `trip_requests`.

### Model
- `Modules/TripManagement/Entities/TripRequest.php` — added `passengers_count`, `pets_count`, `scheduled_at` to `$fillable`; added casts for `passengers_count`/`pets_count` (integer), `scheduled_at` (datetime).

### Validation
- `Modules/TripManagement/Http/Requests/RideRequestCreate.php` — rules: `passengers_count` nullable|integer|min:0|max:20, `pets_count` nullable|integer|min:0|max:5, `scheduled_at` nullable|date_format:Y-m-d H:i:s.

### Service
- `Modules/TripManagement/Service/TripRequestService.php` — in `storeTrip()` sets `passengers_count`, `pets_count`, `scheduled_at` from request (passengers/pets clamped to 0–20 / 0–5); added `getCustomerActiveOrUpcomingRide()` returning `['ride' => TripRequest|null, 'kind' => 'active'|'upcoming'|null]`.
- `Modules/TripManagement/Service/Interface/TripRequestServiceInterface.php` — added `getCustomerActiveOrUpcomingRide(): array`.

### Resource (API response)
- `Modules/TripManagement/Transformers/TripRequestResource.php` — added `passengers_count`, `pets_count`, `scheduled_at` (ISO8601) to payload for rider/driver.

### Controller + routes
- `Modules/TripManagement/Http/Controllers/Api/New/Customer/TripRequestController.php` — added `activeRide()`: GET returns `{ ride: TripRequestResource|null, kind: 'active'|'upcoming'|null }`.
- `Modules/TripManagement/Routes/api.php` — added `GET customer/ride/active-ride` → `activeRide`.

### Stripe (rider app PaymentSheet)
- `routes/api.php` — added `POST api/payment/stripe/create-intent` (auth:api) → `StripePaymentController::createManualIntent`. Request body: `{ "payment_id": "uuid" }`. Response: `{ "client_secret", "payment_intent_id", "status" }`.

---

## Rider app (what to implement)

The rider app is not in this repo; ensure it does the following.

### Payment flow (Stripe PaymentSheet)
1. **Create payment record**  
   Use existing flow that creates a `PaymentRequest` for the trip (e.g. after “Select Driver” / before “Payment”). That yields a `payment_id` (UUID).
2. **Create PaymentIntent from backend**  
   Call **POST** `{API_BASE}/api/payment/stripe/create-intent` with:
   - Header: `Authorization: Bearer {customer_token}`
   - Body: `{ "payment_id": "<uuid>" }`  
   Backend returns `client_secret`, `payment_intent_id`, `status`. Use `client_secret` with Stripe’s PaymentSheet (e.g. `presentPaymentSheet`).
3. **After successful payment**  
   Use existing success/capture flow (e.g. redirect or callback to `authorized/success` or your app’s “Booking Confirmed” screen).

### Navigation
- **Select Driver** → **Payment** (show PaymentSheet using `client_secret` from step 2) → **Booking Confirmed** (after payment success).

### Active / upcoming ride
- Call **GET** `{API_BASE}/api/customer/ride/active-ride` with `Authorization: Bearer {customer_token}`.
- Response: `{ "ride": <trip object or null>, "kind": "active"|"upcoming"|null }`.
- **Rider “active bar”:**  
  - If `kind === 'active'`: show current active ride.  
  - If `kind === 'upcoming'`: show next scheduled ride (use `scheduled_at`, `passengers_count`, `pets_count` from `ride`).  
  - If `ride === null`: no active or upcoming ride.

### Ride creation payload
- On **POST** `api/customer/ride/create` (or equivalent), send when applicable:
  - `passengers_count` (0–20)
  - `pets_count` (0–5)
  - `scheduled_at` (e.g. `Y-m-d H:i:s` for future pickup time)

---

## Short test checklist

### Home booking NOW with passengers/pets → driver select → Stripe → confirmed → active bar
- [ ] Create a ride **now** with `passengers_count` and `pets_count` set.
- [ ] Complete driver selection.
- [ ] On Payment step: rider app calls `POST api/payment/stripe/create-intent` with `payment_id`, then shows PaymentSheet with returned `client_secret`.
- [ ] Complete card payment; navigate to “Booking Confirmed”.
- [ ] **GET** `api/customer/ride/active-ride`: response has `kind: "active"` and `ride` with `passengers_count`, `pets_count`; active bar shows this ride.

### Home booking FUTURE date/time → scheduled ride in active bar
- [ ] Create a ride with `scheduled_at` set to a future datetime (e.g. tomorrow 10:00).
- [ ] Complete flow (driver select, payment if applicable).
- [ ] **GET** `api/customer/ride/active-ride`: with no current active ride, response has `kind: "upcoming"` and `ride` with `scheduled_at`; active bar shows “Upcoming” with that time.

### Event booking
- [ ] Use same flow as home booking (create → driver select → Stripe); ensure no errors and Stripe PaymentSheet appears when payment method is Stripe.

### Driver app
- [ ] Driver sees trip details including `passengers_count`, `pets_count`, and `scheduled_at` (from trip details/ride list API that uses `TripRequestResource`).

---

## API summary

| What | Method | Endpoint | Auth | Body / response |
|------|--------|----------|------|------------------|
| Active/upcoming ride | GET | `api/customer/ride/active-ride` | Bearer | Response: `{ ride, kind }` |
| Create Stripe intent | POST | `api/payment/stripe/create-intent` | Bearer | Body: `{ payment_id }` → `{ client_secret, payment_intent_id, status }` |
| Create ride | POST | `api/customer/ride/create` | Bearer | Optional: `passengers_count`, `pets_count`, `scheduled_at` |
