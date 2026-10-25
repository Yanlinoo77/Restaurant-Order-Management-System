<?php $page_title = 'Products & Categories'; require_once __DIR__ . '/../shared/admin_layout_start.php'; ?>

<div style="display:flex;gap:12px;margin-bottom:24px" class="animate-in">
  <button class="btn btn-primary" onclick="openModal('add-product-modal')">➕ Add Product</button>
  <button class="btn btn-secondary" onclick="openModal('add-category-modal')">🏷️ Add Category</button>
</div>

<!-- Categories -->
<div class="card animate-in-delay-1" style="margin-bottom:24px">
  <div class="card-header"><div class="card-title">🏷️ Categories (<?= count($categories) ?>)</div></div>
  <div style="display:flex;flex-wrap:wrap;gap:12px;padding:20px">
    <?php foreach ($categories as $cat): ?>
    <div style="background:var(--bg);border-radius:var(--radius);padding:12px 20px;display:flex;align-items:center;gap:12px;border:1px solid var(--border)">
      <span style="font-weight:700"><?= htmlspecialchars($cat['category_name']) ?></span>
      <span style="font-size:0.78rem;color:var(--text-secondary)"><?= htmlspecialchars($cat['description'] ?? '') ?></span>
      <button onclick="editCategory(<?= $cat['category_id'] ?>,'<?= addslashes($cat['category_name']) ?>','<?= addslashes($cat['description']??'') ?>')" class="btn btn-secondary btn-sm btn-icon">✏️</button>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Products Table -->
