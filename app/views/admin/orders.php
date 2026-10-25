<?php $page_title = 'Orders'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<?php
$currentDate   = $_GET['date']   ?? '';
$currentStatus = $_GET['status'] ?? '';
$isToday       = ($currentDate === 'today');

// Build base URL helpers
function ordersUrl($date = '', $status = '') {
    $p = [];
    if ($date)   $p[] = 'date='   . urlencode($date);
    if ($status) $p[] = 'status=' . urlencode($status);
    return BASE_URL . '/admin/orders' . ($p ? '?' . implode('&', $p) : '');
}
?>

<!-- ── TODAY'S DAILY SNAPSHOT (always visible at top) ────────────────── -->
<div class="stats-grid animate-in" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="stat-card primary" style="cursor:pointer" onclick="window.location='<?= ordersUrl('today') ?>'">
    <div class="stat-icon">📋</div>
    <div class="stat-value" data-count="<?= $today_stats['cnt'] ?>">0</div>
    <div class="stat-label">Today's Orders</div>
  </div>
  <div class="stat-card success" style="cursor:pointer" onclick="window.location='<?= ordersUrl('today') ?>'">
    <div class="stat-icon">💰</div>
    <div class="stat-value" data-count="<?= $today_stats['revenue'] ?>" data-suffix=" MMK">0 MMK</div>
    <div class="stat-label">Today's Revenue</div>
  </div>
  <div class="stat-card info">
    <div class="stat-icon">🍽️</div>
    <div class="stat-value" data-count="<?= $today_stats['items'] ?>">0</div>
    <div class="stat-label">Items Sold Today</div>
  </div>
</div>

<!-- ── FILTERS + NEW ORDER ───────────────────────────────────────────── -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px" class="animate-in-delay-1">
  <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">

    <!-- Date picker row -->
    <div style="display:flex;align-items:center;gap:8px">
      <a href="<?= ordersUrl('', $currentStatus) ?>"
         class="btn btn-sm <?= empty($currentDate) ? 'btn-primary' : 'btn-secondary' ?>">
        📋 All Days
      </a>
      <a href="<?= ordersUrl('today', $currentStatus) ?>"
         class="btn btn-sm <?= $isToday ? 'btn-primary' : 'btn-secondary' ?>">
        📅 Today
      </a>
      <!-- Pick any date -->
      <form method="GET" action="<?= BASE_URL ?>/admin/orders" style="display:flex;align-items:center;gap:6px">
        <?php if ($currentStatus): ?>
        <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">
        <?php endif; ?>
        <input type="date" name="date"
               value="<?= (preg_match('/^\d{4}-\d{2}-\d{2}$/', $currentDate) ? $currentDate : '') ?>"
               class="form-control no-icon"
               style="padding:6px 12px;height:36px;font-size:0.85rem;width:160px"
               onchange="this.form.submit()"
               title="Pick a specific date">
        <?php if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $currentDate)): ?>
        <a href="<?= ordersUrl('', $currentStatus) ?>" class="btn btn-secondary btn-sm" title="Clear date">✕</a>
        <?php endif; ?>
      </form>
    </div>

    <div style="width:1px;height:28px;background:var(--border)"></div>

    <!-- Status tabs -->
    <div class="filter-tabs">
      <a href="<?= ordersUrl($currentDate, '') ?>"          class="filter-tab <?= empty($currentStatus) ? 'active' : '' ?>">All</a>
      <a href="<?= ordersUrl($currentDate, 'pending') ?>"   class="filter-tab <?= $currentStatus==='pending'   ? 'active' : '' ?>">⏳ Pending</a>
      <a href="<?= ordersUrl($currentDate, 'preparing') ?>" class="filter-tab <?= $currentStatus==='preparing' ? 'active' : '' ?>">🔥 Preparing</a>
      <a href="<?= ordersUrl($currentDate, 'served') ?>"    class="filter-tab <?= $currentStatus==='served'    ? 'active' : '' ?>">✅ Served</a>
      <a href="<?= ordersUrl($currentDate, 'completed') ?>" class="filter-tab <?= $currentStatus==='completed' ? 'active' : '' ?>">💚 Completed</a>
      <a href="<?= ordersUrl($currentDate, 'cancelled') ?>" class="filter-tab <?= $currentStatus==='cancelled' ? 'active' : '' ?>">❌ Cancelled</a>
    </div>

  </div>
  <a href="<?= BASE_URL ?>/admin/add-order" class="btn btn-primary">➕ New Order</a>
</div>


