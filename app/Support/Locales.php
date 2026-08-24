<?php

namespace App\Support;

/**
 * Registry of locales supported by the boilerplate.
 *
 * Add a language by creating `lang/<code>/messages.php` and listing the code
 * here; the profile screen and shared Inertia props pick it up automatically.
 */
class Locales
{
    /**
     * @var array<string, string> Map of locale codes to display labels.
     */
    private const SUPPORTED = [
        'en' => 'English',
    ];

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::SUPPORTED;
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::SUPPORTED);
    }

    public static function label(string $code): string
    {
        return self::SUPPORTED[$code] ?? $code;
    }

    public static function isSupported(?string $code): bool
    {
        return $code !== null && array_key_exists($code, self::SUPPORTED);
    }

    /**
     * The fallback used for users without an explicit preference.
     */
    public static function defaultCode(): string
    {
        return config('app.locale', 'en');
    }
}
