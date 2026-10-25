<?php $page_title = 'New Order'; require_once __DIR__ . '/../shared/waiter_layout_start.php'; ?>

<div class="order-layout animate-in">
  <!-- Menu -->
  <div>
    <div class="card" style="margin-bottom:20px">
      <div class="card-body" style="padding:16px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
        <div class="search-bar" style="flex:1;min-width:200px">
          <span class="search-icon">🔍</span>
          <input type="text" id="menu-search" class="form-control" placeholder="Search menu..." style="padding-left:36px">
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap">
          <button class="btn btn-secondary btn-sm cat-filter active" data-cat="" onclick="filterCategory('')">All</button>
          <?php foreach ($categories as $cat): ?>
          <button class="btn btn-secondary btn-sm cat-filter" data-cat="<?= $cat['category_id'] ?>" onclick="filterCategory(<?= $cat['category_id'] ?>)">
            <?= htmlspecialchars($cat['category_name']) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="menu-grid">
      <?php
      $catIcons = [
        'Amuse-Bouche & Canapés' => '🥂',
        'Plats Principaux' => '🍽️',
        'Desserts & Pâtisserie' => '🍰',
        'Vins & Boissons' => '🍷',
        'Soupes & Salades' => '🥗',
        'Fromages' => '🧀',
        'Charcuterie' => '🥩',
        'Appetizers' => '🥗',
        'Main Course' => '🍽️',
        'Desserts' => '🍰',
        'Beverages' => '🍷',
        'Soups & Salads' => '🥣',
      ];
      foreach ($products as $product): $icon = $catIcons[$product['category_name'] ?? ''] ?? '🍽️'; ?>
      <div class="menu-item-wrap" data-category="<?= $product['category_id'] ?>" data-name="<?= strtolower(htmlspecialchars($product['product_name'])) ?>">
        <div class="menu-item" onclick="addToCart(<?= $product['product_id'] ?>,'<?= addslashes($product['product_name']) ?>',<?= $product['price'] ?>)">
          <?php if (!empty($product['image'])): ?>
            <div class="menu-item-img"><img src="<?= BASE_URL ?>/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>"></div>
          <?php else: ?>
            <div class="menu-item-icon"><?= $icon ?></div>
          <?php endif; ?>
          <div class="menu-item-name"><?= htmlspecialchars($product['product_name']) ?></div>
          <div style="font-size:0.72rem;color:var(--text-secondary);margin-top:2px"><?= htmlspecialchars($product['category_name']??'') ?></div>
          <div class="menu-item-price"><?= number_format($product['price'], 0) ?> MMK</div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Cart -->
  <div>
    <div class="order-cart">
      <div class="card-header">
        <div class="card-title">🛒 Order <span id="cart-count" style="background:var(--success);color:#fff;font-size:0.75rem;padding:2px 8px;border-radius:12px;margin-left:4px">0</span></div>
      </div>
      <form action="<?= BASE_URL ?>/waiter/save-order" method="POST" id="order-form">
        <div style="padding:16px;border-bottom:1px solid var(--border)">
          <div class="form-field" style="margin-bottom:12px">
            <label>🪑 Table</label>
              <select name="table_id" class="form-control no-icon" required>
              <option value="">Select table...</option>
              <?php foreach ($tables as $t): ?>
              <option value="<?= $t['table_id'] ?>">
                <?= htmlspecialchars($t['table_number']) ?> (<?= $t['location'] ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field">
            <label>📝 Notes</label>
            <textarea name="notes" class="form-control no-icon" rows="2" placeholder="Special requests..."></textarea>
          </div>
        </div>
        <div id="cart-items" class="cart-items">
          <div class="empty-state" style="padding:30px">
            <div class="empty-icon">🛒</div>
            <p>Tap items to add</p>
          </div>
        </div>
        <div class="cart-total">
          <div class="cart-total-row grand"><span>Total</span><span id="cart-total">0 MMK</span></div>
          <button type="submit" id="submit-order" class="btn btn-primary btn-lg" style="width:100%;margin-top:16px;justify-content:center" disabled>
            ✓ Place Order
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../shared/waiter_layout_end.php'; ?>
