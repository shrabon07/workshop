<?php
/**
 * Admin — lightweight real-time poll. Returns anything new since `since`:
 * newly placed orders + fresh live-chat messages (guest or another admin),
 * plus live nav counts. The admin panel polls this every ~15s from any
 * device/browser and fires a browser notification for each item.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

$since = (int) post('since');
if ($since <= 0) {
    $since = time() - 600; // generous retro window for manual page loads
}
$limit = 5;

$orders = DB::all(
    'SELECT o.id, o.status, o.budget,
            COALESCE(u.name, o.name) AS client_name,
            COALESCE(s.title_en, "Custom request") AS svc
       FROM orders o
       LEFT JOIN users u ON u.id = o.user_id
       LEFT JOIN services s ON s.id = o.service_id
      WHERE UNIX_TIMESTAMP(o.created_at) > ?
      ORDER BY o.created_at ASC
      LIMIT ' . (int) $limit,
    [$since]
);

$chats = DB::all(
    'SELECT m.id, m.chat_id, m.sender, m.message, UNIX_TIMESTAMP(m.created_at) AS ts,
            s.guest_name, u.name AS user_name
       FROM chat_messages m
       JOIN chat_sessions s ON s.id = m.chat_id
       LEFT JOIN users u ON u.id = s.user_id
      WHERE m.sender IN ("guest", "admin")
        AND UNIX_TIMESTAMP(m.created_at) > ?
      ORDER BY m.created_at ASC
      LIMIT ' . (int) $limit,
    [$since]
);

$orderOut = [];
foreach ($orders as $o) {
    $orderOut[] = [
        'id'     => (int) $o['id'],
        'status' => $o['status'],
        'budget' => (float) $o['budget'],
        'client' => $o['client_name'],
        'svc'    => $o['svc'],
    ];
}

$chatOut = [];
foreach ($chats as $m) {
    $chatOut[] = [
        'id'      => (int) $m['chat_id'],
        'msg'     => (int) $m['id'],
        'sender'  => $m['sender'],
        'name'    => $m['user_name'] !== null ? $m['user_name'] : ($m['guest_name'] !== null ? $m['guest_name'] : 'Guest'),
        'preview' => mb_substr(trim((string) $m['message']), 0, 90),
        'ts'      => (int) $m['ts'],
    ];
}

json_ok([
    'now'    => time(),
    'orders' => $orderOut,
    'chats'  => $chatOut,
    'counts' => [
        'orders_pending' => (int) DB::value('SELECT COUNT(*) FROM orders WHERE status = "pending"'),
        'chats_open'     => (int) DB::value('SELECT COUNT(*) FROM chat_sessions WHERE status = "open"'),
    ],
]);