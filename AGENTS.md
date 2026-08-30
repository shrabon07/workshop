# AGENTS.md

Guidance for AI coding agents working in this repository.

## Git workflow (REQUIRED)
- After completing any task or meaningful change, ALWAYS `git add -A`, commit with a concise message, and `git push` to `origin/main`.
- Do NOT wait to be asked to commit or push — the user expects every change to be committed and pushed automatically.

## Environment
- Working copy: `D:\infinity\facebook-ecommerce\workshop`
- Served (dev): junction `D:\Bismillah\empty\htdocs\workshop` -> project, Apache on :80 -> `http://localhost/workshop`
- PHP (8.2.12, no GD): `D:\Bismillah\empty\php\php.exe`
- Stack: vanilla PHP + MySQL/MariaDB, HTML5 + Tailwind + vanilla JS, PHPMailer-or-offline mail fallback, `wa.me` links.

## Commands
- CSS build: `npm run build:css` (compiles to `assets/css/tailwind.css`; run after editing tailwind classes referenced in new markup)
- PHP lint all: loop `php -l` over `*.php`
- DB reseed to pristine demo data: `http://localhost/workshop/install.php?fresh=1`

## Conventions & gotchas
- Public JS must use `window.AURORA_BASE` (set in `includes/public-footer.php`) for any API fetch — relative `api/...` URLs 404 on `/account/*` pages.
- Verification ticks: red = nothing, grey = email only, green = email+whatsapp; `admin_override` wins.
- `verification_status` columns: `email_verified`, `whatsapp_verified`, `admin_override`.
- Admin pages live in `/admin/`; relative `api/admin/...` URLs are fine there.
- Correct include for `api/admin/*`, `api/verify/*`, `api/order/submit.php` is `../../includes/api.php`.
- InfinityFree gotcha: its firewall 403s any URL containing `chat`, plus `api/verify/*` and `api/order/create.php` — the shipped names are the safe renames (`support.js`/`api/support.php`/`admin/support.php`, `api/order/submit.php`, `api/verify/send.php|check.php`). DONT reintroduce those names in URLs.
- Bangla shows as `?` in Windows console/mysql byte-dump — display-only; HTTP/DB bytes are correct UTF-8.
- CLI `install.php` without `fresh` fails with duplicate-key; reseed only via web `?fresh=1`.
- demo admin: `maileditorportfolio@gmail.com`/`776654` (live + seeded); customers `customer@demo.com`/`customer123`, `verified@demo.com`/`verified123`, `full@demo.com`/`full123`, `demo@auroracyber.com`/`demo12345`.

## Live deployment & state (memorized)
- Live: `https://aurora-cyber.infy.click` (InfinityFree). FTP `ftpupload.net`, user `if0_42779072`, pass `XUOQ8Cw7nU1`, docroot `/htdocs`. DB `sql202.infinityfree.com`, db `if0_42779072_aurora_cyber`, user `if0_42779072`, pass `XUOQ8Cw7nU1`.
- Staging dir: `C:\Users\user\AppData\Local\Temp\opencode\deploy\aurora-cyber` (mirror of repo; source for FTP uploads and the Desktop zip `C:\Users\user\OneDrive\Desktop\aurora-cyber-deploy.zip`). Rebuild zip after any deploy change (exclude `__*` files).
- Deploy loop: edit repo files → copy to staging → FTP put changed files → rebuild zip → commit+push. **ftp-sync compares only SIZE** — force-upload same-length edits.
- Live HTTP tests MUST use Chrome + `puppeteer-core` (from `C:\Users\user\AppData\Local\Temp\opencode\chatdebug`) — InfinityFree anti-bot blocks curl. Login pages: admin `maileditorportfolio@gmail.com`/`776654`, customers as listed above.
- Egress/SMTP source IP of server: `185.27.134.67` (whitelisted in Brevo).

