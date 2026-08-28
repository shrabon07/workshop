<?php
/** Admin — Add / Edit service (all bilingual fields, dynamic features, uploads). */
require_once __DIR__ . '/../config.php';
require_admin();

$svc = null;
$id  = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($id) {
    $svc = DB::get('SELECT * FROM services WHERE id = ?', [$id]) ?: null;
}
$categories = DB::all('SELECT * FROM service_categories ORDER BY sort_order, id');

function fld($svc, $k, $def = '') { return $svc ? ($svc[$k] ?? $def) : $def; }

$featEn = $svc ? (json_decode((string) $svc['features_en'], true) ?: []) : [];
$featBn = $svc ? (json_decode((string) $svc['features_bn'], true) ?: []) : [];
$gallery = $svc ? (json_decode((string) $svc['gallery'], true) ?: []) : [];

$isEdit = (bool) $svc;
$PAGE_TITLE = $isEdit ? 'Edit Service' : 'Add Service';
$ACTIVE = 'services';
$LOAD_ADMIN_SERVICES = true;
require_once __DIR__ . '/inc/head.php';

$labels = ['Starts from', 'Fixed', 'Custom Quote'];
?>
<div class="grid gap-6 xl:grid-cols-[1fr_320px]">

  <form id="service-form" data-id="<?= $isEdit ? (int) $svc['id'] : '' ?>" class="glass-strong rounded-3xl p-6 sm:p-8 space-y-8" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" id="fv-slug-auto" value="<?= $isEdit ? 0 : 1 ?>">
    <input type="hidden" id="fv-thumbnail" value="<?= e(fld($svc, 'thumbnail')) ?>">
    <input type="hidden" id="fv-gallery" value="<?= e(implode(',', $gallery)) ?>">

    <!-- ===== identity ===== -->
    <div>
      <h2 class="panel-h"><?= l('Service identity', 'সার্ভিস পরিচয়') ?></h2>
      <div class="grid gap-5 md:grid-cols-2">
        <div>
          <label class="label"><?= l('Title (English)', 'টাইটেল (ইংরেজি)') ?> *</label>
          <input id="fv-title-en" class="input" required value="<?= e(fld($svc, 'title_en')) ?>">
        </div>
        <div>
          <label class="label"><?= l('Title (বাংলা)', 'টাইটেল (বাংলা)') ?> *</label>
          <input id="fv-title-bn" class="input" required value="<?= e(fld($svc, 'title_bn')) ?>">
        </div>
        <div>
          <label class="label"><?= l('Slug (auto from title)', 'স্লাগ (টাইটেল থেকে অটো)') ?> *</label>
          <input id="fv-slug" class="input" required pattern="[a-z0-9\-_]+" value="<?= e(fld($svc, 'slug')) ?>">
        </div>
        <div>
          <label class="label"><?= l('Category', 'ক্যাটাগরি') ?> *</label>
          <select id="fv-category" class="input" required>
            <option value="">—</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= fld($svc, 'category_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name_en']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- ===== descriptions ===== -->
    <div>
      <h2 class="panel-h"><?= l('Descriptions', 'বর্ণনা') ?></h2>
      <div class="grid gap-5 md:grid-cols-2">
        <div>
          <label class="label"><?= l('Short (English)', 'সংক্ষিপ্ত (ইংরেজি)') ?></label>
          <textarea id="fv-short-en" class="input" rows="3"><?= e(fld($svc, 'short_desc_en')) ?></textarea>
        </div>
        <div>
          <label class="label"><?= l('Short (বাংলা)', 'সংক্ষিপ্ত (বাংলা)') ?></label>
          <textarea id="fv-short-bn" class="input" rows="3"><?= e(fld($svc, 'short_desc_bn')) ?></textarea>
        </div>
        <div>
          <label class="label"><?= l('Full details (English)', 'বিস্তারিত (ইংরেজি)') ?></label>
          <textarea id="fv-full-en" class="input" rows="6"><?= e(fld($svc, 'full_desc_en')) ?></textarea>
        </div>
        <div>
          <label class="label"><?= l('Full details (বাংলা)', 'বিস্তারিত (বাংলা)') ?></label>
          <textarea id="fv-full-bn" class="input" rows="6"><?= e(fld($svc, 'full_desc_bn')) ?></textarea>
        </div>
      </div>
    </div>

    <!-- ===== pricing ===== -->
    <div>
      <h2 class="panel-h"><?= l('Pricing', 'মূল্য') ?></h2>
      <div class="grid gap-5 md:grid-cols-3">
        <div>
          <label class="label"><?= l('Starting price (BDT)', 'প্রারম্ভিক মূল্য (টাকা)') ?></label>
          <input id="fv-price" class="input" type="number" min="0" step="500" required value="<?= e(fld($svc, 'price', 0)) ?>">
        </div>
        <div>
          <label class="label"><?= l('Price label', 'মূল্য লেবেল') ?></label>
          <select id="fv-price-label" class="input">
            <?php foreach ($labels as $lb): ?>
              <option value="<?= e($lb) ?>" <?= fld($svc, 'price_label', 'Starts from') === $lb ? 'selected' : '' ?>><?= e($lb) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label"><?= l('Sort order', 'অর্ডার') ?></label>
          <input id="fv-sort" class="input" type="number" min="0" value="<?= e(fld($svc, 'sort_order', 0)) ?>">
        </div>
      </div>
    </div>

    <!-- ===== features ===== -->
    <div>
      <h2 class="panel-h flex items-center justify-between">
        <?= l('Features (bullet points)', 'ফিচার (বুলেট পয়েন্ট)') ?>
        <button type="button" id="fv-feat-add" class="btn-accent !py-2 !px-4 text-xs">+ <?= l('Add', 'যুক্ত করুন') ?></button>
      </h2>
      <div id="fv-features" class="space-y-2.5">
        <?php foreach ($featEn as $i => $fenn): ?>
          <div class="feat-row">
            <input class="input !py-2.5 feat-en" placeholder="English feature" value="<?= e($fenn) ?>">
            <input class="input !py-2.5 feat-bn" placeholder="বাংলা ফিচার" value="<?= e($featBn[$i] ?? '') ?>">
            <button type="button" class="feat-del glass-chip rounded-xl px-3 text-rose-300 hover:bg-rose-500/10 transition-colors" aria-label="Remove">✕</button>
          </div>
        <?php endforeach; ?>
        <?php if (!$featEn): ?>
          <div class="feat-row">
            <input class="input !py-2.5 feat-en" placeholder="English feature">
            <input class="input !py-2.5 feat-bn" placeholder="বাংলা ফিচার">
            <button type="button" class="feat-del glass-chip rounded-xl px-3 text-rose-300 hover:bg-rose-500/10 transition-colors" aria-label="Remove">✕</button>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ===== media ===== -->
    <div class="grid gap-6 md:grid-cols-2">
      <div>
        <h2 class="panel-h"><?= l('Thumbnail', 'থাম্বনেইল') ?></h2>
        <div class="mt-3">
          <?php if ($svc && $svc['thumbnail']): ?>
            <img id="fv-thumb-preview" src="<?= e(uploads_url($svc['thumbnail'])) ?>" class="thumb-preview w-full h-44 object-cover">
          <?php else: ?>
            <img id="fv-thumb-preview" src="#" class="thumb-preview w-full h-44 object-cover hidden" alt="">
          <?php endif; ?>
          <label class="btn-ghost w-full mt-3 !py-3 cursor-pointer text-xs">
            ⬆ <?= l('Upload thumbnail (max 4MB)', 'থাম্বনেইল আপলোড (সর্বোচ্চ ৪এমবি)') ?>
            <input id="fv-thumb-file" type="file" accept="image/*" class="hidden">
          </label>
        </div>
      </div>
      <div>
        <h2 class="panel-h"><?= l('Gallery (optional)', 'গ্যালারি (ঐচ্ছিক)') ?></h2>
        <div id="fv-gallery-thumbs" class="mt-3 flex flex-wrap gap-2">
          <?php foreach ($gallery as $gi): ?>
            <img src="<?= e(uploads_url($gi)) ?>" class="w-20 h-14 object-cover rounded-lg border border-white/10" alt="">
          <?php endforeach; ?>
        </div>
        <label class="btn-ghost w-full mt-3 !py-3 cursor-pointer text-xs">
          ⬆ <?= l('Upload gallery (multi)', 'গ্যালারি আপলোড (একাধিক)') ?>
          <input id="fv-gallery-file" type="file" accept="image/*" multiple class="hidden">
        </label>
      </div>
    </div>

    <!-- ===== status & flags ===== -->
    <div class="grid gap-6 md:grid-cols-2">
      <div>
        <h2 class="panel-h"><?= l('Status', 'অবস্থা') ?></h2>
        <div class="grid gap-3 sm:grid-cols-3 mt-3">
          <?php foreach (['active', 'inactive'] as $st): ?>
            <label class="glass-chip rounded-2xl px-4 py-3 text-xs font-bold text-slate-200 cursor-pointer flex items-center gap-2">
              <input type="radio" name="status-vis" class="accent-cyan-400" value="<?= $st ?>" <?= fld($svc, 'status', 'active') === $st ? 'checked' : '' ?>>
              <?= $st === 'active' ? l('Active', 'সক্রিয়') : l('Inactive', 'নিষ্ক্রিয়') ?>
            </label>
          <?php endforeach; ?>
          <input type="hidden" id="fv-status" value="<?= e(fld($svc, 'status', 'active')) ?>">
        </div>
        <label class="glass-chip rounded-2xl px-4 py-3 text-xs font-bold text-slate-200 cursor-pointer flex items-center gap-2 mt-3">
          <input id="fv-featured" type="checkbox" class="accent-amber-400" <?= (int) fld($svc, 'is_featured', 0) ? 'checked' : '' ?>>
          ★ <?= l('Featured / Popular badge', 'ফিচারড ব্যাজ') ?>
        </label>
      </div>
      <div class="flex items-end justify-end gap-3">
        <a href="services.php" class="btn-ghost !py-3 !px-6"><?= l('Cancel', 'বাতিল') ?></a>
        <button type="submit" class="btn-teal !py-3 !px-8"><?= l('Save service', 'সার্ভিস সংরক্ষণ') ?></button>
      </div>
    </div>
  </form>

  <!-- sticky preview note -->
  <aside class="space-y-5">
    <div class="glass rounded-3xl p-6 grad-border">
      <h3 class="font-bold text-white text-sm mb-3"><?= l('Quick preview', 'দ্রুত প্রিভিউ') ?></h3>
      <p class="text-xs text-slate-400 leading-relaxed"><?= l('The thumbnail and featured badge render on the public Services grid. Inactive services stay saved but hidden. Archived services disappear from the list until restored.', 'থাম্বনেইল ও ফিচারড ব্যাজ পাবলিক সার্ভিস গ্রিডে দেখাবে। নিষ্ক্রিয় সার্ভিস সংরক্ষিত থাকে কিন্তু লুকানো। ডিলিট হলে আর্কাইভ হয় এবং পরে ফিরিয়ে আনা যায়।') ?></p>
      <div class="mt-4 glass-chip rounded-2xl p-4" id="live-slug-hint">
        <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1"><?= l('Public URL', 'পাবলিক ইউআরএল') ?></div>
        <code class="text-xs text-cyan-300 break-all"><?= e(url('')) ?>/#services</code>
      </div>
    </div>
    <div class="glass rounded-3xl p-6 grad-border">
      <h3 class="font-bold text-white text-sm mb-3"><?= l('Safe delete', 'নিরাপদ ডিলিট') ?></h3>
      <p class="text-xs text-slate-400 leading-relaxed"><?= l('Deleting only archives — orders linked to this service are never removed.', 'ডিলিট করলে শুধু আর্কাইভ হয় — এই সার্ভিসের সঙ্গে থাকা অর্ডার কখনো মুছে যায় না।') ?></p>
    </div>
  </aside>
</div>
<?php
require_once __DIR__ . '/inc/foot.php';
?>