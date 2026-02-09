#!/usr/bin/env bash
set -euo pipefail

BASE="https://api.1wayride.com"
PHONE="${DRIVER_PHONE:-15555550124}"
PASS="${DRIVER_PASS:-password}"

echo "== Driver Smoke Test =="
echo "Using driver phone=$PHONE"

echo "1) Login driver..."
LOGIN_JSON=$(curl -sS -X POST "$BASE/api/driver/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-urlencode "phone_or_email=$PHONE" \
  --data-urlencode "password=$PASS")

TOKEN=$(php -r '
  $j = json_decode(stream_get_contents(STDIN), true);
  echo $j["data"]["token"] ?? "";
' <<< "$LOGIN_JSON")

if [[ -z "$TOKEN" ]]; then
  echo "❌ Driver login failed:"
  echo "$LOGIN_JSON"
  exit 1
fi

echo "✅ Driver login OK"

AUTH=("-H" "Accept: application/json" "-H" "Authorization: Bearer $TOKEN")

echo "2) Set driver online (best-effort)..."
curl -sS -X POST "$BASE/api/driver/update-online-status" "${AUTH[@]}" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-urlencode "is_online=1" >/dev/null || true

echo "✅ Online status attempted"

echo "3) Fetch pending ride list..."
LIMIT="${LIMIT:-20}"
OFFSET="${OFFSET:-1}"

PENDING_JSON=$(curl -sS -X GET \
  "$BASE/api/driver/ride/pending-ride-list?limit=$LIMIT&offset=$OFFSET" \
  "${AUTH[@]}")

echo "$PENDING_JSON" | head -c 800; echo

RIDE_ID=$(php -r '
$j=json_decode(stream_get_contents(STDIN),true);
$rid="";
if (is_array($j)) {
  if (!empty($j["data"][0]["id"])) $rid=$j["data"][0]["id"];
  if (!$rid && !empty($j["data"]["trips"][0]["id"])) $rid=$j["data"]["trips"][0]["id"];
  if (!$rid && !empty($j["data"]["ride_requests"][0]["id"])) $rid=$j["data"]["ride_requests"][0]["id"];
}
echo $rid;
' <<<"$PENDING_JSON")

if [[ -z "$RIDE_ID" ]]; then
  echo "❌ No pending rides found for driver."
  echo "Create a ride with rider_smoke_test.sh first, then rerun this."
  exit 2
fi

echo "✅ Found pending ride: $RIDE_ID"

echo "4) Try ACCEPT via trip-action (best-effort)..."
ACCEPT_JSON=$(curl -sS -X POST "$BASE/api/driver/ride/trip-action" "${AUTH[@]}" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-urlencode "ride_request_id=$RIDE_ID" \
  --data-urlencode "action=accepted" || true)

echo "$ACCEPT_JSON" | head -c 800; echo

echo "5) Try update-status => accepted (fallback)..."
UPD_JSON=$(curl -sS -X PUT "$BASE/api/driver/ride/update-status" "${AUTH[@]}" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-urlencode "id=$RIDE_ID" \
  --data-urlencode "status=accepted" || true)

echo "$UPD_JSON" | head -c 800; echo

echo "6) Check current ride status..."
curl -sS -X GET "$BASE/api/driver/ride/details/$RIDE_ID" "${AUTH[@]}" | head -c 1000; echo

echo "🎉 Driver smoke test finished (accept flow attempted)."
echo "Next: we’ll implement start/complete once we confirm the exact payloads the controller expects."
