<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= ucfirst($role ?? '') ?> Login — RestaurantOS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<div class="login-wrapper">
  <!-- Left Panel -->
  <div class="login-left">
    <div class="login-brand">
      <div class="logo-icon">🍽️</div>
      <h1>RestaurantOS</h1>
      <p>Streamlined Order Management</p>
    </div>
    <?php if (!$role): ?>
    <div class="login-roles">
      <a href="<?= BASE_URL ?>/login/admin" class="role-card">
        <div class="icon">👨‍💼</div>
        <div class="label">Admin</div>
      </a>
      <a href="<?= BASE_URL ?>/login/waiter" class="role-card">
        <div class="icon">🧑‍🍳</div>
        <div class="label">Waiter</div>
      </a>
      <a href="<?= BASE_URL ?>/login/kitchen" class="role-card">
        <div class="icon">🔥</div>
        <div class="label">Kitchen</div>
      </a>
    </div>
    <?php else: ?>
    <div style="text-align:center;color:rgba(255,255,255,0.7);animation:fadeInUp 0.8s ease 0.3s both">
      <p style="font-size:1.1rem;margin-bottom:20px">Welcome back!</p>
      <p style="font-size:0.9rem;opacity:0.7">Sign in to access your<br><?= ucfirst($role ?? '') ?> dashboard.</p>
    </div>
    <div class="login-roles" style="margin-top:40px">
      <?php $roles = ['admin'=>'👨‍💼','waiter'=>'🧑‍🍳','kitchen'=>'🔥']; ?>
      <?php foreach($roles as $r => $icon): ?>
      <a href="<?= BASE_URL ?>/login/<?= $r ?>" class="role-card" style="<?= $r===$role?'border-color:var(--primary);background:rgba(255,107,53,0.2)':'' ?>">
        <div class="icon"><?= $icon ?></div>
        <div class="label"><?= ucfirst($r) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Right Panel -->
  <div class="login-right">
    <div class="login-card">
      <?php if ($role): ?>
      <span class="role-badge <?= $role ?>">
        <?= $role==='admin'?'👨‍💼':($role==='waiter'?'🧑‍🍳':'🔥') ?>
        <?= ucfirst($role) ?> Portal
      </span>
      <h2>Welcome back</h2>
      <p class="subtitle">Sign in to your account to continue</p>

      <?php if (!empty($error)): ?>
      <div class="alert alert-danger" data-dismiss="4000">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/login" method="POST">
        <input type="hidden" name="role" value="<?= $role ?>">
        <div class="form-group">
          <label>Email Address</label>
          <div class="input-wrap">
            <span class="icon">📧</span>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required autocomplete="email">
          </div>
        </div>
        <div class="form-group">
          <label>Password</label>
          <div class="input-wrap">
            <span class="icon">🔒</span>
            <input type="password" name="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
          </div>
        </div>
        <button type="submit" class="btn-login">Sign In →</button>
      </form>



      <?php else: ?>
      <h2>RestaurantOS</h2>
      <p class="subtitle">Choose your role to get started</p>
      <div style="display:flex;flex-direction:column;gap:12px;margin-top:24px">
        <?php foreach(['admin'=>['👨‍💼','Admin Dashboard','Manage everything'],'waiter'=>['🧑‍🍳','Waiter Portal','Manage orders & tables'],'kitchen'=>['🔥','Kitchen Display','View & process orders']] as $r=>[$ic,$lab,$desc]): ?>
        <a href="<?= BASE_URL ?>/login/<?= $r ?>" style="display:flex;align-items:center;gap:16px;padding:16px;background:var(--bg);border-radius:var(--radius);text-decoration:none;transition:var(--transition);border:2px solid var(--border)" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
          <span style="font-size:1.8rem"><?= $ic ?></span>
          <div><strong style="color:var(--text-primary)"><?= $lab ?></strong><br><span style="font-size:0.8rem;color:var(--text-secondary)"><?= $desc ?></span></div>
          <span style="margin-left:auto;color:var(--text-secondary)">→</span>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
// Add ripple effect to login button
document.querySelector('.btn-login')?.addEventListener('click', function(e) {
  const btn = this;
  const ripple = document.createElement('span');
  ripple.className = 'ripple';
  const rect = btn.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  ripple.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px;`;
  btn.appendChild(ripple);
  ripple.addEventListener('animationend', () => ripple.remove());
});
</script>
</body>
</html>
