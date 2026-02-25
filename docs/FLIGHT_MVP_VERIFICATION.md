# Flight MVP Verification

## Commands

```bash
php artisan route:list | rg -n "flights|flight\.ics|trip.*flight|airport" -S
php artisan migrate:status | rg -n "flight|trip_flight" -S || true
php artisan migrate --force
./vendor/bin/phpunit --filter Flight --testdox
```

## Expected route proof

- `POST api/customer/flights/lookup`
- `GET api/customer/ride/{trip_request_id}/flight.ics`

## Example lookup curl

```bash
curl -X POST "http://localhost/api/customer/flights/lookup"   -H "Authorization: Bearer <CUSTOMER_TOKEN>"   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{
    "input_type":"flight_number",
    "flight_number":"AA123",
    "date":"2026-02-24",
    "ride_airport_mode":"airport_pickup"
  }'
```

## Example create ride with flight curl

```bash
curl -X POST "http://localhost/api/customer/ride/create"   -H "Authorization: Bearer <CUSTOMER_TOKEN>"   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{
    "pickup_coordinates":"[41.4993,-81.6944]",
    "destination_coordinates":"[41.4117,-81.8498]",
    "customer_coordinates":"[41.4993,-81.6944]",
    "customer_request_coordinates":"[41.4993,-81.6944]",
    "estimated_time":15,
    "estimated_distance":7,
    "estimated_fare":20,
    "bid":false,
    "pickup_address":"Downtown",
    "destination_address":"Airport",
    "type":"ride_request",
    "zone_id":1,
    "ride_airport_mode":"airport_pickup",
    "flight_input_type":"flight_number",
    "flight_number":"DL404",
    "flight_date":"2026-02-24"
  }'
```

## Example ICS curl

```bash
curl -i -X GET "http://localhost/api/customer/ride/<TRIP_ID>/flight.ics"   -H "Authorization: Bearer <CUSTOMER_TOKEN>"   -H "Accept: text/calendar"
```

## Environment variables

```env
FLIGHT_PROVIDER=mock
DEFAULT_AIRPORT_BUFFER_MIN=25
# Optional real provider
# FLIGHT_PROVIDER=real
# FLIGHT_PROVIDER_KEY=your_key
# FLIGHT_PROVIDER_BASE_URL=http://api.aviationstack.com/v1
```
