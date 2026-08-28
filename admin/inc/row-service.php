<?php
/* Service table rows — used for the initial server render (expects $services). */
foreach ($services as $f) {
    $st = $f['status'];
    $stBadge = $st === 'active' ? 'st-active' : ($st === 'inactive' ? 'st-inactive' : 'st-archived');
    $stLabel = $st === 'active' ? ['Active', 'সক্রিয়'] : ($st === 'inactive' ? ['Inactive', 'নিষ্ক্রিয়'] : ['Archived', 'আর্কাইভড']);
    ?>
    <tr data-service-id="<?= (int) $f['id'] ?>">
      <td>
        <div class="flex items-center gap-3">
          <span class="w-14 h-10 rounded-lg overflow-hidden border border-white/10 bg-white/5 grid place-items-center shrink-0">
            <?php if (!empty($f['thumbnail'])): ?>
              <img src="<?= e(uploads_url($f['thumbnail'])) ?>" alt="" class="w-full h-full object-cover" loading="lazy">
            <?php else: ?>
              <span class="text-sm">▤</span>
            <?php endif; ?>
          </span>
          <div class="min-w-0">
            <div class="font-bold text-slate-100 text-sm truncate max-w-[220px]"><?= e($f['title_en']) ?></div>
            <div class="text-xs text-slate-500 truncate max-w-[220px]"><?= e($f['title_bn']) ?> · <code><?= e($f['slug']) ?></code></div>
          </div>
        </div>
      </td>
      <td><span class="text-slate-300 text-xs font-semibold"><?= e($f['cat_en'] ?: '—') ?></span></td>
      <td>
        <span class="font-extrabold text-slate-100">৳ <?= e(number_format((float) $f['price'])) ?></span>
        <div class="text-[11px] text-slate-500"><?= e($f['price_label']) ?></div>
      </td>
      <td><span class="st-badge <?= e($stBadge) ?>"><?= $st === 'active' ? '●' : ($st === 'archived' ? '✕' : '○') ?> <?= l($stLabel[0], $stLabel[1]) ?></span></td>
      <td><span class="<?= (int) $f['is_featured'] ? 'text-amber-300' : 'text-slate-700' ?> text-sm">★</span></td>
      <td>
        <div class="flex items-center justify-end gap-2">
          <?php if ($st === 'archived'): ?>
            <button type="button" class="restore-btn glass-chip rounded-lg px-3 py-1.5 text-xs font-bold text-emerald-300 hover:bg-emerald-400/10 transition-colors" data-id="<?= (int) $f['id'] ?>"><?= l('Restore', 'পুনরুদ্ধার') ?></button>
          <?php else: ?>
            <a href="service-form.php?id=<?= (int) $f['id'] ?>" class="glass-chip rounded-lg px-3 py-1.5 text-xs font-bold text-slate-200 hover:text-cyan-300 transition-colors"><?= l('Edit', 'সম্পাদনা') ?></a>
            <button type="button" class="toggle-btn glass-chip rounded-lg px-3 py-1.5 text-xs font-bold <?= $st === 'active' ? 'text-slate-400 hover:text-amber-300' : 'text-emerald-300 hover:bg-emerald-400/10' ?>" data-id="<?= (int) $f['id'] ?>" data-status="<?= e($st) ?>">
              <?= $st === 'active' ? l('Deactivate', 'নিষ্ক্রিয়') : l('Activate', 'সক্রিয়') ?>
            </button>
            <button type="button" class="delete-btn rounded-lg px-3 py-1.5 text-xs font-bold text-rose-300 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 transition-colors" data-id="<?= (int) $f['id'] ?>" data-title="<?= e($f['title_en']) ?>"><?= l('Delete', 'মুছুন') ?></button>
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php
}