<div class="card animate-in-delay-2">
  <div class="card-header">
    <div class="card-title">🍕 Products (<?= count($products) ?>)</div>
    <div class="search-bar">
      <span class="search-icon">🔍</span>
      <input type="text" id="table-search" class="form-control no-icon" placeholder="Search products..." style="padding-left:36px;width:200px">
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Image</th><th>Product</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
          <td style="width:60px">
            <?php if (!empty($p['image'])): ?>
              <img src="<?= BASE_URL ?>/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:10px;border:2px solid var(--border)">
            <?php else: ?>
              <div style="width:50px;height:50px;background:var(--bg);border-radius:10px;border:2px dashed var(--border);display:flex;align-items:center;justify-content:center;font-size:1.4rem">🍽️</div>
            <?php endif; ?>
          </td>
          <td>
            <div style="font-weight:600"><?= htmlspecialchars($p['product_name']) ?></div>
            <div style="font-size:0.78rem;color:var(--text-secondary)"><?= htmlspecialchars(substr($p['description']??'',0,60)) ?><?= strlen($p['description']??'')>60?'...':'' ?></div>
          </td>
          <td><span style="background:rgba(255,107,53,0.1);color:var(--primary);padding:3px 10px;border-radius:12px;font-size:0.8rem"><?= htmlspecialchars($p['category_name']??'') ?></span></td>
          <td><strong style="color:var(--primary)"><?= number_format($p['price'], 0) ?> MMK</strong></td>
          <td><span class="badge <?= $p['is_available']?'badge-active':'badge-inactive' ?>"><?= $p['is_available']?'Available':'Hidden' ?></span></td>
          <td>
            <div style="display:flex;gap:6px">
              <button onclick="editProduct(<?= $p['product_id'] ?>,'<?= addslashes($p['product_name']) ?>','<?= addslashes($p['description']??'') ?>',<?= $p['price'] ?>,<?= $p['category_id'] ?>,<?= $p['is_available'] ?>,'<?= addslashes($p['image']??'') ?>')" class="btn btn-secondary btn-sm btn-icon" data-tooltip="Edit">✏️</button>
              <form action="<?= BASE_URL ?>/admin/toggle-product/<?= $p['product_id'] ?>" method="POST" style="display:inline">
                <button type="submit" class="btn btn-sm btn-icon <?= $p['is_available'] ? 'btn-warning' : 'btn-success' ?>" data-tooltip="<?= $p['is_available'] ? 'Mark Unavailable' : 'Mark Available' ?>" style="font-size:0.85rem">
                  <?= $p['is_available'] ? '🚫' : '✅' ?>
                </button>
              </form>
              <form action="<?= BASE_URL ?>/admin/delete-product/<?= $p['product_id'] ?>" method="POST" style="display:inline">
                <button type="submit" class="btn btn-danger btn-sm btn-icon" data-confirm="Permanently hide this product?">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal-overlay" id="add-product-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="product-modal-title">➕ Add Product</div>
      <button class="modal-close" onclick="closeModal('add-product-modal')">✕</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/save-product" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="product_id" id="edit-product-id">
      <div class="modal-body">
        <!-- Image Upload -->
        <div class="form-field" style="margin-bottom:16px">
          <label>Product Image</label>
          <div id="img-upload-area" style="border:2px dashed var(--border);border-radius:12px;padding:16px;text-align:center;cursor:pointer;transition:border-color 0.2s;position:relative" onclick="document.getElementById('product-image-input').click()" ondragover="event.preventDefault();this.style.borderColor='var(--primary)'" ondragleave="this.style.borderColor='var(--border)'" ondrop="handleImgDrop(event)">
            <div id="img-preview-wrap" style="display:none;margin-bottom:10px">
              <img id="img-preview" src="" alt="Preview" style="max-height:120px;max-width:100%;border-radius:8px;object-fit:cover">
            </div>
            <div id="img-placeholder">
              <div style="font-size:2rem;margin-bottom:6px">📷</div>
              <div style="font-size:0.85rem;color:var(--text-secondary)">Click or drag & drop an image here</div>
              <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:4px">JPG, PNG, WEBP up to 5MB</div>
            </div>
            <input type="file" name="product_image" id="product-image-input" accept="image/*" style="display:none" onchange="previewImage(this)">
            <input type="hidden" name="remove_image" id="remove-image-flag" value="0">
          </div>
          <div style="display:flex;align-items:center;gap:8px;margin-top:6px">
            <div id="img-filename" style="font-size:0.78rem;color:var(--text-secondary);flex:1"></div>
            <button type="button" id="remove-img-btn" onclick="removeImage()" style="display:none;background:var(--danger);color:#fff;border:none;border-radius:8px;padding:4px 12px;font-size:0.78rem;cursor:pointer;font-weight:600">🗑️ Remove</button>
          </div>
        </div>

        <div class="form-row cols-2">
          <div class="form-field">
            <label>Product Name</label>
            <input type="text" name="product_name" id="edit-product-name" class="form-control no-icon" required>
          </div>
          <div class="form-field">
            <label>Price (MMK)</label>
            <input type="number" name="price" id="edit-product-price" class="form-control no-icon" step="0.01" min="0" required>
          </div>
        </div>
        <div class="form-field" style="margin-bottom:16px">
          <label>Category</label>
          <select name="category_id" id="edit-product-category" class="form-control no-icon">
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-field" style="margin-bottom:16px">
          <label>Description</label>
          <textarea name="description" id="edit-product-desc" class="form-control no-icon" rows="3"></textarea>
        </div>
        <div class="form-field">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
            <input type="checkbox" name="is_available" id="edit-product-available" value="1" checked style="width:auto">
            Available for ordering
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('add-product-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Product</button>
      </div>
    </form>
  </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal-overlay" id="add-category-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="cat-modal-title">🏷️ Add Category</div>
      <button class="modal-close" onclick="closeModal('add-category-modal')">✕</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/save-category" method="POST">
      <input type="hidden" name="category_id" id="edit-cat-id">
      <div class="modal-body">
        <div class="form-field" style="margin-bottom:16px">
          <label>Category Name</label>
          <input type="text" name="category_name" id="edit-cat-name" class="form-control no-icon" required>
        </div>
        <div class="form-field">
          <label>Description</label>
          <textarea name="description" id="edit-cat-desc" class="form-control no-icon" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('add-category-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Category</button>
      </div>
    </form>
  </div>
</div>

<script>
function previewImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('img-preview').src = e.target.result;
      document.getElementById('img-preview-wrap').style.display = 'block';
      document.getElementById('img-placeholder').style.display = 'none';
      document.getElementById('img-filename').textContent = '📎 ' + input.files[0].name;
      const btn = document.getElementById('remove-img-btn');
      if (btn) { btn.style.display = 'inline-block'; }
      const flag = document.getElementById('remove-image-flag');
      if (flag) { flag.value = '0'; }
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function handleImgDrop(e) {
  e.preventDefault();
  document.getElementById('img-upload-area').style.borderColor = 'var(--border)';
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    const input = document.getElementById('product-image-input');
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    previewImage(input);
  }
}
function removeImage() {
  document.getElementById('img-preview-wrap').style.display = 'none';
  document.getElementById('img-placeholder').style.display = 'block';
  document.getElementById('img-filename').textContent = '❌ Image will be removed on save';
  document.getElementById('product-image-input').value = '';
  const btn = document.getElementById('remove-img-btn');
  if (btn) { btn.style.display = 'none'; }
  const flag = document.getElementById('remove-image-flag');
  if (flag) { flag.value = '1'; }
}
function resetImageUpload() {
  document.getElementById('img-preview-wrap').style.display = 'none';
  document.getElementById('img-placeholder').style.display = 'block';
  document.getElementById('img-filename').textContent = '';
  document.getElementById('product-image-input').value = '';
  const btn = document.getElementById('remove-img-btn');
  if (btn) { btn.style.display = 'none'; }
  const flag = document.getElementById('remove-image-flag');
  if (flag) { flag.value = '0'; }
}
function editProduct(id, name, desc, price, catId, avail, imgPath) {
  document.getElementById('product-modal-title').textContent = '✏️ Edit Product';
  document.getElementById('edit-product-id').value = id;
  document.getElementById('edit-product-name').value = name;
  document.getElementById('edit-product-desc').value = desc;
  document.getElementById('edit-product-price').value = price;
  document.getElementById('edit-product-category').value = catId;
  document.getElementById('edit-product-available').checked = avail == 1;
  resetImageUpload();
  if (imgPath) {
    document.getElementById('img-preview').src = '<?= BASE_URL ?>/' + imgPath;
    document.getElementById('img-preview-wrap').style.display = 'block';
    document.getElementById('img-placeholder').style.display = 'none';
    document.getElementById('img-filename').textContent = '📷 Current image (upload new to replace)';
    const btn = document.getElementById('remove-img-btn');
    if (btn) { btn.style.display = 'inline-block'; }
  }
  openModal('add-product-modal');
}
function editCategory(id, name, desc) {
  document.getElementById('cat-modal-title').textContent = '✏️ Edit Category';
  document.getElementById('edit-cat-id').value = id;
  document.getElementById('edit-cat-name').value = name;
  document.getElementById('edit-cat-desc').value = desc;
  openModal('add-category-modal');
}
// Reset form when opening Add Product modal
document.querySelector('[onclick="openModal(\'add-product-modal\')"]')?.addEventListener('click', function() {
  document.getElementById('product-modal-title').textContent = '➕ Add Product';
  document.getElementById('edit-product-id').value = '';
  document.getElementById('edit-product-name').value = '';
  document.getElementById('edit-product-desc').value = '';
  document.getElementById('edit-product-price').value = '';
  document.getElementById('edit-product-available').checked = true;
  resetImageUpload();
});
</script>

<?php require_once __DIR__ . '/../shared/admin_layout_end.php'; ?>