<!-- ── ORDERS TABLE ──────────────────────────────────────────────────── -->
<div class="card animate-in-delay-2">
  <div class="card-header">
    <div class="card-title">
      📋
      <?php if ($isToday): ?>
        Today's Orders
      <?php elseif ($currentDate): ?>
        Orders for <?= date('F j, Y', strtotime($currentDate)) ?>
      <?php else: ?>
        All Orders
      <?php endif; ?>
      (<?= count($orders) ?>)
    </div>
    <div class="search-bar">
      <span class="search-icon">🔍</span>
      <input type="text" id="table-search" class="form-control no-icon" placeholder="Search orders..." style="padding-left:36px;width:220px">
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#ID</th>
          <th>Table</th>
          <th>Waiter</th>
          <th>Items</th>
          <th>Status</th>
          <th>Amount</th>
          <th>Payment</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
        <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">📋</div><h3>No orders found</h3><p>Try a different filter</p></div></td></tr>
        <?php else: ?>
        <?php
        $db2 = Database::getInstance();
        foreach ($orders as $i => $order):
          $item_count = $db2->fetchOne("SELECT COUNT(*) as cnt FROM order_item WHERE order_id=?", [$order['order_id']], 'i')['cnt'];
        ?>
        <tr class="animate-in" style="animation-delay:<?= $i*0.03 ?>s">
          <td><strong>#<?= $order['order_id'] ?></strong></td>
          <td>🪑 <strong><?= htmlspecialchars($order['table_number'] ?? 'N/A') ?></strong></td>
          <td><?= htmlspecialchars($order['waiter_name'] ?? 'N/A') ?></td>
          <td><span style="background:var(--bg);padding:3px 10px;border-radius:20px;font-size:0.82rem"><?= $item_count ?> item<?= $item_count!=1?'s':'' ?></span></td>
          <td><span class="badge badge-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span></td>
          <td><strong><?= number_format($order['total_amount'], 0) ?> MMK</strong></td>
          <td><span class="badge badge-<?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span></td>
          <td style="font-size:0.82rem;color:var(--text-secondary)"><?= date('M j, g:ia', strtotime($order['order_date'])) ?></td>
          <td>
            <div style="display:flex;gap:6px">
              <a href="<?= BASE_URL ?>/admin/order/<?= $order['order_id'] ?>" class="btn btn-info btn-sm btn-icon" data-tooltip="View Details">👁️</a>
              <?php if ($order['payment_status'] === 'unpaid' && in_array($order['order_status'], ['served','completed'])): ?>
              <button class="btn btn-success btn-sm btn-icon" onclick="openPaymentModal(<?= $order['order_id'] ?>,<?= $order['total_amount'] ?>)" data-tooltip="Process Payment">💳</button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Payment Modal -->
<div class="modal-overlay" id="payment-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">💳 Process Payment</div>
      <button class="modal-close" onclick="closeModal('payment-modal')">✕</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/process-payment" method="POST">
      <div class="modal-body">
        <input type="hidden" name="order_id" id="pay-order-id">
        <div class="form-row cols-2">
          <div class="form-field">
            <label>Order Amount</label>
            <input type="number" name="amount_paid" id="pay-amount" step="0.01" class="form-control no-icon" required>
          </div>
          <div class="form-field">
            <label>Payment Method</label>
            <select name="payment_method" class="form-control no-icon" required>
              <option value="cash">💵 Cash</option>
              <option value="credit_card">💳 Credit Card</option>
              <option value="debit_card">🏦 Debit Card</option>
              <option value="digital_wallet">📱 Digital Wallet</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('payment-modal')">Cancel</button>
        <button type="submit" class="btn btn-success">✓ Confirm Payment</button>
      </div>
    </form>
  </div>
</div>

<script>
function openPaymentModal(orderId, amount) {
  document.getElementById('pay-order-id').value = orderId;
  document.getElementById('pay-amount').value = parseFloat(amount).toFixed(2);
  openModal('payment-modal');
}
</script>

<!-- Today's Menu Panel -->
<div class="card animate-in-delay-3" style="margin-top:24px">
  <div class="card-header" style="cursor:pointer" onclick="toggleMenuPanel()">
    <div class="card-title">🍽️ Today's Menu</div>
    <div style="display:flex;align-items:center;gap:12px">
      <?php $total_ordered_today = array_sum(array_column($todays_menu, 'ordered_today')); ?>
      <span style="font-size:0.85rem;color:var(--text-secondary)"><?= count($todays_menu) ?> items &nbsp;·&nbsp; <?= $total_ordered_today ?> ordered today</span>
      <span id="menu-toggle-icon" style="font-size:0.85rem;color:var(--text-secondary)">▼ Show</span>
    </div>
  </div>
  <div id="todays-menu-panel" style="display:none">
    <?php
    $menuByCategory = [];
    foreach ($todays_menu as $item) {
        $cat = $item['category_name'] ?? 'Uncategorized';
        $menuByCategory[$cat][] = $item;
    }
    ?>
    <?php if (empty($menuByCategory)): ?>
    <div style="padding:48px;text-align:center;color:var(--text-secondary)">
      <div style="font-size:2.5rem;margin-bottom:12px">🍽️</div>
      <div>No menu items available today.</div>
    </div>
    <?php else: ?>
    <?php foreach ($menuByCategory as $cat => $items): ?>
    <div style="border-bottom:1px solid var(--border)">
      <div style="padding:10px 20px;background:var(--bg)">
        <span style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-secondary)"><?= htmlspecialchars($cat) ?></span>
      </div>
      <div style="padding:12px 20px;display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:8px">
        <?php foreach ($items as $item): ?>
        <div style="background:var(--bg);border-radius:8px;padding:11px 14px;display:flex;justify-content:space-between;align-items:center;gap:10px">
          <div style="min-width:0">
            <div style="font-weight:600;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($item['product_name']) ?></div>
            <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:2px">
              <?= number_format($item['price'], 0) ?> MMK
              <?php if ($item['ordered_today'] > 0): ?>
              &nbsp;·&nbsp;<span style="color:var(--success)">Ordered <?= $item['ordered_today'] ?>×</span>
              &nbsp;·&nbsp;Last: <?= date('g:ia', strtotime($item['last_ordered_at'])) ?>
              <?php endif; ?>
            </div>
          </div>
          <?php if ($item['ordered_today'] > 0): ?>
          <span style="flex-shrink:0;background:rgba(39,174,96,0.12);color:var(--success);padding:3px 10px;border-radius:12px;font-size:0.82rem;font-weight:700"><?= $item['ordered_today'] ?></span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleMenuPanel() {
  var panel = document.getElementById('todays-menu-panel');
  var icon  = document.getElementById('menu-toggle-icon');
  var open  = panel.style.display !== 'none';
  panel.style.display = open ? 'none' : 'block';
  icon.textContent = open ? '▼ Show' : '▲ Hide';
}
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
