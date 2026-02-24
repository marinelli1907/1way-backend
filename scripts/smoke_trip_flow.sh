#!/usr/bin/env bash
#
# 1Way Backend – Full trip flow smoke test
# Tests: customer login -> estimated fare -> create ride -> driver login -> pending list ->
#        driver accept -> driver start (match OTP) -> driver complete -> customer ride details
#
# Usage:
#   BASE_URL="https://api.1wayride.com" \
#   CUSTOMER_PHONE="15555550124" CUSTOMER_PASSWORD="Test1234!" \
#   DRIVER_PHONE="15555550999"   DRIVER_PASSWORD="Test1234!" \
#   ./scripts/smoke_trip_flow.sh
#
# For local nginx with Host header:
#   BASE_URL="https://127.0.0.1" BASE_HOST="api.1wayride.com" \
#   CUSTOMER_PHONE=... DRIVER_PHONE=... ./scripts/smoke_trip_flow.sh
#
set -euo pipefail

BASE_URL="${BASE_URL:-https://127.0.0.1}"
BASE_HOST="${BASE_HOST:-api.1wayride.com}"
CUSTOMER_PHONE="${CUSTOMER_PHONE:-}"
CUSTOMER_PASSWORD="${CUSTOMER_PASSWORD:-}"
DRIVER_PHONE="${DRIVER_PHONE:-}"
DRIVER_PASSWORD="${DRIVER_PASSWORD:-}"

# Test coordinates (must lie inside an active zone with trip_fare and vehicle category)
PICKUP_LAT="${PICKUP_LAT:-40.7128}"
PICKUP_LNG="${PICKUP_LNG:--74.0060}"
DEST_LAT="${DEST_LAT:-40.7580}"
DEST_LNG="${DEST_LNG:--73.9855}"
PICKUP_ADDR="${PICKUP_ADDR:-123 Main St}"
DEST_ADDR="${DEST_ADDR:-456 Oak Ave}"

FAILED=0
REASONS=()

curl_extra=()
if [[ -n "${BASE_HOST:-}" ]]; then
  curl_extra+=(-H "Host: $BASE_HOST")
fi
if [[ "$BASE_URL" == https* ]]; then
  curl_extra+=(-k)
fi

api() {
  local method="${1:-GET}"
  local path="$2"
  local data="${3:-}"
  local token="${4:-}"
  local extra_header="${5:-}"
  local url="${BASE_URL%/}/api${path}"
  local cmd=(curl -sS -w "\n%{http_code}" -X "$method" "$url" "${curl_extra[@]}" -H "Accept: application/json")
  if [[ -n "$token" ]]; then
    cmd+=(-H "Authorization: Bearer $token")
  fi
  if [[ -n "$extra_header" ]]; then
    cmd+=(-H "$extra_header")
  fi
  if [[ -n "$data" && "$method" == POST || "$method" == PUT ]]; then
    cmd+=(-H "Content-Type: application/json" -d "$data")
  fi
  "${cmd[@]}"
}

check_json_ok() {
  local body="$1"
  local code="$2"
  local step="$3"
  if [[ "$code" != 200 && "$code" != 201 ]]; then
    REASONS+=("$step: HTTP $code")
    echo "$body" | jq . 2>/dev/null || echo "$body"
    return 1
  fi
  if echo "$body" | jq -e '.errors != null and (.errors | length) > 0' >/dev/null 2>&1; then
    REASONS+=("$step: API returned errors")
    echo "$body" | jq .
    return 1
  fi
  local rc
  rc="$(echo "$body" | jq -r '.response_code // empty')"
  case "$rc" in
    default_200|default_201|default_store_200|default_update_200|default_verified_200|auth_login_200|trip_request_store_200) ;;
    "")
      # no response_code is ok for some endpoints
      ;;
    *)
      REASONS+=("$step: response_code=$rc")
      echo "$body" | jq .
      return 1
      ;;
  esac
  return 0
}

assert_not_empty() {
  local val="$1"
  local step="$2"
  local name="${3:-value}"
  if [[ -z "$val" || "$val" == "null" ]]; then
    REASONS+=("$step: $name is empty")
    return 1
  fi
  return 0
}

echo "========== 1Way Trip Flow Smoke Test =========="
echo "BASE_URL=$BASE_URL BASE_HOST=${BASE_HOST:-none}"

