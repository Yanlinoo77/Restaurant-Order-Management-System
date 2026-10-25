<?php $page_title = 'All Orders'; require_once __DIR__ . '/../shared/waiter_layout_start.php'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px" class="animate-in">
  <div class="filter-tabs">
    <a href="<?= BASE_URL ?>/waiter/orders" class="filter-tab <?= empty($_GET['status'])?'active':'' ?>">All</a>
    <a href="<?= BASE_URL ?>/waiter/orders?status=pending" class="filter-tab <?= ($_GET['status']??'')==='pending'?'active':'' ?>">⏳ Pending</a>
    <a href="<?= BASE_URL ?>/waiter/orders?status=preparing" class="filter-tab <?= ($_GET['status']??'')==='preparing'?'active':'' ?>">🔥 Preparing</a>
    <a href="<?= BASE_URL ?>/waiter/orders?status=served" class="filter-tab <?= ($_GET['status']??'')==='served'?'active':'' ?>">✅ Served</a>
    <a href="<?= BASE_URL ?>/waiter/orders?status=completed" class="filter-tab <?= ($_GET['status']??'')==='completed'?'active':'' ?>">💚 Completed</a>
  </div>
  <a href="<?= BASE_URL ?>/waiter/new-order" class="btn btn-primary">➕ New Order</a>
</div>

<div class="card animate-in-delay-1">
  <div class="card-header">
    <div class="card-title">📋 My Orders (<?= count($orders) ?>)</div>
    <div class="search-bar">
      <span class="search-icon">🔍</span>
      <input type="text" id="table-search" class="form-control no-icon" placeholder="Search..." style="padding-left:36px;width:200px">
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#ID</th><th>Table</th><th>Items</th><th>Status</th><th>Amount</th><th>Payment</th><th>Time</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📋</div><h3>No orders found</h3></div></td></tr>
        <?php else:
          $db2 = Database::getInstance();
          foreach ($orders as $i => $order):
            $cnt = $db2->fetchOne("SELECT COUNT(*) as c FROM order_item WHERE order_id=?", [$order['order_id']], 'i')['c'];
        ?>
        <tr style="animation-delay:<?= $i*0.03 ?>s" class="animate-in">
          <td><strong>#<?= $order['order_id'] ?></strong></td>
          <td>🪑 <strong><?= htmlspecialchars($order['table_number']??'N/A') ?></strong></td>
          <td><?= $cnt ?> item<?= $cnt!=1?'s':'' ?></td>
          <td><span class="badge badge-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span></td>
          <td><strong><?= number_format($order['total_amount'], 0) ?> MMK</strong></td>
          <td><span class="badge badge-<?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span></td>
          <td style="font-size:0.8rem;color:var(--text-secondary)"><?= date('M j, g:ia', strtotime($order['order_date'])) ?></td>
          <td>
            <div style="display:flex;gap:6px">
              <a href="<?= BASE_URL ?>/waiter/order/<?= $order['order_id'] ?>" class="btn btn-secondary btn-sm">👁️ View</a>
              <?php if ($order['order_status']==='served' && $order['payment_status']==='unpaid'): ?>
              <a href="<?= BASE_URL ?>/waiter/order/<?= $order['order_id'] ?>" class="btn btn-success btn-sm">💳 Pay</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../shared/waiter_layout_end.php'; ?>
