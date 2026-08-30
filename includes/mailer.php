<?php
/**
 * Email delivery with a graceful ladder:
 *   1. PHPMailer via SMTP        → when MAIL_HOST is configured (free SMTP)
 *   2. PHPMailer via PHP mail()  → when library present but no SMTP host
 *   3. Offline fallback          → writes the mail to /storage/mailout so the
 *                                  full OTP flow still works on a sandbox (dev).
 *
 * To use PHPMailer: place its src/ files in /includes/PHPMailer/src
 * (or download with:  composer require phpmailer/phpmailer  inside ./)
 */

declare(strict_types=1);

/* Lazy autoload for PHPMailer (no composer required). */
spl_autoload_register(function (string $class): void {
    $prefix = 'PHPMailer\\PHPMailer\\';
    if (str_starts_with($class, $prefix)) {
        $file = __DIR__ . '/PHPMailer/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

class_exists('PHPMailer\PHPMailer\Exception'); // pre-load exception class

/**
 * Encrypt a secret (per-admin SMTP app password) for storage at rest.
 * AES-256-CBC, key derived from ENCRYPTION_KEY. Falls back to a trivial
 * encoding ONLY when no ENCRYPTION_KEY is configured (dev default).
 */
function app_encrypt(string $plain): string
{
    $key = defined('ENCRYPTION_KEY') ? (string) constant('ENCRYPTION_KEY') : '';
    if ($key === '') {
        return 'b64:' . base64_encode($plain);
    }
    $iv = random_bytes(16);
    $cipher = openssl_encrypt($plain, 'AES-256-CBC', hash('sha256', $key, true), OPENSSL_RAW_DATA, $iv);
    return 'aes:' . base64_encode($iv . $cipher);
}

/** Decrypt a value produced by app_encrypt(). */
function app_decrypt(string $enc): string
{
    $raw = $enc;
    $prefix = substr($enc, 0, 4);
    if ($prefix === 'aes:') {
        $key = defined('ENCRYPTION_KEY') ? (string) constant('ENCRYPTION_KEY') : '';
        $data = base64_decode(substr($enc, 4));
        $iv = substr($data, 0, 16);
        return openssl_decrypt(substr($data, 16), 'AES-256-CBC', hash('sha256', $key, true), OPENSSL_RAW_DATA, $iv) ?: '';
    }
    if ($prefix === 'b64:') {
        return base64_decode(substr($enc, 4)) ?: '';
    }
    return $raw;
}

/**
 * The acting admin's verified personal SMTP sender profile, if any.
 * Never applies to the super admin (site account is the permanent sender).
 * Returns ['email' => ..., 'pass' => ...] or null.
 */
function admin_mail_profile(?int $adminId = null): ?array
{
    $adminId = $adminId ?: current_user_id();
    if ($adminId === null) {
        return null;
    }
    $user = DB::get('SELECT is_super_admin FROM users WHERE id = ? AND role = "admin"', [$adminId]);
    if (!$user || (int) ($user['is_super_admin'] ?? 0) === 1) {
        return null; // super admin always sends from the site account
    }
    $row = DB::get('SELECT smtp_email, smtp_pass, verified FROM admin_mail_settings WHERE admin_id = ?', [$adminId]);
    if (!$row || (int) $row['verified'] !== 1 || trim((string) $row['smtp_pass']) === '') {
        return null;
    }
    $email = trim((string) $row['smtp_email']);
    $pass  = app_decrypt((string) $row['smtp_pass']);
    if ($email === '' || $pass === '') {
        return null;
    }
    return ['email' => $email, 'pass' => $pass];
}

/**
 * Brevo (Sendinblue) REST API send — HTTPS, so it works even on hosts that
 * disable outbound SMTP (InfinityFree blocks smtp.gmail.com etc.). Uses the
 * xkeysib-… API key. Returns true only when Brevo accepted the message.
 */
function brevo_api_send(string $to, string $subject, string $html, string $plain = '', array $embedded = [], string $fromDisplay = '', array $replyTo = []): bool
{
    if (!defined('BREVO_API_KEY') || BREVO_API_KEY === '') {
        return false;
    }
    $payload = [
        'sender'      => ['name' => $fromDisplay !== '' ? $fromDisplay : MAIL_FROM_NAME, 'email' => MAIL_FROM],
        'to'          => [['email' => $to]],
        'subject'     => $subject,
        'htmlContent' => $html,
    ];
    if (!empty($replyTo['email'])) {
        $payload['replyTo'] = ['name' => $replyTo['name'] ?? '', 'email' => $replyTo['email']];
    }
    if ($plain !== '') {
        $payload['textContent'] = $plain;
    }
    $attachments = [];
    foreach ($embedded as $cid => $file) {
        if (is_file($file)) {
            $attachments[] = [
                'name'    => basename($file),
                'content' => base64_encode((string) file_get_contents($file)),
                'cid'     => (string) $cid,
            ];
        }
    }
    if ($attachments !== []) {
        $payload['attachment'] = $attachments;
    }

    $json = json_encode($payload);
    $ctx  = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Authorization: Bearer " . BREVO_API_KEY . "\r\n" .
                               "Content-Type: application/json\r\n" .
                               "Accept: application/json\r\n" .
                               "Content-Length: " . strlen($json) . "\r\n",
            'content'       => $json,
            'timeout'       => 25,
            'ignore_errors' => true,
        ],
    ]);
    $body = @file_get_contents('https://api.brevo.com/v3/smtp/email', false, $ctx);
    $status = 0;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $h) {
            if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) {
                $status = (int) $m[1];
            }
        }
    }
    return $status >= 200 && $status < 300;
}

