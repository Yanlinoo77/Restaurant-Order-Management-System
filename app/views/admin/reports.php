<?php $page_title = 'Monthly Reports'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<!-- ── MONTH PICKER ──────────────────────────────────────────────────── -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px" class="animate-in">
  <form method="GET" style="display:flex;gap:12px;align-items:center">
    <div class="form-field" style="margin:0">
      <input type="month" name="month" value="<?= $month ?>" class="form-control no-icon">
    </div>
    <button type="submit" class="btn btn-primary">📊 Generate Report</button>
  </form>
  <div style="font-size:0.9rem;color:var(--text-secondary)">
    <?= date('F Y', mktime(0,0,0,(int)$mon,1,(int)$year)) ?>
  </div>
</div>

<!-- ── SUMMARY STATS ─────────────────────────────────────────────────── -->
<div class="stats-grid animate-in-delay-1" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px">
  <div class="stat-card success">
    <div class="stat-icon">💰</div>
    <div class="stat-value" data-count="<?= $revenue['total'] ?>" data-prefix="" data-suffix=" MMK">0 MMK</div>
    <div class="stat-label">Monthly Revenue</div>
  </div>
  <div class="stat-card primary">
    <div class="stat-icon">📋</div>
    <div class="stat-value" data-count="<?= array_sum(array_column($daily_orders,'total_orders')) ?>">0</div>
    <div class="stat-label">Total Orders</div>
  </div>
  <div class="stat-card info">
    <div class="stat-icon">📈</div>
    <?php $avg = count($daily) > 0 ? $revenue['total'] / count($daily) : 0; ?>
    <div class="stat-value" data-count="<?= $avg ?>" data-prefix="" data-suffix=" MMK">0 MMK</div>
    <div class="stat-label">Avg Daily Revenue</div>
  </div>
  <div class="stat-card warning">
    <div class="stat-icon">🍽️</div>
    <div class="stat-value" data-count="<?= array_sum(array_column($daily_orders,'items_sold')) ?>">0</div>
    <div class="stat-label">Items Sold</div>
  </div>
</div>

<!-- ── CHART + TOP PRODUCTS ─────────────────────────────────────────── -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px" class="animate-in-delay-2">
  <div class="card">
    <div class="card-header"><div class="card-title">📈 Daily Revenue</div></div>
    <div class="card-body">
      <canvas id="daily-chart" width="550" height="220"></canvas>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">🍕 Top Products</div></div>
    <div class="card-body" style="padding:16px">
      <?php foreach (array_slice($top_products, 0, 5) as $i => $p): ?>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
        <div style="width:28px;height:28px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem"><?= $i+1 ?></div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:600;font-size:0.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($p['product_name']) ?></div>
          <div style="font-size:0.75rem;color:var(--text-secondary)"><?= $p['qty'] ?> orders · <?= number_format($p['revenue'], 0) ?> MMK</div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── DAILY REPORT TABLE ─────────────────────────────────────────────── -->
