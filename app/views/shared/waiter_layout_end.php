    </div>
  </div>
</div>
<div id="toast-container" class="toast-container"></div>
<script src="<?= BASE_URL ?>/js/app.js"></script>
<script>
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