/**
 * One strictly-tried PHPMailer send with the given SMTP config. Returns bool,
 * never throws to the caller, does NOT fall back to other routes. Used both by
 * send_mail()'s candidate loop and by smtp_test_auth() so a bad app password
 * can be detected instead of silently falling back to the site account.
 */
function phpmailer_try(array $cfg, string $to, string $subject, string $html, string $plain, array $replyTo = [], array $embedded = []): bool
{
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return false;
    }
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $cfg['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg['user'];
        $mail->Password   = $cfg['pass'];
        $mail->Port       = $cfg['port'];
        $mail->SMTPSecure = $cfg['sec'];
        $mail->SMTPDebug  = MAIL_DEBUG ? 2 : 0;
        if (!empty($cfg['sni'])) {
            // Host is a pinned IP while the TLS cert/SNI belongs to
            // the real hostname (smtp.gmail.com on InfinityFree).
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'      => true,
                    'verify_peer_name' => false,
                    'SNI_enabled'      => true,
                    'peer_name'        => $cfg['sni'],
                ],
            ];
        }

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($cfg['from'], $cfg['fname'] ?? MAIL_FROM_NAME);
        if (!empty($replyTo['email'])) {
            $mail->addReplyTo($replyTo['email'], $replyTo['name']);
        }
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = $html;
        $mail->AltBody = $plain;

        foreach ($embedded as $cid => $file) {
            if (is_file($file)) {
                $mail->addEmbeddedImage($file, $cid, basename($file));
            }
        }

        return $mail->send();
    } catch (Throwable $e) {
        return false;
    }
}

/** Sends a tiny proof-mail using ONLY the given credentials (no fallback). */
function smtp_test_auth(string $email, string $pass): bool
{
    if (MAIL_HOST === '') {
        return false;
    }
    return phpmailer_try(
        [
            'host'  => MAIL_HOST,
            'port'  => MAIL_PORT,
            'user'  => $email,
            'pass'  => $pass,
            'sec'   => MAIL_ENCRYPTION,
            'sni'   => defined('MAIL_HOST_SNI') ? MAIL_HOST_SNI : '',
            'from'  => $email,
            'fname' => SITE_NAME . ' — sender test',
        ],
        $email,
        'Test mail from ' . SITE_NAME . ' — your sender works',
        '<div style="font-family:Sans-serif;color:#334155;background:#f8fafc;padding:18px;border-radius:12px;border:1px solid #e2e8f0;">' .
        'If you are reading this, your Gmail app password works — emails you send from the ' . SITE_NAME . ' admin panel will now come from your own address.</div>',
        'If you are reading this, your app password works.'
    );
}

/**
 * Sends an HTML email. Returns true on success (or when recorded to disk).
 *
 * Ladder: per-admin personal SMTP (if the acting admin has a verified
 * sender profile) → Brevo REST API (if key set, no admin profile) →
 * PHPMailer SMTP with the site account → mail() → disk fallback.
 *
 * $embedded = ['cidname' => '/absolute/path/to/image.png', ...] — attached as
 * inline (cid:) images so <img src="cid:cidname"> renders in Gmail/Outlook.
 *
 * $sentBy = ['name' => ..., 'email' => ..., 'smtp' => ['email','pass']??] —
 * the admin who initiated the mail. Without 'smtp' the envelope sender stays
 * MAIL_FROM (the site Gmail) while the From display name shows the admin and
 * a Reply-To routes replies straight to that admin. With a verified 'smtp'
 * profile the mail authenticates AS that admin, so it genuinely comes from
 * their own address (falls back to the site account on auth failure).
 */
