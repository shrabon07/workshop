<?php
/**
 * Shared admin service payload parsing — used by create_service.php and
 * update_service.php so both forms behave identically.
 */

declare(strict_types=1);

function services_payload(): array
{
    $featuresEn = [];
    foreach ((array) post('features_en') as $f) {
        $f = trim((string) $f);
        if ($f !== '') $featuresEn[] = $f;
    }
    $featuresBn = [];
    foreach ((array) post('features_bn') as $f) {
        $f = trim((string) $f);
        if ($f !== '') $featuresBn[] = $f;
    }
    // force bilingual parity: pad shorter list
    $max = max(count($featuresEn), count($featuresBn));
    while (count($featuresEn) < $max) $featuresEn[] = '';
    while (count($featuresBn) < $max) $featuresBn[] = '';

    $gallery = [];
    foreach ((array) post('gallery') as $g) {
        $g = trim((string) $g);
        if ($g !== '') $gallery[] = $g;
    }

    return [
        'category_id'   => post('category_id') !== '' && post('category_id') !== null ? (int) post('category_id') : null,
        'title_en'      => trim((string) post('title_en')),
        'title_bn'      => trim((string) post('title_bn')),
        'slug'          => trim((string) post('slug')),
        'short_desc_en' => trim((string) post('short_desc_en')),
        'short_desc_bn' => trim((string) post('short_desc_bn')),
        'full_desc_en'  => trim((string) post('full_desc_en')),
        'full_desc_bn'  => trim((string) post('full_desc_bn')),
        'price'         => max(0, (float) post('price')),
        'price_label'   => trim((string) post('price_label')) ?: 'Starts from',
        'features_en'   => json_encode($featuresEn, JSON_UNESCAPED_UNICODE),
        'features_bn'   => json_encode($featuresBn, JSON_UNESCAPED_UNICODE),
        'thumbnail'     => post('thumbnail') !== '' ? trim((string) post('thumbnail')) : null,
        'gallery'       => json_encode($gallery, JSON_UNESCAPED_UNICODE),
        'status'        => in_array(post('status'), ['active', 'inactive', 'archived'], true) ? post('status') : 'active',
        'is_featured'   => post('is_featured') ? 1 : 0,
        'sort_order'    => max(0, (int) post('sort_order')),
    ];
}

function services_validate(array $p): ?string
{
    if (mb_strlen($p['title_en']) < 2) return 'English title is required.';
    if (mb_strlen($p['title_bn']) < 2) return 'বাংলা টাইটেল প্রয়োজন।';
    if ($p['slug'] === '') return 'Slug is required.';
    if (!preg_match('/^[a-z0-9][a-z0-9\-_]*$/', $p['slug'])) return 'Slug may only contain lowercase letters, numbers, - and _.';
    if ($p['category_id'] === null) return 'Please choose a category.';
    return null;
}