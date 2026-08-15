<?php
declare(strict_types=1);

/**
 * functions.php - shared helpers
 */

if (!function_exists('e')) {
    /** Escape output for HTML. */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    /** Redirect and stop execution. */
    function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

if (!function_exists('flash')) {
    /** Queue a one-time flash message: success | error | info. */
    function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('get_flashes')) {
    function get_flashes(): array
    {
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token = null): bool
    {
        $token = $token ?? ($_POST['csrf_token'] ?? '');
        return $token !== '' && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return !empty($_SESSION['admin_id']);
    }
}

if (!function_exists('admin_name')) {
    function admin_name(): string
    {
        return $_SESSION['admin_name'] ?? 'Administrator';
    }
}

if (!function_exists('require_admin')) {
    /** Protect a page/action behind the admin session. */
    function require_admin(): void
    {
        if (!is_admin()) {
            flash('error', 'Please log in to access that area.');
            redirect('login.php');
        }
    }
}

if (!function_exists('avatar_hue')) {
    /** Deterministic hue for a string (used for generated avatars). */
    function avatar_hue(string $seed): int
    {
        $h = 0;
        $len = strlen($seed);
        for ($i = 0; $i < $len; $i++) {
            $h = ($h * 31 + ord($seed[$i])) % 360;
        }
        return $h;
    }
}

if (!function_exists('avatar_initials')) {
    function avatar_initials(string $first, string $last): string
    {
        $f = mb_strtoupper(mb_substr($first, 0, 1));
        $l = mb_strtoupper(mb_substr($last, 0, 1));
        return $f . $l;
    }
}

if (!function_exists('day_abbr')) {
    function day_abbr(string $day): string
    {
        $map = [
            'Monday' => 'Mon', 'Tuesday' => 'Tue', 'Wednesday' => 'Wed',
            'Thursday' => 'Thu', 'Friday' => 'Fri', 'Saturday' => 'Sat', 'Sunday' => 'Sun',
        ];
        return $map[$day] ?? $day;
    }
}

if (!function_exists('fmt_time')) {
    /** 08:30:00 -> 8:30 AM */
    function fmt_time(string $time): string
    {
        $ts = strtotime($time);
        if ($ts === false) {
            return $time;
        }
        return date('g:i A', $ts);
    }
}
