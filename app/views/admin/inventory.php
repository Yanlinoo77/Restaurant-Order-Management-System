<?php $page_title = 'Inventory Management'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px" class="animate-in">
  <div style="display:flex;gap:12px">
    <?php $critical = count(array_filter($inventory, fn($i) => $i['quantity_in_stock'] <= $i['reorder_level']/2)); ?>
    <?php $low = count(array_filter($inventory, fn($i) => $i['quantity_in_stock'] <= $i['reorder_level'] && $i['quantity_in_stock'] > $i['reorder_level']/2)); ?>
    <span style="background:rgba(231,76,60,0.1);color:var(--danger);padding:6px 14px;border-radius:20px;font-size:0.85rem;font-weight:600">🔴 Critical: <?= $critical ?></span>
    <span style="background:rgba(243,156,18,0.1);color:var(--warning);padding:6px 14px;border-radius:20px;font-size:0.85rem;font-weight:600">🟡 Low: <?= $low ?></span>
    <span style="background:rgba(39,174,96,0.1);color:var(--success);padding:6px 14px;border-radius:20px;font-size:0.85rem;font-weight:600">🟢 OK: <?= count($inventory)-$critical-$low ?></span>
  </div>
  <button class="btn btn-primary" onclick="openModal('add-inv-modal')">➕ Add Item</button>
</div>

<div class="card animate-in-delay-1">
  <div class="card-header">
    <div class="card-title">📦 Inventory Items (<?= count($inventory) ?>)</div>
    <div class="search-bar">
      <span class="search-icon">🔍</span>
      <input type="text" id="table-search" class="form-control no-icon" placeholder="Search items..." style="padding-left:36px;width:200px">
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Ingredient</th><th>Product</th><th>Stock Level</th><th>Quantity</th><th>Reorder At</th><th>Unit</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($inventory as $item):
          $pct = $item['reorder_level'] > 0 ? ($item['quantity_in_stock'] / ($item['reorder_level'] * 3)) * 100 : 100;
          $pct = min($pct, 100);
          $status = $item['quantity_in_stock'] <= $item['reorder_level']/2 ? 'critical' : ($item['quantity_in_stock'] <= $item['reorder_level'] ? 'low' : 'ok');
          $statusColors = ['ok'=>['badge-active','OK'], 'low'=>['badge-pending','Low Stock'], 'critical'=>['badge-unpaid','Critical']];
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($item['ingredient_name']) ?></strong></td>
          <td style="color:var(--text-secondary);font-size:0.85rem"><?= htmlspecialchars($item['product_name'] ?? '—') ?></td>
          <td style="min-width:140px">
            <div class="inv-progress">
              <div class="inv-progress-bar <?= $status ?>" data-pct="<?= $pct ?>" style="width:0%"></div>
            </div>
            <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:3px"><?= round($pct) ?>% of reorder x3</div>
          </td>
          <td><strong style="font-size:1.1rem"><?= number_format($item['quantity_in_stock']) ?></strong></td>
          <td><?= number_format($item['reorder_level']) ?></td>
          <td><?= htmlspecialchars($item['unit']) ?></td>
          <td><span class="badge <?= $statusColors[$status][0] ?>"><?= $statusColors[$status][1] ?></span></td>
          <td>
            <button onclick="editInventory(<?= $item['inventory_id'] ?>, '<?= addslashes($item['ingredient_name']) ?>', <?= $item['quantity_in_stock'] ?>, <?= $item['reorder_level'] ?>, '<?= $item['unit'] ?>')" class="btn btn-secondary btn-sm">✏️ Update</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add/Update Inventory Modal -->
