# 1Way Backend – Smoke Test Runbook

This runbook describes how to run the **full trip flow smoke test** that validates the Rider + Driver apps against the Laravel backend.

## What the smoke test does

The script `scripts/smoke_trip_flow.sh` runs these steps in order:

| Step | Description |
|------|-------------|
| a | **Customer login** – POST `/api/customer/auth/login` → obtain Bearer token |
| b | **Get estimated fare** – POST `/api/customer/ride/get-estimated-fare` → returns fare and `zone_id` / `vehicle_category_id` |
| c | **Create ride** – POST `/api/customer/ride/create` → returns `trip_request_id` |
| d | **Driver login** – POST `/api/driver/auth/login` → obtain Bearer token |
| e | **Driver pending list** – GET `/api/driver/ride/pending-ride-list` with `zoneId` header → must include the created trip |
| f | **Driver accept** – POST `/api/driver/ride/trip-action` with `action=accepted` |
| g | **Driver start trip** – POST `/api/driver/ride/match-otp` with `otp=0000` (non-live env) → status becomes ongoing |
| h | **Driver complete** – PUT `/api/driver/ride/update-status` with `status=completed` |
| i | **Customer ride details** – GET `/api/customer/ride/details/{id}` → must show `current_status=completed` |

Each step is checked for HTTP 200/201 and a successful API `response_code`. The script prints **PASS** or **FAIL** with reasons and exits with 0 (all pass) or 1 (any fail).

## Prerequisites

- **Server**: Run on the app server (e.g. `ssh root@147.79.75.242`) or any host that can reach the API.
- **Credentials**: A **customer** and a **driver** account that exist in the DB and can log in (phone_or_email + password).
- **Environment**: Test coordinates must fall inside an **active zone** that has **trip fare** and **vehicle category** configured; otherwise estimated-fare or create may fail (e.g. ZONE_404, NO_ACTIVE_CATEGORY_IN_ZONE_404).
- **Driver**: The driver must have a vehicle assigned, be **online** (`is_online=1`), have a **last location** in the same zone as the trip, and the vehicle’s **vehicle_category_id** must match the trip’s. Otherwise the pending-ride-list may not return the trip.
- **Optional**: `jq` for JSON parsing (script uses `jq` for response checks).

## How to run

### 1. On the server (recommended)

```bash
cd /var/www/1way-backend

# Required: set customer and driver credentials
export CUSTOMER_PHONE="<customer_phone_or_email>"
export CUSTOMER_PASSWORD="<customer_password>"
export DRIVER_PHONE="<driver_phone_or_email>"
export DRIVER_PASSWORD="<driver_password>"
```

**Against production API (https://api.1wayride.com):**

```bash
export BASE_URL="https://api.1wayride.com"
# Leave BASE_HOST unset so no Host override is sent
./scripts/smoke_trip_flow.sh
```

**Against local nginx (same server) with Host header:**

```bash
export BASE_URL="https://127.0.0.1"
export BASE_HOST="api.1wayride.com"
./scripts/smoke_trip_flow.sh
```

This uses `curl -k -H "Host: api.1wayride.com" https://127.0.0.1/...` so nginx serves the correct vhost without 301/302 scheme issues.

### 2. One-liner (example)

```bash
CUSTOMER_PHONE="15555550124" CUSTOMER_PASSWORD="Test1234!" \
DRIVER_PHONE="15555550999" DRIVER_PASSWORD="Test1234!" \
BASE_URL="https://api.1wayride.com" \
./scripts/smoke_trip_flow.sh
```

### 3. Optional overrides

- **Coordinates** (defaults: NYC-area): `PICKUP_LAT`, `PICKUP_LNG`, `DEST_LAT`, `DEST_LNG`, `PICKUP_ADDR`, `DEST_ADDR`
- **Base URL**: `BASE_URL` (default `https://127.0.0.1`)
- **Host header**: `BASE_HOST` (default `api.1wayride.com`; set empty when calling the real domain)

## Where to look when it fails

| Failure | Where to look | What to check |
|--------|----------------|----------------|
| Customer/Driver login 403 | `storage/logs/laravel.log` | Wrong credentials, user inactive, or auth middleware |
| Get estimated fare 403 | `storage/logs/laravel.log` | Zone/coverage (ZONE_404, ZONE_RESOURCE_404), no active vehicle category, route/map API |
| Create ride 403 | `storage/logs/laravel.log`, validation | Missing/invalid fields (zone_id, vehicle_category_id, bid, etc.) |
| Pending list empty | Driver state, DB | Driver `is_online`, vehicle in same zone, `vehicle_category_id` matches trip, `zoneId` header = trip’s zone_id |
| Trip-action / match-otp / update-status 403 | `storage/logs/laravel.log` | TRIP_REQUEST_404, driver not assigned, status transition (e.g. TripStatusTransition), OTP (non-live uses 0000) |
| Redirects (301/302) | nginx, Laravel | Nginx server_name, `APP_URL`, trusted proxies, `ForceHttps` / URL scheme |

### Useful commands

```bash
# List routes for customer/driver ride endpoints
php artisan route:list --path=api/customer/ride
php artisan route:list --path=api/driver/ride

# Clear config/cache if you changed .env or config
php artisan config:clear
php artisan cache:clear

# Tail Laravel log while running the script
tail -f storage/logs/laravel.log
```

### Log locations (typical)

- **Laravel**: `storage/logs/laravel.log`
- **Nginx**: `/var/log/nginx/access.log`, `/var/log/nginx/error.log`
- **PHP-FPM**: distro-dependent (e.g. `/var/log/php*-fpm.log` or in syslog)

## Fixes applied in this repo

- **Driver matchOtp**: `$user` was used before definition when writing to `trip_status`; added `$user = auth('api')->user();` so driver_id is set correctly (Modules/TripManagement/Http/Controllers/Api/Driver/TripRequestController.php).

## Deliverables

- **Script**: `/var/www/1way-backend/scripts/smoke_trip_flow.sh` – runnable with the env vars above.
- **Runbook**: This file – `/var/www/1way-backend/RUNBOOK_SMOKE_TEST.md`.

Success: script exits 0 and prints **========== PASS ==========**. Any step failure exits 1 and prints **========== FAILED ==========** plus reasons.
