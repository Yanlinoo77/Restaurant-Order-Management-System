<?php $page_title = 'Kitchen Order Board'; require_once __DIR__ . '/../shared/kitchen_layout_start.php'; ?>

<style>
.k-board { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
.k-col { border-radius: 16px; overflow: hidden; min-height: 200px; }
.k-header { padding: 16px 20px; font-weight: 800; font-size: 1rem; display: flex; align-items: center; justify-content: space-between; }
.k-header.pending { background: linear-gradient(135deg, #B7791F, #F6AD55); color: #fff; }
.k-header.preparing { background: linear-gradient(135deg, #2B6CB0, #4299E1); color: #fff; }
.k-header.ready { background: linear-gradient(135deg, #276749, #48BB78); color: #fff; }
.k-cards { padding: 12px; display: flex; flex-direction: column; gap: 10px; background: #1A1D2E; min-height: 100px; }
.k-card {
  background: #0F1117;
  border-radius: 12px;
  padding: 16px;
  border-left: 4px solid var(--border);
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
}
.k-card::after { content: ''; position: absolute; inset: 0; opacity: 0; transition: opacity 0.2s; background: rgba(255,255,255,0.02); pointer-events: none; }
.k-card:hover::after { opacity: 1; }
.k-card:hover { transform: translateX(3px); }
.k-card.pending { border-left-color: #F6AD55; }
.k-card.preparing { border-left-color: #4299E1; }
.k-card.served { border-left-color: #48BB78; }
.k-card-num { font-size: 1.3rem; font-weight: 900; color: #fff; }
.k-card-table { font-size: 0.8rem; color: rgba(255,255,255,0.5); margin-top: 2px; }
.k-card-time { font-size: 0.78rem; }
.k-card-waiter { font-size: 0.78rem; color: rgba(255,255,255,0.4); margin: 6px 0; }
.k-badge { display: inline-block; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8); padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.k-actions { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; position: relative; z-index: 2; }
.k-btn { padding: 7px 14px; border-radius: 8px; border: none; cursor: pointer; font-size: 0.8rem; font-weight: 700; transition: all 0.2s; flex: 1; position: relative; z-index: 2; }
.k-btn:hover { transform: translateY(-1px); filter: brightness(1.15); }
.k-btn.start { background: linear-gradient(135deg, #4299E1, #2B6CB0); color: #fff; }
.k-btn.done { background: linear-gradient(135deg, #48BB78, #276749); color: #fff; }
.k-btn.cancel { background: rgba(231,76,60,0.2); color: #E74C3C; border: 1px solid rgba(231,76,60,0.3); }
.k-btn.view { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); }
.timer-badge { padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
.timer-badge.ok { background: rgba(39,174,96,0.2); color: #48BB78; }
.timer-badge.warn { background: rgba(243,156,18,0.2); color: #F6AD55; }
.timer-badge.urgent { background: rgba(231,76,60,0.2); color: #FC8181; animation: pulse 1s infinite; }
@media (max-width: 900px) { .k-board { grid-template-columns: 1fr; } }
</style>

<!-- Stats Row -->
<div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
  <div style="background:#1A1D2E;border-radius:12px;padding:14px 22px;flex:1;min-width:140px;text-align:center;border:1px solid #2D2F45">
    <div style="font-size:1.8rem;font-weight:800;color:#F6AD55"><?= $stats['pending'] ?></div>
    <div style="font-size:0.8rem;color:rgba(255,255,255,0.5);margin-top:2px">⏳ Pending</div>
  </div>
  <div style="background:#1A1D2E;border-radius:12px;padding:14px 22px;flex:1;min-width:140px;text-align:center;border:1px solid #2D2F45">
    <div style="font-size:1.8rem;font-weight:800;color:#4299E1"><?= $stats['preparing'] ?></div>
    <div style="font-size:0.8rem;color:rgba(255,255,255,0.5);margin-top:2px">🔥 In Progress</div>
  </div>
  <div style="background:#1A1D2E;border-radius:12px;padding:14px 22px;flex:1;min-width:140px;text-align:center;border:1px solid #2D2F45">
    <div style="font-size:1.8rem;font-weight:800;color:#48BB78"><?= $stats['completed_today'] ?></div>
    <div style="font-size:0.8rem;color:rgba(255,255,255,0.5);margin-top:2px">✅ Done Today</div>
  </div>
  <div style="background:#1A1D2E;border-radius:12px;padding:14px 22px;flex:1;min-width:140px;text-align:center;border:1px solid #2D2F45">
    <div style="font-size:1.8rem;font-weight:800;color:rgba(255,255,255,0.7)" id="live-clock-big"></div>
    <div style="font-size:0.8rem;color:rgba(255,255,255,0.5);margin-top:2px">🕐 Current Time</div>
  </div>
</div>

<!-- Kanban Board -->
<div class="k-board">
  <!-- PENDING -->
  <div class="k-col">
    <div class="k-header pending">⏳ New Orders <span style="background:rgba(0,0,0,0.2);padding:3px 12px;border-radius:20px"><?= count($pending) ?></span></div>
    <div class="k-cards">
      <?php if (empty($pending)): ?>
      <div style="text-align:center;padding:40px;color:rgba(255,255,255,0.3)">
        <div style="font-size:2.5rem;margin-bottom:8px">✅</div>
        All caught up!
      </div>
      <?php endif; ?>
      <?php foreach ($pending as $order):
        $mins = round((time() - strtotime($order['order_date'])) / 60);
        $timerClass = $mins < 5 ? 'ok' : ($mins < 10 ? 'warn' : 'urgent');
      ?>
      <div class="k-card pending">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
          <div>
            <div class="k-card-num">🪑 <?= htmlspecialchars($order['table_number']??'#'.$order['order_id']) ?></div>
            <div class="k-card-table">Order #<?= $order['order_id'] ?></div>
          </div>
          <span class="timer-badge <?= $timerClass ?>">⏱️ <?= $mins ?>m</span>
        </div>
        <div class="k-card-waiter">👨‍🍳 <?= htmlspecialchars($order['waiter_name']??'') ?></div>
        <span class="k-badge"><?= $order['item_count'] ?> items</span>
        <div class="k-actions">
          <button class="k-btn view" onclick="viewOrderItems(<?= $order['order_id'] ?>,'<?= addslashes($order['table_number']??'') ?>')">🔍 View</button>
          <button class="k-btn start" onclick="kitchenUpdateStatus(<?= $order['order_id'] ?>,'preparing',this)">🔥 Start</button>
          <button class="k-btn cancel" onclick="kitchenUpdateStatus(<?= $order['order_id'] ?>,'cancelled',this)">✕</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- PREPARING -->
  <div class="k-col">
    <div class="k-header preparing">🔥 Preparing <span style="background:rgba(0,0,0,0.2);padding:3px 12px;border-radius:20px"><?= count($preparing) ?></span></div>
    <div class="k-cards">
      <?php if (empty($preparing)): ?>
      <div style="text-align:center;padding:40px;color:rgba(255,255,255,0.3)">
        <div style="font-size:2.5rem;margin-bottom:8px">👨‍🍳</div>
        Nothing cooking
      </div>
      <?php endif; ?>
      <?php foreach ($preparing as $order):
        $mins = round((time() - strtotime($order['order_date'])) / 60);
        $timerClass = $mins < 10 ? 'ok' : ($mins < 20 ? 'warn' : 'urgent');
      ?>
      <div class="k-card preparing">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
          <div>
            <div class="k-card-num">🪑 <?= htmlspecialchars($order['table_number']??'#'.$order['order_id']) ?></div>
            <div class="k-card-table">Order #<?= $order['order_id'] ?></div>
          </div>
          <span class="timer-badge <?= $timerClass ?>">⏱️ <?= $mins ?>m</span>
        </div>
        <div class="k-card-waiter">👨‍🍳 <?= htmlspecialchars($order['waiter_name']??'') ?></div>
        <span class="k-badge"><?= $order['item_count'] ?> items</span>
        <div class="k-actions">
          <button class="k-btn view" onclick="viewOrderItems(<?= $order['order_id'] ?>,'<?= addslashes($order['table_number']??'') ?>')">🔍 View</button>
          <button class="k-btn done" onclick="kitchenUpdateStatus(<?= $order['order_id'] ?>,'served',this)">✅ Ready</button>
          <button class="k-btn cancel" onclick="kitchenUpdateStatus(<?= $order['order_id'] ?>,'cancelled',this)">✕</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- SERVED/DONE -->
  <div class="k-col">
    <div class="k-header ready">✅ Ready / Served <span style="background:rgba(0,0,0,0.2);padding:3px 12px;border-radius:20px"><?= count($ready) ?></span></div>
    <div class="k-cards">
      <?php if (empty($ready)): ?>
      <div style="text-align:center;padding:40px;color:rgba(255,255,255,0.3)">
        <div style="font-size:2.5rem;margin-bottom:8px">🍽️</div>
        Nothing served yet
      </div>
      <?php endif; ?>
      <?php foreach ($ready as $order): ?>
      <div class="k-card served" style="opacity:0.75">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
          <div>
            <div class="k-card-num">🪑 <?= htmlspecialchars($order['table_number']??'#'.$order['order_id']) ?></div>
            <div class="k-card-table">Order #<?= $order['order_id'] ?></div>
          </div>
          <span style="background:rgba(72,187,120,0.2);color:#48BB78;padding:3px 8px;border-radius:6px;font-size:0.75rem;font-weight:700">Served</span>
        </div>
        <div class="k-card-waiter">👨‍🍳 <?= htmlspecialchars($order['waiter_name']??'') ?></div>
        <div style="margin-top:8px">
          <button class="k-btn view" onclick="viewOrderItems(<?= $order['order_id'] ?>,'<?= addslashes($order['table_number']??'') ?>')">🔍 View Items</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
// Big clock
function updateBigClock() {
  const el = document.getElementById('live-clock-big');
  if (el) el.textContent = new Date().toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
}
setInterval(updateBigClock, 1000);
updateBigClock();
</script>

<?php require_once __DIR__ . '/../shared/kitchen_layout_end.php'; ?>
