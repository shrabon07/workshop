<?php
/**
 * Aurora Cyber branded email wrapping.
 * All transactional mail is rendered inside this layout so every message
 * carries the same logo bar, palette, contact footer and safe unsubscribe/opt-out line.
 */

function email_layout(string $heading, string $bodyHtml, array $opts = []): string
{
    $siteName = $opts['site_name'] ?? SITE_NAME;
    $tagline  = $opts['tagline']  ?? 'Websites, e-commerce & SaaS for growing businesses.';
    $footer   = $opts['footer']   ?? email_layout_footer();

    return
'<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . e($siteName) . '</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Outfit,Segoe UI,Arial,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:28px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(2,6,23,.08);">

          <!-- wordmark bar -->
          <tr>
            <td style="background:linear-gradient(120deg,#0f766e,#14b8a6 55%,#0e7490);padding:22px 32px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="font-size:26px;font-weight:800;letter-spacing:-.5px;color:#ffffff;">
                    Aurora<span style="color:#99f6e4;">Cyber</span>
                  </td>
                  <td align="right" style="vertical-align:middle;">
                    <span style="font-size:11px;letter-spacing:2px;color:#ccfbf1;text-transform:uppercase;font-weight:600;">' . e($opts['badge'] ?? 'Update') . '</span>
                  </td>
                </tr>
              </table>
              <div style="font-size:12px;color:#a7f3d0;margin-top:2px;">' . e($tagline) . '</div>
            </td>
          </tr>

          <!-- heading -->
          <tr>
            <td style="padding:32px 32px 8px;">
              <h1 style="margin:0;font-size:22px;color:#0f172a;font-weight:800;">' . e($heading) . '</h1>
            </td>
          </tr>

          <!-- body -->
          <tr>
            <td style="padding:14px 32px 26px;font-size:14px;line-height:1.7;color:#334155;">' . $bodyHtml . '</td>
          </tr>

          <!-- footer -->
          <tr>
            <td style="padding:0 32px 28px;">
              <div style="border-top:1px solid #e2e8f0;padding-top:18px;font-size:12px;line-height:1.8;color:#64748b;">' . $footer . '</div>
            </td>
          </tr>

        </table>
        <p style="font-size:11px;color:#94a3b8;margin:14px 0 0;">You are receiving this because you reached out to ' . e($siteName) . '. If you didn`t request it, you can safely ignore this email.</p>
      </td>
    </tr>
  </table>
</body>
</html>';
}

/**
 * Small styled action button, safe for email clients.
 */
function email_button(string $label, string $href): string
{
    return
'<table role="presentation" cellpadding="0" cellspacing="0" style="margin:10px 0 4px;">
  <tr>
    <td align="center" style="border-radius:12px;background:linear-gradient(120deg,#0f766e,#0e7490);">
      <a href="' . e($href) . '" style="display:inline-block;padding:12px 26px;color:#ffffff;font-size:14px;font-weight:700;border-radius:12px;text-decoration:none;">' . e($label) . '</a>
    </td>
  </tr>
</table>';
}

function email_layout_footer(): string
{
    $whatsapp = defined('WHATSAPP_LINK') ? WHATSAPP_LINK : '#';
    $mail     = defined('SITE_EMAIL') ? SITE_EMAIL : '';
    return
'<span style="font-weight:700;color:#0f172a;">Aurora Cyber</span> — Dhaka, Bangladesh<br>
<a href="mailto:' . e($mail) . '" style="color:#0f766e;">' . e($mail) . '</a> · ' .
'<a href="' . e($whatsapp) . '" style="color:#0f766e;">WhatsApp</a><br>
© ' . date('Y') . ' ' . e(SITE_NAME) . '. All rights reserved.';
}

/**
 * Row of order facts used by both order confirmation and status emails.
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
    $out = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0;border:1px solid #e2e8f0;border-radius:12px;font-size:13px;">';
    foreach ($rows as $k => $v) {
        $out .= '<tr>' .
                '<td style="padding:9px 14px;border-bottom:1px solid #f1f5f9;color:#64748b;width:120px;">' . e($k) . '</td>' .
                '<td style="padding:9px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;font-weight:700;">' . $v . '</td>' .
                '</tr>';
    }
    $out .= '</table>';
    return $out;
}

/** Recap of the submitted brief (details) for confirmation emails. */
function email_brief_block(string $details): string
{
    if ($details === '') {
        return '';
    }
    return '<div style="background:#f8fafc;border-left:3px solid #14b8a6;padding:12px 14px;border-radius:8px;color:#334155;font-size:13px;font-style:italic;margin:12px 0 0;">' . nl2br(e($details)) . '</div>';
}