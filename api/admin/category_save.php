<?php
/** Add / edit a service category (admin). */

require_once __DIR__ . '/../../includes/admin-guard.php';

$id       = post('id') !== '' && post('id') !== null ? (int) post('id') : null;
$nameEn   = trim((string) post('name_en'));
$nameBn   = trim((string) post('name_bn'));
$slug     = trim((string) post('slug'));
$sortOrder = max(0, (int) post('sort_order'));

if (mb_strlen($nameEn) < 2) json_error('English category name is required.');
if (mb_strlen($nameBn) < 2) json_error('বাংলা ক্যাটাগরি নাম প্রয়োজন।');
if ($slug === '') json_error('Slug is required.');
if ($id === null && DB::get('SELECT id FROM service_categories WHERE slug = ?', [$slug])) {
    json_error('This slug is already in use.');
}

$data = ['name_en' => $nameEn, 'name_bn' => $nameBn, 'slug' => $slug, 'sort_order' => $sortOrder];

if ($id) {
    DB::update('service_categories', $data, 'id = ?', [$id]);
} else {
    $id = DB::insert('service_categories', $data);
}

json_ok(['id' => $id]);