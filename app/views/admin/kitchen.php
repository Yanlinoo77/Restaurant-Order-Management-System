<?php $page_title = 'Kitchen Monitor'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px" class="animate-in">
  <div>
    <p style="color:var(--text-secondary)">Live kitchen monitor — Read-only view. Kitchen staff manage their own orders.</p>
  </div>
  <button onclick="location.reload()" class="btn btn-secondary" id="refresh-btn">🔄 Refresh</button>
</div>

<!-- Info banner -->
<div style="background:rgba(52,152,219,0.08);border:1px solid rgba(52,152,219,0.2);border-radius:var(--radius);padding:12px 18px;margin-bottom:24px;display:flex;align-items:center;gap:10px;font-size:0.85rem;color:var(--info)" class="animate-in">
  ℹ️ <strong>Admin View Only:</strong> This is a read-only overview. Kitchen staff update order statuses from their own Kitchen Display screen.
</div>

<div class="kitchen-board animate-in-delay-1">
  <!-- Pending -->
  <div class="kitchen-col">
    <div class="kitchen-column-header pending">
      ⏳ Pending Orders
      <?php $pending = array_filter($orders, fn($o) => $o['order_status'] === 'pending'); ?>
      <span style="background:rgba(255,255,255,0.3);padding:3px 10px;border-radius:20px"><?= count($pending) ?></span>
    </div>
    <div class="kitchen-cards">
      <?php if (empty($pending)): ?>
      <div style="text-align:center;padding:30px;color:var(--text-secondary)"><div style="font-size:2rem;margin-bottom:8px">✅</div>No pending orders</div>
      <?php else: ?>
      <?php foreach ($pending as $o): ?>
      <div class="kitchen-card pending">
        <div class="kitchen-card-header">
          <div class="kitchen-card-title">🪑 <?= htmlspecialchars($o['table_number'] ?? '#'.$o['order_id']) ?></div>
          <div class="kitchen-card-time">⏱️ <?= date('g:i a', strtotime($o['order_date'])) ?></div>
        </div>
        <div style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:10px">
          By: <?= htmlspecialchars($o['waiter_name'] ?? 'N/A') ?> · <?= $o['item_count'] ?> items
        </div>
        <div style="display:flex;gap:8px">
          <a href="<?= BASE_URL ?>/admin/order/<?= $o['order_id'] ?>" class="btn btn-secondary btn-sm" style="flex:1;justify-content:center">👁️ View Details</a>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Preparing -->
  <div class="kitchen-col">
    <div class="kitchen-column-header preparing">
      🔥 Preparing
      <?php $prep = array_filter($orders, fn($o) => $o['order_status'] === 'preparing'); ?>
      <span style="background:rgba(255,255,255,0.3);padding:3px 10px;border-radius:20px"><?= count($prep) ?></span>
    </div>
    <div class="kitchen-cards">
      <?php if (empty($prep)): ?>
      <div style="text-align:center;padding:30px;color:var(--text-secondary)"><div style="font-size:2rem;margin-bottom:8px">👨‍🍳</div>Nothing cooking</div>
      <?php else: ?>
      <?php foreach ($prep as $o): ?>
      <div class="kitchen-card preparing">
        <div class="kitchen-card-header">
          <div class="kitchen-card-title">🪑 <?= htmlspecialchars($o['table_number'] ?? '#'.$o['order_id']) ?></div>
          <div class="kitchen-card-time">⏱️ <?= date('g:i a', strtotime($o['order_date'])) ?></div>
        </div>
        <div style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:10px">
          By: <?= htmlspecialchars($o['waiter_name'] ?? 'N/A') ?> · <?= $o['item_count'] ?> items
        </div>
        <div style="display:flex;gap:8px">
          <a href="<?= BASE_URL ?>/admin/order/<?= $o['order_id'] ?>" class="btn btn-secondary btn-sm" style="flex:1;justify-content:center">👁️ View Details</a>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
// Auto-refresh with countdown
let countdown = 30;
const refreshBtn = document.getElementById('refresh-btn');
setInterval(() => {
  countdown--;
  if (refreshBtn) refreshBtn.textContent = `🔄 Refresh (${countdown}s)`;
  if (countdown <= 0) location.reload();
}, 1000);
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
