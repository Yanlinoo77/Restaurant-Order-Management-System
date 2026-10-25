<?php $page_title = 'Order #' . ($order['order_id']??''); require_once __DIR__ . '/../shared/waiter_layout_start.php'; ?>

<?php if (!$order): ?>
<div class="card"><div class="empty-state"><div class="empty-icon">❌</div><h3>Order not found</h3><a href="<?= BASE_URL ?>/waiter/orders" class="btn btn-secondary" style="margin-top:16px">← Back</a></div></div>
<?php else: ?>

<div style="margin-bottom:20px" class="animate-in">
  <a href="<?= BASE_URL ?>/waiter/orders" class="btn btn-secondary">← Back to Orders</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px" class="animate-in-delay-1">
  <div>
    <div class="card" style="margin-bottom:24px">
      <div class="card-header">
        <div class="card-title">🛒 Order Items</div>
        <span class="badge badge-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
          <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($item['product_name']??'Item') ?></strong>
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
              <td style="padding:14px 16px"><strong style="color:var(--primary);font-size:1.1rem"><?= number_format($order['total_amount'], 0) ?> MMK</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Status Timeline -->
    <div class="card">
      <div class="card-header"><div class="card-title">📜 Order Timeline</div></div>
      <div class="card-body">
        <?php foreach ($history as $i => $h): ?>
        <div style="display:flex;gap:14px;<?= $i<count($history)-1?'margin-bottom:18px':'' ?>">
          <div style="width:32px;height:32px;border-radius:50%;background:var(--bg);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <?= ['pending'=>'⏳','preparing'=>'🔥','served'=>'✅','completed'=>'💚','cancelled'=>'❌'][$h['status']]??'📌' ?>
          </div>
          <div>
            <strong><?= ucfirst($h['status']) ?></strong>
            <div style="font-size:0.8rem;color:var(--text-secondary)"><?= date('g:i a, M j', strtotime($h['timestamp'])) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div>
    <!-- Order Details -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-header"><div class="card-title">ℹ️ Details</div></div>
      <div class="card-body">
        <?php foreach ([['🪑 Table',$order['table_number']??'N/A'],['📅 Time',date('g:i a M j',strtotime($order['order_date']))],['💳 Payment',ucfirst($order['payment_status'])]] as [$l,$v]): ?>
        <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-secondary)"><?= $l ?></span>
          <span style="font-weight:600"><?= htmlspecialchars($v) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if ($order['notes']): ?><div style="margin-top:12px;padding:10px;background:var(--bg);border-radius:var(--radius);font-size:0.85rem">📝 <?= htmlspecialchars($order['notes']) ?></div><?php endif; ?>
      </div>
    </div>

    <!-- PAYMENT FORM — only show when served and unpaid -->
    <?php if ($order['order_status'] === 'served' && $order['payment_status'] === 'unpaid'): ?>
    <div class="card" style="border:2px solid var(--success)">
      <div class="card-header" style="background:rgba(39,174,96,0.06)">
        <div class="card-title" style="color:var(--success)">💳 Process Payment</div>
      </div>
      <div class="card-body">
        <div style="background:rgba(39,174,96,0.08);border-radius:var(--radius);padding:14px;margin-bottom:18px;text-align:center">
          <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px">Amount Due</div>
          <div style="font-size:2rem;font-weight:800;color:var(--success)"><?= number_format($order['total_amount'], 0) ?> MMK</div>
        </div>
        <form action="<?= BASE_URL ?>/waiter/process-payment" method="POST" id="payment-form">
          <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
          <div class="form-field" style="margin-bottom:14px">
            <label>Payment Method</label>
            <select name="payment_method" class="form-field" required style="width:100%;padding:10px 14px;border:2px solid var(--border);border-radius:var(--radius);font-size:0.9rem;color:var(--text-primary);background:var(--bg);outline:none;font-family:inherit">
              <option value="cash">💵 Cash</option>
              <option value="card">💳 Card</option>
              <option value="digital_wallet">📱 Digital Wallet</option>
            </select>
          </div>
          <div class="form-field" style="margin-bottom:18px">
            <label>Amount Received ($)</label>
            <input type="number" name="amount_paid" step="0.01" min="<?= $order['total_amount'] ?>"
              value="<?= number_format($order['total_amount'],2,'.','') ?>"
              required
              style="width:100%;padding:10px 14px;border:2px solid var(--border);border-radius:var(--radius);font-size:1rem;font-weight:700;color:var(--text-primary);background:var(--bg);outline:none;font-family:inherit">
          </div>
          <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;font-size:1rem;padding:12px">
            ✅ Confirm Payment
          </button>
        </form>
      </div>
    </div>

    <?php elseif ($order['order_status'] === 'served' && $order['payment_status'] === 'paid'): ?>
    <div class="card" style="border:2px solid var(--success)">
      <div class="card-body" style="text-align:center;padding:24px">
        <div style="font-size:2.5rem;margin-bottom:10px">✅</div>
        <div style="font-weight:700;color:var(--success);font-size:1rem">Payment Received</div>
        <div style="color:var(--text-secondary);font-size:0.85rem;margin-top:6px"><?= number_format($order['total_amount'], 0) ?> MMK</div>
      </div>
    </div>

    <?php elseif ($order['order_status'] === 'completed'): ?>
    <div class="card">
      <div class="card-body" style="text-align:center;padding:24px">
        <div style="font-size:2.5rem;margin-bottom:10px">🏁</div>
        <div style="font-weight:700;color:var(--text-primary)">Order Completed</div>
        <div style="color:var(--text-secondary);font-size:0.85rem;margin-top:6px">Payment status: <?= ucfirst($order['payment_status']) ?></div>
      </div>
    </div>

    <?php elseif ($order['order_status'] === 'cancelled'): ?>
    <div class="card" style="border:2px solid var(--danger)">
      <div class="card-body" style="text-align:center;padding:24px">
        <div style="font-size:2.5rem;margin-bottom:10px">❌</div>
        <div style="font-weight:700;color:var(--danger)">Order Cancelled</div>
      </div>
    </div>

    <?php else: ?>
    <!-- Order still pending/preparing — no payment yet -->
    <div class="card">
      <div class="card-body" style="text-align:center;padding:24px">
        <div style="font-size:2.5rem;margin-bottom:10px">⏳</div>
        <div style="font-weight:700;color:var(--text-primary)">Awaiting Kitchen</div>
        <div style="color:var(--text-secondary);font-size:0.85rem;margin-top:6px">Payment can be processed once order is served</div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../shared/waiter_layout_end.php'; ?>
