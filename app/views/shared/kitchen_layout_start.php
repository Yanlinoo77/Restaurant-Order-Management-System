<?php
$kitchen_user = $_SESSION['kitchen'] ?? [];
$kitchen_name = $kitchen_user['name'] ?? 'Kitchen';
$initials = strtoupper(substr($kitchen_name,0,1));
$db = Database::getInstance();
$pending_cnt = $db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE order_status='pending'")['cnt'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'Kitchen' ?> — RestaurantOS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<style>
  body { background: #0F1117; }
  .sidebar { background: linear-gradient(180deg, #1A0A00 0%, #2D1200 100%); }
  .sidebar-brand .brand-icon { background: linear-gradient(135deg, #F39C12, #D68910); box-shadow: 0 4px 12px rgba(243,156,18,0.3); }
  .nav-item.active { background: linear-gradient(135deg, #F39C12, #D68910); box-shadow: 0 4px 12px rgba(243,156,18,0.3); }
  .nav-item:hover { background: rgba(243,156,18,0.15); }
  .main-content { background: #0F1117; }
  .topbar { background: #1A1D2E; border-bottom-color: #2D2F45; }
  .topbar-title { color: #fff; }
  .topbar-subtitle { color: rgba(255,255,255,0.5); }
  .topbar-time { background: #2D2F45; color: rgba(255,255,255,0.8); }
  .page-content { background: #0F1117; }
  .kitchen-main { padding: 24px; }
</style>
</head>
<body>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<div class="app-layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <a href="<?= BASE_URL ?>/kitchen/dashboard" class="sidebar-brand">
        <div class="brand-icon">🔥</div>
        <div class="brand-text">
          <strong>RestaurantOS</strong>
          <span>Kitchen Display</span>
        </div>
      </a>
    </div>
    <div class="sidebar-user">
      <div class="user-avatar" style="background:linear-gradient(135deg,#F39C12,#D68910)"><?= $initials ?></div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($kitchen_name) ?></div>
        <div class="user-role">Kitchen Staff</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section">
        <div class="nav-section-title">Kitchen</div>
        <a href="<?= BASE_URL ?>/kitchen/dashboard" class="nav-item active">
          <span class="icon">📋</span> Order Board
          <?php if ($pending_cnt > 0): ?><span class="badge"><?= $pending_cnt ?></span><?php endif; ?>
        </a>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">Quick Stats</div>
        <div style="padding:12px 10px">
          <?php
          $completed_today = $db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE order_status='completed' AND DATE(order_date)=CURDATE()")['cnt']??0;
          $prep_count = $db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE order_status='preparing'")['cnt']??0;
          ?>
          <div style="background:rgba(39,174,96,0.15);border-radius:var(--radius);padding:12px;margin-bottom:8px;text-align:center">
            <div style="font-size:1.5rem;font-weight:800;color:#27AE60"><?= $completed_today ?></div>
            <div style="font-size:0.72rem;color:rgba(255,255,255,0.5)">Completed Today</div>
          </div>
          <div style="background:rgba(52,152,219,0.15);border-radius:var(--radius);padding:12px;text-align:center">
            <div style="font-size:1.5rem;font-weight:800;color:#3498DB"><?= $prep_count ?></div>
            <div style="font-size:0.72rem;color:rgba(255,255,255,0.5)">In Progress</div>
          </div>
        </div>
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
        <div class="topbar-title"><?= $page_title ?? 'Kitchen Display' ?></div>
        <div class="topbar-subtitle">Auto-refreshes every 20 seconds · <?= date('l, F j') ?></div>
      </div>
      <div class="topbar-right">
        <div class="topbar-time" id="live-clock"></div>
        <div id="refresh-indicator" style="font-size:0.8rem;color:rgba(255,255,255,0.5)">🔄 <span id="refresh-countdown">20</span>s</div>
        <button onclick="location.reload()" class="btn btn-secondary btn-sm">🔄 Refresh</button>
        <button class="hamburger" id="hamburger" style="color:#fff">☰</button>
      </div>
    </div>
    <div class="page-content">
