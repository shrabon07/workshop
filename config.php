<?php
/**
 * Aurora Cyber — Configuration
 * ---------------------------------------------------------
 * Single source of truth for the application. All constants are
 * defined here. Copy nothing to git — secrets go in your
 * environment variables (MAIL_HOST, MAIL_USER, MAIL_PASS).
 */

declare(strict_types=1);

/* ------------------------------------------------------------------ *
 *  LOCAL SECRETS (optional, git-ignored) — must load BEFORE the
 *  constants below. Hosts like InfinityFree disable putenv(), so
 *  config.secrets.php defines SECRET_* constants (no putenv needed).
 *  Copy config.secrets.php.example → config.secrets.php and fill in.
 * ------------------------------------------------------------------ */
if (is_file(__DIR__ . '/config.secrets.php')) {
    require_once __DIR__ . '/config.secrets.php';
}

/** Resolve a config value: SECRET_* constant > getenv() > default. */
function ac_env(string $secret, string $env, ?string $default): string
{
    if (defined($secret)) {
        return (string) constant($secret);
    }
    $v = getenv($env);
    return $v !== false && $v !== '' ? $v : (string) $default;
}

/* ------------------------------------------------------------------ *
 *  DATABASE (XAMPP defaults — root / empty password on 127.0.0.1:3306)
 * ------------------------------------------------------------------ */
