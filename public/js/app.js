// ===== RESTAURANT ORDER MANAGEMENT SYSTEM - MAIN JS =====

// Ripple effect on buttons — CRITICAL: position:absolute must be set so the ripple
// span does NOT push/grow the button's layout (isolation:isolate + overflow:hidden in CSS).
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.btn-login, .btn');
  if (!btn) return;
  // Skip tiny fixed-size elements that shouldn't ripple
  if (btn.classList.contains('modal-close') || btn.classList.contains('qty-btn')) return;
  const ripple = document.createElement('span');
  ripple.className = 'ripple';
  // MUST be position:absolute so it doesn't affect layout
  const rect = btn.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  const x = e.clientX - rect.left - size / 2;
  const y = e.clientY - rect.top - size / 2;
  ripple.style.cssText = `position:absolute;pointer-events:none;width:${size}px;height:${size}px;left:${x}px;top:${y}px;`;
  btn.appendChild(ripple);
  ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
});

// Clock
function updateClock() {
  const el = document.getElementById('live-clock');
  if (el) {
    const now = new Date();
    el.textContent = now.toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
  }
}
setInterval(updateClock, 1000);
updateClock();

// Toast notifications
function showToast(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${icons[type]}</span><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'fadeIn 0.3s ease reverse';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

// Modal management
function openModal(id) {
  const overlay = document.getElementById(id);
  if (overlay) { overlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
  const overlay = document.getElementById(id);
  if (overlay) { overlay.classList.remove('active'); document.body.style.overflow = ''; }
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
    document.body.style.overflow = '';
  }
});

// Mobile sidebar
const sidebar = document.querySelector('.sidebar');
const overlay = document.querySelector('.sidebar-overlay');
document.querySelector('.hamburger')?.addEventListener('click', () => {
  sidebar?.classList.toggle('open');
  overlay?.classList.toggle('active');
});
overlay?.addEventListener('click', () => {
  sidebar?.classList.remove('open');
  overlay?.classList.remove('active');
});

// Order cart (waiter new order page)
let cart = {};

function addToCart(productId, name, price) {
  if (cart[productId]) {
    cart[productId].qty++;
  } else {
    cart[productId] = { name, price: parseFloat(price), qty: 1 };
  }
  renderCart();
  showToast(`${name} added to order`, 'success');
}

function removeFromCart(productId) {
  if (cart[productId]) {
    cart[productId].qty--;
    if (cart[productId].qty <= 0) delete cart[productId];
  }
  renderCart();
}

function renderCart() {
  const cartEl = document.getElementById('cart-items');
  const totalEl = document.getElementById('cart-total');
  const countEl = document.getElementById('cart-count');
  const submitBtn = document.getElementById('submit-order');
  if (!cartEl) return;

  const items = Object.entries(cart);
  if (items.length === 0) {
    cartEl.innerHTML = '<div class="empty-state" style="padding:30px"><div class="empty-icon">🛒</div><p>No items added yet</p></div>';
    if (totalEl) totalEl.textContent = '$0.00';
    if (countEl) countEl.textContent = '0';
    if (submitBtn) submitBtn.disabled = true;
    updateHiddenInputs();
    return;
  }

  let total = 0;
  let html = '';
  items.forEach(([id, item]) => {
    const subtotal = item.price * item.qty;
    total += subtotal;
    html += `
      <div class="cart-item" id="cart-item-${id}">
        <div class="cart-item-name">${item.name}</div>
        <div class="cart-qty">
          <button class="qty-btn" onclick="removeFromCart(${id})">−</button>
          <span style="font-weight:700;min-width:20px;text-align:center">${item.qty}</span>
          <button class="qty-btn" onclick="addToCart(${id},'${item.name.replace(/'/g,"\\'")}',${item.price})">+</button>
        </div>
        <div class="cart-item-price">${Math.round(subtotal).toLocaleString()} MMK</div>
      </div>`;
  });
  cartEl.innerHTML = html;
  if (totalEl) totalEl.textContent = Math.round(total).toLocaleString() + ' MMK';
  if (countEl) countEl.textContent = items.length;
  if (submitBtn) submitBtn.disabled = false;
  updateHiddenInputs();
}

