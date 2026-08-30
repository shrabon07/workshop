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
 * Sends an HTML email. Returns true on success (or when recorded to disk).
 *
 * Ladder: Brevo REST API (if key set) → PHPMailer SMTP/mail() → disk fallback.
 *
 * $embedded = ['cidname' => '/absolute/path/to/image.png', ...] — attached as
 * inline (cid:) images so <img src="cid:cidname"> renders in Gmail/Outlook.
 *
 * $sentBy = ['name' => ..., 'email' => ...] — the admin who initiated the mail.
 * The envelope sender stays MAIL_FROM (the auth'd Gmail), but the From display
 * name shows the admin and a Reply-To routes replies straight to that admin.
 */
function send_mail(string $to, string $subject, string $html, string $plain = '', array $embedded = [], array $sentBy = []): bool
{
    if ($plain === '') {
        $plain = strip_tags((string) preg_replace('/<br\s*\/?>/i', "\n", $html));
    }

    $fromDisplay = MAIL_FROM_NAME;
    $replyTo     = [];
    if (!empty($sentBy['email']) && filter_var($sentBy['email'], FILTER_VALIDATE_EMAIL)) {
        $byName       = trim((string) ($sentBy['name'] ?? ''));
        $byName       = $byName !== '' ? $byName : $sentBy['email'];
        $fromDisplay  = MAIL_FROM_NAME . ' · ' . $byName;
        $replyTo      = ['email' => trim($sentBy['email']), 'name' => $byName];
    }

    if (brevo_api_send($to, $subject, $html, $plain, $embedded, $fromDisplay, $replyTo)) {
        return true;
    }

    $usesLibrary = class_exists('PHPMailer\PHPMailer\PHPMailer');
    $hasSmtp     = MAIL_HOST !== '' && MAIL_USER !== '';

    // SMTP route: primary Mail host (Gmail via pinned IP, because InfinityFree
    // DNS-blocks the smtp.gmail.com hostname). No third-party relay fallback —
    // relays rewrite the From to their own domain (e.g. @<id>.brevosend.com).
    $smtpCandidates = [];
    if ($hasSmtp) {
        $smtpCandidates[] = [
            'host' => MAIL_HOST,
            'port' => MAIL_PORT,
            'user' => MAIL_USER,
            'pass' => MAIL_PASS,
            'sec'  => MAIL_ENCRYPTION, // 'tls' / 'ssl' / ''
            'sni'  => defined('MAIL_HOST_SNI') ? MAIL_HOST_SNI : '',
        ];
    }

    try {
        if ($usesLibrary) {
            foreach ($smtpCandidates as $cfg) {
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
                    if ($cfg['sni'] !== '') {
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
                    $mail->setFrom(MAIL_FROM, $fromDisplay);
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

                    if ($mail->send()) {
                        return true;
                    }
                } catch (Throwable $e) {
                    // try the next candidate route
                }
            }
            // all SMTP routes failed → fall through to disk so dev flows keep working.
        }

        if (function_exists('mail')) {
            $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";
            $headers .= 'From: ' . $fromDisplay . ' <' . MAIL_FROM . ">\r\n";
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