<?php
/**
 * Aurora Cyber — One-click installer
 * ------------------------------------
 * Creates the MySQL database from setup.sql and seeds demo data.
 *   http://localhost/workshop/install.php?fresh=1   → drop & re-seed
 */

declare(strict_types=1);

define('CLI_HTTP_WEBROOT', true);
require_once __DIR__ . '/config.php';

$isCli = (PHP_SAPI === 'cli');
$flag  = storage_path('installed.flag');
$fresh = isset($_GET['fresh']) || isset($_POST['fresh']);

$html = function (string $title, string $body) {
    $body = '<div class="bg-slate-950 min-h-screen flex items-center justify-center p-6">
        <div class="glass w-full max-w-xl rounded-3xl p-8">
            <div class="text-cyan-300 font-bold text-2xl mb-4">Aurora Cyber</div>' . $body . '</div></div>';
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>' . e($title) . '</title>
    <style>
      body{margin:0;font-family:Outfit,system-ui,sans-serif;background:radial-gradient(1200px 600px at 20% -10%,#134e5e22,transparent),radial-gradient(1000px 500px at 90% 10%,#4c1d9522,transparent),#020617;color:#e2e8f0}
      .glass{background:rgba(15,23,42,.55);backdrop-filter:blur(22px);border:1px solid rgba(255,255,255,.1);border-radius:24px;box-shadow:0 24px 60px -20px rgba(6,182,212,.25)}
      .btn{display:inline-block;padding:.7rem 1.2rem;border-radius:.75rem;font-weight:600;color:#062018;background:linear-gradient(135deg,#0F766E,#06B6D4);text-decoration:none;transition:.2s}
      .btn:active{transform:scale(.97)}
      code{background:#0f172a;padding:2px 8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);font-size:.9em}
      .ok{color:#34d399}.err{color:#fb7185}
      table{width:100%;border-collapse:collapse;font-size:.92rem}
      td,th{text-align:left;padding:.35rem .5rem;border-bottom:1px solid rgba(255,255,255,.07)}
    </style></head><body>' . $body . '</body></html>';
};

if (!$isCli && $fresh === false && is_file($flag)) {
    $html('Installed', '<p>✅ Already installed.</p>
        <a class="btn" href="' . e(url('install.php?fresh=1')) . '">Re-install (reset database)</a>
        <a class="btn" style="background:linear-gradient(135deg,#6366F1,#A855F7);color:#fff" href="' . e(url('')) . '">Open website</a>');
    exit;
}

$log = [];

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;charset=%s', DB_HOST, DB_PORT, DB_CHARSET),
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($fresh) {
        $pdo->exec('DROP DATABASE IF EXISTS `' . DB_NAME . '`');
        $log[] = 'Dropped existing database `' . DB_NAME . '`.';
    }

    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . DB_NAME . '`');
    $log[] = 'Database `' . DB_NAME . '` ready ✓';

    $sql = file_get_contents(__DIR__ . '/setup.sql');
    if ($sql === false) {
        throw new RuntimeException('setup.sql not found next to install.php.');
    }
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $chunk) {
        $pdo->exec($chunk);
    }
    $log[] = 'Tables created + demo data seeded ✓';

    // Protect demo admin password note later; create flag.
    @file_put_contents($flag, date('c'));
    $log[] = 'Install flag written ✓';

    $body = '<ol class="ok" style="line-height:1.9">';
    foreach ($log as $line) {
        $body .= '<li>' . e($line) . '</li>';
    }
    $body .= '</ol>
    <table><tr><th>Role</th><th>Email</th><th>Password</th></tr>
    <tr><td>Admin</td><td>maileditorportfolio@gmail.com</td><td>776654</td></tr>
    <tr><td>Customer (red tick)</td><td>customer@demo.com</td><td>customer123</td></tr>
    <tr><td>Customer (grey tick)</td><td>verified@demo.com</td><td>verified123</td></tr>
    <tr><td>Customer (green tick)</td><td>full@demo.com</td><td>full123</td></tr></table>
    <p style="margin-top:1.4rem"><a class="btn" href="' . e(url('')) . '">Open website →</a>
       <a class="btn" style="background:linear-gradient(135deg,#6366F1,#A855F7);color:#fff" href="' . e(url('admin/login.php')) . '">Open admin →</a></p>';

    if ($isCli) {
        echo "INSTALL OK\n" . implode("\n", $log) . "\n";
    } else {
        $html('Install complete', $body);
    }
} catch (Throwable $e) {
    $err = 'Install failed: ' . $e->getMessage();
    $body = '<p class="err">' . e($err) . '</p>
             <p>Check that MySQL is running and the credentials in <code>config.php</code> are correct.</p>
             <p><a class="btn" href="' . e(url('install.php?fresh=1')) . '">Retry (fresh)</a></p>';
    if ($isCli) {
        echo "INSTALL FAIL\n" . $err . "\n";
        exit(1);
    }
    $html('Install failed', $body);
}