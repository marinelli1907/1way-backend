<?php
/*
 * 1Way brand defaults — these values match 1way-brand.css.
 * They are overridden if the admin has saved custom colours in Business Settings.
 */
$color = businessConfig('website_color')?->value;
$text  = businessConfig('text_color')?->value;

// 1Way palette defaults (applied when DB has no override)
$defaultPrimary   = '#CC0000';
$defaultSecondary = '#FFE5E5';
$defaultBg        = '#F5F6FA';
$defaultTitle     = '#0A0E1A';
$defaultBody      = '#4B5563';
$defaultLight     = '#9CA3AF';
?>

<style>
    /* 1Way brand token defaults — overridden by DB config below if set */
    :root {
        --text-primary:      {{ $color['primary']    ?? $defaultPrimary }};
        --text-secondary:    {{ $color['secondary']  ?? $defaultSecondary }};
        --bs-body-bg:        {{ $color['background'] ?? $defaultBg }};
        --bs-primary:        {{ $color['primary']    ?? $defaultPrimary }};
        --bs-primary-rgb:    {{ hexToRgb($color['primary'] ?? $defaultPrimary) }};
        --bs-secondary-rgb:  {{ hexToRgb($color['secondary'] ?? $defaultSecondary) }};
        --bs-secondary:      {{ $color['secondary']  ?? $defaultSecondary }};
        --title-color:       {{ $text['primary']     ?? $defaultTitle }};
        --title-color-rgb:   {{ hexToRgb($text['primary'] ?? $defaultTitle) }};
        --bs-body-color:     {{ $text['secondary']   ?? $defaultBody }};
        --secondary-body-color: {{ $text['light']    ?? $defaultLight }};
    }
</style>
