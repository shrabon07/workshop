<?php
/**
 * JSON API bootstrap — loads config, silences HTML-ish warnings.
 */
declare(strict_types=1);

if (!defined('APP_PATH')) {
    require_once __DIR__ . '/../config.php';
}

ini_set('display_errors', '0');
header_remove('X-Powered-By');

if (function_exists('header') && !headers_sent()) {
    // allow CORS only for same-site widget calls if ever cross-origin
}

function body(): array
{
    static $body = null;
    if ($body !== null) {
        return $body;
    }
    $raw = file_get_contents('php://input');
    $json = json_decode((string) $raw, true);
    $body = is_array($json) ? array_merge($_POST, $json) : $_POST;
    return $body;
}