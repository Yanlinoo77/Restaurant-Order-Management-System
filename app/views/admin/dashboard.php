<?php $page_title = 'Dashboard'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<div class="stats-grid animate-in">
  <div class="stat-card primary animate-in-delay-1">
    <div class="stat-icon">📋</div>
    <div class="stat-value" data-count="<?= $stats['total_orders_today'] ?>">0</div>
    <div class="stat-label">Orders Today</div>
  </div>
  <div class="stat-card success animate-in-delay-2">
    <div class="stat-icon">💰</div>
    <div class="stat-value" data-count="<?= $stats['revenue_today'] ?>" data-prefix="" data-suffix=" MMK">0 MMK</div>
    <div class="stat-label">Revenue Today</div>
  </div>
  <div class="stat-card warning animate-in-delay-3">
    <div class="stat-icon">⏳</div>
    <div class="stat-value" data-count="<?= $stats['pending_orders'] ?>">0</div>
    <div class="stat-label">Pending Orders</div>
  </div>
  <div class="stat-card info animate-in-delay-4">
    <div class="stat-icon">🪑</div>
    <div class="stat-value" data-count="<?= $stats['active_tables'] ?>">0</div>
    <div class="stat-label">Active Tables</div>
  </div>
  <div class="stat-card primary">
    <div class="stat-icon">🍕</div>
    <div class="stat-value" data-count="<?= $stats['total_products'] ?>">0</div>
    <div class="stat-label">Products Available</div>
  </div>
  <div class="stat-card success">
    <div class="stat-icon">👨‍🍳</div>
    <div class="stat-value" data-count="<?= $stats['total_waiters'] ?>">0</div>
    <div class="stat-label">Active Waiters</div>
  </div>
  <div class="stat-card warning">
    <div class="stat-icon">📈</div>
    <div class="stat-value" data-count="<?= $stats['monthly_revenue'] ?>" data-prefix="" data-suffix=" MMK">0 MMK</div>
    <div class="stat-label">Monthly Revenue</div>
  </div>
  <div class="stat-card danger">
    <div class="stat-icon">⚠️</div>
    <div class="stat-value" data-count="<?= $stats['low_inventory'] ?>">0</div>
    <div class="stat-label">Low Stock Alerts</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px" class="animate-in-delay-2">
  <!-- Revenue Chart -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📊 Revenue Trend (Last 6 Months)</div>
      <a href="<?= BASE_URL ?>/admin/reports" class="btn btn-secondary btn-sm">View Reports</a>
    </div>
    <div class="card-body">
      <canvas id="revenue-chart" width="600" height="220"></canvas>
    </div>
  </div>

  <!-- Order Status Donut -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🍩 Order Status</div>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;align-items:center">
      <canvas id="status-chart" width="180" height="180"></canvas>
      <div class="chart-legend" style="justify-content:center;margin-top:16px">
        <div class="legend-item"><div class="legend-dot" style="background:#F39C12"></div>Pending</div>
        <div class="legend-item"><div class="legend-dot" style="background:#3498DB"></div>Preparing</div>
        <div class="legend-item"><div class="legend-dot" style="background:#27AE60"></div>Served</div>
        <div class="legend-item"><div class="legend-dot" style="background:#E74C3C"></div>Cancelled</div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Orders -->
<div class="card animate-in-delay-3">
  <div class="card-header">
    <div class="card-title">🕐 Recent Orders</div>
    <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-primary btn-sm">View All</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#ID</th>
          <th>Table</th>
          <th>Waiter</th>
          <th>Status</th>
          <th>Amount</th>
          <th>Payment</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recent_orders)): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-secondary)">No orders yet</td></tr>
        <?php else: ?>
        <?php foreach ($recent_orders as $i => $order): ?>
        <tr class="animate-in" style="animation-delay:<?= $i*0.05 ?>s">
          <td><strong>#<?= $order['order_id'] ?></strong></td>
          <td>🪑 <?= htmlspecialchars($order['table_number'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars($order['waiter_name'] ?? 'N/A') ?></td>
          <td><span class="badge badge-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span></td>
          <td><strong><?= number_format($order['total_amount'], 0) ?> MMK</strong></td>
          <td><span class="badge badge-<?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span></td>
          <td style="color:var(--text-secondary);font-size:0.82rem"><?= date('M j, g:ia', strtotime($order['order_date'])) ?></td>
          <td><a href="<?= BASE_URL ?>/admin/order/<?= $order['order_id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
window.addEventListener('load', function() {
  // Revenue line chart
  const months = <?= json_encode(array_column($monthly_data, 'month')) ?>;
  const revenues = <?= json_encode(array_map(fn($r) => floatval($r['revenue']), $monthly_data)) ?>;
  if (months.length > 0) {
    drawLineChart('revenue-chart', months, [{ data: revenues, color: '#FF6B35', fill: true }], { prefix: '' });
  } else {
    const ctx = document.getElementById('revenue-chart').getContext('2d');
    ctx.fillStyle = '#6B7280'; ctx.font = '14px Inter'; ctx.textAlign = 'center';
    ctx.fillText('No revenue data yet', 300, 110);
  }

  // Status donut
  <?php
  $db2 = Database::getInstance();
  $statuses = $db2->fetchAll("SELECT order_status, COUNT(*) as cnt FROM `order` GROUP BY order_status");
  $status_map = ['pending'=>0,'preparing'=>0,'served'=>0,'cancelled'=>0,'completed'=>0];
  foreach($statuses as $s) $status_map[$s['order_status']] = intval($s['cnt']);
  ?>
  drawDonutChart('status-chart',
    [<?= $status_map['pending'] ?>, <?= $status_map['preparing'] ?>, <?= $status_map['served'] ?>, <?= $status_map['cancelled'] + $status_map['completed'] ?>],
    ['Pending','Preparing','Served','Done'],
    ['#F39C12','#3498DB','#27AE60','#95A5A6']
  );
});
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
