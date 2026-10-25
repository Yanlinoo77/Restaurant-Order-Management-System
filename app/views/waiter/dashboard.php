<?php $page_title = 'My Dashboard'; require_once __DIR__ . '/../shared/waiter_layout_start.php'; ?>

<div class="stats-grid animate-in">
  <div class="stat-card primary animate-in-delay-1">
    <div class="stat-icon">📋</div>
    <div class="stat-value" data-count="<?= $stats['my_orders_today'] ?>">0</div>
    <div class="stat-label">My Orders Today</div>
  </div>
  <div class="stat-card warning animate-in-delay-2">
    <div class="stat-icon">⏳</div>
    <div class="stat-value" data-count="<?= $stats['pending_orders'] ?>">0</div>
    <div class="stat-label">Pending Orders</div>
  </div>
  <div class="stat-card info animate-in-delay-3">
    <div class="stat-icon">🔥</div>
    <div class="stat-value" data-count="<?= $stats['active_orders'] ?>">0</div>
    <div class="stat-label">Active Orders</div>
  </div>
  <div class="stat-card success animate-in-delay-4">
    <div class="stat-icon">💰</div>
    <div class="stat-value" data-count="<?= $stats['my_revenue_today'] ?>" data-prefix="$">$0</div>
    <div class="stat-label">Revenue Today</div>
  </div>
</div>

<!-- Active Orders -->
<div class="card animate-in-delay-2">
  <div class="card-header">
    <div class="card-title">🍽️ My Active Orders</div>
    <a href="<?= BASE_URL ?>/waiter/new-order" class="btn btn-primary btn-sm">➕ New Order</a>
  </div>
  <?php if (empty($my_orders)): ?>
  <div class="empty-state">
    <div class="empty-icon">🍽️</div>
    <h3>No active orders</h3>
    <p>Start by creating a new order</p>
    <a href="<?= BASE_URL ?>/waiter/new-order" class="btn btn-primary" style="margin-top:16px">➕ Take Order</a>
  </div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding:20px">
    <?php
    $db2 = Database::getInstance();
    foreach ($my_orders as $i => $order):
      $items = $db2->fetchAll("SELECT oi.quantity, p.product_name FROM order_item oi LEFT JOIN product p ON oi.product_id=p.product_id WHERE oi.order_id=?", [$order['order_id']], 'i');
      $elapsed = round((time() - strtotime($order['order_date'])) / 60);
    ?>
    <div class="card" style="border-left:4px solid <?= ['pending'=>'var(--warning)','preparing'=>'var(--info)','served'=>'var(--success)'][$order['order_status']]??'var(--border)' ?>;animation-delay:<?= $i*0.05 ?>s" class="animate-in">
      <div class="card-header" style="padding:14px 16px">
        <div>
          <div style="font-weight:700">🪑 <?= htmlspecialchars($order['table_number']??'Table') ?></div>
          <div style="font-size:0.78rem;color:var(--text-secondary)">⏱️ <?= $elapsed ?> min ago</div>
        </div>
        <span class="badge badge-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span>
      </div>
      <div style="padding:12px 16px">
        <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:8px">
          <?php foreach(array_slice($items,0,3) as $item): ?>
          <div>• <?= $item['quantity'] ?>x <?= htmlspecialchars($item['product_name']??'Item') ?></div>
          <?php endforeach; ?>
          <?php if(count($items)>3): ?><div style="color:var(--primary)">+<?= count($items)-3 ?> more...</div><?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px">
          <strong style="color:var(--primary)"><?= number_format($order['total_amount'], 0) ?> MMK</strong>
          <div style="display:flex;gap:6px">
            <a href="<?= BASE_URL ?>/waiter/order/<?= $order['order_id'] ?>" class="btn btn-secondary btn-sm">View</a>
            <?php if ($order['order_status'] === 'served'): ?>
            <form action="<?= BASE_URL ?>/waiter/mark-served" method="POST" style="display:inline">
              <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
              <button type="submit" class="btn btn-success btn-sm">✓ Complete</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../shared/waiter_layout_end.php'; ?>
