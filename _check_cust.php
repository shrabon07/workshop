<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/bootstrap.php';
$rows = DB::all('SELECT u.id, u.name, v.admin_override, v.email_verified, v.whatsapp_verified FROM users u LEFT JOIN verification_status v ON v.user_id = u.id WHERE u.role = "customer" ORDER BY u.id');
foreach ($rows as $r) {
    echo sprintf("id=%d %-16s override=%-5s email=%d whatsapp=%d\n", $r['id'], $r['name'], $r['admin_override'], $r['email_verified'], $r['whatsapp_verified']);
}