function updateHiddenInputs() {
  const form = document.getElementById('order-form');
  if (!form) return;
  form.querySelectorAll('.cart-hidden').forEach(el => el.remove());
  Object.entries(cart).forEach(([id, item], index) => {
    ['product_id','quantity'].forEach(field => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = `items[${index}][${field}]`;
      input.value = field === 'product_id' ? id : item.qty;
      input.className = 'cart-hidden';
      form.appendChild(input);
    });
  });
}

// Category filter for menu
function filterCategory(catId) {
  document.querySelectorAll('.menu-item-wrap').forEach(el => {
    if (!catId || el.dataset.category == catId) { el.style.display = ''; }
    else el.style.display = 'none';
  });
  document.querySelectorAll('.cat-filter').forEach(btn => {
    btn.classList.toggle('active', !catId && btn.dataset.cat === '' || btn.dataset.cat == catId);
  });
}

// Search for menu/product
document.addEventListener('input', function(e) {
  if (e.target.id === 'menu-search') {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('.menu-item-wrap').forEach(el => {
      el.style.display = el.dataset.name?.toLowerCase().includes(q) ? '' : 'none';
    });
  }
  if (e.target.id === 'table-search') {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  }
});

// Kitchen auto-refresh
if (document.getElementById('kitchen-board')) {
  setInterval(() => location.reload(), 30000);
}

