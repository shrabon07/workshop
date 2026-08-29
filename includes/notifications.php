<?php
/**
 * In-app customer notifications — created when order status changes so the
 * customer can see updates on their account dashboard.
 */

declare(strict_types=1);

function ensure_notifications_table(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    DB::run('CREATE TABLE IF NOT EXISTS app_notifications (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type VARCHAR(40) NOT NULL DEFAULT \'order_status\',
        ref_id INT DEFAULT NULL,
        title_en VARCHAR(190) NOT NULL,
        title_bn VARCHAR(190) NOT NULL DEFAULT \'\',
        body_en TEXT,
        body_bn TEXT,
        link VARCHAR(255) NOT NULL DEFAULT \'\',
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        KEY idx_user (user_id, is_read, id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $done = true;
}

function notification_create(
    int    $userId,
    string $titleEn,
    string $titleBn = '',
    string $bodyEn = '',
    string $bodyBn = '',
    string $link = '',
    string $type = 'order_status',
    ?int   $refId = null
): void {
    ensure_notifications_table();
    DB::insert('app_notifications', [
        'user_id'    => $userId,
        'type'       => $type,
        'ref_id'     => $refId,
        'title_en'   => $titleEn,
        'title_bn'   => $titleBn !== '' ? $titleBn : $titleEn,
        'body_en'    => $bodyEn,
        'body_bn'    => $bodyBn !== '' ? $bodyBn : $bodyEn,
        'link'       => $link,
        'is_read'    => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

/** Customer-facing notification for an order status change (logged-in orders only). */
function notify_order_status(array $order, string $status): void
{
    $uid = (int) ($order['user_id'] ?? 0);
    if ($uid <= 0) {
        return; // guest orders are covered by email only
    }

    $num  = '#' . (int) $order['id'];
    $proj = trim((string) ($order['project_type'] ?: ''));
    $link = url('account/dashboard.php');

    switch ($status) {
        case 'in_progress':
            notification_create(
                $uid,
                'Your project is now in progress — ' . $num,
                'আপনার প্রজেক্ট এখন চলছে — ' . $num,
                $proj !== '' ? 'We have started working on ' . $proj . '. Our team keeps you posted here.' : 'We have started working on your order. Updates land here as we go.',
                $proj !== '' ? 'আমরা ' . $proj . '-এ কাজ শুরু করেছি। অগ্রগতি এখানেই পাবেন।' : 'আমরা আপনার অর্ডারের কাজ শুরু করেছি। অগ্রগতি এখানেই পাবেন।',
                $link,
                'order_status',
                (int) $order['id']
            );
            break;

        case 'delivered':
            notification_create(
                $uid,
                'Your project has been delivered — ' . $num,
                'আপনার প্রজেক্ট ডেলিভারি সম্পন্ন হয়েছে — ' . $num,
                $proj !== '' ? $proj . ' is delivered! Please test it and message us for any tweaks.' : 'Your order is delivered! Please test it and message us for any tweaks.',
                $proj !== '' ? $proj . ' ডেলিভারি সম্পন্ন! পরীক্ষা করে দেখুন, কোনো পরিবর্তন চাইলে জানান।' : 'আপনার অর্ডার ডেলিভারি সম্পন্ন! পরীক্ষা করে দেখুন, কোনো পরিবর্তন চাইলে জানান।',
                $link,
                'order_status',
                (int) $order['id']
            );
            break;

        case 'cancelled':
            notification_create(
                $uid,
                'Order cancelled — ' . $num,
                'অর্ডার বাতিল হয়েছে — ' . $num,
                $proj !== '' ? 'Order for ' . $proj . ' was cancelled. Talk to us on WhatsApp to restart.' : 'Your order was cancelled. Talk to us on WhatsApp to restart.',
                $proj !== '' ? $proj . ' এর অর্ডার বাতিল হয়েছে। পুনরায় শুরু করতে হোয়াটসঅ্যাপে জানান।' : 'আপনার অর্ডার বাতিল হয়েছে। পুনরায় শুরু করতে হোয়াটসঅ্যাপে জানান।',
                $link,
                'order_status',
                (int) $order['id']
            );
            break;

        default: // pending
            notification_create(
                $uid,
                'Order received — ' . $num,
                'অর্ডার গৃহীত হয়েছে — ' . $num,
                $proj !== '' ? 'We received your ' . $proj . ' order. Our team will contact you soon.' : 'We received your order. Our team will contact you soon.',
                $proj !== '' ? 'আমরা আপনার ' . $proj . ' অর্ডার পেয়েছি। আমাদের টিম শীঘ্রই যোগাযোগ করবে।' : 'আমরা আপনার অর্ডার পেয়েছি। আমাদের টিম শীঘ্রই যোগাযোগ করবে।',
                $link,
                'order_status',
                (int) $order['id']
            );
            break;
    }
}

/** Latest notifications for a user (newest first). */
function user_notifications(int $userId, int $limit = 20): array
{
    ensure_notifications_table();
    return DB::all('SELECT * FROM app_notifications WHERE user_id = ? ORDER BY id DESC LIMIT ' . (int) $limit, [$userId]);
}

/** Unread notification count for a user. */
function user_unread_count(int $userId): int
{
    ensure_notifications_table();
    return (int) DB::value('SELECT COUNT(*) FROM app_notifications WHERE user_id = ? AND is_read = 0', [$userId]);
}