<?php $page_title = 'Table Management'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px" class="animate-in">
  <div>
    <span style="color:var(--text-secondary)">
      🟢 Available: <?= count(array_filter($tables, fn($t) => !$t['is_occupied'])) ?>
      &nbsp;🔴 Occupied: <?= count(array_filter($tables, fn($t) => $t['is_occupied'])) ?>
      &nbsp;📊 Total: <?= count($tables) ?>
    </span>
  </div>
  <button class="btn btn-primary" onclick="openModal('add-table-modal')">➕ Add Table</button>
</div>

<div class="table-grid animate-in-delay-1">
  <?php foreach ($tables as $i => $table): ?>
  <div class="table-card <?= $table['is_occupied'] ? 'occupied' : 'free' ?> animate-in" style="animation-delay:<?= $i*0.04 ?>s">
    <div class="table-icon"><?= $table['is_occupied'] ? '🔴' : '🟢' ?></div>
    <div class="table-number"><?= htmlspecialchars($table['table_number']) ?></div>
    <div class="table-info">👥 Cap: <?= $table['capacity'] ?> · <?= htmlspecialchars($table['location']) ?></div>
    <div class="table-status">
      <?php if ($table['is_occupied']): ?>
        <span class="badge badge-unpaid">Occupied</span>
        <?php if ($table['order_id']): ?>
        <div style="margin-top:8px">
          <a href="<?= BASE_URL ?>/admin/order/<?= $table['order_id'] ?>" class="btn btn-info btn-sm">View Order</a>
        </div>
        <?php endif; ?>
      <?php else: ?>
        <span class="badge badge-active">Available</span>
        <div style="margin-top:8px;display:flex;gap:6px;justify-content:center">
          <button class="btn btn-secondary btn-sm" onclick="editTable(<?= $table['table_id'] ?>, '<?= addslashes($table['table_number']) ?>', <?= $table['capacity'] ?>, '<?= addslashes($table['location']) ?>')">✏️ Edit</button>
          <form action="<?= BASE_URL ?>/admin/delete-table/<?= $table['table_id'] ?>" method="POST" style="display:inline">
            <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this table?">🗑️</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Add/Edit Table Modal -->
<div class="modal-overlay" id="add-table-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="table-modal-title">➕ Add Table</div>
      <button class="modal-close" onclick="closeModal('add-table-modal')">✕</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/save-table" method="POST">
      <input type="hidden" name="table_id" id="edit-table-id" value="">
      <div class="modal-body">
        <div class="form-row cols-2">
          <div class="form-field">
            <label>Table Number</label>
            <input type="text" name="table_number" id="edit-table-number" class="form-control no-icon" placeholder="e.g. T-11" required>
          </div>
          <div class="form-field">
            <label>Capacity</label>
            <input type="number" name="capacity" id="edit-table-capacity" class="form-control no-icon" min="1" max="20" value="4" required>
          </div>
        </div>
        <div class="form-field">
          <label>Location</label>
          <select name="location" id="edit-table-location" class="form-control no-icon">
            <option>Main Hall</option>
            <option>Window</option>
            <option>Patio</option>
            <option>Private Room</option>
            <option>Bar Area</option>
            <option>Event Space</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('add-table-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Table</button>
      </div>
    </form>
  </div>
</div>

<script>
function editTable(id, number, cap, loc) {
  document.getElementById('table-modal-title').textContent = '✏️ Edit Table';
  document.getElementById('edit-table-id').value = id;
  document.getElementById('edit-table-number').value = number;
  document.getElementById('edit-table-capacity').value = cap;
  document.getElementById('edit-table-location').value = loc;
  openModal('add-table-modal');
}
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