<div class="modal-overlay" id="add-inv-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="inv-modal-title">📦 Add Inventory Item</div>
      <button class="modal-close" onclick="closeModal('add-inv-modal')">✕</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/update-inventory" method="POST">
      <input type="hidden" name="inventory_id" id="edit-inv-id">
      <div class="modal-body">
        <div class="form-field" style="margin-bottom:16px" id="inv-name-wrap">
          <label>Ingredient Name</label>
          <input type="text" name="ingredient_name" id="edit-inv-name" class="form-control no-icon">
        </div>
        <div class="form-row cols-2">
          <div class="form-field">
            <label>Quantity in Stock</label>
            <input type="number" name="quantity_in_stock" id="edit-inv-qty" class="form-control no-icon" min="0" required>
          </div>
          <div class="form-field">
            <label>Reorder Level</label>
            <input type="number" name="reorder_level" id="edit-inv-level" class="form-control no-icon" min="0" required>
          </div>
        </div>
        <div class="form-field" id="inv-unit-wrap">
          <label>Unit</label>
          <select name="unit" id="edit-inv-unit" class="form-control no-icon">
            <option>pcs</option><option>g</option><option>kg</option><option>ml</option><option>l</option><option>bottles</option><option>boxes</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('add-inv-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function editInventory(id, name, qty, level, unit) {
  document.getElementById('inv-modal-title').textContent = '✏️ Update Stock: ' + name;
  document.getElementById('edit-inv-id').value = id;
  document.getElementById('edit-inv-name').value = name;
  document.getElementById('edit-inv-qty').value = qty;
  document.getElementById('edit-inv-level').value = level;
  document.getElementById('edit-inv-unit').value = unit;
  document.getElementById('inv-name-wrap').style.display = 'none';
  document.getElementById('inv-unit-wrap').style.display = 'none';
  openModal('add-inv-modal');
}
</script>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- 🔗 Recipe / Auto-Deduct Linking Panel                                  -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="card animate-in-delay-2" style="margin-top:24px;border:2px solid rgba(52,152,219,0.25)">
  <div class="card-header" style="cursor:pointer;background:rgba(52,152,219,0.06)" onclick="toggleRecipePanel()">
    <div style="display:flex;align-items:center;gap:10px">
      <span style="font-size:1.2rem">🔗</span>
      <div class="card-title" style="margin:0">Recipe Links <span style="font-size:0.8rem;font-weight:400;color:var(--text-secondary)">&nbsp;— connects menu items to ingredients for auto-deduct</span></div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <span style="background:rgba(52,152,219,0.15);color:#3498db;padding:3px 12px;border-radius:12px;font-size:0.8rem;font-weight:600"><?= count($recipes) ?> links</span>
      <span id="recipe-toggle-icon" style="font-size:0.85rem;color:var(--text-secondary)">▼ Show</span>
    </div>
  </div>
  <div id="recipe-panel" style="display:none">

    <!-- Existing recipe links table -->
    <?php if (!empty($recipes)):
      // Group by product for cleaner display
      $recipesByProduct = [];
      foreach ($recipes as $r) {
          $recipesByProduct[$r['product_name']][] = $r;
      }
    ?>
    <div class="table-wrap" style="max-height:320px;overflow-y:auto">
      <table>
        <thead><tr>
          <th>Menu Item</th>
          <th>Ingredient</th>
          <th style="text-align:right">Qty Used / order</th>
          <th>Unit</th>
          <th style="text-align:center">Action</th>
        </tr></thead>
        <tbody>
          <?php foreach ($recipesByProduct as $pname => $links): ?>
          <?php foreach ($links as $i => $r): ?>
          <tr>
            <?php if ($i === 0): ?>
            <td rowspan="<?= count($links) ?>" style="vertical-align:top;padding-top:14px;font-weight:600;border-right:2px solid var(--border)">
              <?= htmlspecialchars($pname) ?>
            </td>
            <?php endif; ?>
            <td>📦 <?= htmlspecialchars($r['ingredient_name']) ?></td>
            <td style="text-align:right;font-weight:700;color:#3498db;font-size:1.05rem"><?= number_format($r['quantity_used'], 1) ?></td>
            <td style="color:var(--text-secondary)"><?= htmlspecialchars($r['unit']) ?></td>
            <td style="text-align:center">
              <form method="POST" action="<?= BASE_URL ?>/admin/delete-recipe/<?= $r['recipe_id'] ?>" onsubmit="return confirm('Remove this link?')" style="display:inline">
                <button type="submit" class="btn btn-sm" style="background:rgba(231,76,60,0.1);color:var(--danger);border:none;cursor:pointer;padding:4px 10px;border-radius:6px">✕ Remove</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div style="padding:24px 20px;color:var(--text-secondary);font-size:0.9rem">
      ⚠️ No recipe links yet. Add one below to enable auto-deduct when orders are placed.
    </div>
    <?php endif; ?>

    <!-- Add new recipe link form -->
    <div style="padding:20px;border-top:1px solid var(--border);background:var(--bg)">
      <div style="font-weight:600;margin-bottom:14px;font-size:0.9rem">➕ Add New Link</div>
      <form method="POST" action="<?= BASE_URL ?>/admin/save-recipe" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
        <div style="flex:2;min-width:180px">
          <label style="font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px">Menu Item (Product)</label>
          <select name="product_id" class="form-control no-icon" required style="width:100%">
            <option value="">— Select menu item —</option>
            <?php foreach ($products as $p): ?>
            <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex:2;min-width:160px">
          <label style="font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px">Ingredient</label>
          <select name="inventory_id" class="form-control no-icon" required style="width:100%">
            <option value="">— Select ingredient —</option>
            <?php foreach ($inventory as $inv): ?>
            <option value="<?= $inv['inventory_id'] ?>"><?= htmlspecialchars($inv['ingredient_name']) ?> (<?= number_format($inv['quantity_in_stock']) ?> <?= $inv['unit'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex:0 0 110px">
          <label style="font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px">Qty per dish</label>
          <input type="number" name="quantity_used" class="form-control no-icon" min="0.001" step="0.001" value="1" required style="width:100%">
        </div>
        <div style="flex:0 0 100px">
          <label style="font-size:0.8rem;color:var(--text-secondary);display:block;margin-bottom:4px">Unit</label>
          <select name="unit" class="form-control no-icon" style="width:100%">
            <option>pcs</option><option>g</option><option>kg</option><option>ml</option><option>l</option><option>bottles</option><option>boxes</option>
          </select>
        </div>
        <div style="flex:0 0 auto">
          <button type="submit" class="btn btn-primary">🔗 Link</button>
        </div>
      </form>
      <div style="margin-top:10px;font-size:0.78rem;color:var(--text-secondary)">
        💡 Example: "Coq au Vin" → "Chicken Breast" → Qty: 1 pcs &nbsp;|&nbsp; Each time this dish is ordered, 1 pcs of Chicken Breast will be auto-deducted from stock.
      </div>
    </div>
  </div>
</div>

<script>
function toggleRecipePanel() {
  var p = document.getElementById('recipe-panel');
  var i = document.getElementById('recipe-toggle-icon');
  var open = p.style.display !== 'none';
  p.style.display = open ? 'none' : 'block';
  i.textContent   = open ? '▼ Show' : '▲ Hide';
}
</script>

<!-- Daily Inventory History -->
<div class="card animate-in-delay-2" style="margin-top:24px">
  <div class="card-header" style="cursor:pointer" onclick="toggleHistoryPanel()">
    <div class="card-title">📅 Daily Inventory History</div>
    <div style="display:flex;align-items:center;gap:12px">
      <span style="font-size:0.85rem;color:var(--text-secondary)"><?= count($history) ?> recent changes</span>
      <span id="history-toggle-icon" style="font-size:0.85rem;color:var(--text-secondary)">▼ Show</span>
    </div>
  </div>
  <div id="history-panel" style="display:none">
    <?php if (empty($history)): ?>
    <div style="padding:48px;text-align:center;color:var(--text-secondary)">
      <div style="font-size:2.5rem;margin-bottom:12px">📋</div>
      <div style="font-weight:600;margin-bottom:6px">No history yet</div>
      <div style="font-size:0.85rem">Inventory changes will be tracked here after each update.</div>
    </div>
    <?php else: ?>
    <?php
    $historyByDate = [];
    foreach ($history as $h) {
        $historyByDate[$h['change_date']][] = $h;
    }
    ?>
    <?php foreach ($historyByDate as $date => $changes): ?>
    <div style="border-bottom:1px solid var(--border)">
      <div style="padding:12px 20px;background:var(--bg);display:flex;align-items:center;gap:10px">
        <span style="font-weight:700;font-size:0.9rem">📅 <?= date('F j, Y', strtotime($date)) ?></span>
        <?php if ($date === date('Y-m-d')): ?>
        <span style="background:rgba(39,174,96,0.12);color:var(--success);padding:2px 10px;border-radius:10px;font-size:0.78rem;font-weight:600">Today</span>
        <?php endif; ?>
        <span style="color:var(--text-secondary);font-size:0.82rem"><?= count($changes) ?> change<?= count($changes) !== 1 ? 's' : '' ?></span>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>Ingredient</th>
            <th>Type</th>
            <th>What Changed</th>
            <th>Net &#916;</th>
            <th>Time</th>
            <th>Note</th>
          </tr></thead>
          <tbody>
            <?php foreach ($changes as $h):
              $changeType   = $h['change_type']   ?? 'manual';
              $changedField = $h['changed_field']  ?? 'quantity_in_stock';
              $qBefore = (int)$h['quantity_before'];
              $qAfter  = (int)$h['quantity_after'];
              $rBefore = (isset($h['reorder_before']) && $h['reorder_before'] !== null) ? (int)$h['reorder_before'] : null;
              $rAfter  = (isset($h['reorder_after'])  && $h['reorder_after']  !== null) ? (int)$h['reorder_after']  : null;
              $diff    = $qAfter - $qBefore;

              $typeCfg = [
                'manual'      => ['&#9998; Manual',      'rgba(52,152,219,0.15)', '#3498db'],
                'auto_deduct' => ['&#127860; Auto-Deduct','rgba(231,76,60,0.12)',  '#e74c3c'],
                'initial'     => ['&#10024; Initial Stock','rgba(39,174,96,0.12)', '#27ae60'],
              ];
              [$typeLabel, $typeBg, $typeColor] = $typeCfg[$changeType] ?? $typeCfg['manual'];
            ?>
            <tr>
              <td><strong><?= htmlspecialchars($h['ingredient_name']) ?></strong></td>

              <!-- Type badge -->
              <td>
                <span style="background:<?= $typeBg ?>;color:<?= $typeColor ?>;padding:3px 10px;border-radius:12px;font-size:0.78rem;font-weight:600;white-space:nowrap">
                  <?= $typeLabel ?>
                </span>
              </td>

              <!-- What Changed -->
              <td style="min-width:220px">
                <?php if ($changedField === 'reorder_level'): ?>
                  <div style="display:flex;align-items:center;gap:6px;font-size:0.85rem">
                    <span style="color:var(--warning);font-weight:600">&#9888; Reorder Level</span>
                    <span style="color:var(--text-secondary)"><?= number_format($rBefore ?? $qBefore) ?></span>
                    <span>&#8594;</span>
                    <span style="font-weight:700;color:<?= ($rAfter ?? $qAfter) > ($rBefore ?? $qBefore) ? 'var(--warning)' : 'var(--success)' ?>">
                      <?= number_format($rAfter ?? $qAfter) ?>
                    </span>
                  </div>

                <?php elseif ($changedField === 'both'): ?>
                  <div style="font-size:0.83rem;line-height:1.9">
                    <div style="display:flex;align-items:center;gap:5px">
                      <span style="color:var(--text-secondary);width:62px">&#128230; Stock</span>
                      <span style="color:var(--text-secondary)"><?= number_format($qBefore) ?></span>
                      <span>&#8594;</span>
                      <span style="font-weight:700;color:<?= $qAfter >= $qBefore ? 'var(--success)' : 'var(--danger)' ?>"><?= number_format($qAfter) ?></span>
                    </div>
                    <?php if ($rBefore !== null): ?>
                    <div style="display:flex;align-items:center;gap:5px">
                      <span style="color:var(--text-secondary);width:62px">&#9888; Reorder</span>
                      <span style="color:var(--text-secondary)"><?= number_format($rBefore) ?></span>
                      <span>&#8594;</span>
                      <span style="font-weight:700;color:<?= $rAfter > $rBefore ? 'var(--warning)' : 'var(--success)' ?>"><?= number_format($rAfter) ?></span>
                    </div>
                    <?php endif; ?>
                  </div>

                <?php else: ?>
                  <div style="display:flex;align-items:center;gap:6px;font-size:0.85rem">
                    <span style="color:var(--text-secondary);font-weight:500">&#128230; Stock</span>
                    <span style="color:var(--text-secondary)"><?= number_format($qBefore) ?></span>
                    <span>&#8594;</span>
                    <span style="font-weight:700;color:<?= $qAfter >= $qBefore ? 'var(--success)' : 'var(--danger)' ?>">
                      <?= number_format($qAfter) ?>
                    </span>
                  </div>
                <?php endif; ?>
              </td>

              <!-- Net delta -->
              <td>
                <?php if ($changedField !== 'reorder_level'): ?>
                <span style="font-weight:700;font-size:1rem;color:<?= $diff >= 0 ? 'var(--success)' : 'var(--danger)' ?>">
                  <?= $diff >= 0 ? '+' : '' ?><?= number_format($diff) ?>
                </span>
                <?php else: ?>
                <span style="color:var(--text-secondary);font-size:0.82rem">&#8212;</span>
                <?php endif; ?>
              </td>

              <td style="font-size:0.82rem;color:var(--text-secondary);white-space:nowrap"><?= date('g:ia', strtotime($h['changed_at'])) ?></td>
              <td style="font-size:0.82rem;color:var(--text-secondary);max-width:200px"><?= htmlspecialchars($h['change_note'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleHistoryPanel() {
  var panel = document.getElementById('history-panel');
  var icon  = document.getElementById('history-toggle-icon');
  var open  = panel.style.display !== 'none';
  panel.style.display = open ? 'none' : 'block';
  icon.textContent    = open ? '▼ Show' : '▲ Hide';
}
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>

