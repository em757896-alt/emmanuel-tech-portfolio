<?php
/**
 * Application Configuration
 * PBO Compliance Platform - CRECO Kenya
 */

// â”€â”€ Environment â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
define('APP_ENV', 'production'); // 'development' | 'production'
define('APP_DEBUG', false);
define('APP_VERSION', '1.0.0');

// â”€â”€ Application â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
define('APP_NAME', 'PBO Kenya Platform');
define('APP_FULL_NAME', 'Public Benefit Organizations Compliance & Awareness Platform');
define('APP_TAGLINE', 'Empowering Civil Society Through Legal Knowledge');
define('APP_URL', 'https://civiccompliancehub.gt.tc');
define('APP_EMAIL', 'info@crecokenya.org');
define('APP_PHONE', '+254 700 000 000');
define('ORGANIZATION', 'CRECO Kenya');

// â”€â”€ Database (see database.php) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// DB_HOST: sql303.infinityfree.com
// DB_NAME: if0_42280606_if0_42280606_
// DB_USER: if0_42280606
// DB_PASS: AES256:4m0deNaMM0HA+yKw/HIgbYzFLvAjq8o1cD7cfheTaOSB8M/MqTc/Edx85mfbuzOL

// â”€â”€ Security â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
define('SECRET_KEY', 'd4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a'); // Change this in production
define('JWT_EXPIRY', 3600); // 1 hour
define('SESSION_LIFETIME', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes
define('PASSWORD_MIN_LENGTH', 8);
define('BCRYPT_COST', 12);

// â”€â”€ Paths â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('DOCS_PATH', UPLOAD_PATH . 'documents/');
define('IMAGES_PATH', UPLOAD_PATH . 'images/');

// â”€â”€ Upload Limits â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx', 'xlsx', 'xls', 'pptx', 'txt']);
define('ALLOWED_IMG_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

// â”€â”€ Email (SMTP) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
define('SMTP_HOST', 'mail.crecokenya.org');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@crecokenya.org');
define('SMTP_PASS', '(Your Email Password)');
define('SMTP_ENCRYPTION', 'tls');
define('MAIL_FROM_NAME', 'PBO Kenya Platform');

// â”€â”€ Pagination â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 25);

// â”€â”€ Cache â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600);

// â”€â”€ Kenya Counties â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
define('KENYA_COUNTIES', [
    'Baringo','Bomet','Bungoma','Busia','Elgeyo Marakwet','Embu',
    'Garissa','Homa Bay','Isiolo','Kajiado','Kakamega','Kericho',
    'Kiambu','Kilifi','Kirinyaga','Kisii','Kisumu','Kitui','Kwale',
    'Laikipia','Lamu','Machakos','Makueni','Mandera','Marsabit',
    'Meru','Migori','Mombasa','Murang\'a','Nairobi','Nakuru',
    'Nandi','Narok','Nyamira','Nyandarua','Nyeri','Samburu',
    'Siaya','Taita Taveta','Tana River','Tharaka Nithi','Trans Nzoia',
    'Turkana','Uasin Gishu','Vihiga','Wajir','West Pokot'
]);

// â”€â”€ Error Handling â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
}

// â”€â”€ Session Config â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
ini_set('session.cookie_httponly', 1);
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// â”€â”€ Timezone â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
date_default_timezone_set('Africa/Nairobi');

// Helper functions
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateUUID(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function formatDate(string $date, string $format = 'd M Y'): string {
    return date($format, strtotime($date));
}

function generateReportNumber(string $prefix = 'RPT'): string {
    return $prefix . '-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function dd($data): void {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

function sanitizeInput(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function checkRateLimit(string $key, int $maxAttempts, int $windowSeconds): bool {
    $file = sys_get_temp_dir() . '/' . md5($key) . '.rate';
    $now = time();
    $data = [];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?? [];
        $data = array_filter($data, fn($t) => $t > ($now - $windowSeconds));
    }
    $data[] = $now;
    file_put_contents($file, json_encode($data));
    return count($data) <= $maxAttempts;
}

function generateCSRFTokenValue(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function generateCSRFToken(): string {
    return generateCSRFTokenValue();
}

function generateCSRFField(): string {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFTokenValue() . '">';
}

function validateCSRFToken(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}