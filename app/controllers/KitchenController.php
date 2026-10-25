<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/App.php';

class KitchenController {
    private $db;

    public function __construct() {
        session_start_if_not_started();
        if (!isset($_SESSION['kitchen_id'])) {
            // If this is an AJAX/JSON request, return JSON error instead of redirect
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
                      (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                      ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false);
            // For kitchen/update-status and kitchen/order-items, always return JSON
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($uri, '/kitchen/update-status') !== false || strpos($uri, '/kitchen/order-items') !== false) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh the page.']);
                exit;
            }
            App::redirect('/login/kitchen');
        }
        $this->db = Database::getInstance();
    }

    public function dashboard() {
        $pending = $this->db->fetchAll("SELECT o.*, t.table_number, w.waiter_name, COUNT(oi.order_item_id) as item_count FROM `order` o LEFT JOIN restaurant_table t ON o.table_id=t.table_id LEFT JOIN waiter w ON o.waiter_id=w.waiter_id LEFT JOIN order_item oi ON o.order_id=oi.order_id WHERE o.order_status='pending' GROUP BY o.order_id ORDER BY o.order_date ASC");
        $preparing = $this->db->fetchAll("SELECT o.*, t.table_number, w.waiter_name, COUNT(oi.order_item_id) as item_count FROM `order` o LEFT JOIN restaurant_table t ON o.table_id=t.table_id LEFT JOIN waiter w ON o.waiter_id=w.waiter_id LEFT JOIN order_item oi ON o.order_id=oi.order_id WHERE o.order_status='preparing' GROUP BY o.order_id ORDER BY o.order_date ASC");
        $ready = $this->db->fetchAll("SELECT o.*, t.table_number, w.waiter_name FROM `order` o LEFT JOIN restaurant_table t ON o.table_id=t.table_id LEFT JOIN waiter w ON o.waiter_id=w.waiter_id WHERE o.order_status='served' ORDER BY o.order_date DESC LIMIT 10");
        $stats = [
            'pending'         => count($pending),
            'preparing'       => count($preparing),
            'completed_today' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE order_status='completed' AND DATE(order_date)=CURDATE()")['cnt'],
        ];
        require_once __DIR__ . '/../views/kitchen/dashboard.php';
    }

    public function orderItems($id) {
        $items = $this->db->fetchAll("SELECT oi.*, p.product_name, p.description FROM order_item oi JOIN product p ON oi.product_id=p.product_id WHERE oi.order_id=?", [$id], 'i');
        header('Content-Type: application/json');
        echo json_encode($items);
    }

    public function updateStatus() {
        // Always return JSON — never redirect on AJAX
        header('Content-Type: application/json');

        // Session check (manual, so we can return JSON instead of redirect)
        session_start_if_not_started();
        if (!isset($_SESSION['kitchen_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh and login again.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $order_id = intval($_POST['order_id'] ?? 0);
        $status   = trim($_POST['status'] ?? '');

        if ($order_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
            return;
        }

        $allowed = ['preparing', 'served', 'cancelled'];
        if (!in_array($status, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status: ' . $status]);
            return;
        }

        $this->db->execute("UPDATE `order` SET order_status=? WHERE order_id=?", [$status, $order_id], 'si');
        $this->db->execute(
            "INSERT INTO order_status_history (order_id, status, changed_by_role, changed_by_id) VALUES (?,?,'kitchen',?)",
            [$order_id, $status, intval($_SESSION['kitchen_id'])], 'isi'
        );

        if ($status === 'cancelled') {
            $order = $this->db->fetchOne("SELECT table_id FROM `order` WHERE order_id=?", [$order_id], 'i');
            if ($order) $this->db->execute("UPDATE restaurant_table SET is_occupied=0 WHERE table_id=?", [$order['table_id']], 'i');
        }

        echo json_encode(['success' => true]);
    }
}
