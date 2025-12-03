<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Modules\BusinessManagement\Entities\BusinessSetting;

/**
 * Global Helper Functions for 1Way Backend
 * ---------------------------------------------------------------
 * This file is autoloaded via composer.json under "files".
 * All functions here become globally available inside controllers,
 * Blade views, middleware, jobs, etc.
 */


/* ================================================================
 |  SIMPLE HELPERS
 ================================================================ */

if (!function_exists('isDemo')) {
    function isDemo(): bool
    {
        return (bool) config('app.demo', false);
    }
}

if (!function_exists('settings')) {
    function settings(string $key, $default = null)
    {
        return config($key, $default);
    }
}


/* ================================================================
 |  LOCALIZATION HELPERS
 ================================================================ */

if (!function_exists('translateKeys')) {
    function translateKeys(array $data, ?string $locale = null): array
    {
        $locale = $locale ?: App::getLocale();
        $translated = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && array_key_exists($locale, $value)) {
                $translated[$key] = $value[$locale];
            } else {
                $translated[$key] = $value;
            }
        }
        return $translated;
    }
}

if (!function_exists('defaultLang')) {
    function defaultLang(): string
    {
        try {
            if (class_exists(BusinessSetting::class)) {
                $setting = BusinessSetting::where('key_name', 'system_language')->first();

                if ($setting) {
                    $value = $setting->value;

                    if (is_array($value) && isset($value['code'])) {
                        return $value['code'];
                    }

                    if (is_string($value) && $value !== '') {
                        return $value;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return config('app.locale', 'en');
    }
}


/* ================================================================
 |  BUSINESS SETTINGS HELPERS
 ================================================================ */

if (!function_exists('businessConfig')) {
    function businessConfig(string $keyName, ?string $settingsType = null): ?BusinessSetting
    {
        try {
            if (!class_exists(BusinessSetting::class)) {
                return null;
            }

            $query = BusinessSetting::where('key_name', $keyName);

            if ($settingsType) {
                $query->where('settings_type', $settingsType);
            }

            return $query->first();

        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('businessConfigValue')) {
    function businessConfigValue(string $keyName, ?string $settingsType = null, $default = null)
    {
        $config = businessConfig($keyName, $settingsType);
        return $config->value ?? $default;
    }
}


/* ================================================================
 |  PAGINATION HELPERS
 ================================================================ */

if (!function_exists('paginateCollection')) {
    function paginateCollection($items, int $perPage = 15, ?int $page = null, array $options = []): LengthAwarePaginator
    {
        $page = $page ?: (LengthAwarePaginator::resolveCurrentPage() ?: 1);

        if ($items instanceof Collection) {
            $items = $items->all();
        }

        $items = array_values((array) $items);
        $slice = array_slice($items, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $slice,
            count($items),
            $perPage,
            $page,
            $options
        );
    }
}

if (!function_exists('paginationLimit')) {
    /**
     * Get pagination limit for a given section
     * Controllers *must* pass a key:
     *     paginationLimit('landing_page')
     */
    function paginationLimit(string $key, int $default = 10): int
    {
        $limit = null;

        try {
            if (class_exists(BusinessSetting::class)) {
                $setting = BusinessSetting::where('key_name', 'pagination_limit')->first();

                if ($setting) {
                    $value = $setting->value;

                    if (is_array($value) && isset($value[$key])) {
                        $limit = (int) $value[$key];
                    } elseif (is_string($value)) {
                        $maybe = (int) $value;
                        if ($maybe > 0) {
                            $limit = $maybe;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {}

        if ($limit === null) {
            $limit = (int) config("pagination.$key", $default);
        }

        return $limit > 0 ? $limit : $default;
    }
}


/* ================================================================
 |  DYNAMIC ASSET HELPERS
 ================================================================ */

if (!function_exists('dynamicAsset')) {
    function dynamicAsset(?string $path): string
    {
        if (!$path) return '';

        if (preg_match('#^https?://#', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'public/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }
}

if (!function_exists('dynamicStorage')) {
    function dynamicStorage(?string $path): string
    {
        if (!$path) return '';

        if (preg_match('#^https?://#', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'public/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }
}


/* ================================================================
 |  SESSION-LIKE SETTINGS (USED BY BLADE)
 ================================================================ */

if (!function_exists('getSession')) {
    /**
     * NOT actual Laravel session.
     * This reads BusinessSetting rows used for the landing page.
     */
    function getSession(string $key, $default = null)
    {
        try {
            if (class_exists(BusinessSetting::class)) {
                $setting = BusinessSetting::where('key_name', $key)->first();

                if ($setting) {
                    $value = $setting->value;

                    if (is_string($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            return $decoded ?: $default;
                        }
                    }

                    return $value ?? $default;
                }
            }
        } catch (\Throwable $e) {}

        return $default;
    }
}


/* ================================================================
 |  MISC
 ================================================================ */

if (!function_exists('systemCurrency')) {
    function systemCurrency(): string
    {
        return config('app.currency', 'USD');
    }
}

if (!function_exists('safeJsonDecode')) {
    function safeJsonDecode(?string $value, $default = null)
    {
        if ($value === null || $value === '') {
            return $default;
        }

        try {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

