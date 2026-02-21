<?php

namespace App\Lib;

/**
 * 1Way Units Helper
 * -----------------
 * All display-level conversions live here.
 * Backend always stores metric (km, kg, °C) and plain float amounts.
 * This class converts at the DISPLAY layer only — never mutate stored data.
 *
 * Usage:
 *   Units::distance(42.5)          → "26.41 mi"
 *   Units::speed(100)              → "62.14 mph"
 *   Units::weight(1.5)             → "3.31 lbs"
 *   Units::temperature(25)         → "77.00 °F"
 *   Units::currency(19.99)         → "$19.99"
 *   Units::distanceRaw(42.5)       → 26.407... (float, no label)
 */
class Units
{
    // ─────────────────────────────────────────────
    // Distance  (stored: km)
    // ─────────────────────────────────────────────

    /**
     * Convert km to display unit and return formatted string with label.
     * @param float $km   Distance in kilometres
     * @param int   $decimals
     */
    public static function distance(float $km, int $decimals = 2): string
    {
        $cfg = config('units.distance');
        $value = $cfg['unit'] === 'mi'
            ? $km * $cfg['km_to_mi']
            : $km;

        return number_format($value, $decimals) . ' ' . $cfg['label'];
    }

    /** Raw numeric conversion (no label). */
    public static function distanceRaw(float $km): float
    {
        $cfg = config('units.distance');
        return $cfg['unit'] === 'mi' ? $km * $cfg['km_to_mi'] : $km;
    }

    /** Convert display-unit input back to km for storage. */
    public static function distanceToKm(float $value): float
    {
        $cfg = config('units.distance');
        return $cfg['unit'] === 'mi' ? $value / $cfg['km_to_mi'] : $value;
    }

    /** Label only: "mi" or "km" */
    public static function distanceLabel(): string
    {
        return config('units.distance.label');
    }

    // ─────────────────────────────────────────────
    // Speed  (stored: km/h)
    // ─────────────────────────────────────────────

    public static function speed(float $kmh, int $decimals = 1): string
    {
        $cfg   = config('units.speed');
        $value = $cfg['unit'] === 'mph'
            ? $kmh * $cfg['kmh_to_mph']
            : $kmh;

        return number_format($value, $decimals) . ' ' . $cfg['label'];
    }

    public static function speedRaw(float $kmh): float
    {
        $cfg = config('units.speed');
        return $cfg['unit'] === 'mph' ? $kmh * $cfg['kmh_to_mph'] : $kmh;
    }

    public static function speedLabel(): string
    {
        return config('units.speed.label');
    }

    // ─────────────────────────────────────────────
    // Weight  (stored: kg)
    // ─────────────────────────────────────────────

    public static function weight(float $kg, int $decimals = 2): string
    {
        $cfg   = config('units.weight');
        $value = $cfg['unit'] === 'lbs'
            ? $kg * $cfg['kg_to_lbs']
            : $kg;

        return number_format($value, $decimals) . ' ' . $cfg['label'];
    }

    public static function weightRaw(float $kg): float
    {
        $cfg = config('units.weight');
        return $cfg['unit'] === 'lbs' ? $kg * $cfg['kg_to_lbs'] : $kg;
    }

    /** Convert display-unit input back to kg for storage. */
    public static function weightToKg(float $value): float
    {
        $cfg = config('units.weight');
        return $cfg['unit'] === 'lbs' ? $value / $cfg['kg_to_lbs'] : $value;
    }

    public static function weightLabel(): string
    {
        return config('units.weight.label');
    }

    // ─────────────────────────────────────────────
    // Temperature  (stored: °C)
    // ─────────────────────────────────────────────

    public static function temperature(float $celsius, int $decimals = 1): string
    {
        $cfg   = config('units.temperature');
        $value = $cfg['unit'] === 'F'
            ? ($celsius * 9 / 5) + 32
            : $celsius;

        return number_format($value, $decimals) . ' ' . $cfg['label'];
    }

    public static function temperatureRaw(float $celsius): float
    {
        $cfg = config('units.temperature');
        return $cfg['unit'] === 'F' ? ($celsius * 9 / 5) + 32 : $celsius;
    }

    // ─────────────────────────────────────────────
    // Currency
    // ─────────────────────────────────────────────

    public static function currency(float $amount, int $decimals = 2): string
    {
        $symbol   = config('units.currency.symbol', '$');
        $decimals = config('units.currency.decimal_places', $decimals);
        return $symbol . number_format($amount, $decimals);
    }

    public static function currencySymbol(): string
    {
        return config('units.currency.symbol', '$');
    }

    public static function currencyCode(): string
    {
        return config('units.currency.code', 'USD');
    }
}