// Animated counters
function animateCounters() {
  document.querySelectorAll('.stat-value[data-count]').forEach(el => {
    const target = parseFloat(el.dataset.count);
    const isFloat = el.dataset.count.includes('.');
    const prefix = el.dataset.prefix || '';
    let start = 0;
    const duration = 1200;
    const step = (timestamp) => {
      if (!start) start = timestamp;
      const progress = Math.min((timestamp - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const value = target * eased;
      el.textContent = prefix + (isFloat ? value.toFixed(2) : Math.floor(value));
      if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  });
}
window.addEventListener('load', animateCounters);

// Chart rendering (simple canvas)
function drawLineChart(canvasId, labels, datasets, options = {}) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;
  const pad = { top: 30, right: 30, bottom: 50, left: 60 };
  const chartW = W - pad.left - pad.right;
  const chartH = H - pad.top - pad.bottom;

  ctx.clearRect(0, 0, W, H);

  const allValues = datasets.flatMap(d => d.data);
  const maxVal = Math.max(...allValues, 1);
  const minVal = 0;

  // Grid lines
  ctx.strokeStyle = '#E5E7EB';
  ctx.lineWidth = 1;
  for (let i = 0; i <= 5; i++) {
    const y = pad.top + chartH - (i / 5) * chartH;
    ctx.beginPath();
    ctx.moveTo(pad.left, y);
    ctx.lineTo(pad.left + chartW, y);
    ctx.stroke();
    ctx.fillStyle = '#6B7280';
    ctx.font = '11px Inter, sans-serif';
    ctx.textAlign = 'right';
    const val = (maxVal * i / 5);
    ctx.fillText(options.prefix ? options.prefix + val.toFixed(0) : val.toFixed(0), pad.left - 8, y + 4);
  }

  // X labels
  ctx.fillStyle = '#6B7280';
  ctx.font = '11px Inter, sans-serif';
  ctx.textAlign = 'center';
  labels.forEach((label, i) => {
    const x = pad.left + (i / (labels.length - 1)) * chartW;
    ctx.fillText(label, x, H - 10);
  });

  // Datasets
  datasets.forEach(dataset => {
    const { data, color = '#FF6B35', fill = true } = dataset;
    if (data.length === 0) return;
    const denominator = data.length > 1 ? data.length - 1 : 1;
    const points = data.map((v, i) => ({
      x: pad.left + (i / denominator) * chartW,
      y: pad.top + chartH - ((v - minVal) / (maxVal - minVal || 1)) * chartH
    }));

    // Fill
    if (fill) {
      const gradient = ctx.createLinearGradient(0, pad.top, 0, pad.top + chartH);
      gradient.addColorStop(0, color + '30');
      gradient.addColorStop(1, color + '00');
      ctx.beginPath();
      ctx.moveTo(points[0].x, pad.top + chartH);
      points.forEach(p => ctx.lineTo(p.x, p.y));
      ctx.lineTo(points[points.length-1].x, pad.top + chartH);
      ctx.closePath();
      ctx.fillStyle = gradient;
      ctx.fill();
    }

    // Line
    ctx.beginPath();
    ctx.strokeStyle = color;
    ctx.lineWidth = 3;
    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';
    points.forEach((p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y));
    ctx.stroke();

    // Points
    points.forEach(p => {
      ctx.beginPath();
      ctx.arc(p.x, p.y, 5, 0, Math.PI * 2);
      ctx.fillStyle = '#fff';
      ctx.fill();
      ctx.strokeStyle = color;
      ctx.lineWidth = 2.5;
      ctx.stroke();
    });
  });
}

function drawBarChart(canvasId, labels, data, color = '#FF6B35') {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;
  const pad = { top: 20, right: 20, bottom: 50, left: 60 };
  const chartW = W - pad.left - pad.right;
  const chartH = H - pad.top - pad.bottom;
  const maxVal = Math.max(...data, 1);
  const barW = (chartW / data.length) * 0.6;
  const barGap = (chartW / data.length) * 0.4;

  ctx.clearRect(0, 0, W, H);
  ctx.strokeStyle = '#E5E7EB';
  ctx.lineWidth = 1;
  for (let i = 0; i <= 4; i++) {
    const y = pad.top + chartH - (i / 4) * chartH;
    ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(pad.left + chartW, y); ctx.stroke();
    ctx.fillStyle = '#6B7280'; ctx.font = '11px Inter'; ctx.textAlign = 'right';
    ctx.fillText((maxVal * i / 4).toFixed(0), pad.left - 8, y + 4);
  }

  const gradient = ctx.createLinearGradient(0, pad.top, 0, pad.top + chartH);
  gradient.addColorStop(0, color);
  gradient.addColorStop(1, color + '60');

  data.forEach((v, i) => {
    const x = pad.left + i * (barW + barGap) + barGap / 2;
    const barH = (v / maxVal) * chartH;
    const y = pad.top + chartH - barH;
    const radius = 6;
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + barW - radius, y);
    ctx.quadraticCurveTo(x + barW, y, x + barW, y + radius);
    ctx.lineTo(x + barW, y + barH);
    ctx.lineTo(x, y + barH);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.fillStyle = gradient;
    ctx.fill();
    ctx.fillStyle = '#6B7280'; ctx.font = '11px Inter'; ctx.textAlign = 'center';
    ctx.fillText(labels[i], x + barW / 2, H - 10);
  });
}

function drawDonutChart(canvasId, data, labels, colors) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;
  const cx = W/2, cy = H/2, r = Math.min(W,H)/2 - 20, inner = r * 0.6;
  const total = data.reduce((a,b)=>a+b,0);
  let angle = -Math.PI / 2;

  ctx.clearRect(0, 0, W, H);
  data.forEach((v, i) => {
    const sweep = (v / total) * Math.PI * 2;
    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, r, angle, angle + sweep);
    ctx.closePath();
    ctx.fillStyle = colors[i];
    ctx.fill();
    angle += sweep;
  });
  // Hole
  ctx.beginPath();
  ctx.arc(cx, cy, inner, 0, Math.PI * 2);
  ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--card-bg').trim() || '#fff';
  ctx.fill();
  // Center text
  ctx.fillStyle = '#2D3250';
  ctx.font = 'bold 20px Inter';
  ctx.textAlign = 'center';
  ctx.fillText(total, cx, cy + 7);
}

// Inventory progress bars
document.querySelectorAll('.inv-progress-bar').forEach(bar => {
  const pct = parseFloat(bar.dataset.pct || 0);
  bar.style.width = '0%';
  setTimeout(() => { bar.style.width = Math.min(pct, 100) + '%'; }, 300);
});

// Confirm dialogs
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', function(e) {
    if (!confirm(this.dataset.confirm)) e.preventDefault();
  });
});

// Status update via AJAX
function updateOrderStatus(orderId, status, callback) {
  const formData = new FormData();
  formData.append('order_id', orderId);
  formData.append('status', status);

  fetch(BASE_URL + '/kitchen/update-status', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Order status updated to ' + status, 'success');
        if (callback) callback();
        else location.reload();
      }
    })
    .catch(() => showToast('Failed to update status', 'error'));
}

// Number formatting
function formatCurrency(amount) {
  return Math.round(parseFloat(amount)).toLocaleString() + ' MMK';
}

// Auto-dismiss alerts
document.querySelectorAll('.alert[data-dismiss]').forEach(el => {
  setTimeout(() => {
    el.style.animation = 'fadeIn 0.3s ease reverse';
    setTimeout(() => el.remove(), 300);
  }, parseInt(el.dataset.dismiss) || 4000);
});
