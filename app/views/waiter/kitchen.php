<?php $page_title = 'Kitchen Display'; require_once __DIR__ . '/../shared/waiter_layout_start.php'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px" class="animate-in">
  <p style="color:var(--text-secondary)">Live kitchen status — Read only view</p>
  <button onclick="location.reload()" class="btn btn-secondary">🔄 Refresh</button>
</div>

<?php if (empty($orders)): ?>
<div class="card animate-in-delay-1">
  <div class="empty-state" style="padding:60px">
    <div class="empty-icon">🍽️</div>
    <h3>Kitchen is clear!</h3>
    <p>No active orders in the kitchen</p>
  </div>
</div>
<?php else: ?>
<div class="kitchen-board animate-in-delay-1">
  <?php
  $pending_orders = array_filter($orders, fn($o) => $o['order_status']==='pending');
  $prep_orders = array_filter($orders, fn($o) => $o['order_status']==='preparing');
  ?>

  <div class="kitchen-col">
    <div class="kitchen-column-header pending">⏳ Pending <span style="background:rgba(255,255,255,0.3);padding:2px 10px;border-radius:20px"><?= count($pending_orders) ?></span></div>
    <div class="kitchen-cards">
      <?php if (empty($pending_orders)): ?><div style="padding:20px;text-align:center;color:var(--text-secondary)">No pending orders</div><?php endif; ?>
      <?php foreach ($pending_orders as $o): ?>
      <div class="kitchen-card pending">
        <div class="kitchen-card-header">
          <div class="kitchen-card-title">🪑 <?= htmlspecialchars($o['table_number']??'#'.$o['order_id']) ?></div>
          <div class="kitchen-card-time">⏱️ <?= date('g:i a',strtotime($o['order_date'])) ?></div>
        </div>
        <div style="font-size:0.8rem;color:var(--text-secondary)"><?= $o['item_count'] ?> items · By <?= htmlspecialchars($o['waiter_name']??'N/A') ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="kitchen-col">
    <div class="kitchen-column-header preparing">🔥 Preparing <span style="background:rgba(255,255,255,0.3);padding:2px 10px;border-radius:20px"><?= count($prep_orders) ?></span></div>
    <div class="kitchen-cards">
      <?php if (empty($prep_orders)): ?><div style="padding:20px;text-align:center;color:var(--text-secondary)">Nothing cooking</div><?php endif; ?>
      <?php foreach ($prep_orders as $o): ?>
      <div class="kitchen-card preparing">
        <div class="kitchen-card-header">
          <div class="kitchen-card-title">🪑 <?= htmlspecialchars($o['table_number']??'#'.$o['order_id']) ?></div>
          <div class="kitchen-card-time">⏱️ <?= date('g:i a',strtotime($o['order_date'])) ?></div>
        </div>
        <div style="font-size:0.8rem;color:var(--text-secondary)"><?= $o['item_count'] ?> items · By <?= htmlspecialchars($o['waiter_name']??'N/A') ?></div>
        <?php if ($o['waiter_id']==$_SESSION['waiter_id']): ?>
        <div style="margin-top:8px"><span style="background:rgba(39,174,96,0.1);color:var(--success);padding:3px 8px;border-radius:8px;font-size:0.75rem">👈 Your order</span></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<script>setTimeout(() => location.reload(), 30000);</script>

<?php require_once __DIR__ . '/../shared/waiter_layout_end.php'; ?>
