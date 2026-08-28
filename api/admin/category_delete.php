<?php
/**
 * Delete a category (admin) — refuses if services are still assigned so no
 * orphaned services can exist. Admin must reassign services first.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

$id = (int) post('id');

$assigned = DB::value('SELECT COUNT(*) FROM services WHERE category_id = ? AND status != "archived"', [$id]);
if ($assigned > 0) {
    json_error(
        "This category still has $assigned " . ($assigned > 1 ? 'services' : 'service') . '. Reassign them to another category first.',
        409
    );
}
// also block if only archived orphans remain? Allow deletion (FK SET NULL).
DB::update('services', ['category_id' => null], 'category_id = ?', [$id]);
DB::run('DELETE FROM service_categories WHERE id = ?', [$id]);

json_ok(['message' => 'Category deleted.']);