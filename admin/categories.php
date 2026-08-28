<?php
/** Admin — Service categories management (add / edit / delete w/ reassignment guard). */
require_once __DIR__ . '/../config.php';
require_admin();

$cats = DB::all(
    'SELECT c.*, (SELECT COUNT(*) FROM services s WHERE s.category_id = c.id AND s.status != "archived") AS live_count
       FROM service_categories c
       ORDER BY c.sort_order ASC, c.id ASC'
);

$PAGE_TITLE = 'Service Categories';
$ACTIVE = 'categories';
$LOAD_ADMIN_SERVICES = true;
require_once __DIR__ . '/inc/head.php';
?>
<div id="cat-wrap" class="glass-strong rounded-3xl p-6">
  <div class="flex items-center justify-between gap-4 flex-wrap">
    <p class="text-sm text-slate-400"><?= l('Categories control the filter tabs on the public site.', 'ক্যাটাগরিগুলো পাবলিক সাইটের ফিল্টার ট্যাব নিয়ন্ত্রণ করে।') ?></p>
    <button type="button" data-cat-add class="btn-teal !py-2.5 !px-5 text-xs"><span class="e">+ Add category</span><span class="b">+ ক্যাটাগরি যোগ</span></button>
  </div>

  <div class="mt-5 overflow-x-auto nice-scroll rounded-2xl border border-white/10">
    <table class="admin-table min-w-[620px]">
      <thead>
        <tr>
          <th><span class="e">Category</span><span class="b">ক্যাটাগরি</span></th>
          <th><span class="e">Slug</span></th>
          <th><span class="e">Sort</span><span class="b">ক্রম</span></th>
          <th><span class="e">Live services</span><span class="b">সক্রিয় সার্ভিস</span></th>
          <th class="text-right"><span class="e">Actions</span><span class="b">অ্যাকশন</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cats as $c): ?>
        <tr>
          <td>
            <span class="font-bold text-slate-100 text-sm"><?= e($c['name_en']) ?></span>
            <div class="text-xs text-slate-500"><?= e($c['name_bn']) ?></div>
          </td>
          <td><code class="text-xs text-cyan-300/80"><?= e($c['slug']) ?></code></td>
          <td class="text-slate-300"><?= (int) $c['sort_order'] ?></td>
          <td><span class="badge <?= (int) $c['live_count'] ? 'text-emerald-950 bg-emerald-400/90' : 'text-slate-400 glass-chip' ?>"><?= (int) $c['live_count'] ?></span></td>
          <td>
            <div class="flex items-center justify-end gap-2">
              <button type="button" data-cat-edit data-id="<?= (int) $c['id'] ?>"
                      data-en="<?= e($c['name_en']) ?>" data-bn="<?= e($c['name_bn']) ?>"
                      data-slug="<?= e($c['slug']) ?>" data-sort="<?= (int) $c['sort_order'] ?>"
                      class="glass-chip rounded-lg px-3 py-1.5 text-xs font-bold text-slate-200 hover:text-cyan-300 transition-colors">
                <?= l('Edit', 'সম্পাদনা') ?>
              </button>
              <button type="button" data-cat-del data-id="<?= (int) $c['id'] ?>"
                      class="rounded-lg px-3 py-1.5 text-xs font-bold text-rose-300 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 transition-colors"
                      <?= (int) $c['live_count'] ? 'data-blocked="1" title="Reassign services first"' : '' ?>>
                <?= l('Delete', 'মুছুন') ?>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
require_once __DIR__ . '/inc/foot.php';
?>