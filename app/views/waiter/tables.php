<?php $page_title = 'Tables View'; require_once __DIR__ . '/../shared/waiter_layout_start.php'; ?>

<div style="display:flex;gap:16px;margin-bottom:20px" class="animate-in">
  <div style="background:rgba(39,174,96,0.1);color:var(--success);padding:8px 16px;border-radius:20px;font-weight:600">
    🟢 Available: <?= count(array_filter($tables, fn($t)=>!$t['is_occupied'])) ?>
  </div>
  <div style="background:rgba(231,76,60,0.1);color:var(--danger);padding:8px 16px;border-radius:20px;font-weight:600">
    🔴 Occupied: <?= count(array_filter($tables, fn($t)=>$t['is_occupied'])) ?>
  </div>
</div>

<div class="table-grid animate-in-delay-1">
  <?php foreach ($tables as $i => $table): ?>
  <div class="table-card <?= $table['is_occupied']?'occupied':'free' ?> animate-in" style="animation-delay:<?= $i*0.04 ?>s">
    <div class="table-icon"><?= $table['is_occupied']?'🔴':'🟢' ?></div>
    <div class="table-number"><?= htmlspecialchars($table['table_number']) ?></div>
    <div class="table-info">👥 <?= $table['capacity'] ?> seats · <?= $table['location'] ?></div>
    <div class="table-status" style="margin-top:12px">
      <?php if ($table['is_occupied']): ?>
        <span class="badge badge-unpaid">Occupied</span>
        <?php if ($table['order_id']): ?>
          <div style="margin-top:10px">
            <a href="<?= BASE_URL ?>/waiter/order/<?= $table['order_id'] ?>" class="btn btn-info btn-sm" style="display:block;text-align:center">
              <?= $table['waiter_id']==$_SESSION['waiter_id']?'My Order':'View Order' ?>
            </a>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <span class="badge badge-active">Available</span>
        <div style="margin-top:10px">
          <a href="<?= BASE_URL ?>/waiter/new-order" class="btn btn-primary btn-sm" style="display:block;text-align:center">➕ Take Order</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../shared/waiter_layout_end.php'; ?>