## Email/mail sending (fixed, working)
- Gmail SMTP is the ONLY outbound route now (no Brevo relay — Brevo rewrote From to `@<id>.brevosend.com` because the sender isn't verified there).
- `smtp.gmail.com` does NOT resolve on InfinityFree (DNS-blocked) → connect to pinned IP `173.194.41.109:587` with TLS `SNI peer_name=smtp.gmail.com`, `verify_peer_name=false` via `$mail->SMTPOptions` (in `includes/mailer.php`).
- Secrets (`config.secrets.php`, git-ignored; staging/live use `SECRET_*` constants, local uses `putenv`): `MAIL_HOST=173.194.41.109`, `MAIL_HOST_SNI=smtp.gmail.com`, `MAIL_USER=maileditorportfolio@gmail.com`, `MAIL_PASS=wggrdyhsddxvkdaa` (Gmail App Password — Gmail revokes these without notice; on `535 BadCredentials` ask the user to regenerate at myaccount.google.com/apppasswords and swap).
- Ladder in `send_mail()`: Brevo REST API (`BREVO_API_KEY`, currently 401-invalid — harmless, fast-fails) → Gmail SMTP → `mail()` → disk fallback `/storage/mailout`. Disk files = failed sends; if you see recent ones, mail is broken → check Gmail auth first.
- `SITE_EMAIL=maileditorportfolio@gmail.com` (used by mailto/footer). Order emails, OTPs, admin alerts all deliver via Gmail SMTP.

## Recently completed work
1. Firewall-safe renames shipped (chat→support, order/create→order/submit, verify/send-otp→send, verify/confirm→check + admin renames); old blocked URLs deleted remotely.
2. Root DB cause fixed: deployed `config.php` was stale pre-`ac_env` (APP_ENV=dev, DB_HOST=127.0.0.1) — now APP_ENV=prod, DB_HOST=sql202.
3. Email now delivers (see above). Deployed/test probes were cleaned up.
4. Mobile responsiveness fixed & verified 320/390/768/1024 clean (no overflow/zoom/pan) across home, account, admin pages:
   - `index.php` `#order` + `admin/inc/head.php` `<main>` got `overflow-hidden` (aurora-blobs pushed pages past viewport; Chrome shrink-to-fit zoomed out).
   - `admin/login.php` blobs wrapped in `absolute inset-0 overflow-hidden` (body-level overflow doesn't stop the zoom).
   - `account/dashboard.php` orders table: `min-w-0` + `grid-cols-1` on its grid so `min-w-[560px]` table scrolls in-card (was 627px).
   - Rebuilt `assets/css/tailwind.css` after adding those utilities (rebuild required whenever new classes are used).
5. Footers updated site-wide: email `maileditorportfolio@gmail.com` (mailto), site `aurora-cyber.infy.click`, address `Mirpur, Dhaka-1215` on public + admin footers; WhatsApp is icon-only (`wa.me/8801977665421`); removed old `hello@auroracyber.com` and the visible `01977665421` text.
6. New public pages `terms-privacy.php` + `payment-methods.php` (root), linked from public footer Company column AND admin footer. Bilingual via `.e/.b` spans (rely on CSS `html.lang-*` toggle, no i18n keys needed). Keep static pages free of off-screen aurora-blobs (overflow risk).
7. Admin staff management (super admin model): **Aurora Admin** (`maileditorportfolio@gmail.com`, users id#1) is the only **super admin** (`is_super_admin=1`). Users table has `is_active` + `is_super_admin` columns (added to `setup.sql` AND live via one-off migration).
   - `includes/auth.php`: `is_admin()` now also requires `is_active=1` (deactivated admins are locked out immediately); new `is_super_admin()`.
   - `admin/admins.php` lists admin accounts — super admin sees Add button + per-row Edit/Deactivate(⏻)/Delete; regular admins see it read-only with a notice; own row is tagged YOU and hides toggle/delete (server double-guards: `400 You cannot deactivate/delete your own account.`).
   - APIs (all behind `admin-guard.php` → 401 to non-admins): `admin_create.php`, `admin_edit.php`, `admin_deactivate.php`, `admin_delete.php` — each requires `is_super_admin()` else **403**. Regular admins got 403 on all four in live tests; anon/customer got 401.
   - `admin/login.php` + `api/auth/login.php` reject deactivated admins with a "deactivated/contact the super admin" message.
   - `assets/js/admin-admins.js` — create/edit/deactivate/delete handlers (uses `window.Admin`; keep ALL helpers inside `boot()` so `A` is in scope — a helper outside threw `A is not defined`).
   - **Mail identity:** admin-triggered emails (order status, payment request/paid, customer email single+bulk, customer create, admin create) stamp the acting admin — `admin_identity()` helper in `includes/auth.php`, `send_mail()` 6th arg `$sentBy`, `email_layout()` `"sent_by"` opt renders a "Sent by <admin> · <email>" footer line.
   - **Per-admin sender (app password):** regular admins can connect their OWN Gmail so their customer emails come FROM their address. `admin_mail_settings` table (admin_id PK FK users CASCADE, smtp_email, smtp_pass AES-encrypted via `ENCRYPTION_KEY`, verified, verified_at). Page `admin/mail-settings.php` (nav item `mail` ONLY for non-super; i18n `a_mail`), API `mail_settings_save.php` (403 for super; requires exactly-16-char app password; tests creds with `smtp_test_auth()` — a STRICT single-SMTP proof-mail using ONLY those creds, no fallback; stores encrypted + verified flag). `admin_mail_profile()` returns verified profile for the acting admin; `send_mail()` tries the admin's own SMTP first, then site account; **Brevo skipped when an admin sender is active** (so From isn't rewritten). SMS ladder + SMTP code refactored into `phpmailer_try()` (one strict attempt, used by send_mail AND smtp_test_auth so bad creds are detectable). **Super admin + OTP verification always send from the site account** (`admin_mail_profile` returns null for super; customer OTP flows never pass `$sentBy`). Live-verified: super blocked (403, no nav, no form), regular admin bad-password → verified=false + clear guidance, good-password → verified + proof-mail + subsequent sends route via profile; row cascades when admin deleted.
   - **At-rest encryption gotcha:** `app_encrypt()` uses `ENCRYPTION_KEY` (config.php: `define('ENCRYPTION_KEY', ac_env('SECRET_ENCRYPTION_KEY','ENCRYPTION_KEY',''))`). That value MUST come from `config.secrets.php` — `SECRET_ENCRYPTION_KEY` (live) / `putenv('ENCRYPTION_KEY=…')` (local). It was empty (false "defined but len=0") until 2026-08-30, so older stored rows are `b64:` plain-base64 (still decrypt fine — `app_decrypt` honors the `b64:` fallback prefix). The current key `cfd24d786a2f966318dda6c9b44a99698338067467150cfa2e942296ff5d60d4` lives in the live `htdocs/config.secrets.php` + local `config.secrets.php` + tracked `config.secrets.php.example`. Keep every deploy's key identical (changing it orphans `aes:`-stored passwords). **Never put config.secrets.php in the staging/zip** (the deploy zip leaked it once — DEBUG/DB/Gmail secrets; delete old zips if shared).
   - **`smtp_test_auth()` retries 3× (2s sleep)** to ride over transient Gmail/InfinityFree egress throttles — a one-off hiccup must not strand an admin "saved-unverified". Verified LIVE with flameshrabon's own app password (`hmsr lnhy naeb xcqz`, stored `aes:` + verified=1, decrypt-check OK; earlier diag proved `535`/`235` lines + account acceptance).
   - Live DB admins: #1 Aurora Admin (super, `maileditorportfolio@gmail.com`) + #9 `flameshrabon@gmail.com` (Nahidul Islam, regular, active). Test admins cleaned after every round.
   - **New-admin self-serves app password (live-verified end-to-end):** a freshly-created regular admin logs in, sees the `mail-settings.php` nav item, the form loads (email/password/save, NOT blocked/403), and can save their own app password — a bogus 16-char creds submit returns `verified:false` + "Not verified" (real SMTP check, not a 403). Super admin stays blocked (403, no nav, no form). Verified with a throwaway `apppw-test@example.local` admin created via super, signed-in-as via an isolated puppeteer context, submitted save, then the super deleted it (cascade removes its settings row).
   - **Careful when writing cleanup scripts that parse `/admin/admins.php`:** the page's sidebar/footer also contains admins' emails, so naive regex over the whole HTML can match the WRONG row and delete the wrong admin — only parse `tr[data-id]` rows, and always prefer deleting by the exact id from the API. During this round a bad cleanup deleted flameshrabon (#9); restored via a temp `__restore.php` probe (re-created id 9, same name/email, active, verified Gmail sender re-stored, credentials emailed to the owner). Restore probe was FTP-deleted afterward. Also note AUTO_INCREMENT reuses freed ids (apppw-test came back as id 9, then 18, then 19) — always identify by email/content, never assume ids stay stable.
   - **Permission model (super-only):** delete order, delete customer, mark payment paid/approve payment status. Regular admins CAN request payment (`payment_request_create` — sends customer email), create/edit customers, `customer_email` single/bulk, `verify_override` status ticks, order status change, everything else.
     - `api/admin/order_delete.php` (super-only, `payment_requests` cascade; 404 when already gone), `customer_delete.php` (super-only 403), `payment_request_paid.php` (super-only 403 → "Only the super admin can approve payment status.", sends paid email).
     - `admin/orders.php` 🗑 button, `admin/payments.php` "Mark as paid" button + 🔒 lock for regulars, `admin/customers.php` delete button — all rendered only when `is_super_admin()`; `assets/js/admin-orders.js` handles `order-del`. Verified live end-to-end (throwaway order/customer/admin created+deleted via real flows).
8. **Admin browser notifications** (any logged-in admin, any device/browser, while an admin page is open):
   - `api/admin/push_poll.php` — admin-guarded poll (`since` unix-sec; first call `now`-600 retro). Returns new orders (`created_at > since`, joins client name + service title) and new live-chat messages (`sender IN guest,admin`, joins chat guest/user name, preview ≤90 chars) plus live `counts` (`orders_pending`, `chats_open`). POST → CSRF enforced (no token = 403).
   - `assets/js/admin-push.js` — loaded via `admin/inc/foot.php` on EVERY admin page (after admin.js; waits for `window.Admin`). Polls every 15s via `A.post` (abs path via AURORA_BASE). Baseline first poll sets `lastSince=d.now` (no retro burst). Fires browser `Notification` (permission granted + `acpush=1` in localStorage) AND an in-panel toast; beeps when the tab is hidden. Orders grouped per order-id, chat bursts grouped per chat (only latest msg notifies, dedup via `chatMsgs`); suppresses when already viewing that chat on `support.php?chat_id=`. Click-to-open navigations (orders.php / support.php?chat_id=).
   - 🔔 bell in `admin/inc/head.php` topbar with red badge = pending orders + open chats (initial from `data-orders`/`data-chats`, then from `counts`). Click: request permission → toggle on/off (`acpush`). Prompt toast shown once (`acpushasked`). Icon dims when off.
   - HTTPS origin required for `Notification` (live site is HTTPS — fine). Live-verified: bell UI + attrs, seeded order#12 + chat msg showed in poll, counts 4→5 pending / 5→6 open, no-CSRF → 403, clean-up removed rows.

## Remaining/known items
- Live DB has test data: order #7 (`maileditorportfolio@gmail.com` mail-test), order #8 (`Payment request` was not delivered during the 535 outage — optional manual resend), earlier order #5 (`e2e-smoke…@example.com`), guest chat sessions. Cleanup via phpMyAdmin if desired.
- Brevo REST API key is invalid (401) — left configured; if the user provides a working key AND verifies a Brevo sender, REST becomes primary. Otherwise leave as-is.
- Any further blobs added to new sections must be inside a clipped parent (`overflow-hidden`) or they re-break mobile.