if [[ -z "$CUSTOMER_PHONE" || -z "$CUSTOMER_PASSWORD" || -z "$DRIVER_PHONE" || -z "$DRIVER_PASSWORD" ]]; then
  echo "Set CUSTOMER_PHONE, CUSTOMER_PASSWORD, DRIVER_PHONE, DRIVER_PASSWORD (and optionally BASE_URL, BASE_HOST)."
  exit 1
fi

# ---- a) Customer login ----
echo ""
echo "[Step a] Customer login"
res=$(api POST "/customer/auth/login" "{\"phone_or_email\":\"$CUSTOMER_PHONE\",\"password\":\"$CUSTOMER_PASSWORD\"}")
body="${res%$'\n'*}"
code="${res##*$'\n'}"
if ! check_json_ok "$body" "$code" "Customer login"; then
  FAILED=1
  echo "FAIL: customer login"
else
  CUSTOMER_TOKEN="$(echo "$body" | jq -r '.data.token')"
  if ! assert_not_empty "$CUSTOMER_TOKEN" "Customer login" "token"; then
    FAILED=1
    echo "FAIL: no token in response"
  else
    echo "PASS: customer token obtained"
  fi
fi

if [[ $FAILED -ne 0 ]]; then
  echo ""
  echo "========== FAILED =========="
  printf '%s\n' "${REASONS[@]}"
  exit 1
fi

# ---- b) Customer get-estimated-fare ----
echo ""
echo "[Step b] Customer get-estimated-fare"
EST_PAYLOAD="{
  \"pickup_coordinates\": \"[$PICKUP_LAT, $PICKUP_LNG]\",
  \"destination_coordinates\": \"[$DEST_LAT, $DEST_LNG]\",
  \"pickup_address\": \"$PICKUP_ADDR\",
  \"destination_address\": \"$DEST_ADDR\",
  \"type\": \"ride_request\"
}"
res=$(api POST "/customer/ride/get-estimated-fare" "$EST_PAYLOAD" "$CUSTOMER_TOKEN")
body="${res%$'\n'*}"
code="${res##*$'\n'}"
if ! check_json_ok "$body" "$code" "Get estimated fare"; then
  FAILED=1
  echo "FAIL: get-estimated-fare"
else
  # Response can be single object (parcel) or array of vehicle options (ride_request). Take first element if array.
  DATA="$(echo "$body" | jq '.data')"
  if echo "$DATA" | jq -e 'type == "array"' >/dev/null 2>&1; then
    ZONE_ID="$(echo "$DATA" | jq -r '.[0].zone_id')"
    VEHICLE_CATEGORY_ID="$(echo "$DATA" | jq -r '.[0].vehicle_category_id')"
    ESTIMATED_FARE="$(echo "$DATA" | jq -r '.[0].estimated_fare')"
    ESTIMATED_DISTANCE="$(echo "$DATA" | jq -r '.[0].estimated_distance')"
    ESTIMATED_DURATION="$(echo "$DATA" | jq -r '.[0].estimated_duration')"
  else
    ZONE_ID="$(echo "$DATA" | jq -r '.zone_id')"
    VEHICLE_CATEGORY_ID="$(echo "$DATA" | jq -r '.vehicle_category_id')"
    ESTIMATED_FARE="$(echo "$DATA" | jq -r '.estimated_fare')"
    ESTIMATED_DISTANCE="$(echo "$DATA" | jq -r '.estimated_distance')"
    ESTIMATED_DURATION="$(echo "$DATA" | jq -r '.estimated_duration')"
  fi
  if ! assert_not_empty "$ZONE_ID" "Get estimated fare" "zone_id"; then
    FAILED=1
  fi
  if ! assert_not_empty "$VEHICLE_CATEGORY_ID" "Get estimated fare" "vehicle_category_id"; then
    FAILED=1
  fi
  if [[ -z "$ESTIMATED_FARE" || "$ESTIMATED_FARE" == "null" ]]; then
    ESTIMATED_FARE="0"
  fi
  if [[ -z "$ESTIMATED_DISTANCE" || "$ESTIMATED_DISTANCE" == "null" ]]; then
    ESTIMATED_DISTANCE="0"
  fi
  if [[ -z "$ESTIMATED_DURATION" || "$ESTIMATED_DURATION" == "null" ]]; then
    ESTIMATED_DURATION="0"
  fi
  if [[ $FAILED -eq 0 ]]; then
    echo "PASS: estimated fare (zone_id=$ZONE_ID, vehicle_category_id=$VEHICLE_CATEGORY_ID, fare=$ESTIMATED_FARE)"
  fi