<div class="card animate-in-delay-3" style="margin-bottom:24px">
  <div class="card-header">
    <div class="card-title">📅 Daily Breakdown — <?= date('F Y', mktime(0,0,0,(int)$mon,1,(int)$year)) ?></div>
    <div style="font-size:0.82rem;color:var(--text-secondary)"><?= count($daily_orders) ?> active day<?= count($daily_orders)!=1?'s':'' ?></div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Day</th>
          <th>Orders</th>
          <th>Items Sold</th>
          <th>Gross Amount</th>
          <th>Paid</th>
          <th>Unpaid</th>
          <th>Cancelled</th>
          <th>Avg / Order</th>
          <th>View</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($daily_orders)): ?>
        <tr><td colspan="10"><div class="empty-state" style="padding:40px"><div class="empty-icon">📅</div><p>No orders this month</p></div></td></tr>
        <?php else: ?>
        <?php
        $grandTotal  = array_sum(array_column($daily_orders, 'gross_amount'));
        $grandOrders = array_sum(array_column($daily_orders, 'total_orders'));
        $grandItems  = array_sum(array_column($daily_orders, 'items_sold'));
        foreach ($daily_orders as $i => $d):
          $avg = $d['total_orders'] > 0 ? $d['gross_amount'] / $d['total_orders'] : 0;
          $isToday = ($d['day'] === date('Y-m-d'));
        ?>
        <tr class="animate-in" style="animation-delay:<?= $i*0.02 ?>s<?= $isToday ? ';background:rgba(255,107,53,0.06)' : '' ?>">
          <td>
            <strong style="<?= $isToday ? 'color:var(--primary)' : '' ?>">
              <?= date('M j, Y', strtotime($d['day'])) ?>
            </strong>
            <?php if ($isToday): ?><span class="badge badge-active" style="margin-left:6px;font-size:0.7rem">Today</span><?php endif; ?>
          </td>
          <td style="color:var(--text-secondary)"><?= date('l', strtotime($d['day'])) ?></td>
          <td><strong><?= $d['total_orders'] ?></strong></td>
          <td><?= $d['items_sold'] ?></td>
          <td><strong style="color:var(--success)"><?= number_format($d['gross_amount'], 0) ?> MMK</strong></td>
          <td>
            <?php if ($d['paid_orders'] > 0): ?>
            <span style="color:var(--success);font-weight:600"><?= $d['paid_orders'] ?></span>
            <?php else: ?><span style="color:var(--text-secondary)">—</span><?php endif; ?>
          </td>
          <td>
            <?php if ($d['unpaid_orders'] > 0): ?>
            <span style="color:var(--warning);font-weight:600"><?= $d['unpaid_orders'] ?></span>
            <?php else: ?><span style="color:var(--text-secondary)">—</span><?php endif; ?>
          </td>
          <td>
            <?php if ($d['cancelled_orders'] > 0): ?>
            <span style="color:var(--danger);font-weight:600"><?= $d['cancelled_orders'] ?></span>
            <?php else: ?><span style="color:var(--text-secondary)">—</span><?php endif; ?>
          </td>
          <td style="color:var(--text-secondary)"><?= number_format($avg, 0) ?> MMK</td>
          <td>
            <a href="<?= BASE_URL ?>/admin/orders?date=<?= $d['day'] ?>" class="btn btn-info btn-sm btn-icon" data-tooltip="View orders for this day">👁️</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <!-- Grand Total Row -->
        <tr style="background:var(--bg);border-top:2px solid var(--border)">
          <td colspan="2"><strong>Monthly Total</strong></td>
          <td><strong><?= $grandOrders ?></strong></td>
          <td><strong><?= $grandItems ?></strong></td>
          <td><strong style="color:var(--success)"><?= number_format($grandTotal, 0) ?> MMK</strong></td>
          <td><strong style="color:var(--success)"><?= array_sum(array_column($daily_orders,'paid_orders')) ?></strong></td>
          <td><strong style="color:var(--warning)"><?= array_sum(array_column($daily_orders,'unpaid_orders')) ?></strong></td>
          <td><strong style="color:var(--danger)"><?= array_sum(array_column($daily_orders,'cancelled_orders')) ?></strong></td>
          <td><strong><?= $grandOrders > 0 ? number_format($grandTotal/$grandOrders, 0) : 0 ?> MMK</strong></td>
          <td></td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── WAITER PERFORMANCE ─────────────────────────────────────────────── -->
<div class="card animate-in-delay-4">
  <div class="card-header"><div class="card-title">👨‍🍳 Waiter Performance</div></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Waiter</th><th>Orders</th><th>Revenue</th><th>Avg/Order</th></tr></thead>
      <tbody>
        <?php foreach ($waiter_stats as $i => $w): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($w['waiter_name']) ?></strong></td>
          <td><?= $w['orders'] ?></td>
          <td><strong style="color:var(--success)"><?= number_format($w['revenue'], 0) ?> MMK</strong></td>
          <td><?= $w['orders']>0 ? number_format($w['revenue']/$w['orders'], 0) . ' MMK' : '0 MMK' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── MONTHLY MENU REPORT ───────────────────────────────────────────── -->
