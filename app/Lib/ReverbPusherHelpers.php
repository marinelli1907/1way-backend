<?php

/**
 * Temporary shim to satisfy composer "autoload.files".
 * Replace with the real helper file later if you find it.
 */

if (!function_exists('reverb_pusher_config')) {
    function reverb_pusher_config(): array
    {
        return [];
    }
}