fi

if [[ $FAILED -ne 0 ]]; then
  echo ""
  echo "========== FAILED =========="
  printf '%s\n' "${REASONS[@]}"
  exit 1
fi

# ---- c) Customer create ride ----
echo ""
echo "[Step c] Customer create ride"
# customer_coordinates / customer_request_coordinates: same as pickup for smoke
CREATE_PAYLOAD="{
  \"pickup_coordinates\": \"[$PICKUP_LAT, $PICKUP_LNG]\",
  \"destination_coordinates\": \"[$DEST_LAT, $DEST_LNG]\",
  \"customer_coordinates\": \"[$PICKUP_LAT, $PICKUP_LNG]\",
  \"customer_request_coordinates\": \"[$PICKUP_LAT, $PICKUP_LNG]\",
  \"pickup_address\": \"$PICKUP_ADDR\",
  \"destination_address\": \"$DEST_ADDR\",
  \"estimated_time\": \"$ESTIMATED_DURATION\",
  \"estimated_distance\": \"$ESTIMATED_DISTANCE\",
  \"estimated_fare\": \"$ESTIMATED_FARE\",
  \"actual_fare\": \"$ESTIMATED_FARE\",
  \"vehicle_category_id\": \"$VEHICLE_CATEGORY_ID\",
  \"type\": \"ride_request\",
  \"bid\": false,
  \"zone_id\": $ZONE_ID
}"
res=$(api POST "/customer/ride/create" "$CREATE_PAYLOAD" "$CUSTOMER_TOKEN")
body="${res%$'\n'*}"
code="${res##*$'\n'}"
if ! check_json_ok "$body" "$code" "Create ride"; then
  FAILED=1
  echo "FAIL: create ride"
else
  TRIP_REQUEST_ID="$(echo "$body" | jq -r '.data.id // .data.trip_request_id // .data[0].id // empty')"
  if ! assert_not_empty "$TRIP_REQUEST_ID" "Create ride" "trip_request_id"; then
    FAILED=1
  else
    echo "PASS: ride created (trip_request_id=$TRIP_REQUEST_ID)"
  fi
fi

if [[ $FAILED -ne 0 ]]; then
  echo ""
  echo "========== FAILED =========="
  printf '%s\n' "${REASONS[@]}"
  exit 1
fi

# ---- d) Driver login ----
echo ""
echo "[Step d] Driver login"
res=$(api POST "/driver/auth/login" "{\"phone_or_email\":\"$DRIVER_PHONE\",\"password\":\"$DRIVER_PASSWORD\"}")
body="${res%$'\n'*}"
code="${res##*$'\n'}"
if ! check_json_ok "$body" "$code" "Driver login"; then
  FAILED=1
  echo "FAIL: driver login"
else
  DRIVER_TOKEN="$(echo "$body" | jq -r '.data.token')"
  if ! assert_not_empty "$DRIVER_TOKEN" "Driver login" "token"; then
    FAILED=1
  else
    echo "PASS: driver token obtained"
  fi
fi

if [[ $FAILED -ne 0 ]]; then
  echo ""
  echo "========== FAILED =========="
  printf '%s\n' "${REASONS[@]}"
  exit 1
fi

# ---- e) Driver pending-ride-list (with zoneId so trip is visible) ----
echo ""
echo "[Step e] Driver pending-ride-list"
res=$(api GET "/driver/ride/pending-ride-list?limit=20&offset=1" "" "$DRIVER_TOKEN" "zoneId: $ZONE_ID")
body="${res%$'\n'*}"
code="${res##*$'\n'}"
if ! check_json_ok "$body" "$code" "Pending ride list"; then
  FAILED=1
  echo "FAIL: pending-ride-list"
