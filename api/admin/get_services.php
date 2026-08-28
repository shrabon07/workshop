<?php
/**
 * GET /api/admin/get_services.php?q=&category_id=&status=
 * Returns services (search + filter) for the admin list page.
 */

require_once __DIR__ . '/../../includes/admin-guard.php';

$q    = trim((string) get('q'));
$cat  = get('category_id') !== null && get('category_id') !== '' ? (int) get('category_id') : null;
$stat = get('status') ?: 'all';

$statusFilter = [
    'all'       => 's.status != "archived"',
    'active'    => 's.status = "active"',
    'inactive'  => 's.status = "inactive"',
    'archived'  => 's.status = "archived"',
];
$sql  = 'SELECT s.*, c.name_en AS cat_en, c.name_bn AS cat_bn
           FROM services s
           LEFT JOIN service_categories c ON c.id = s.category_id
          WHERE ' . ($statusFilter[$stat] ?? $statusFilter['all']);
$p = [];

if ($cat) {
    $sql .= ' AND s.category_id = ?';
    $p[]  = $cat;
}
if ($q !== '') {
    $sql .= ' AND (s.title_en LIKE ? OR s.title_bn LIKE ? OR s.slug LIKE ?)';
    $like = '%' . $q . '%';
    $p[] = $like; $p[] = $like; $p[] = $like;
}

$sql .= ' ORDER BY s.status = "archived", s.sort_order ASC, s.id DESC';

$rows = DB::all($sql, $p);
foreach ($rows as &$r) {
    $r['features_en'] = json_decode((string) $r['features_en'], true) ?: [];
    $r['features_bn'] = json_decode((string) $r['features_bn'], true) ?: [];
    $r['gallery']     = json_decode((string) $r['gallery'], true) ?: [];
    $r['price_label'] = $r['price_label'];
    unset($r['full_desc_en'], $r['full_desc_bn']); // keep payload lean
}

json_ok(['count' => count($rows), 'services' => $rows]);