<?php
$waiter_user = $_SESSION['waiter'] ?? [];
$waiter_name = $waiter_user['waiter_name'] ?? 'Waiter';
$current_path = $_SERVER['REQUEST_URI'] ?? '';
$initials = strtoupper(substr($waiter_name,0,1));

$db = Database::getInstance();
$my_pending = $db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE waiter_id=? AND order_status='pending'", [$_SESSION['waiter_id']], 'i')['cnt'] ?? 0;
$ready_orders = $db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE waiter_id=? AND order_status='served' AND payment_status='unpaid'", [$_SESSION['waiter_id']], 'i')['cnt'] ?? 0;

function isActiveW($path) {
  return strpos($_SERVER['REQUEST_URI'] ?? '', $path) !== false ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'Waiter' ?> — RestaurantOS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<style>
  .sidebar { background: linear-gradient(180deg, #1A4731 0%, #0F2A1D 100%); }
  .sidebar-brand .brand-icon { background: linear-gradient(135deg, #27AE60, #1E8449); box-shadow: 0 4px 12px rgba(39,174,96,0.3); }
  .nav-item.active { background: linear-gradient(135deg, #27AE60, #1E8449); box-shadow: 0 4px 12px rgba(39,174,96,0.3); }
  .nav-item:hover { background: rgba(39,174,96,0.15); }
  .topbar-notif:hover { background: #27AE60; }
</style>
</head>
<body>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<div class="app-layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <a href="<?= BASE_URL ?>/waiter/dashboard" class="sidebar-brand">
        <div class="brand-icon">🧑‍🍳</div>
        <div class="brand-text">
          <strong>RestaurantOS</strong>
          <span>Waiter Portal</span>
        </div>
      </a>
    </div>
    <div class="sidebar-user">
      <div class="user-avatar" style="background:linear-gradient(135deg,#27AE60,#1E8449)"><?= $initials ?></div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($waiter_name) ?></div>
        <div class="user-role">Waiter</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section">
        <div class="nav-section-title">Overview</div>
        <a href="<?= BASE_URL ?>/waiter/dashboard" class="nav-item <?= isActiveW('/waiter/dashboard') ?>"><span class="icon">📊</span> Dashboard</a>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">Orders</div>
        <a href="<?= BASE_URL ?>/waiter/orders" class="nav-item <?= isActiveW('/waiter/orders') || isActiveW('/waiter/order/') ? 'active' : '' ?>">
          <span class="icon">📋</span> All Orders
          <?php if ($my_pending > 0): ?><span class="badge"><?= $my_pending ?></span><?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>/waiter/new-order" class="nav-item <?= isActiveW('/waiter/new-order') ?>"><span class="icon">➕</span> New Order</a>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">View</div>
        <a href="<?= BASE_URL ?>/waiter/tables" class="nav-item <?= isActiveW('/waiter/tables') ?>"><span class="icon">🪑</span> Tables</a>
        <a href="<?= BASE_URL ?>/waiter/payment" class="nav-item <?= isActiveW('/waiter/payment') ?>">
          <span class="icon">💳</span> Payments
          <?php if ($ready_orders > 0): ?><span class="badge"><?= $ready_orders ?></span><?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>/waiter/kitchen" class="nav-item <?= isActiveW('/waiter/kitchen') ?>"><span class="icon">🔥</span> Kitchen Display</a>
      </div>
    </nav>
    <div class="sidebar-footer">
      <form action="<?= BASE_URL ?>/logout" method="POST">
        <button type="submit" class="nav-item nav-logout" style="color:rgba(255,255,255,0.6)">
          <span class="icon">🚪</span> Sign Out
        </button>
      </form>
    </div>
  </aside>

  <div class="main-content">
    <div class="topbar">
      <div class="topbar-left">
        <div class="topbar-title"><?= $page_title ?? 'Dashboard' ?></div>
        <div class="topbar-subtitle"><?= date('l, F j, Y') ?></div>
      </div>
      <div class="topbar-right">
        <div class="topbar-time" id="live-clock"></div>
        <?php if ($ready_orders > 0): ?>
        <div style="background:rgba(39,174,96,0.1);color:var(--success);padding:6px 14px;border-radius:20px;font-size:0.82rem;font-weight:600;animation:pulse 2s infinite">
          ✅ <?= $ready_orders ?> order<?= $ready_orders>1?'s':'' ?> ready to serve
        </div>
        <?php endif; ?>
        <button class="hamburger" id="hamburger">☰</button>
      </div>
    </div>
    <div class="page-content">