function send_mail(string $to, string $subject, string $html, string $plain = '', array $embedded = [], array $sentBy = []): bool
{
    if ($plain === '') {
        $plain = strip_tags((string) preg_replace('/<br\s*\/?>/i', "\n", $html));
    }

    $fromDisplay = MAIL_FROM_NAME;
    $replyTo     = [];
    $adminSender = null;
    if (!empty($sentBy['email']) && filter_var($sentBy['email'], FILTER_VALIDATE_EMAIL)) {
        $byName       = trim((string) ($sentBy['name'] ?? ''));
        $byName       = $byName !== '' ? $byName : $sentBy['email'];
        $fromDisplay  = MAIL_FROM_NAME . ' · ' . $byName;
        $replyTo      = ['email' => trim($sentBy['email']), 'name' => $byName];
        if (!empty($sentBy['smtp']['email']) && !empty($sentBy['smtp']['pass'])) {
            $adminSender = ['email' => trim($sentBy['smtp']['email']), 'pass' => (string) $sentBy['smtp']['pass']];
        }
    }

    $usesLibrary = class_exists('PHPMailer\PHPMailer\PHPMailer');
    $hasSmtp     = MAIL_HOST !== '' && MAIL_USER !== '';
    $sni         = defined('MAIL_HOST_SNI') ? MAIL_HOST_SNI : '';

    // SMTP candidates: the acting admin's own account first (real From), then
    // the site account. Gmail servers share the same host/pinned-IP/SNI.
    $smtpCandidates = [];
    if ($adminSender) {
        $smtpCandidates[] = [
            'host'   => MAIL_HOST,
            'port'   => MAIL_PORT,
            'user'   => $adminSender['email'],
            'pass'   => $adminSender['pass'],
            'sec'    => MAIL_ENCRYPTION,
            'sni'    => $sni,
            'from'   => $adminSender['email'],
            'fname'  => $byName,
        ];
    }
    if ($hasSmtp) {
        $smtpCandidates[] = [
            'host'   => MAIL_HOST,
            'port'   => MAIL_PORT,
            'user'   => MAIL_USER,
            'pass'   => MAIL_PASS,
            'sec'    => MAIL_ENCRYPTION,
            'sni'    => $sni,
            'from'   => MAIL_FROM,
            'fname'  => $fromDisplay,
        ];
    }

    // Brevo only when NOT sending as the admin personally — otherwise their
    // From header would be rewritten to the relay's sender.
    if (!$adminSender && brevo_api_send($to, $subject, $html, $plain, $embedded, $fromDisplay, $replyTo)) {
        return true;
    }

    try {
        if ($usesLibrary && $smtpCandidates !== []) {
            foreach ($smtpCandidates as $cfg) {
                if (phpmailer_try($cfg, $to, $subject, $html, $plain, $replyTo, $embedded)) {
                    return true;
                }
            }
            // all SMTP routes failed → fall through to disk so dev flows keep working.
        }

        if (function_exists('mail')) {
            $fallbackFrom = $adminSender ? $adminSender['email'] : MAIL_FROM;
            $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";
            $headers .= 'From: ' . $fromDisplay . ' <' . $fallbackFrom . ">\r\n";
            if (!empty($replyTo['email'])) {
                $headers .= 'Reply-To: ' . $replyTo['name'] . ' <' . $replyTo['email'] . ">\r\n";
            }
            if (@mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $headers)) {
                return true;
            }
        }
    } catch (Throwable $e) {
        // don't let sending break the request.
    }

    // Offline sandbox fallback — persist the mail so OTPs remain testable.
    $dir = storage_path('mailout');
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $file = sprintf('%s/m-%s-%d.html', $dir, date('Ymd-His'), random_int(100, 999));
    @file_put_contents($file, e($subject) . "<hr>" . $html);
    return true;
}

function mail_list_last(int $limit = 5): array
{
    $dir = storage_path('mailout');
    if (!is_dir($dir)) {
        return [];
    }
    $files = glob($dir . '/m-*.html') ?: [];
    rsort($files);
    $out = [];
    foreach (array_slice($files, 0, $limit) as $f) {
        $html = (string) file_get_contents($f);
        $subject = explode('<hr>', $html, 2)[0];
        $out[] = ['file' => basename($f), 'subject' => strip_tags($subject), 'written' => date('M j, H:i', filemtime($f))];
    }
    return $out;
}