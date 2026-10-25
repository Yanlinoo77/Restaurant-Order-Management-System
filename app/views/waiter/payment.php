<?php $page_title = 'Payments'; require_once __DIR__ . '/../shared/waiter_layout_start.php'; ?>

<?php if (!empty($orders)): ?>
<div style="margin-bottom:24px" class="animate-in">
  <h3 style="font-size:1rem;font-weight:700;color:var(--danger);margin-bottom:16px">⚠️ Awaiting Payment (<?= count($orders) ?>)</h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
    <?php foreach ($orders as $order): ?>
    <div class="card" style="border:2px solid var(--danger)">
      <div class="card-header" style="background:rgba(231,76,60,0.05)">
        <div class="card-title">🪑 <?= htmlspecialchars($order['table_number']??'N/A') ?></div>
        <strong style="color:var(--primary)"><?= number_format($order['total_amount'], 0) ?> MMK</strong>
      </div>
      <div class="card-body" style="padding:16px;text-align:center">
        <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:12px">Order #<?= $order['order_id'] ?> · <?= date('g:ia',strtotime($order['order_date'])) ?></p>
        <a href="<?= BASE_URL ?>/waiter/order/<?= $order['order_id'] ?>" class="btn btn-primary" style="width:100%;justify-content:center">💳 View & Process</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="card animate-in-delay-1">
  <div class="card-header"><div class="card-title">✅ Recent Payments</div></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Order</th><th>Table</th><th>Method</th><th>Amount</th><th>Date</th></tr></thead>
      <tbody>
        <?php if (empty($paid)): ?>
        <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">💳</div><h3>No payments yet</h3></div></td></tr>
        <?php else: ?>
        <?php foreach ($paid as $p): ?>
        <tr>
          <td><a href="<?= BASE_URL ?>/waiter/order/<?= $p['order_id'] ?>" style="color:var(--primary)">#<?= $p['order_id'] ?></a></td>
          <td><?= htmlspecialchars($p['table_number']??'N/A') ?></td>
          <td><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></td>
          <td><strong style="color:var(--success)"><?= number_format($p['amount_paid'], 0) ?> MMK</strong></td>
          <td style="font-size:0.82rem;color:var(--text-secondary)"><?= date('M j, g:ia',strtotime($p['payment_date'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../shared/waiter_layout_end.php'; ?>
