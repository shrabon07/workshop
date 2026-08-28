<?php
/** Admin manual override of a customer's verification tick (no/red/grey/green). */

require_once __DIR__ . '/../../includes/admin-guard.php';

$userId    = (int) post('user_id');
$override  = (string) post('override');

if (!in_array($override, ['none', 'red', 'grey', 'green'], true)) {
    json_error('Invalid override value.');
}

$user = DB::get('SELECT id, name, email FROM users WHERE id = ? AND role = "customer"', [$userId]);
if (!$user) {
    json_error('Customer not found.', 404);
}

$rec = verification_record($userId);
DB::update('verification_status', ['admin_override' => $override], 'user_id = ?', [$userId]);

$tick = verification_tick(['id' => $userId, 'role' => 'customer']);

json_ok([
    'user_id' => $userId,
    'override' => $override,
    'tick' => $tick,
]);