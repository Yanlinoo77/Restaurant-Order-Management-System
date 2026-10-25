    </div>
  </div>
</div>
<div id="toast-container" class="toast-container"></div>

<!-- Order Items Modal -->
<div class="modal-overlay" id="items-modal">
  <div class="modal" style="background:#1A1D2E;border:1px solid #2D2F45">
    <div class="modal-header" style="border-bottom-color:#2D2F45">
      <div class="modal-title" id="items-modal-title" style="color:#fff">Order Items</div>
      <button class="modal-close" onclick="closeModal('items-modal')" style="background:#2D2F45;color:#fff">✕</button>
    </div>
    <div class="modal-body" id="items-modal-body" style="color:rgba(255,255,255,0.8)">
      <div class="loading-spinner"></div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/js/app.js"></script>
<script>
// Auto refresh with countdown
let countdown = 20;
const countEl = document.getElementById('refresh-countdown');
const interval = setInterval(() => {
  countdown--;
  if (countEl) countEl.textContent = countdown;
  if (countdown <= 0) {
    clearInterval(interval);
    location.reload();
  }
}, 1000);

// Kitchen status update — with full error handling
function kitchenUpdateStatus(orderId, status, btn) {
  if (status === 'cancelled' && !confirm('Cancel this order?')) return;

  // Visual feedback on the clicked button
  let originalText = '';
  if (btn) {
    originalText = btn.textContent;
    btn.disabled = true;
    btn.style.opacity = '0.6';
    btn.textContent = '⏳ ...';
  }

  const formData = new FormData();
  formData.append('order_id', orderId);
  formData.append('status', status);

  fetch(BASE_URL + '/kitchen/update-status', { method: 'POST', body: formData })
    .then(r => {
      if (r.status === 401) {
        showToast('Session expired — please refresh the page and login again.', 'error');
        if (btn) { btn.disabled = false; btn.style.opacity = '1'; btn.textContent = originalText; }
        setTimeout(() => location.reload(), 2000);
        return null;
      }
      return r.json();
    })
    .then(data => {
      if (!data) return;
      if (data.success) {
        showToast('Order #' + orderId + ' → ' + status, 'success');
        setTimeout(() => location.reload(), 700);
      } else {
        showToast('Error: ' + (data.message || 'Update failed'), 'error');
        if (btn) { btn.disabled = false; btn.style.opacity = '1'; btn.textContent = originalText; }
      }
    })
    .catch(err => {
      showToast('Network error — please try again.', 'error');
      if (btn) { btn.disabled = false; btn.style.opacity = '1'; btn.textContent = originalText; }
    });
}

function viewOrderItems(orderId, tableNum) {
  document.getElementById('items-modal-title').textContent = '🧾 Order #' + orderId + ' · Table ' + tableNum;
  document.getElementById('items-modal-body').innerHTML = '<div class="loading-spinner"></div>';
  openModal('items-modal');
  fetch(BASE_URL + '/kitchen/order-items/' + orderId)
    .then(r => r.json())
    .then(items => {
      let html = '<div style="display:flex;flex-direction:column;gap:10px">';
      items.forEach(item => {
        html += `<div style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:#0F1117;border-radius:8px">
          <div>
            <strong style="color:#fff;font-size:1rem">${item.product_name}</strong>
            ${item.special_request ? `<div style="font-size:0.8rem;color:#F39C12;margin-top:4px">📝 ${item.special_request}</div>` : ''}
          </div>
          <div style="background:linear-gradient(135deg,#F39C12,#D68910);color:#fff;font-size:1.2rem;font-weight:800;padding:6px 14px;border-radius:8px">
            ×${item.quantity}
          </div>
        </div>`;
      });
      html += '</div>';
      document.getElementById('items-modal-body').innerHTML = html;
    });
}

document.getElementById('hamburger')?.addEventListener('click', () => {
  document.getElementById('sidebar')?.classList.toggle('open');
  document.getElementById('sidebar-overlay')?.classList.toggle('active');
});
document.getElementById('sidebar-overlay')?.addEventListener('click', () => {
  document.getElementById('sidebar')?.classList.remove('open');
  document.getElementById('sidebar-overlay')?.classList.remove('active');
});
</script>
</body>
</html>
