#!/usr/bin/env bash
set -e

BASE_URL="https://api.1wayride.com"

echo "== FULL SYSTEM SMOKE TEST =="

echo
echo "1) Backend reachable..."
curl -fsS "$BASE_URL" >/dev/null || {
  echo "❌ Backend not reachable"
  exit 1
}
echo "✅ Backend OK"

echo
echo "2) Rider flow..."
./dev/rider_smoke_test.sh || {
  echo "❌ Rider flow failed"
  exit 1
}
echo "✅ Rider flow OK"

echo
echo "3) Driver flow..."
./dev/driver_smoke_test.sh || {
  echo "❌ Driver flow failed"
  exit 1
}
echo "✅ Driver flow OK"

echo
echo "🎉 CORE SYSTEM PASSED (auth + rides)"
