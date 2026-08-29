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