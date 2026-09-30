<?php
/**
 * Secret loading layer.
 *
 * Resolution order for every setting below:
 *   1. config/local.php   (real secrets - git-ignored, never committed)
 *   2. environment variable
 *   3. safe fallback
 *
 * No credential is stored in this repository. Copy config/local.example.php to
 * config/local.php and fill in the values for your host.
 */

require_once __DIR__ . '/local.php';

/**
 * Resolve a configuration value from local.php, then the environment, then a fallback.
 */
if (!function_exists('cfg')) {
    function cfg(string $key, string $default = ''): string {
        if (defined($key)) {
            return (string) constant($key);
        }
        $env = getenv($key);
        if ($env === false || $env === '') {
            return $default;
        }
        return (string) $env;
    }
}

// ── Database ────────────────────────────────────────────────────────────
defined('DB_HOST')            || define('DB_HOST',            cfg('DB_HOST'));
defined('DB_NAME')            || define('DB_NAME',            cfg('DB_NAME'));
defined('DB_USER')            || define('DB_USER',            cfg('DB_USER'));
defined('DB_PASS')            || define('DB_PASS',            cfg('DB_PASS'));
defined('DB_CHARSET')         || define('DB_CHARSET',         cfg('DB_CHARSET', 'utf8mb4'));

// ── Security ────────────────────────────────────────────────────────────
defined('SECRET_KEY')         || define('SECRET_KEY',         cfg('SECRET_KEY'));

// ── Cloudflare Turnstile (CAPTCHA) ──────────────────────────────────────
defined('TURNSTILE_SITE_KEY')     || define('TURNSTILE_SITE_KEY',     cfg('TURNSTILE_SITE_KEY'));
defined('TURNSTILE_SECRET_KEY')   || define('TURNSTILE_SECRET_KEY',   cfg('TURNSTILE_SECRET_KEY'));
defined('TURNSTILE_WIDGET_THEME') || define('TURNSTILE_WIDGET_THEME', cfg('TURNSTILE_WIDGET_THEME', 'light'));
