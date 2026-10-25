<?php $page_title = 'Order #' . $order['order_id']; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<div style="margin-bottom:20px" class="animate-in">
  <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-secondary">← Back to Orders</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px" class="animate-in-delay-1 order-detail-grid">
  <!-- Order Items -->
  <div>
    <div class="card" style="margin-bottom:24px">
      <div class="card-header">
        <div class="card-title">🛒 Order Items</div>
        <span class="badge badge-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
          <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
              <td><strong><?= htmlspecialchars($item['product_name']??'Deleted Product') ?></strong>
                <?php if (!empty($item['special_request'])): ?>
                <div style="font-size:0.78rem;color:var(--primary)">📝 <?= htmlspecialchars($item['special_request']) ?></div>
                <?php endif; ?>
              </td>
              <td><?= $item['quantity'] ?>x</td>
              <td><?= number_format($item['item_price'], 0) ?> MMK</td>
              <td><strong><?= number_format($item['total_item_price'], 0) ?> MMK</strong></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="border-top:2px solid var(--border)">
              <td colspan="3" style="text-align:right;font-weight:700;padding:14px 16px">Total:</td>
              <td style="padding:14px 16px"><strong style="font-size:1.1rem;color:var(--primary)"><?= number_format($order['total_amount'], 0) ?> MMK</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Status History -->
    <div class="card">
      <div class="card-header"><div class="card-title">📜 Status History</div></div>
      <div class="card-body">
        <?php foreach ($history as $i => $h): ?>
        <div style="display:flex;gap:16px;align-items:flex-start;<?= $i<count($history)-1?'margin-bottom:20px':'' ?>">
          <div style="flex-shrink:0">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:1rem">
              <?= ['pending'=>'⏳','preparing'=>'🔥','served'=>'✅','completed'=>'💚','cancelled'=>'❌'][$h['status']] ?? '📌' ?>
            </div>
          </div>
          <div>
            <div style="font-weight:600"><?= ucfirst($h['status']) ?></div>
            <div style="font-size:0.8rem;color:var(--text-secondary)">By <?= ucfirst($h['changed_by_role']??'system') ?> · <?= date('M j, Y g:ia', strtotime($h['timestamp'])) ?></div>
            <?php if (!empty($h['notes'])): ?><div style="font-size:0.82rem;margin-top:4px"><?= htmlspecialchars($h['notes']) ?></div><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Order Info -->
  <div>
    <div class="card" style="margin-bottom:20px">
      <div class="card-header"><div class="card-title">ℹ️ Order Info</div></div>
      <div class="card-body">
        <?php $rows = [['🪑 Table',$order['table_number']??'N/A'],['👨‍🍳 Waiter',$order['waiter_name']??'N/A'],['📅 Date',date('M j, Y g:ia',strtotime($order['order_date']))],['💳 Payment',ucfirst($order['payment_status'])]]; ?>
        <?php foreach ($rows as [$label,$val]): ?>
        <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-secondary)"><?= $label ?></span>
          <span style="font-weight:600"><?= htmlspecialchars($val) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (!empty($order['notes'])): ?>
        <div style="margin-top:12px;padding:10px;background:var(--bg);border-radius:var(--radius);font-size:0.85rem">
          📝 <?= htmlspecialchars($order['notes']) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Payment Info -->
    <?php if ($payment): ?>
    <div class="card" style="margin-bottom:20px">
      <div class="card-header"><div class="card-title">💳 Payment</div></div>
      <div class="card-body">
        <div style="text-align:center">
          <div style="font-size:2rem;font-weight:800;color:var(--success)"><?= number_format($payment['amount_paid'], 0) ?> MMK</div>
          <div style="color:var(--text-secondary);margin-top:4px"><?= ucfirst(str_replace('_',' ',$payment['payment_method'])) ?></div>
          <div style="margin-top:12px"><span class="badge badge-active">Paid</span></div>
        </div>
      </div>
    </div>
    <?php elseif (in_array($order['order_status'], ['served','completed'])): ?>
    <div class="card">
      <div class="card-body" style="text-align:center">
        <div style="font-size:2rem;margin-bottom:8px">💳</div>
        <p style="color:var(--text-secondary);margin-bottom:16px">Payment not yet processed</p>
        <button onclick="openModal('payment-modal')" class="btn btn-success" style="width:100%">Process Payment</button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Current Status (view only) -->
    <div class="card">
      <div class="card-header"><div class="card-title">📌 Order Status</div></div>
      <div class="card-body" style="text-align:center;padding:20px">
        <?php
        $statusIcons  = ['pending'=>'⏳','preparing'=>'🔥','served'=>'✅','completed'=>'💚','cancelled'=>'❌'];
        $statusLabels = ['pending'=>'Pending','preparing'=>'Preparing','served'=>'Served','completed'=>'Completed','cancelled'=>'Cancelled'];
        $s = $order['order_status'];
        ?>
        <div style="font-size:2.5rem;margin-bottom:8px"><?= $statusIcons[$s] ?? '📌' ?></div>
        <span class="badge badge-<?= $s ?>" style="font-size:1rem;padding:8px 20px">
          <?= $statusLabels[$s] ?? ucfirst($s) ?>
        </span>
        <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:12px">
          Status is managed by kitchen &amp; waiter staff
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Payment Modal -->
<div class="modal-overlay" id="payment-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">💳 Process Payment — Order #<?= $order['order_id'] ?></div>
      <button type="button" class="modal-close" onclick="closeModal('payment-modal')">✕</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/process-payment" method="POST">
      <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
      <div class="modal-body">
        <div class="form-row cols-2">
          <div class="form-field">
            <label>Amount ($)</label>
            <input type="number" name="amount_paid" value="<?= $order['total_amount'] ?>" step="0.01" min="0" class="form-control no-icon" required>
          </div>
          <div class="form-field">
            <label>Payment Method</label>
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
        <button type="button" class="btn btn-secondary" onclick="closeModal('payment-modal')">✕ Cancel</button>
        <button type="submit" class="btn btn-success">✓ Confirm Payment</button>
      </div>
    </form>
  </div>
</div>

<style>
.order-detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
@media (max-width: 900px) { .order-detail-grid { grid-template-columns: 1fr; } }
</style>

<script>
// Status updates are handled by kitchen & waiter staff only
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
