<?php $page_title = 'Profile'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<div class="profile-header animate-in">
  <div class="profile-avatar"><?= strtoupper(substr($admin['name'],0,1)) ?></div>
  <div class="profile-info">
    <h2><?= htmlspecialchars($admin['name']) ?></h2>
    <p><?= htmlspecialchars($admin['email']) ?></p>
    <p style="margin-top:8px;font-size:0.8rem;opacity:0.7">Administrator · Joined <?= date('M Y', strtotime($admin['created_at'])) ?></p>
  </div>
</div>

<div class="card animate-in-delay-1">
  <div class="card-header"><div class="card-title">✏️ Edit Profile</div></div>
  <div class="card-body">
    <form action="<?= BASE_URL ?>/admin/update-profile" method="POST">
      <div class="form-row cols-2">
        <div class="form-field">
          <label>Full Name</label>
          <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" class="form-control no-icon" required>
        </div>
        <div class="form-field">
          <label>Email Address</label>
          <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="form-control no-icon" required>
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="form-field">
          <label>Phone Number</label>
          <input type="text" name="phone_number" value="<?= htmlspecialchars($admin['phone_number']??'') ?>" class="form-control no-icon">
        </div>
        <div class="form-field">
          <label>New Password <span style="color:var(--text-secondary);font-weight:400">(leave blank to keep)</span></label>
          <input type="password" name="password" class="form-control no-icon" placeholder="Enter new password">
        </div>
      </div>
      <button type="submit" class="btn btn-primary">💾 Save Changes</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
