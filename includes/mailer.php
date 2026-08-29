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
function brevo_api_send(string $to, string $subject, string $html, string $plain = '', array $embedded = []): bool
{
    if (!defined('BREVO_API_KEY') || BREVO_API_KEY === '') {
        return false;
    }
    $payload = [
        'sender'      => ['name' => MAIL_FROM_NAME, 'email' => MAIL_FROM],
        'to'          => [['email' => $to]],
        'subject'     => $subject,
        'htmlContent' => $html,
    ];
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
 */
function send_mail(string $to, string $subject, string $html, string $plain = '', array $embedded = []): bool
{
    if ($plain === '') {
        $plain = strip_tags((string) preg_replace('/<br\s*\/?>/i', "\n", $html));
    }

    if (brevo_api_send($to, $subject, $html, $plain, $embedded)) {
        return true;
    }

    $usesLibrary = class_exists('PHPMailer\PHPMailer\PHPMailer');
    $hasSmtp     = MAIL_HOST !== '' && MAIL_USER !== '';

    try {
        if ($usesLibrary) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            if ($hasSmtp) {
                $mail->isSMTP();
                $mail->Host       = MAIL_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_USER;
                $mail->Password   = MAIL_PASS;
                $mail->Port       = MAIL_PORT;
                $mail->SMTPSecure = MAIL_ENCRYPTION; // 'tls' / 'ssl' / ''
                $mail->SMTPDebug  = MAIL_DEBUG ? 2 : 0;
            } else {
                $mail->isMail();
            }

            $mail->CharSet = 'UTF-8';
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
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
            // SMTP failed → fall through to disk so dev flows keep working.
        }

        if (function_exists('mail')) {
            $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";
            $headers .= 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . ">\r\n";
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