define('DB_HOST', ac_env('SECRET_DB_HOST', 'DB_HOST', '127.0.0.1'));
define('DB_PORT', (int) ac_env('SECRET_DB_PORT', 'DB_PORT', '3306'));
define('DB_NAME', ac_env('SECRET_DB_NAME', 'DB_NAME', 'workshop'));
define('DB_USER', ac_env('SECRET_DB_USER', 'DB_USER', 'root'));
define('DB_PASS', ac_env('SECRET_DB_PASS', 'DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

/* ------------------------------------------------------------------ *
 *  SITE IDENTITY
 * ------------------------------------------------------------------ */
define('SITE_NAME', 'Aurora Cyber');
define('SITE_NAME_BN', 'অরোরা সাইবার');
define('SITE_EMAIL', 'hello@auroracyber.com');

/* ------------------------------------------------------------------ *
 *  BASE URL — auto detected. Override here if behind a proxy/vhost.
 * ------------------------------------------------------------------ */
if (!defined('APP_BASE_URL')) {
    $scheme = 'http';
    if (isset($_SERVER['HTTP_HOST'])) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        /*
         * Base URL must be the *web root of the app*, not the directory of
         * whatever script was requested (admin pages live one level deeper).
         * Compute it by stripping the app-relative suffix of SCRIPT_FILENAME
         * from SCRIPT_NAME — this also works when the app is exposed through
         * a filesystem junction (XAMPP htdocs → project folder).
         */
        $base = '/';
        if (isset($_SERVER['SCRIPT_NAME'], $_SERVER['SCRIPT_FILENAME'])) {
            $appPhys = str_replace('\\', '/', realpath(__DIR__) ?: __DIR__);
            $scrPhys = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']) ?: $_SERVER['SCRIPT_FILENAME']);
            $suffix  = '';
            if ($appPhys !== '/' && strncmp($scrPhys, $appPhys . '/', strlen($appPhys) + 1) === 0) {
                $suffix = substr($scrPhys, strlen($appPhys) + 1);
            }
            $scr = '/' . ltrim((string) $_SERVER['SCRIPT_NAME'], '/');
            $base = $scr;
            if ($suffix !== '' && substr($scr, -strlen('/' . $suffix)) === '/' . $suffix) {
                $base = substr($scr, 0, -strlen('/' . $suffix));
            } elseif ($suffix !== '' && $scr === '/' . dirname($suffix)) {
                $base = $scr;
            }
            $base = rtrim($base, '/');
        }
        define('APP_BASE_URL', $scheme . '://' . $host . ($base === '' ? '' : $base));
    } else {
        define('APP_BASE_URL', 'http://localhost/workshop');
    }
}

define('APP_PATH', __DIR__);
define('APP_URL', APP_BASE_URL);

define('UPLOAD_DIR', APP_PATH . '/uploads');
define('UPLOAD_URL', APP_BASE_URL . '/uploads');

/* ------------------------------------------------------------------ *
 *  CONTACT
 *  WhatsApp in international format WITHOUT "+" (BD: 8801XXXXXXXXX)
 * ------------------------------------------------------------------ */
define('WHATSAPP_NUMBER', '8801977665421');
define('WHATSAPP_LINK', 'https://wa.me/' . WHATSAPP_NUMBER);

/* ------------------------------------------------------------------ *
 *  EMAIL (free SMTP — Gmail app password, Brevo, Mailtrap, Zoho …)
 *  Leave MAIL_HOST empty to use the offline/dev fallback (mails are
 *  written to /storage/mailout so OTP flows still work when offline).
 * ------------------------------------------------------------------ */
define('MAIL_FROM', ac_env('SECRET_MAIL_FROM', 'MAIL_FROM', SITE_EMAIL));
define('MAIL_FROM_NAME', ac_env('SECRET_MAIL_FROM_NAME', 'MAIL_FROM_NAME', SITE_NAME));
define('MAIL_HOST', ac_env('SECRET_MAIL_HOST', 'MAIL_HOST', ''));      // e.g. smtp.gmail.com / smtp-relay.brevo.com
define('MAIL_PORT', (int) ac_env('SECRET_MAIL_PORT', 'MAIL_PORT', '587'));
define('MAIL_USER', ac_env('SECRET_MAIL_USER', 'MAIL_USER', ''));
define('MAIL_PASS', ac_env('SECRET_MAIL_PASS', 'MAIL_PASS', ''));
define('MAIL_ENCRYPTION', ac_env('SECRET_MAIL_ENCRYPTION', 'MAIL_ENCRYPTION', 'tls'));
define('MAIL_DEBUG', false);

/* Public logo URLs used inside branded emails. They mirror the exact asset the
 * homepage renders (assets/img/logo.*). An SVG is shown by Apple Mail via the
 * <picture> source; PNG covers Gmail/Outlook/Android. Override per deployment:
 *   putenv('EMAIL_LOGO_URL=https://yourdomain.com/assets/img/logo.svg');
 *   putenv('EMAIL_LOGO_PNG_URL=https://yourdomain.com/assets/img/logo.png'); */
define('EMAIL_LOGO_URL', getenv('EMAIL_LOGO_URL') ?: 'https://raw.githubusercontent.com/shrabon07/workshop/main/assets/img/logo.svg');
define('EMAIL_LOGO_PNG_URL', getenv('EMAIL_LOGO_PNG_URL') ?: 'https://raw.githubusercontent.com/shrabon07/workshop/main/assets/img/logo.png');

/* Real SMTP configured → the verification code lands in the inbox, so the
 * on-screen dev_reveal shortcut is only shown while delivery is offline. */
define('DEV_REVEAL_OTP', !(MAIL_HOST !== '' && MAIL_USER !== ''));

/* ------------------------------------------------------------------ *
 *  SECURITY & BEHAVIOUR
 * ------------------------------------------------------------------ */
define('APP_ENV', ac_env('SECRET_APP_ENV', 'APP_ENV', 'dev'));
define('OTP_TTL_MINUTES', 10);           // minutes an OTP stays valid
define('OTP_RESEND_SECONDS', 60);        // cooldown before re-sending

/* ------------------------------------------------------------------ *
 *  PHP / SESSION SHAPE
 * ------------------------------------------------------------------ */
error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'dev' ? '1' : '0');
ini_set('session.use_only_cookies', '1');
if (!headers_sent()) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => false]);
}
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Dhaka');
}

require_once __DIR__ . '/includes/bootstrap.php';