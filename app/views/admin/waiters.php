<?php $page_title = 'Waiters Management'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<div style="display:flex;justify-content:space-between;margin-bottom:24px" class="animate-in">
  <div></div>
  <button class="btn btn-primary" onclick="openModal('add-waiter-modal')">➕ Add Waiter</button>
</div>

<div class="card animate-in-delay-1">
  <div class="card-header">
    <div class="card-title">👨‍🍳 Waiters (<?= count($waiters) ?>)</div>
    <div class="search-bar">
      <span class="search-icon">🔍</span>
      <input type="text" id="table-search" class="form-control no-icon" placeholder="Search waiters..." style="padding-left:36px;width:200px">
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($waiters as $w): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700"><?= strtoupper(substr($w['waiter_name'],0,1)) ?></div>
              <strong><?= htmlspecialchars($w['waiter_name']) ?></strong>
            </div>
          </td>
          <td><?= htmlspecialchars($w['email']) ?></td>
          <td><?= htmlspecialchars($w['phone_number'] ?? '—') ?></td>
          <td><?= $w['total_orders'] ?></td>
          <td><span class="badge badge-<?= $w['status'] ?>"><?= ucfirst($w['status']) ?></span></td>
          <td>
            <div style="display:flex;gap:6px">
              <button onclick="editWaiter(<?= $w['waiter_id'] ?>,'<?= addslashes($w['waiter_name']) ?>','<?= addslashes($w['email']) ?>','<?= addslashes($w['phone_number']??'') ?>','<?= $w['status'] ?>')" class="btn btn-secondary btn-sm btn-icon" data-tooltip="Edit">✏️</button>
              <?php if ($w['status'] === 'active'): ?>
              <form action="<?= BASE_URL ?>/admin/delete-waiter/<?= $w['waiter_id'] ?>" method="POST" style="display:inline">
                <button type="submit" class="btn btn-danger btn-sm btn-icon" data-confirm="Deactivate this waiter?" data-tooltip="Deactivate">🚫</button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add/Edit Waiter Modal -->
<div class="modal-overlay" id="add-waiter-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="waiter-modal-title">➕ Add Waiter</div>
      <button class="modal-close" onclick="closeModal('add-waiter-modal')">✕</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/save-waiter" method="POST">
      <input type="hidden" name="waiter_id" id="edit-waiter-id">
      <div class="modal-body">
        <div class="form-row cols-2">
          <div class="form-field">
            <label>Full Name</label>
            <input type="text" name="waiter_name" id="edit-waiter-name" class="form-control no-icon" required>
          </div>
          <div class="form-field">
            <label>Phone</label>
            <input type="text" name="phone_number" id="edit-waiter-phone" class="form-control no-icon">
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-field">
            <label>Email</label>
            <input type="email" name="email" id="edit-waiter-email" class="form-control no-icon" required>
          </div>
          <div class="form-field">
            <label>Status</label>
            <select name="status" id="edit-waiter-status" class="form-control no-icon">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="form-field" id="waiter-pass-wrap">
          <label>Password <span style="color:var(--text-secondary);font-weight:400">(leave blank to keep)</span></label>
          <input type="password" name="password" id="edit-waiter-pass" class="form-control no-icon">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('add-waiter-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Waiter</button>
      </div>
    </form>
  </div>
</div>

<script>
function editWaiter(id, name, email, phone, status) {
  document.getElementById('waiter-modal-title').textContent = '✏️ Edit Waiter';
  document.getElementById('edit-waiter-id').value = id;
  document.getElementById('edit-waiter-name').value = name;
  document.getElementById('edit-waiter-email').value = email;
  document.getElementById('edit-waiter-phone').value = phone;
  document.getElementById('edit-waiter-status').value = status;
  document.getElementById('edit-waiter-pass').required = false;
  openModal('add-waiter-modal');
}
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
