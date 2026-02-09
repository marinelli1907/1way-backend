#!/usr/bin/env bash
set -euo pipefail

BASE="${BASE:-https://api.1wayride.com}"
LOGIN="${LOGIN:-15555550999}"
PASS="${PASS:-Test1234!}"
LIMIT="${LIMIT:-20}"
OFFSET="${OFFSET:-1}"

echo "== Driver login =="
TOKEN="$(curl -sS -X POST "$BASE/api/driver/auth/login" \
  -H "Accept: application/json" \
  --data-urlencode "phone_or_email=$LOGIN" \
  --data-urlencode "password=$PASS" | jq -r '.data.token')"

if [[ -z "$TOKEN" || "$TOKEN" == "null" ]]; then
  echo "LOGIN FAILED:"
  curl -sS -X POST "$BASE/api/driver/auth/login" \
    -H "Accept: application/json" \
    --data-urlencode "phone_or_email=$LOGIN" \
    --data-urlencode "password=$PASS" | jq .
  exit 1
fi

call_api () {
  local label="$1"
  local url="$2"

  echo "== $label =="
  local out
  out="$(curl -sS "$url" -H "Authorization: Bearer $TOKEN" -H "Accept: application/json")"

  # If API complains about limit/offset, retry with defaults
  if echo "$out" | jq -e '.errors[]? | select(.error_code=="limit" or .error_code=="offset")' >/dev/null 2>&1; then
    if [[ "$url" == *"?"* ]]; then
      out="$(curl -sS "${url}&limit=${LIMIT}&offset=${OFFSET}" \
        -H "Authorization: Bearer $TOKEN" -H "Accept: application/json")"
    else
      out="$(curl -sS "${url}?limit=${LIMIT}&offset=${OFFSET}" \
        -H "Authorization: Bearer $TOKEN" -H "Accept: application/json")"
    fi
  fi

  echo "$out" | jq .
}

call_api "current-ride-status" "$BASE/api/driver/ride/current-ride-status"
call_api "ride/list" "$BASE/api/driver/ride/list"
call_api "pending-ride-list" "$BASE/api/driver/ride/pending-ride-list"

echo "== ride/details (from current ride) =="
RIDE_ID="$(curl -sS "$BASE/api/driver/ride/current-ride-status" \
  -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" | jq -r '.data.id // empty')"

if [[ -n "$RIDE_ID" ]]; then
  call_api "ride/details/$RIDE_ID" "$BASE/api/driver/ride/details/$RIDE_ID"
else
  echo "No active ride to test details."
fi

echo "== OK =="