else
  FOUND="$(echo "$body" | jq -r --arg id "$TRIP_REQUEST_ID" '.data[]? | select(.id == ($id | tonumber) or (.id | tostring) == $id) | .id' 2>/dev/null | head -1)"
  if [[ -z "$FOUND" ]]; then
    FOUND="$(echo "$body" | jq -r --arg id "$TRIP_REQUEST_ID" '.data[]? | select(.id == $id) | .id' 2>/dev/null | head -1)"
  fi
  if [[ -z "$FOUND" ]]; then
    REASONS+=("Pending list: trip_request_id $TRIP_REQUEST_ID not in list (driver zone/vehicle may not match)")
    echo "Response data: $(echo "$body" | jq -c '.data[0:3]')"
    FAILED=1
  else
    echo "PASS: pending list contains trip_request_id=$TRIP_REQUEST_ID"
  fi
fi

if [[ $FAILED -ne 0 ]]; then
  echo ""
  echo "========== FAILED =========="
  printf '%s\n' "${REASONS[@]}"
  exit 1
fi

# ---- f) Driver trip-action accept ----
echo ""
echo "[Step f] Driver trip-action accept"
res=$(api POST "/driver/ride/trip-action" "{\"trip_request_id\":\"$TRIP_REQUEST_ID\",\"action\":\"accepted\"}" "$DRIVER_TOKEN")
body="${res%$'\n'*}"
code="${res##*$'\n'}"
if ! check_json_ok "$body" "$code" "Driver accept"; then
  FAILED=1
  echo "FAIL: trip-action accept"
else
  echo "PASS: trip accepted"
fi

if [[ $FAILED -ne 0 ]]; then
  echo ""
  echo "========== FAILED =========="
  printf '%s\n' "${REASONS[@]}"
  exit 1
fi

# ---- g) Driver start (match OTP; non-live uses 0000) ----
echo ""
echo "[Step g] Driver match OTP (start trip)"
res=$(api POST "/driver/ride/match-otp" "{\"trip_request_id\":\"$TRIP_REQUEST_ID\",\"otp\":\"0000\"}" "$DRIVER_TOKEN")
body="${res%$'\n'*}"
code="${res##*$'\n'}"
if ! check_json_ok "$body" "$code" "Match OTP"; then
  FAILED=1
  echo "FAIL: match-otp (start)"
else
  echo "PASS: trip started (ongoing)"
fi

if [[ $FAILED -ne 0 ]]; then
  echo ""
  echo "========== FAILED =========="
  printf '%s\n' "${REASONS[@]}"
  exit 1
fi

# ---- h) Driver trip-action complete ----
echo ""
echo "[Step h] Driver ride status update -> completed"
res=$(api PUT "/driver/ride/update-status" "{\"trip_request_id\":\"$TRIP_REQUEST_ID\",\"status\":\"completed\"}" "$DRIVER_TOKEN")
body="${res%$'\n'*}"
code="${res##*$'\n'}"
if ! check_json_ok "$body" "$code" "Driver complete"; then
  FAILED=1
  echo "FAIL: update-status completed"
else
  echo "PASS: trip completed"
fi

if [[ $FAILED -ne 0 ]]; then
  echo ""
  echo "========== FAILED =========="
  printf '%s\n' "${REASONS[@]}"
  exit 1
fi

# ---- i) Customer ride details ----
echo ""
echo "[Step i] Customer ride details"
res=$(api GET "/customer/ride/details/$TRIP_REQUEST_ID" "" "$CUSTOMER_TOKEN")
body="${res%$'\n'*}"
code="${res##*$'\n'}"
if ! check_json_ok "$body" "$code" "Ride details"; then
  FAILED=1
  echo "FAIL: ride details"
else
  STATUS="$(echo "$body" | jq -r '.data.current_status // empty')"
  if [[ "$STATUS" != "completed" ]]; then
    REASONS+=("Ride details: expected current_status=completed, got $STATUS")
    FAILED=1
    echo "FAIL: status is $STATUS"
  else
    echo "PASS: ride details show status=completed"
  fi
fi

echo ""
if [[ $FAILED -ne 0 ]]; then
  echo "========== FAILED =========="
  printf '%s\n' "${REASONS[@]}"
  exit 1
fi

echo "========== PASS =========="
echo "Full trip flow: customer quote -> create -> driver accept -> start -> complete -> details OK."
exit 0
