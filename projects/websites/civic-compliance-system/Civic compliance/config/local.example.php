<?php
/**
 * TEMPLATE for local secrets — COPY THIS FILE TO `config/local.php` AND FILL IT IN.
 *
 * `config/local.php` is git-ignored on purpose. Never commit real values here.
 *
 * Generate a strong SECRET_KEY with:  php -r "echo bin2hex(random_bytes(32));"
 */

// ── Database (InfinityFree values come from your control panel) ─────────
define('DB_HOST', '');     // e.g. sql000000.infinityfree.com
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

// ── Security ────────────────────────────────────────────────────────────
define('SECRET_KEY', '');  // 64 hex chars, unique per installation

// ── Cloudflare Turnstile (CAPTCHA) ──────────────────────────────────────
define('TURNSTILE_SITE_KEY', '');     // public, shown in the widget
define('TURNSTILE_SECRET_KEY', '');   // server-side only; blank = check skipped
define('TURNSTILE_WIDGET_THEME', 'light');
