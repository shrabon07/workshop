<?php
/**
 * Aurora Cyber branded email wrapping.
 * Mirrors the site design system (tailwind.config.js + app.css):
 *   ink-950 canvas #020617 · glass cards #0f172a · brand #0f766e→#06b6d4
 *   accent #6366f1→#a855f7 · neon-teal glow · Outfit font.
 */
function email_layout(string $heading, string $bodyHtml, array $opts = []): string
{
    $siteName = $opts['site_name'] ?? SITE_NAME;
    $tagline  = $opts['tagline']  ?? 'Websites, e-commerce & SaaS for growing businesses.';
    $badge    = $opts['badge']    ?? 'Update';
    $footer   = $opts['footer']   ?? email_layout_footer();

    return
'<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . e($siteName) . '</title>
</head>
<body style="margin:0;padding:0;background:#020617;font-family:Outfit,Segoe UI,Helvetica,Arial,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#020617;padding:32px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

          <!-- faint aurora glow behind the card -->
          <tr>
            <td style="padding:0 0 10px;border-radius:22px;background:linear-gradient(135deg,rgba(6,182,212,.5),rgba(255,255,255,.06) 40%,rgba(168,85,247,.45));">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;background:#0b1220;border-radius:21px;">

                <!-- wordmark bar (plain text brand name, no image) -->
                <tr>
                  <td style="padding:26px 30px 18px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="font-size:24px;font-weight:800;letter-spacing:-.5px;color:#ffffff;vertical-align:middle;">
                          ' . email_layout_logo($opts) . '
                        </td>
                        <td align="right" style="vertical-align:middle;">
                          <span style="font-size:10px;letter-spacing:2px;text-transform:uppercase;font-weight:800;color:#c4b5fd;background:rgba(99,102,241,.16);border:1px solid rgba(168,85,247,.35);padding:5px 10px;border-radius:999px;">' . e($badge) . '</span>
                        </td>
                      </tr>
                    </table>
                    <div style="font-size:12px;color:#64748b;margin-top:6px;">' . e($tagline) . '</div>
                  </td>
                </tr>

                <!-- divider -->
                <tr>
                  <td style="padding:0 30px;">
                    <div style="border-top:1px solid rgba(255,255,255,.08);"></div>
                  </td>
                </tr>

                <!-- heading -->
                <tr>
                  <td style="padding:26px 30px 6px;">
                    <h1 style="margin:0;font-size:22px;color:#f8fafc;font-weight:800;letter-spacing:-.3px;">' . e($heading) . '</h1>
                  </td>
                </tr>

                <!-- body -->
                <tr>
                  <td style="padding:12px 30px 26px;font-size:14px;line-height:1.7;color:#cbd5e1;">' . $bodyHtml . '</td>
                </tr>

                <!-- footer -->
                <tr>
                  <td style="padding:0 30px 28px;">
                    <div style="border-top:1px solid rgba(255,255,255,.08);padding-top:18px;font-size:12px;line-height:1.9;color:#64748b;">' . $footer . '</div>
                  </td>
                </tr>

              </table>
            </td>
          </tr>

        </table>
        <p style="font-size:11px;color:#475569;margin:16px 0 0;">You are receiving this because you reached out to ' . e($siteName) . '. If you didn`t request it, you can safely ignore this email.</p>
      </td>
    </tr>
  </table>
</body>
</html>';
}

/**
 * Wordmark — plain text brand name (no image, so nothing can be blocked).
 */
function email_layout_logo(array $opts): string
{
    unset($opts);
    return '<span style="vertical-align:middle;">Aurora</span>' .
'<span style="color:#22d3ee;vertical-align:middle;">Cyber</span>';
}

/**
 * Small styled action button (btn-teal), safe for email clients.
 */
function email_button(string $label, string $href): string
{
    return
'<table role="presentation" cellpadding="0" cellspacing="0" style="margin:10px 0 4px;">
  <tr>
    <td align="center" style="border-radius:14px;background:linear-gradient(90deg,#0f766e,#06b6d4);box-shadow:0 8px 40px -8px rgba(15,118,110,.55);">
      <a href="' . e($href) . '" style="display:inline-block;padding:12px 26px;color:#020617;font-size:14px;font-weight:800;border-radius:14px;text-decoration:none;">' . e($label) . '</a>
    </td>
  </tr>
</table>';
}

function email_layout_footer(): string
{
    $whatsapp = defined('WHATSAPP_LINK') ? WHATSAPP_LINK : '#';
    $mail     = defined('SITE_EMAIL') ? SITE_EMAIL : '';
    return
'<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td style="padding-bottom:10px;">
      <span style="font-size:14px;font-weight:800;color:#f8fafc;">Aurora</span><span style="font-size:14px;font-weight:800;color:#22d3ee;">Cyber</span>
    </td>
  </tr>
  <tr>
    <td style="color:#64748b;">Dhaka, Bangladesh<br>
      <a href="mailto:' . e($mail) . '" style="color:#06b6d4;text-decoration:none;">' . e($mail) . '</a> · ' .
      '<a href="' . e($whatsapp) . '" style="color:#06b6d4;text-decoration:none;">WhatsApp</a>
    </td>
  </tr>
  <tr>
    <td style="padding-top:8px;color:#475569;font-size:11px;">© ' . date('Y') . ' ' . e(SITE_NAME) . '. All rights reserved.</td>
  </tr>
</table>';
}

/**
 * Row of order facts used by both order confirmation and status emails
 * (glass-chip style: slate-900/50 + 1px white/10 border).
 */
function email_order_facts(array $order): string
{
    $rows = [
        'Order number' => '#' . (int) $order['id'],
        'Project type' => $order['project_type'] ?: '—',
    ];
    if (!empty($order['budget']) && (float) $order['budget'] > 0) {
        $rows['Budget'] = price_fmt((float) $order['budget']);
    }
    if (!empty($order['phone'])) {
        $rows['Phone'] = e($order['phone']);
    }
    $out = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0;border:1px solid rgba(255,255,255,.1);border-radius:14px;font-size:13px;overflow:hidden;">';
    $i = 0;
    foreach ($rows as $k => $v) {
        $bg = $i % 2 === 1 ? 'background:rgba(255,255,255,.03);' : 'background:rgba(255,255,255,.06);';
        $out .= '<tr>' .
                '<td style="padding:10px 14px;color:#94a3b8;width:130px;' . $bg . '">' . e($k) . '</td>' .
                '<td style="padding:10px 14px;color:#f8fafc;font-weight:700;' . $bg . '">' . $v . '</td>' .
                '</tr>';
        $i++;
    }
    $out .= '</table>';
    return $out;
}

/** Recap of the submitted brief (details) — cyan accent block. */
function email_brief_block(string $details): string
{
    if ($details === '') {
        return '';
    }
    return '<div style="background:rgba(6,182,212,.08);border-left:3px solid #06b6d4;padding:12px 14px;border-radius:10px;color:#e2e8f0;font-size:13px;font-style:italic;margin:12px 0 0;">' . nl2br(e($details)) . '</div>';
}

/**
 * Branded order-status email (sent to the client on EVERY admin update).
 * Returns [subject, heading, tagline, body].
 */
function email_order_status_message(array $order, string $status): array
{
    $meta      = order_status_meta($status);
    $label     = $meta['label_en'];
    $project   = $order['project_type'] ?: 'your project';
    $number    = '#' . (int) $order['id'];

    switch ($status) {
        case 'in_progress':
            $subject  = 'Your project is in progress — ' . SITE_NAME;
            $heading  = 'Update: project in progress';
            $tagline  = 'We are building your project.';
            $body     = '<p>Hi ' . e($order['name']) . ',</p>
<p>Great news — <strong>' . e($project) . '</strong> (order ' . $number . ') has moved to <strong style="color:#f59e0b;">In Progress</strong> 🚀</p>
' . email_order_facts($order) . '
<p>Our team is on it. You will hear from us as we hit milestones — and you can jump in any time.</p>
' . email_button('Message us on WhatsApp', WHATSAPP_LINK);
            break;

        case 'delivered':
            $subject  = 'Your project is delivered — ' . SITE_NAME;
            $heading  = 'Project delivered';
            $tagline  = 'Your website is live.';
            $body     = '<p>Hi ' . e($order['name']) . ',</p>
<p>Great news — <strong>' . e($project) . '</strong> is <strong style="color:#059669;">delivered</strong> 🎉</p>
' . email_order_facts($order) . '
<p>Please test everything and tell us if you need any tweaks — we are one message away.</p>
' . email_button('Message us on WhatsApp', WHATSAPP_LINK) . '
<p style="margin-top:14px;font-size:13px;color:#64748b;">Thanks for trusting ' . e(SITE_NAME) . ' with your project. Take care, and let&#39;s grow from here!</p>';
            break;

        case 'cancelled':
            $subject  = 'Your order has been cancelled — ' . SITE_NAME;
            $heading  = 'Order cancelled';
            $tagline  = 'We are here if you change your mind.';
            $body     = '<p>Hi ' . e($order['name']) . ',</p>
<p>Order ' . $number . ' (' . e($project) . ') has been <strong style="color:#f43f5e;">cancelled</strong>.</p>
' . email_order_facts($order) . '
<p>No hard feelings — if you would like to restart or adjust anything, just talk to us.</p>
' . email_button('Talk to us on WhatsApp', WHATSAPP_LINK);
            break;

        default: // pending
            $subject  = 'Order received — ' . SITE_NAME;
            $heading  = 'Order received';
            $tagline  = 'Your project is back in the queue.';
            $body     = '<p>Hi ' . e($order['name']) . ',</p>
<p>Order ' . $number . ' (' . e($project) . ') is back to <strong style="color:#38bdf8;">Pending</strong>.</p>
' . email_order_facts($order) . '
<p>We will get back to you soon with next steps.</p>
' . email_button('Message us on WhatsApp', WHATSAPP_LINK);
            break;
    }

    $badge = $label . ' · Order ' . $number;
    return [$subject, $heading, $tagline, $body, $badge];
}

/**
 * Branded payment email — $paid=false: a custom payment request from admin;
 * $paid=true: confirmation that the payment was received and logged as paid.
 * Returns [subject, heading, tagline, body, badge].
 */
function email_payment_message(array $order, float $amount, string $note = '', bool $paid = false): array
{
    $project = $order['project_type'] ?: 'your project';
    $number  = '#' . (int) $order['id'];
    $price   = price_fmt($amount);

    if ($paid) {
        $subject = 'Payment received — order ' . $number . ' — ' . SITE_NAME;
        $heading = 'Payment received';
        $tagline = 'Thank you — your project stays on track.';
        $noteHtml = '';
        $body = '<p>Hi ' . e($order['name']) . ',</p>
<p>We received <strong style="color:#059669;">' . e($price) . '</strong> for <strong>' . e($project) . '</strong> (order ' . $number . '). Your payment is logged as <strong style="color:#34d399;">paid</strong> ✔</p>
' . email_payment_facts($order, $price, true) . '
<p>We will continue with your project without interruption — if we owe you anything, just say so.</p>
' . email_button('Message us on WhatsApp', WHATSAPP_LINK) . '
<p style="margin-top:14px;font-size:13px;color:#64748b;">Thanks for trusting ' . e(SITE_NAME) . '. — The team</p>';
        $badge = 'Receipt · Order ' . $number;
    } else {
        $subject = 'Payment request — order ' . $number . ' — ' . SITE_NAME;
        $heading = 'Payment request';
        $tagline = 'Please complete your payment.';
        $noteHtml = $note !== '' ? '<div style="background:rgba(245,158,11,.08);border-left:3px solid #f59e0b;padding:12px 14px;border-radius:10px;color:#f8fafc;font-size:13px;margin:12px 0 0;">' . nl2br(e($note)) . '</div>' : '';
        $body = '<p>Hi ' . e($order['name']) . ',</p>
<p>A payment of <strong style="color:#f59e0b;">' . e($price) . '</strong> is requested for <strong>' . e($project) . '</strong> (order ' . $number . ').</p>
' . email_payment_facts($order, $price, false) . $noteHtml . '
<p>To complete your payment, message us on WhatsApp and we can arrange bKash / Nagad / bank transfer right away.</p>
' . email_button('Pay via WhatsApp', WHATSAPP_LINK) . '
<p style="margin-top:14px;font-size:13px;color:#64748b;">The payment request also shows on your account dashboard.</p>';
        $badge = 'Payment · Order ' . $number;
    }

    return [$subject, $heading, $tagline, $body, $badge];
}

/** Facts row for payment emails (order + requested amount). */
function email_payment_facts(array $order, string $price, bool $paid): string
{
    if ($paid) {
        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0;border:1px solid rgba(255,255,255,.1);border-radius:14px;font-size:13px;overflow:hidden;">
  <tr>
    <td style="padding:10px 14px;background:rgba(255,255,255,.03);color:#94a3b8;width:130px;">Order number</td>
    <td style="padding:10px 14px;background:rgba(255,255,255,.03);color:#f8fafc;font-weight:700;">#' . (int) $order['id'] . '</td>
  </tr>
  <tr>
    <td style="padding:10px 14px;background:rgba(255,255,255,.06);color:#94a3b8;width:130px;">Project type</td>
    <td style="padding:10px 14px;background:rgba(255,255,255,.06);color:#f8fafc;font-weight:700;">' . e($order['project_type'] ?: '—') . '</td>
  </tr>
  <tr>
    <td style="padding:10px 14px;background:rgba(255,255,255,.03);color:#94a3b8;width:130px;">Amount paid</td>
    <td style="padding:10px 14px;background:rgba(255,255,255,.03);color:#34d399;font-weight:800;">' . e($price) . '</td>
  </tr>
</table>';
    }
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0;border:1px solid rgba(255,255,255,.1);border-radius:14px;font-size:13px;overflow:hidden;">
  <tr>
    <td style="padding:10px 14px;background:rgba(255,255,255,.03);color:#94a3b8;width:130px;">Order number</td>
    <td style="padding:10px 14px;background:rgba(255,255,255,.03);color:#f8fafc;font-weight:700;">#' . (int) $order['id'] . '</td>
  </tr>
  <tr>
    <td style="padding:10px 14px;background:rgba(255,255,255,.06);color:#94a3b8;width:130px;">Project type</td>
    <td style="padding:10px 14px;background:rgba(255,255,255,.06);color:#f8fafc;font-weight:700;">' . e($order['project_type'] ?: '—') . '</td>
  </tr>
  <tr>
    <td style="padding:10px 14px;background:rgba(255,255,255,.03);color:#94a3b8;width:130px;">Amount requested</td>
    <td style="padding:10px 14px;background:rgba(255,255,255,.03);color:#f59e0b;font-weight:800;">' . e($price) . '</td>
  </tr>
</table>';
}