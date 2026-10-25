<?php
$admin_user = $_SESSION['admin'] ?? [];
$admin_name = $admin_user['name'] ?? 'Admin';
$current_path = $_SERVER['REQUEST_URI'] ?? '';
$initials = strtoupper(substr($admin_name,0,1));

// Count badges
$db = Database::getInstance();
$pending_orders = $db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE order_status='pending'")['cnt'] ?? 0;
$low_inv = $db->fetchOne("SELECT COUNT(*) as cnt FROM inventory WHERE quantity_in_stock <= reorder_level")['cnt'] ?? 0;

function isActive($path) {
  return strpos($_SERVER['REQUEST_URI'] ?? '', $path) !== false ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'Admin' ?> — RestaurantOS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<div class="app-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <a href="<?= BASE_URL ?>/admin/dashboard" class="sidebar-brand">
        <div class="brand-icon">🍽️</div>
        <div class="brand-text">
          <strong>RestaurantOS</strong>
          <span>Admin Panel</span>
        </div>
      </a>
    </div>
    <div class="sidebar-user">
      <div class="user-avatar"><?= $initials ?></div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($admin_name) ?></div>
        <div class="user-role">Administrator</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section">
        <div class="nav-section-title">Overview</div>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="nav-item <?= isActive('/admin/dashboard') ?>"><span class="icon">📊</span> Dashboard</a>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">Orders</div>
        <a href="<?= BASE_URL ?>/admin/orders" class="nav-item <?= isActive('/admin/orders') || isActive('/admin/add-order') || isActive('/admin/order/') ? 'active' : '' ?>">
          <span class="icon">📋</span> View Orders
          <?php if ($pending_orders > 0): ?><span class="badge"><?= $pending_orders ?></span><?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>/admin/add-order" class="nav-item <?= isActive('/admin/add-order') ?>"><span class="icon">➕</span> Add Order</a>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">Management</div>
        <a href="<?= BASE_URL ?>/admin/kitchen" class="nav-item <?= isActive('/admin/kitchen') ?>"><span class="icon">🔥</span> Kitchen</a>
        <a href="<?= BASE_URL ?>/admin/tables" class="nav-item <?= isActive('/admin/tables') ?>"><span class="icon">🪑</span> Tables</a>
        <a href="<?= BASE_URL ?>/admin/payments" class="nav-item <?= isActive('/admin/payments') ?>"><span class="icon">💳</span> Payments</a>
        <a href="<?= BASE_URL ?>/admin/products" class="nav-item <?= isActive('/admin/products') ?>"><span class="icon">🍕</span> Products & Categories</a>
        <a href="<?= BASE_URL ?>/admin/inventory" class="nav-item <?= isActive('/admin/inventory') ?>">
          <span class="icon">📦</span> Inventory
          <?php if ($low_inv > 0): ?><span class="badge"><?= $low_inv ?></span><?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>/admin/waiters" class="nav-item <?= isActive('/admin/waiters') ?>"><span class="icon">👨‍🍳</span> Waiters</a>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">Reports & Account</div>
        <a href="<?= BASE_URL ?>/admin/reports" class="nav-item <?= isActive('/admin/reports') ?>"><span class="icon">📈</span> Monthly Reports</a>
        <a href="<?= BASE_URL ?>/admin/profile" class="nav-item <?= isActive('/admin/profile') ?>"><span class="icon">👤</span> Profile</a>
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

  <!-- MAIN -->
  <div class="main-content">
    <div class="topbar">
      <div class="topbar-left">
        <div class="topbar-title"><?= $page_title ?? 'Dashboard' ?></div>
        <div class="topbar-subtitle"><?= date('l, F j, Y') ?></div>
      </div>
      <div class="topbar-right">
        <div class="topbar-time" id="live-clock"></div>
        <button class="topbar-notif" title="Notifications">🔔<span class="dot"></span></button>
        <button class="hamburger" id="hamburger">☰</button>
      </div>
    </div>
    <div class="page-content">
