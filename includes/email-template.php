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

                <!-- wordmark bar (same as site nav: tile logo + white Aurora + gradient Cyber) -->
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
 * Logo block — a CSS-only render of the site favicon tile (gradient rounded
 * square + the stylised A) so it shows in every mail client with no image
 * attachment or remote URL required.
 */
function email_layout_logo(array $opts): string
{
    unset($opts);
    return
'<table role="presentation" cellpadding="0" cellspacing="0" style="display:inline-table;vertical-align:middle;margin-right:11px;">
  <tr>
    <td width="38" height="38" align="center" valign="middle"
        style="width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,#0f766e,#06b6d4);">
      <span style="font-size:21px;line-height:38px;font-weight:800;color:#03201c;font-family:Georgia,serif;">A</span>
    </td>
  </tr>
</table>' .
'<span style="vertical-align:middle;">Aurora</span>' .
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