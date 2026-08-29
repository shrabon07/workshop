<?php
/**
 * Shared helpers: escaping, i18n rendering, CSRF, money, uploads and the
 * customer verification "tick" engine (Red / Grey / Green).
 */

declare(strict_types=1);

/* ------------------------------------------------------------------ *
 *  ESCAPING & INPUT
 * ------------------------------------------------------------------ */

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function post(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

function get(string $key, $default = null)
{
    return $_GET[$key] ?? $default;
}

function json_out($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error(string $message, int $status = 400, array $extra = []): void
{
    json_out(array_merge(['ok' => false, 'error' => $message], $extra), $status);
}

function json_ok(array $data = []): void
{
    json_out(array_merge(['ok' => true], $data));
}

/* ------------------------------------------------------------------ *
 *  CSRF (session-bound tokens)
 * ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool
{
    $token = $token !== null ? $token : post('csrf_token');
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function csrf_require(): void
{
    if (!verify_csrf()) {
        json_error('Invalid security token. Please refresh the page.', 403);
    }
}

/* ------------------------------------------------------------------ *
 *  I18N — bilingual rendering (EN + বাংলা)
 *  Public/nav strings use the JS dictionary underneath; server-rendered
 *  dynamic strings use the .e/.b span pair toggled purely via CSS.
 * ------------------------------------------------------------------ */

/**
 * Renders an escaped bilingual span pair.
 * <span class="e">Service title</span> <span class="b">সার্ভিস টাইটেল</span>
 * The active language is applied on <html> as .lang-en or .lang-bn by i18n.js
 * (CSS hides the inactive language — zero layout shift, GPU cheap).
 */
function l(string $en, string $bn = ''): string
{
    $bn = $bn === '' ? $en : $bn;
    return '<span class="e">' . e($en) . '</span><span class="b">' . e($bn) . '</span>';
}

/** Current language from cookie/localStorage fallback (server side hint). */
function active_lang(): string
{
    static $lang = null;
    if ($lang === null) {
        $lang = strtolower((string) ($_COOKIE['wclang'] ?? 'en')) === 'bn' ? 'bn' : 'en';
    }
    return $lang;
}

/* ------------------------------------------------------------------ *
 *  MONEY & TEXT
 * ------------------------------------------------------------------ */

function price_fmt($amount): string
{
    return '৳ ' . number_format((float) $amount, 0, '.', ',');
}

function slugify(string $text, string $fallback = 'item'): string
{
    $text = trim($text);
    $text = str_replace(['/', '\\', ' '], ['-', '-', '-'], $text);
    $text = preg_replace('/[^\p{L}\p{N}-]+/u', '', $text) ?? '';
    $text = preg_replace('/-+/', '-', $text) ?? '';
    $text = trim($text, '-');
    if ($text === '') {
        $text = $fallback;
    }
    return strtolower((string) $text) . '-' . substr(bin2hex(random_bytes(3)), 0, 4);
}

function random_token(int $bytes = 16): string
{
    return bin2hex(random_bytes($bytes));
}

function random_otp(int $length = 6): string
{
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= (string) random_int(0, 9);
    }
    return $out;
}

function whatsapp_intl(string $phone): string
{
    $phone = preg_replace('/[^0-9]/', '', (string) $phone);
    if (strlen($phone) === 10) {
        $phone = '880' . $phone;               // 01XXXXXXXXX → 8801XXXXXXXXX
    } elseif (strlen($phone) === 11 && str_starts_with($phone, '01')) {
        $phone = '880' . substr($phone, 1);    // 01XXXXXXXXX → 8801XXXXXXXXX
    } elseif (strlen($phone) === 13 && str_starts_with($phone, '880')) {
        // already good
    } elseif (strlen($phone) === 14 && str_starts_with($phone, '00880')) {
        $phone = substr($phone, 2);
    }
    return $phone;
}

function wa_link(string $number, string $text = ''): string
{
    return 'https://wa.me/' . whatsapp_intl($number) . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

/* ------------------------------------------------------------------ *
 *  URLS & FILES
 * ------------------------------------------------------------------ */

function url(string $path = ''): string
{
    return APP_BASE_URL . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function uploads_url(?string $path): string
{
    return $path ? UPLOAD_URL . '/' . ltrim($path, '/') : '';
}

function storage_path(string $file = ''): string
{
    $dir = APP_PATH . '/storage';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir . ($file !== '' ? '/' . ltrim($file, '/') : '');
}

/* ------------------------------------------------------------------ *
 *  VERIFICATION TICK ENGINE
 *  Red   ❌  → neither channel verified
 *  Grey  ✓  → email verified only
 *  Green ✔  → both email & WhatsApp verified
 *  Admin override (verification_status.admin_override) wins.
 * ------------------------------------------------------------------ */

function verification_tick(array $user): array
{
    $v = DB::get('SELECT * FROM verification_status WHERE user_id = ?', [$user['id']]);
    $v = $v ?: ['email_verified' => 0, 'whatsapp_verified' => 0, 'admin_override' => 'none'];

    $override = $v['admin_override'];
    if ($override !== 'none') {
        switch ($override) {
            case 'red':   return ['key' => 'red', 'icon' => '❌', 'label_en' => 'Not verified', 'label_bn' => 'যাচাই করা হয়নি', 'record' => $v];
            case 'grey':  return ['key' => 'grey', 'icon' => '✓', 'label_en' => 'Email verified', 'label_bn' => 'ইমেইল যাচাইকৃত', 'record' => $v];
            case 'green': return ['key' => 'green', 'icon' => '✔', 'label_en' => 'Fully verified', 'label_bn' => 'সম্পূর্ণ যাচাইকৃত', 'record' => $v];
        }
    }

    $email = (bool) $v['email_verified'];
    $whats = (bool) $v['whatsapp_verified'];

    if ($email && $whats) {
        return ['key' => 'green', 'icon' => '✔', 'label_en' => 'Email + WhatsApp verified', 'label_bn' => 'ইমেইল + হোয়াটসঅ্যাপ যাচাইকৃত', 'record' => $v];
    }
    if ($email) {
        return ['key' => 'grey', 'icon' => '✓', 'label_en' => 'Email verified', 'label_bn' => 'ইমেইল যাচাইকৃত', 'record' => $v];
    }
    return ['key' => 'red', 'icon' => '❌', 'label_en' => 'Not verified', 'label_bn' => 'যাচাই করা হয়নি', 'record' => $v];
}

function verification_record(int $userId): array
{
    $v = DB::get('SELECT * FROM verification_status WHERE user_id = ?', [$userId]);
    if (!$v) {
        DB::insert('verification_status', ['user_id' => $userId]);
        $v = DB::get('SELECT * FROM verification_status WHERE user_id = ?', [$userId]);
    }
    return $v;
}

/* ------------------------------------------------------------------ *
 *  ORDER STATUS
 * ------------------------------------------------------------------ */

function order_status_meta(string $status): array
{
    switch ($status) {
        case 'in_progress': return ['label_en' => 'In Progress', 'label_bn' => 'চলমান', 'cls' => 'amber'];
        case 'delivered':   return ['label_en' => 'Delivered', 'label_bn' => 'ডেলিভারি সম্পন্ন', 'cls' => 'emerald'];
        case 'cancelled':   return ['label_en' => 'Cancelled', 'label_bn' => 'বাতিল', 'cls' => 'rose'];
        default:            return ['label_en' => 'Pending', 'label_bn' => 'অপেক্ষমাণ', 'cls' => 'sky'];
    }
}

function flash(string $message, string $type = 'info'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

function flash_pull(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function version_time(): string
{
    static $t = null;
    if ($t === null) {
        $t = date('ymdHi');
    }
    return $t;
}

function ago(string $datetime): string
{
    $ts  = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return date('M j, Y', $ts);
}