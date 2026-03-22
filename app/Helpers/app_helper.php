<?php

if (!function_exists('app_format_date')) {
    function app_format_date($value, ?string $format = null): string
    {
        $value = is_string($value) ? trim($value) : $value;
        if ($value === null || $value === '') {
            return '';
        }

        $format = $format ?: (defined('APP_DATE_FORMAT') ? (string) APP_DATE_FORMAT : 'd/m/Y');

        try {
            if ($value instanceof \DateTimeInterface) {
                return $value->format($format);
            }

            return (new \DateTimeImmutable((string) $value))->format($format);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }
}

if (!function_exists('app_format_datetime')) {
    function app_format_datetime($value, ?string $format = null): string
    {
        $baseFormat = defined('APP_DATE_FORMAT') ? (string) APP_DATE_FORMAT : 'd/m/Y';
        return app_format_date($value, $format ?: ($baseFormat . ' H:i'));
    }
}
