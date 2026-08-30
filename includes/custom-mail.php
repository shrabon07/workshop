<?php
/**
 * Custom mail composer log — the "mail pad" archive.
 * Every custom mail an admin sends is recorded here; the super admin can
 * read the whole list and delete entries (removed from the DB), while a
 * regular admin only ever sees their own sent mails (read-only).
 */

declare(strict_types=1);

function ensure_custom_mail_log_table(): void
{
    static $done = false;
    static $checked = false;
    if ($done) {
        return;
    }
    if (!$checked) {
        $row = DB::get(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [DB_NAME, 'custom_mail_log']
        );
        $checked = true;
        if ($row) {
            $done = true;
            return;
        }
    }
    DB::run('CREATE TABLE IF NOT EXISTS custom_mail_log (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        admin_id INT UNSIGNED NOT NULL,
        recipients VARCHAR(1500) NOT NULL,
        subject VARCHAR(190) NOT NULL,
        message TEXT NOT NULL,
        sent_count SMALLINT UNSIGNED NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL,
        KEY idx_admin (admin_id, id),
        CONSTRAINT fk_cml_admin FOREIGN KEY (admin_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $done = true;
}

/**
 * Record a sent custom mail. Returns the new log id.
 * $recipients is the comma-separated To list, $sentCount the delivered count.
 */
function custom_mail_log_add(array $recipients, string $subject, string $message, int $sentCount): int
{
    ensure_custom_mail_log_table();
    return DB::insert('custom_mail_log', [
        'admin_id'   => (int) current_user_id(),
        'recipients' => implode(', ', $recipients),
        'subject'    => $subject,
        'message'    => $message,
        'sent_count' => $sentCount,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

/**
 * List custom mails the current admin may read.
 *  - super admin: every mail ever sent (newest first)
 *  - regular admin: only their own sent mails
 * Returns rows joined with the sending admin (name/email) for display.
 */
function custom_mail_log_list(int $limit = 200): array
{
    ensure_custom_mail_log_table();
    if (is_super_admin()) {
        return DB::all(
            'SELECT m.*, u.name AS admin_name, u.email AS admin_email
               FROM custom_mail_log m
               LEFT JOIN users u ON u.id = m.admin_id
              ORDER BY m.id DESC
              LIMIT ' . (int) $limit
        );
    }
    return DB::all(
        'SELECT m.*, u.name AS admin_name, u.email AS admin_email
           FROM custom_mail_log m
           LEFT JOIN users u ON u.id = m.admin_id
          WHERE m.admin_id = ?
          ORDER BY m.id DESC
          LIMIT ' . (int) $limit,
        [current_user_id()]
    );
}

/**
 * Hard-delete a custom-mail log row. Super admin only — caller must guard.
 * Returns true if a row was actually removed.
 */
function custom_mail_log_delete(int $id): bool
{
    ensure_custom_mail_log_table();
    $stmt = DB::run('DELETE FROM custom_mail_log WHERE id = ?', [$id]);
    return $stmt->rowCount() > 0;
}
