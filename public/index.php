<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/WaiterController.php';
require_once __DIR__ . '/../app/controllers/KitchenController.php';

$request = $_SERVER['REQUEST_URI'] ?? '/';
// Strip base path (also handle when browser navigates directly to index.php)
foreach (['/restaurant/public/index.php', '/restaurant/public'] as $base) {
    if (strpos($request, $base) === 0) {
        $request = substr($request, strlen($base));
        break;
    }
}
if (empty($request)) $request = '/';
// Remove query string
$request = strtok($request, '?');

$method = $_SERVER['REQUEST_METHOD'];

// ============ ROUTING ============
switch (true) {

    // ROOT
    case $request === '/' || $request === '':
        App::redirect('/login');
        break;

    // AUTH
    case $request === '/login' && $method === 'POST':
        $auth = new AuthController();
        $auth->login();
        break;

    case $request === '/login':
        $role = null;
        $error = '';
        require_once __DIR__ . '/../app/views/auth/login.php';
        break;

    case preg_match('#^/login/(admin|waiter|kitchen)$#', $request, $m):
        $auth = new AuthController();
        $auth->showLogin($m[1]);
        break;

    case $request === '/logout' && $method === 'POST':
        $auth = new AuthController();
        $auth->logout();
        break;

    // ============ ADMIN ============
    case $request === '/admin/dashboard':
        (new AdminController())->dashboard(); break;

    case $request === '/admin/orders':
        (new AdminController())->orders(); break;

    case $request === '/admin/add-order':
        (new AdminController())->addOrder(); break;

    case $request === '/admin/save-order' && $method === 'POST':
        (new AdminController())->saveOrder(); break;

    case preg_match('#^/admin/order/(\d+)$#', $request, $m):
        (new AdminController())->orderDetail((int)$m[1]); break;

    case $request === '/admin/update-order-status' && $method === 'POST':
        (new AdminController())->updateOrderStatus(); break;

    case $request === '/admin/tables':
        (new AdminController())->tables(); break;

    case $request === '/admin/save-table' && $method === 'POST':
        (new AdminController())->saveTable(); break;

    case preg_match('#^/admin/release-table/(\d+)$#', $request, $m) && $method === 'POST':
        (new AdminController())->releaseTable((int)$m[1]); break;

    case preg_match('#^/admin/force-close-table/(\d+)$#', $request, $m) && $method === 'POST':
        (new AdminController())->forceCloseTable((int)$m[1]); break;

    case preg_match('#^/admin/delete-table/(\d+)$#', $request, $m) && $method === 'POST':
        (new AdminController())->deleteTable((int)$m[1]); break;

    case $request === '/admin/payments':
        (new AdminController())->payments(); break;

    case $request === '/admin/process-payment' && $method === 'POST':
        (new AdminController())->processPayment(); break;

    case $request === '/admin/products':
        (new AdminController())->products(); break;

    case $request === '/admin/save-product' && $method === 'POST':
        (new AdminController())->saveProduct(); break;

    case preg_match('#^/admin/delete-product/(\d+)$#', $request, $m) && $method === 'POST':
        (new AdminController())->deleteProduct((int)$m[1]); break;

    case preg_match('#^/admin/toggle-product/(\d+)$#', $request, $m) && $method === 'POST':
        (new AdminController())->toggleProduct((int)$m[1]); break;

    case $request === '/admin/save-category' && $method === 'POST':
        (new AdminController())->saveCategory(); break;

    case $request === '/admin/inventory':
        (new AdminController())->inventory(); break;

    case $request === '/admin/update-inventory' && $method === 'POST':
        (new AdminController())->updateInventory(); break;

    case $request === '/admin/save-recipe' && $method === 'POST':
        (new AdminController())->saveRecipe(); break;

    case preg_match('#^/admin/delete-recipe/(\d+)$#', $request, $m) && $method === 'POST':
        (new AdminController())->deleteRecipe((int)$m[1]); break;

    case $request === '/admin/waiters':
        (new AdminController())->waiters(); break;

    case $request === '/admin/save-waiter' && $method === 'POST':
        (new AdminController())->saveWaiter(); break;

    case preg_match('#^/admin/delete-waiter/(\d+)$#', $request, $m) && $method === 'POST':
        (new AdminController())->deleteWaiter((int)$m[1]); break;

    case $request === '/admin/kitchen':
        (new AdminController())->kitchen(); break;

    case $request === '/admin/reports':
        (new AdminController())->reports(); break;

    case $request === '/admin/profile':
        (new AdminController())->profile(); break;

    case $request === '/admin/update-profile' && $method === 'POST':
        (new AdminController())->updateProfile(); break;

    // ============ WAITER ============
    case $request === '/waiter/dashboard':
        (new WaiterController())->dashboard(); break;

    case $request === '/waiter/orders':
        (new WaiterController())->orders(); break;

    case $request === '/waiter/new-order':
        (new WaiterController())->newOrder(); break;

    case $request === '/waiter/save-order' && $method === 'POST':
        (new WaiterController())->saveOrder(); break;

    case preg_match('#^/waiter/order/(\d+)$#', $request, $m):
        (new WaiterController())->orderDetail((int)$m[1]); break;

    case $request === '/waiter/tables':
        (new WaiterController())->tables(); break;

    case $request === '/waiter/payment':
        (new WaiterController())->payment(); break;

    case $request === '/waiter/kitchen':
        (new WaiterController())->kitchen(); break;

    case $request === '/waiter/mark-served' && $method === 'POST':
        (new WaiterController())->markServed(); break;

    case $request === '/waiter/process-payment' && $method === 'POST':
        (new WaiterController())->processPayment(); break;

    // ============ KITCHEN ============
    case $request === '/kitchen/dashboard':
        (new KitchenController())->dashboard(); break;

    case preg_match('#^/kitchen/order-items/(\d+)$#', $request, $m):
        (new KitchenController())->orderItems((int)$m[1]); break;

    case $request === '/kitchen/update-status' && $method === 'POST':
        (new KitchenController())->updateStatus(); break;

    // ============ 404 ============
    default:
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>404</title><style>body{font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#F4F6FB}div{text-align:center}h1{font-size:5rem;margin:0;color:#FF6B35}p{color:#6B7280}a{color:#FF6B35;text-decoration:none;font-weight:600}</style></head><body><div><h1>404</h1><p>Page not found.</p><a href="' . BASE_URL . '/">← Go Home</a></div></body></html>';
        break;
}
