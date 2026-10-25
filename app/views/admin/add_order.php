<?php $page_title = 'Add New Order'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<div class="order-layout animate-in">
  <!-- Left: Menu -->
  <div>
    <!-- Filters -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-body" style="padding:16px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
        <div class="search-bar" style="flex:1;min-width:200px">
          <span class="search-icon">🔍</span>
          <input type="text" id="menu-search" class="form-control" placeholder="Search menu items..." style="padding-left:36px">
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

    <!-- Menu Grid -->
    <div class="menu-grid">
      <?php
      // Category-based emoji icons for European restaurant
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
      foreach ($products as $product):
        $icon = $catIcons[$product['category_name'] ?? ''] ?? '🍽️';
      ?>
      <div class="menu-item-wrap" data-category="<?= $product['category_id'] ?>" data-name="<?= strtolower(htmlspecialchars($product['product_name'])) ?>">
        <div class="menu-item" onclick="addToCart(<?= $product['product_id'] ?>, '<?= addslashes($product['product_name']) ?>', <?= $product['price'] ?>)">
          <?php if (!empty($product['image'])): ?>
            <div class="menu-item-img"><img src="<?= BASE_URL ?>/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>"></div>
          <?php else: ?>
            <div class="menu-item-icon"><?= $icon ?></div>
          <?php endif; ?>
          <div class="menu-item-name"><?= htmlspecialchars($product['product_name']) ?></div>
          <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:2px"><?= htmlspecialchars($product['category_name'] ?? '') ?></div>
          <div class="menu-item-price"><?= number_format($product['price'], 0) ?> MMK</div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Right: Cart -->
  <div>
    <div class="order-cart">
      <div class="card-header">
        <div class="card-title">🛒 Order Summary <span id="cart-count" style="background:var(--primary);color:#fff;font-size:0.75rem;padding:2px 8px;border-radius:12px;margin-left:4px">0</span></div>
      </div>

      <!-- Table & Waiter -->
      <form action="<?= BASE_URL ?>/admin/save-order" method="POST" id="order-form">
        <div style="padding:16px;border-bottom:1px solid var(--border)">
          <div class="form-field" style="margin-bottom:12px">
            <label>🪑 Table</label>
            <select name="table_id" class="form-control no-icon" required>
              <option value="">Select table...</option>
              <?php foreach ($tables as $t): ?>
              <option value="<?= $t['table_id'] ?>"><?= htmlspecialchars($t['table_number']) ?> (Cap: <?= $t['capacity'] ?>) — <?= $t['location'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field" style="margin-bottom:12px">
            <label>👨‍🍳 Waiter</label>
            <select name="waiter_id" class="form-control no-icon" required>
              <option value="">Select waiter...</option>
              <?php foreach ($waiters as $w): ?>
              <option value="<?= $w['waiter_id'] ?>"><?= htmlspecialchars($w['waiter_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field">
            <label>📝 Notes</label>
            <textarea name="notes" class="form-control no-icon" placeholder="Special instructions..." rows="2"></textarea>
          </div>
        </div>

        <!-- Cart Items -->
        <div id="cart-items" class="cart-items">
          <div class="empty-state" style="padding:30px">
            <div class="empty-icon">🛒</div>
            <p>No items added yet</p>
          </div>
        </div>

        <!-- Total -->
        <div class="cart-total">
          <div class="cart-total-row"><span>Subtotal</span><span id="cart-total">0 MMK</span></div>
          <div class="cart-total-row"><span style="color:var(--text-secondary);font-size:0.82rem">Tax (10%)</span><span style="color:var(--text-secondary);font-size:0.82rem" id="cart-tax">0 MMK</span></div>
          <div class="cart-total-row grand"><span>Total</span><span id="cart-grand">0 MMK</span></div>
          <button type="submit" class="btn btn-primary btn-lg" id="submit-order" style="width:100%;margin-top:16px;justify-content:center" disabled>
            ✓ Place Order
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Override cart render to show tax
const origRender = window.renderCart;
function renderCartWithTax() {
  if (origRender) origRender();
  const total = Object.values(cart).reduce((s,i)=>s+i.price*i.qty,0);
  const tax = total * 0.1;
  const taxEl = document.getElementById('cart-tax');
  const grandEl = document.getElementById('cart-grand');
  if (taxEl) taxEl.textContent = Math.round(tax).toLocaleString() + ' MMK';
  if (grandEl) grandEl.textContent = Math.round(total + tax).toLocaleString() + ' MMK';
  const totalEl = document.getElementById('cart-total');
  if (totalEl) totalEl.textContent = Math.round(total).toLocaleString() + ' MMK';
}
window.renderCart = renderCartWithTax;
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
