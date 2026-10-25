<?php $page_title = 'Payments'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<?php if (!empty($unpaid)): ?>
<div class="card animate-in" style="margin-bottom:24px;border:2px solid var(--warning)">
  <div class="card-header" style="background:rgba(243,156,18,0.08)">
    <div class="card-title">⚠️ Pending Payments (<?= count($unpaid) ?>)</div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#Order</th><th>Table</th><th>Waiter</th><th>Amount</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($unpaid as $o): ?>
        <tr>
          <td><strong>#<?= $o['order_id'] ?></strong></td>
          <td>🪑 <?= htmlspecialchars($o['table_number']??'N/A') ?></td>
          <td><?= htmlspecialchars($o['waiter_name']??'N/A') ?></td>
          <td><strong style="color:var(--primary)"><?= number_format($o['total_amount'], 0) ?> MMK</strong></td>
          <td>
            <button onclick="openPaymentModal(<?= $o['order_id'] ?>,<?= $o['total_amount'] ?>)" class="btn btn-success btn-sm">💳 Process</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="card animate-in-delay-1">
  <div class="card-header">
    <div class="card-title">💳 Payment History (<?= count($payments) ?>)</div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#Pay</th><th>Order</th><th>Table</th><th>Method</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php if (empty($payments)): ?>
        <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">💳</div><h3>No payments yet</h3></div></td></tr>
        <?php else: ?>
        <?php foreach ($payments as $p): ?>
        <tr>
          <td><strong>#<?= $p['payment_id'] ?></strong></td>
          <td><a href="<?= BASE_URL ?>/admin/order/<?= $p['order_id'] ?>" style="color:var(--primary)">#<?= $p['order_id'] ?></a></td>
          <td><?= htmlspecialchars($p['table_number']??'N/A') ?></td>
          <td><?php $methods=['cash'=>'💵 Cash','credit_card'=>'💳 Credit','debit_card'=>'🏦 Debit','digital_wallet'=>'📱 Wallet']; echo $methods[$p['payment_method']]??$p['payment_method']; ?></td>
          <td><strong style="color:var(--success)"><?= number_format($p['amount_paid'], 0) ?> MMK</strong></td>
          <td><span class="badge badge-<?= $p['payment_status']==='completed'?'active':'pending' ?>"><?= ucfirst($p['payment_status']) ?></span></td>
          <td style="font-size:0.82rem;color:var(--text-secondary)"><?= date('M j, Y g:ia', strtotime($p['payment_date'])) ?></td>
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
            <label>Amount</label>
            <input type="number" name="amount_paid" id="pay-amount" step="0.01" class="form-control no-icon" required>
          </div>
          <div class="form-field">
            <label>Method</label>
            <select name="payment_method" class="form-control no-icon">
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
function openPaymentModal(id, amount) {
  document.getElementById('pay-order-id').value = id;
  document.getElementById('pay-amount').value = parseFloat(amount).toFixed(2);
  openModal('payment-modal');
}
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