<div class="card animate-in-delay-4" style="margin-top:24px">
  <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
    <div class="card-title">🍽️ Monthly Menu Report — <?= date('F Y', mktime(0,0,0,(int)$mon,1,(int)$year)) ?></div>
    <div style="font-size:0.82rem;color:var(--text-secondary)">
      <?= count($menu_report) ?> item<?= count($menu_report) != 1 ? 's' : '' ?> ·
      <?= array_sum(array_column($menu_report, 'total_qty')) ?> sold
    </div>
  </div>

  <?php if (empty($menu_by_category)): ?>
  <div class="card-body">
    <div class="empty-state" style="padding:40px">
      <div class="empty-icon">🍽️</div>
      <p>No menu sales data for this month</p>
    </div>
  </div>
  <?php else: ?>

  <?php
    $grandMenuQty = 0;
    $grandMenuRev = 0;
    foreach ($menu_by_category as $cg) {
      $grandMenuQty += $cg['total_qty'];
      $grandMenuRev += $cg['total_revenue'];
    }
  ?>

  <!-- Summary badges -->
  <div style="display:flex;gap:16px;padding:16px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:rgba(var(--success-rgb,34,197,94),0.1);border-radius:8px">
      <span style="font-size:1.1rem">💰</span>
      <div>
        <div style="font-size:0.75rem;color:var(--text-secondary)">Total Menu Revenue</div>
        <div style="font-weight:700;color:var(--success)"><?= number_format($grandMenuRev, 0) ?> MMK</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:rgba(var(--primary-rgb,255,107,53),0.1);border-radius:8px">
      <span style="font-size:1.1rem">📦</span>
      <div>
        <div style="font-size:0.75rem;color:var(--text-secondary)">Total Items Sold</div>
        <div style="font-weight:700;color:var(--primary)"><?= number_format($grandMenuQty) ?></div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:rgba(var(--info-rgb,59,130,246),0.1);border-radius:8px">
      <span style="font-size:1.1rem">📂</span>
      <div>
        <div style="font-size:0.75rem;color:var(--text-secondary)">Categories</div>
        <div style="font-weight:700;color:var(--info)"><?= count($menu_by_category) ?></div>
      </div>
    </div>
  </div>

  <?php foreach ($menu_by_category as $catName => $catData): ?>
  <div style="margin:0">
    <!-- Category header -->
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 20px;background:var(--bg-secondary,rgba(0,0,0,0.03));border-bottom:1px solid var(--border)">
      <div style="font-weight:700;font-size:0.9rem;color:var(--text)">
        📂 <?= htmlspecialchars($catName) ?>
        <span style="margin-left:8px;font-weight:400;font-size:0.78rem;color:var(--text-secondary)"><?= count($catData['items']) ?> items</span>
      </div>
      <div style="font-size:0.85rem;display:flex;gap:20px">
        <span style="color:var(--text-secondary)">Qty: <strong style="color:var(--text)"><?= number_format($catData['total_qty']) ?></strong></span>
        <span style="color:var(--text-secondary)">Revenue: <strong style="color:var(--success)"><?= number_format($catData['total_revenue'], 0) ?> MMK</strong></span>
      </div>
    </div>

    <!-- Products in this category -->
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="width:36px">#</th>
            <th>Product Name</th>
            <th>Unit Price</th>
            <th>Qty Sold</th>
            <th>Orders</th>
            <th>Revenue</th>
            <th>% of Total</th>
            <th>Sales Bar</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($catData['items'] as $ri => $row):
            $pct = $grandMenuRev > 0 ? ($row['total_revenue'] / $grandMenuRev * 100) : 0;
            $qtyPct = $grandMenuQty > 0 ? ($row['total_qty'] / $grandMenuQty * 100) : 0;
          ?>
          <tr class="animate-in" style="animation-delay:<?= $ri * 0.02 ?>s">
            <td style="color:var(--text-secondary);font-size:0.8rem"><?= $ri + 1 ?></td>
            <td>
              <strong><?= htmlspecialchars($row['product_name']) ?></strong>
              <?php if ($ri === 0): ?>
              <span class="badge badge-active" style="margin-left:6px;font-size:0.68rem">⭐ Top</span>
              <?php endif; ?>
            </td>
            <td style="color:var(--text-secondary)"><?= number_format($row['unit_price'], 0) ?> MMK</td>
            <td>
              <strong style="color:var(--primary)"><?= number_format($row['total_qty']) ?></strong>
            </td>
            <td>
              <span style="color:var(--info)"><?= $row['order_count'] ?></span>
            </td>
            <td>
              <?php if ($row['total_revenue'] > 0): ?>
              <strong style="color:var(--success)"><?= number_format($row['total_revenue'], 0) ?> MMK</strong>
              <?php else: ?>
              <span style="color:var(--text-secondary)">—</span>
              <?php endif; ?>
            </td>
            <td style="color:var(--text-secondary);font-size:0.82rem"><?= number_format($pct, 1) ?>%</td>
            <td style="min-width:100px">
              <div style="background:var(--border);border-radius:4px;height:8px;overflow:hidden">
                <div style="height:100%;width:<?= min($qtyPct * 3, 100) ?>%;background:linear-gradient(90deg,var(--primary),var(--primary-dark,#c0392b));border-radius:4px;transition:width 0.6s ease"></div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <!-- Category subtotal -->
          <tr style="background:var(--bg);border-top:1px dashed var(--border)">
            <td colspan="3"><strong style="font-size:0.82rem">Category Total</strong></td>
            <td><strong style="color:var(--primary)"><?= number_format($catData['total_qty']) ?></strong></td>
            <td></td>
            <td><strong style="color:var(--success)"><?= number_format($catData['total_revenue'], 0) ?> MMK</strong></td>
            <td style="color:var(--text-secondary);font-size:0.82rem">
              <?= $grandMenuRev > 0 ? number_format($catData['total_revenue'] / $grandMenuRev * 100, 1) : 0 ?>%
            </td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Grand total footer -->
  <div style="display:flex;justify-content:flex-end;gap:32px;padding:14px 24px;border-top:2px solid var(--border);background:var(--bg)">
    <span style="color:var(--text-secondary)">Grand Total Qty: <strong style="color:var(--primary)"><?= number_format($grandMenuQty) ?></strong></span>
    <span style="color:var(--text-secondary)">Grand Total Revenue: <strong style="color:var(--success)"><?= number_format($grandMenuRev, 0) ?> MMK</strong></span>
  </div>

  <?php endif; ?>
</div>

<script>
window.addEventListener('load', function() {
  const days = <?= json_encode(array_map(fn($d)=>date('d',strtotime($d['day'])), $daily)) ?>;
  const revs = <?= json_encode(array_map(fn($d)=>floatval($d['revenue']), $daily)) ?>;
  if (days.length > 0) {
    drawBarChart('daily-chart', days, revs, '#FF6B35');
  } else {
    const ctx = document.getElementById('daily-chart').getContext('2d');
    ctx.fillStyle = '#6B7280'; ctx.font = '14px Inter'; ctx.textAlign = 'center';
    ctx.fillText('No data for this month', 275, 110);
  }
});
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
