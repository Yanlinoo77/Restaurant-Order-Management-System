<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/App.php';

class WaiterController {
    private $db;
    private $waiter_id;

    public function __construct() {
        App::requireLogin('waiter');
        $this->db = Database::getInstance();
        $this->waiter_id = $_SESSION['waiter_id'];
    }

    public function dashboard() {
        $stats = [
            'my_orders_today' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE waiter_id=? AND DATE(order_date)=CURDATE()", [$this->waiter_id], 'i')['cnt'],
            'pending_orders' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE waiter_id=? AND order_status='pending'", [$this->waiter_id], 'i')['cnt'],
            'active_orders' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE waiter_id=? AND order_status IN ('pending','preparing','served')", [$this->waiter_id], 'i')['cnt'],
            'my_revenue_today' => $this->db->fetchOne("SELECT COALESCE(SUM(o.total_amount),0) as rev FROM `order` o WHERE o.waiter_id=? AND DATE(o.order_date)=CURDATE() AND o.payment_status='paid'", [$this->waiter_id], 'i')['rev'],
        ];
        $my_orders = $this->db->fetchAll("SELECT o.*, t.table_number FROM `order` o LEFT JOIN restaurant_table t ON o.table_id=t.table_id WHERE o.waiter_id=? AND o.order_status NOT IN ('completed','cancelled') ORDER BY o.order_date DESC", [$this->waiter_id], 'i');
        require_once __DIR__ . '/../views/waiter/dashboard.php';
    }

    public function orders() {
        $status = $_GET['status'] ?? '';
        $allowed_statuses = ['pending', 'preparing', 'served', 'completed', 'cancelled'];
        if ($status && !in_array($status, $allowed_statuses)) {
            $status = '';
        }
        if ($status) {
            $orders = $this->db->fetchAll("SELECT o.*, t.table_number FROM `order` o LEFT JOIN restaurant_table t ON o.table_id=t.table_id WHERE o.waiter_id=? AND o.order_status=? ORDER BY o.order_date DESC", [$this->waiter_id, $status], 'is');
        } else {
            $orders = $this->db->fetchAll("SELECT o.*, t.table_number FROM `order` o LEFT JOIN restaurant_table t ON o.table_id=t.table_id WHERE o.waiter_id=? ORDER BY o.order_date DESC", [$this->waiter_id], 'i');
        }
        require_once __DIR__ . '/../views/waiter/orders.php';
    }

    public function newOrder() {
        $tables = $this->db->fetchAll("SELECT * FROM restaurant_table WHERE is_occupied = 0 ORDER BY table_number");
        $categories = $this->db->fetchAll("SELECT * FROM product_category ORDER BY category_name");
        $products = $this->db->fetchAll("SELECT p.*, pc.category_name FROM product p LEFT JOIN product_category pc ON p.category_id=pc.category_id WHERE p.is_available=1 ORDER BY pc.category_name, p.product_name");
        require_once __DIR__ . '/../views/waiter/new_order.php';
    }

    public function saveOrder() {
        $table_id  = intval($_POST['table_id']);
        $notes     = $_POST['notes'] ?? '';
        $items     = $_POST['items'] ?? [];

        $total = 0;
        foreach ($items as $item) {
            $product = $this->db->fetchOne("SELECT price FROM product WHERE product_id=?", [intval($item['product_id'])], 'i');
            $total  += $product['price'] * intval($item['quantity']);
        }

        $order_id = $this->db->insert(
            "INSERT INTO `order` (table_id, waiter_id, order_status, total_amount, payment_status, notes) VALUES (?,?,'pending',?,'unpaid',?)",
            [$table_id, $this->waiter_id, $total, $notes], 'iisd'
        );
        $this->db->execute("UPDATE restaurant_table SET is_occupied=1 WHERE table_id=?", [$table_id], 'i');

        foreach ($items as $item) {
            $product_id = intval($item['product_id']);
            $qty        = intval($item['quantity']);
            $product    = $this->db->fetchOne("SELECT price FROM product WHERE product_id=?", [$product_id], 'i');
            $this->db->execute(
                "INSERT INTO order_item (order_id, product_id, quantity, item_price, total_item_price, special_request) VALUES (?,?,?,?,?,?)",
                [$order_id, $product_id, $qty, $product['price'], $product['price']*$qty, $item['special_request']??''],
                'iiidds'
            );

            // ── Auto-deduct via product_recipe table ─────────────────────────
            $recipes = $this->db->fetchAll(
                "SELECT pr.*, i.ingredient_name, i.quantity_in_stock, i.reorder_level
                 FROM product_recipe pr
                 JOIN inventory i ON pr.inventory_id = i.inventory_id
                 WHERE pr.product_id = ?",
                [$product_id], 'i'
            );
            foreach ($recipes as $recipe) {
                $deduct  = $recipe['quantity_used'] * $qty;
                $old_qty = (int)$recipe['quantity_in_stock'];
                $new_qty = max(0, $old_qty - $deduct);
                $this->db->execute(
                    "UPDATE inventory SET quantity_in_stock = ? WHERE inventory_id = ?",
                    [$new_qty, $recipe['inventory_id']], 'ii'
                );
                $this->db->execute(
                    "INSERT INTO inventory_history
                        (inventory_id, ingredient_name,
                         quantity_before, quantity_after,
                         reorder_before,  reorder_after,
                         changed_field, change_type, changed_by_id, change_note)
                     VALUES (?,?, ?,?, ?,?, 'quantity_in_stock','auto_deduct',?,?)",
                    [
                        $recipe['inventory_id'], $recipe['ingredient_name'],
                        $old_qty, $new_qty,
                        (int)$recipe['reorder_level'], (int)$recipe['reorder_level'],
                        $this->waiter_id,
                        "Auto-deduct: Order #$order_id x{$qty} (used {$deduct} {$recipe['unit']})"
                    ],
                    'isiiiiss'
                );
            }
            // ────────────────────────────────────────────────────────────────
        }

        $this->db->execute(
            "INSERT INTO order_status_history (order_id, status, changed_by_role, changed_by_id) VALUES (?,'pending','waiter',?)",
            [$order_id, $this->waiter_id], 'ii'
        );
        App::redirect('/waiter/orders');
    }


