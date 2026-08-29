<?php
/**
 * Payment requests — admin sends a custom payment request against a customer's
 * active order; the customer sees it on their dashboard and gets notified.
 * Admin later manually marks it paid (red → green).
 */

declare(strict_types=1);

function ensure_payment_requests_table(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    DB::run('CREATE TABLE IF NOT EXISTS payment_requests (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        note TEXT,
        status ENUM(\'unpaid\',\'paid\') NOT NULL DEFAULT \'unpaid\',
        created_at DATETIME NOT NULL,
        paid_at DATETIME NULL,
        KEY idx_user (user_id, status, id),
        KEY idx_order (order_id),
        CONSTRAINT fk_pr_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
        CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $done = true;
}

/**
 * Active (non-cancelled) orders for a customer, flagged with whether an
 * unpaid payment request already exists (those are hidden from the composer).
 */
function payable_orders(int $userId): array
{
    ensure_payment_requests_table();
    return DB::all(
        'SELECT o.*,
                EXISTS(SELECT 1 FROM payment_requests pr
                        WHERE pr.order_id = o.id AND pr.status = \'unpaid\') AS has_unpaid
           FROM orders o
          WHERE o.user_id = ? AND o.status <> \'cancelled\'
          ORDER BY o.id DESC',
        [$userId]
    );
}

/** Insert a payment request. Returns the new id. */
function payment_request_create(int $userId, int $orderId, float $amount, string $note = ''): int
{
    ensure_payment_requests_table();
    return DB::insert('payment_requests', [
        'order_id'    => $orderId,
        'user_id'     => $userId,
        'amount'      => $amount,
        'note'        => $note !== '' ? $note : null,
        'status'      => 'unpaid',
        'created_at'  => date('Y-m-d H:i:s'),
    ]);
}

/** Mark a request as paid (admin override). */
function payment_request_mark_paid(int $reqId): void
{
    ensure_payment_requests_table();
    DB::update('payment_requests', ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')], 'id = ?', [$reqId]);
}

/** All requests (admin list), newest first, joined with the order. */
function all_payment_requests(int $limit = 200): array
{
    ensure_payment_requests_table();
    return DB::all(
        'SELECT pr.*, o.project_type, o.status AS order_status,
                COALESCE(u.name, o.name) AS customer_name
           FROM payment_requests pr
           JOIN orders o ON o.id = pr.order_id
           LEFT JOIN users u ON u.id = pr.user_id
          ORDER BY pr.id DESC
          LIMIT ' . (int) $limit
    );
}

/** A customer's payment requests, newest first, joined with the order. */
function customer_payment_requests(int $userId, int $limit = 30): array
{
    ensure_payment_requests_table();
    return DB::all(
        'SELECT pr.*, o.project_type
           FROM payment_requests pr
           JOIN orders o ON o.id = pr.order_id
          WHERE pr.user_id = ?
          ORDER BY pr.id DESC
          LIMIT ' . (int) $limit,
        [$userId]
    );
}

/** Number of unpaid requests (admin nav badge). */
function unpaid_payment_request_count(): int
{
    ensure_payment_requests_table();
    return (int) DB::value('SELECT COUNT(*) FROM payment_requests WHERE status = \'unpaid\'');
}

/** In-app notification when a payment request is sent. */
function notify_payment_request(int $userId, array $order, float $amount, int $reqId): void
{
    $num  = '#' . (int) $order['id'];
    $amt  = price_fmt($amount);
    $note = trim((string) ($order['project_type'] ?: ''));
    notification_create(
        $userId,
        'Payment request for order ' . $num . ' — ' . $amt,
        'অর্ডার ' . $num . '-এর পেমেন্ট অনুরোধ — ' . $amt,
        $note !== '' ? 'A payment of ' . $amt . ' is requested for ' . $note . ' (' . $num . '). Tap to view.' : 'A payment of ' . $amt . ' is requested for order ' . $num . '. Tap to view.',
        $note !== '' ? $note . ' (' . $num . ') এর জন্য ' . $amt . ' পেমেন্ট অনুরোধ করা হয়েছে। দেখতে ট্যাপ করুন।' : 'অর্ডার ' . $num . ' এর জন্য ' . $amt . ' পেমেন্ট অনুরোধ করা হয়েছে। দেখতে ট্যাপ করুন।',
        url('account/dashboard.php#payments'),
        'payment_request',
        $reqId
    );
}

/** In-app notification when a request is marked paid. */
function notify_payment_paid(int $userId, array $order, float $amount, int $reqId): void
{
    $num = '#' . (int) $order['id'];
    $amt = price_fmt($amount);
    notification_create(
        $userId,
        'Payment received for order ' . $num . ' — ' . $amt,
        'অর্ডার ' . $num . '-এর পেমেন্ট প্রাপ্ত হয়েছে — ' . $amt,
        'Thank you! ' . $amt . ' for order ' . $num . ' has been received and logged as paid.',
        'ধন্যবাদ! অর্ডার ' . $num . ' এর ' . $amt . ' পেমেন্ট প্রাপ্ত হয়েছে এবং পরিশোধিত হিসেবে যুক্ত হয়েছে।',
        url('account/dashboard.php#payments'),
        'payment_paid',
        $reqId
    );
}