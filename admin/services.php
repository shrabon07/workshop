<?php
/** Admin — Services management (list + search + filter + soft delete). */
require_once __DIR__ . '/../config.php';
require_admin();

$categories = DB::all('SELECT * FROM service_categories ORDER BY sort_order, id');
// initial (default) list = everything not archived
$services = DB::all(
    'SELECT s.*, c.name_en AS cat_en, c.name_bn AS cat_bn
       FROM services s
       LEFT JOIN service_categories c ON c.id = s.category_id
      WHERE s.status != "archived"
      ORDER BY s.sort_order ASC, s.id DESC'
);

$PAGE_TITLE = 'Services';
$ACTIVE = 'services';
$LOAD_ADMIN_SERVICES = true;
require_once __DIR__ . '/inc/head.php';
?>
<div class="glass-strong rounded-3xl p-6">
  <!-- toolbar -->
  <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
    <div class="flex flex-wrap items-center gap-2" id="status-tabs">
      <?php
      $tabs = [
        ['all'      , 'All', 'সব'],
        ['active'   , 'Active', 'সক্রিয়'],
        ['inactive' , 'Inactive', 'নিষ্ক্রিয়'],
        ['archived' , 'Archived', 'আর্কাইভড'],
      ];
      foreach ($tabs as $i => $t):
          $classes = 'st-tab rounded-xl px-3.5 py-2 text-xs font-bold transition-all' . ($i === 0 ? ' st-tab-on' : ' text-slate-400 hover:text-white');
      ?>
        <button type="button" class="<?= $classes ?>" data-status="<?= e($t[0]) ?>"><?= l($t[1], $t[2]) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
      <input id="sv-search" class="input !py-2.5 lg:w-64" placeholder="Search title / slug…">
      <select id="sv-cat" class="input !py-2.5 lg:w-52">
        <option value=""><?= l('All categories', 'সব ক্যাটাগরি') ?></option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int) $c['id'] ?>"><?= e($c['name_en']) ?></option>
        <?php endforeach; ?>
      </select>
      <a href="service-form.php" class="btn-teal !py-2.5 !px-5 text-xs shrink-0"><span class="e">+ Add Service</span><span class="b">+ সার্ভিস যোগ করুন</span></a>
    </div>
  </div>

  <!-- table -->
  <div class="mt-5 overflow-x-auto nice-scroll rounded-2xl border border-white/10">
    <table class="admin-table min-w-[820px]">
      <thead>
        <tr>
          <th><span class="e">Service</span><span class="b">সার্ভিস</span></th>
          <th><span class="e">Category</span><span class="b">ক্যাটাগরি</span></th>
          <th><span class="e">Price</span><span class="b">মূল্য</span></th>
          <th><span class="e">Status</span><span class="b">অবস্থা</span></th>
          <th><span class="e">Featured</span><span class="b">ফিচারড</span></th>
          <th class="text-right"><span class="e">Actions</span><span class="b">অ্যাকশন</span></th>
        </tr>
      </thead>
      <tbody id="sv-tbody">
        <?php require __DIR__ . '/inc/row-service.php'; ?>
      </tbody>
    </table>
    <div id="sv-empty" class="hidden text-center py-12 text-slate-500 text-sm"><?= l('No services found.', 'কোনো সার্ভিস পাওয়া যায়নি।') ?></div>
  </div>

  <!-- delete confirmation modal (hidden) -->
  <div id="delete-modal" class="hidden fixed inset-0 z-[90] bg-slate-950/80 backdrop-blur-sm grid place-items-center p-4">
    <div class="glass-strong rounded-3xl p-7 w-full max-w-md neon-glow grad-border fade-swap">
      <div class="flex items-center gap-3">
        <span class="w-11 h-11 rounded-2xl grid place-items-center bg-rose-500/15 text-rose-300 text-xl">🗑</span>
        <div>
          <h3 class="font-bold text-white text-lg"><?= l('Delete this service?', 'এই সার্ভিসটি মুছে ফেলবেন?') ?></h3>
          <p class="text-xs text-slate-400 mt-0.5"><?= l('It will be hidden from the public site. You can restore it later from the Archived tab.', 'এটি পাবলিক সাইট থেকে লুকানো হবে। পরে আর্কাইভড ট্যাব থেকে ফিরিয়ে আনা যাবে।') ?></p>
        </div>
      </div>
      <p id="delete-target" class="mt-4 glass-chip rounded-xl px-4 py-3 text-sm font-bold text-slate-200"></p>
      <div class="mt-6 flex justify-end gap-3">
        <button id="delete-cancel" class="btn-ghost !py-2.5 !px-5 text-xs"><?= l('Cancel', 'বাতিল') ?></button>
        <button id="delete-confirm" class="btn !py-2.5 !px-5 text-xs text-white bg-gradient-to-r from-rose-600 to-red-500 hover:shadow-[0_8px_30px_-10px_rgba(244,63,94,.6)]">
          <?= l('Yes, archive it', 'হ্যাঁ, আর্কাইভ করুন') ?>
        </button>
      </div>
    </div>
  </div>
</div>
<?php
require_once __DIR__ . '/inc/foot.php';
?>