    public function orderDetail($id) {
        $order = $this->db->fetchOne("SELECT o.*, t.table_number FROM `order` o LEFT JOIN restaurant_table t ON o.table_id=t.table_id WHERE o.order_id=? AND o.waiter_id=?", [$id, $this->waiter_id], 'ii');
        $items = $this->db->fetchAll("SELECT oi.*, p.product_name FROM order_item oi LEFT JOIN product p ON oi.product_id=p.product_id WHERE oi.order_id=?", [$id], 'i');
        $history = $this->db->fetchAll("SELECT * FROM order_status_history WHERE order_id=? ORDER BY timestamp DESC", [$id], 'i');
        require_once __DIR__ . '/../views/waiter/order_detail.php';
    }

    public function tables() {
        $tables = $this->db->fetchAll("SELECT t.*, o.order_id, o.order_status, o.waiter_id FROM restaurant_table t LEFT JOIN `order` o ON t.table_id=o.table_id AND o.order_status NOT IN ('completed','cancelled') ORDER BY t.table_number");
        require_once __DIR__ . '/../views/waiter/tables.php';
    }

    public function payment() {
        $orders = $this->db->fetchAll("SELECT o.*, t.table_number FROM `order` o LEFT JOIN restaurant_table t ON o.table_id=t.table_id WHERE o.waiter_id=? AND o.payment_status='unpaid' AND o.order_status IN ('served','completed') ORDER BY o.order_date DESC", [$this->waiter_id], 'i');
        $paid = $this->db->fetchAll("SELECT p.*, o.total_amount, t.table_number FROM payment p JOIN `order` o ON p.order_id=o.order_id LEFT JOIN restaurant_table t ON o.table_id=t.table_id WHERE o.waiter_id=? ORDER BY p.payment_date DESC LIMIT 20", [$this->waiter_id], 'i');
        require_once __DIR__ . '/../views/waiter/payment.php';
    }

    public function processPayment() {
        $order_id = intval($_POST['order_id']);
        $method   = $_POST['payment_method'];
        $amount   = floatval($_POST['amount_paid']);

        // Verify the order belongs to this waiter
        $order = $this->db->fetchOne("SELECT * FROM `order` WHERE order_id=? AND waiter_id=?", [$order_id, $this->waiter_id], 'ii');
        if (!$order) {
            App::redirect('/waiter/orders');
        }

        // Insert payment record
        $this->db->execute(
            "INSERT INTO payment (order_id, payment_method, amount_paid, payment_status) VALUES (?,?,?,'completed')",
            [$order_id, $method, $amount], 'isd'
        );

        // Mark order as completed and paid
        $this->db->execute(
            "UPDATE `order` SET payment_status='paid', order_status='completed' WHERE order_id=? AND waiter_id=?",
            [$order_id, $this->waiter_id], 'ii'
        );

        // Log status change
        $this->db->execute(
            "INSERT INTO order_status_history (order_id, status, changed_by_role, changed_by_id) VALUES (?,'completed','waiter',?)",
            [$order_id, $this->waiter_id], 'ii'
        );

        // Free the table
        $this->db->execute(
            "UPDATE restaurant_table SET is_occupied=0 WHERE table_id=?",
            [$order['table_id']], 'i'
        );

        App::redirect('/waiter/payment');
    }

    public function kitchen() {
        $orders = $this->db->fetchAll("SELECT o.*, t.table_number, COUNT(oi.order_item_id) as item_count FROM `order` o LEFT JOIN restaurant_table t ON o.table_id=t.table_id LEFT JOIN order_item oi ON o.order_id=oi.order_id WHERE o.order_status IN ('pending','preparing') GROUP BY o.order_id ORDER BY o.order_date ASC");
        require_once __DIR__ . '/../views/waiter/kitchen.php';
    }

    public function markServed() {
        $order_id = intval($_POST['order_id']);
        // When the waiter clicks "Complete", it means the order has been served
        // and they're confirming it's fully done — set to 'completed' and free the table
        $this->db->execute("UPDATE `order` SET order_status='completed' WHERE order_id=? AND waiter_id=?", [$order_id, $this->waiter_id], 'ii');
        $this->db->execute("INSERT INTO order_status_history (order_id, status, changed_by_role, changed_by_id) VALUES (?,'completed','waiter',?)", [$order_id, $this->waiter_id], 'ii');
        // Free the table
        $order = $this->db->fetchOne("SELECT table_id FROM `order` WHERE order_id=?", [$order_id], 'i');
        if ($order) $this->db->execute("UPDATE restaurant_table SET is_occupied=0 WHERE table_id=?", [$order['table_id']], 'i');
        App::redirect('/waiter/orders');
    }
}
