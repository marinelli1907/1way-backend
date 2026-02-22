# 1Way Backend API Contract

Audit of backend API vs Rider and Driver app usage. Base URL for apps: `{BASE_URL}/api` (e.g. `https://api.1wayride.com/api` or `http://localhost:8000/api`).

---

## Auth

| Endpoint | Method | Auth | Request | Response | Used by |
|----------|--------|------|---------|----------|---------|
| `api/customer/auth/login` | POST | no | `email` or `phone`, `password` | `{ response_code, message, data: { token, customer } }` | Rider (as customer) |
| `api/customer/auth/registration` | POST | no | `first_name`, `last_name`, `email`, `phone`, `password` | same as login | Rider |
| `api/driver/auth/login` | POST | no | `email`, `password` (or phone) | `{ token, driver }` or wrapped in `data` | Driver ✓ |
| `api/driver/auth/registration` | POST | no | `first_name`, `last_name`, `email`, `phone`, `password`, `service` | same as login | Driver ✓ |
| `api/driver/auth/send-otp` | POST | no | `phone` or `email` | OTP sent | Driver |
| `api/driver/auth/forget-password` | POST | no | `email` | message | Driver ✓ |
| `api/driver/auth/reset-password` | POST | no | `otp`, `password`, `password_confirmation` | message | Driver ✓ |
| `api/user/logout` | POST | Bearer | — | 200 | Rider ✓, Driver ✓ |
| `api/user/profile` | GET | Bearer | — | **MISMATCH:** Apps call this; backend had only `api/customer/info` and `api/driver/info`. **Fixed:** alias added that delegates by user_type. | Rider, Driver |
| `api/customer/info` | GET | Bearer (customer) | — | `{ response_code, message, data: CustomerResource }` | Backend canonical |
| `api/driver/info` | GET | Bearer (driver) | — | `{ response_code, message, data: DriverResource }` | Backend canonical |

**Error shape (typical):** `{ response_code, message, errors?: [...] }` with HTTP 4xx/5xx.

---

## Rider (Customer) – Trip flow

| Endpoint | Method | Auth | Request | Response | Used by |
|----------|--------|------|---------|----------|---------|
| `api/customer/ride/create` | POST | Bearer | pickup/dropoff, vehicle_category_id, etc. | trip request | Rider |
| `api/customer/ride/list` | GET | Bearer | query: limit, offset | list of trips | Rider |
| `api/customer/ride/details/{trip_request_id}` | GET | Bearer | — | trip details | Rider |
| `api/customer/ride/trip-action` | POST | Bearer | action (e.g. cancel) | updated trip | Rider |
| `api/customer/ride/get-estimated-fare` | POST | Bearer | route params | fare estimate | Rider |
| `api/customer/ride/final-fare` | GET | Bearer | trip_request_id (query) | final fare | Rider |
| `api/customer/ride/update-status/{trip_request_id}` | PUT | Bearer | status | updated trip | Rider |

---

## Driver – Trip flow

| Endpoint | Method | Auth | Request | Response | Used by |
|----------|--------|------|---------|----------|---------|
| `api/driver/ride/list` | GET | Bearer | limit, offset | driver's trips | Driver (map to “my” jobs) |
| `api/driver/ride/pending-ride-list` | GET | Bearer | — | available/pending requests | Driver (map to “available” jobs) ✓ |
| `api/driver/ride/details/{ride_request_id}` | GET | Bearer | — | trip details | Driver ✓ |
| `api/driver/ride/bid` | POST | Bearer | trip_request_id, amount, etc. | bid result | Driver ✓ |
| `api/driver/ride/trip-action` | POST | Bearer | action (accept, etc.) | trip | Driver ✓ |
| `api/driver/ride/update-status` | PUT | Bearer | status (e.g. accepted, ongoing, completed) | updated trip | Driver ✓ |
| `api/driver/ride/arrival-time` | PUT | Bearer | — | updated trip | Driver |
| `api/driver/ride/current-ride-status` | GET | Bearer | — | current ride | Driver |

**Driver app mismatch (BACKEND_API.md vs reality):** Doc describes `/api/auth/login`, `/api/jobs/available`, `/api/jobs/:id/accept`. Backend uses `api/driver/auth/login`, `api/driver/ride/pending-ride-list`, `api/driver/ride/trip-action` (accept). Driver app code already uses `/driver/auth/login`; jobs layer in app uses `/jobs/*` which does not exist — app should call `api/driver/ride/*` instead.

---

## Base URL and headers (apps)

- **Rider:** `EXPO_PUBLIC_API_BASE_URL` or default `https://api.1wayride.com/api`; `Accept: application/json`, `Content-Type: application/json`; token in `Authorization: Bearer {token}`.
- **Driver:** `ENV.API_BASE_URL` from `EXPO_PUBLIC_API_BASE_URL` or app config, default `http://YOUR_SERVER_IP:8000/api`; same headers; token from AsyncStorage key `@1way/auth_token`.

---

## Summary of fixes applied

1. **api/user/profile** – Backend did not have this route; Rider and Driver both call GET `/user/profile`. Added an alias that, for the authenticated user, returns the same payload as `api/customer/info` or `api/driver/info` depending on `user_type`.
2. **Driver app** – Auth and logout already match backend. Profile now works via `api/user/profile`. Ride/jobs: driver app should use `api/driver/ride/*` (pending-ride-list, list, details, bid, trip-action, update-status) instead of non-existent `/api/jobs/*`.
3. **Rider app** – Auth and profile: use `api/customer/auth/login`, `api/customer/auth/registration`, and GET `api/user/profile` (or `api/customer/info`). Trip endpoints as in table above.

---

## Error responses

- **401 Unauthorized:** missing or invalid token; or user type mismatch (e.g. customer token on driver route).
- **403 / 4xx:** validation or business rule (e.g. `response_code` in body, `errors` array).
- **500:** server error; body may still be JSON with `message`.

All JSON responses use `Accept: application/json` and `Content-Type: application/json`.
