<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/App.php';

class AdminController {
    private $db;

    public function __construct() {
        App::requireLogin('admin');
        $this->db = Database::getInstance();
    }

    public function dashboard() {
        $stats = [
            'total_orders_today' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE DATE(order_date) = CURDATE()")['cnt'],
            'revenue_today' => $this->db->fetchOne("SELECT COALESCE(SUM(amount_paid),0) as rev FROM payment WHERE DATE(payment_date) = CURDATE()")['rev'],
            'active_tables' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM restaurant_table WHERE is_occupied = 1")['cnt'],
            'pending_orders' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM `order` WHERE order_status IN ('pending','preparing')")['cnt'],
            'total_products' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM product WHERE is_available = 1")['cnt'],
            'total_waiters' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM waiter WHERE status = 'active'")['cnt'],
            'monthly_revenue' => $this->db->fetchOne("SELECT COALESCE(SUM(amount_paid),0) as rev FROM payment WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())")['rev'],
            'low_inventory' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM inventory WHERE quantity_in_stock <= reorder_level")['cnt'],
        ];
        $recent_orders = $this->db->fetchAll("SELECT o.*, t.table_number, w.waiter_name FROM `order` o LEFT JOIN restaurant_table t ON o.table_id = t.table_id LEFT JOIN waiter w ON o.waiter_id = w.waiter_id ORDER BY o.order_date DESC LIMIT 10");
        $monthly_data = $this->db->fetchAll("SELECT DATE_FORMAT(payment_date,'%b') as month, SUM(amount_paid) as revenue, COUNT(*) as orders FROM payment WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY MONTH(payment_date), YEAR(payment_date) ORDER BY payment_date");
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    public function orders() {
        $status = $_GET['status'] ?? '';
        $date   = $_GET['date']   ?? '';   // e.g. 'today' or 'YYYY-MM-DD'

        $allowed_statuses = ['pending', 'preparing', 'served', 'completed', 'cancelled'];
        if ($status && !in_array($status, $allowed_statuses)) $status = '';

        // Resolve date filter
        $dateClause   = '';
        $dateParams   = [];
        $dateTypes    = '';
        $filterDate   = null;

        if ($date === 'today') {
            $filterDate = date('Y-m-d');
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $filterDate = $date;
        }

        if ($filterDate) {
            $dateClause = ' AND DATE(o.order_date) = ?';
            $dateParams[] = $filterDate;
            $dateTypes   .= 's';
        }

        // Build query
        $baseSQL = "SELECT o.*, t.table_number, w.waiter_name
                    FROM `order` o
                    LEFT JOIN restaurant_table t ON o.table_id = t.table_id
                    LEFT JOIN waiter w ON o.waiter_id = w.waiter_id
                    WHERE 1=1 $dateClause";
        $params = $dateParams;
        $types  = $dateTypes;

        if ($status) {
            $baseSQL .= ' AND o.order_status = ?';
            $params[] = $status;
            $types   .= 's';
        }
        $baseSQL .= ' ORDER BY o.order_date DESC';
        $orders = $this->db->fetchAll($baseSQL, $params, $types);

        // Today's summary stats (always today, regardless of filter)
        $today_stats = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt,
                    COALESCE(SUM(o.total_amount),0) as revenue,
                    COALESCE((SELECT SUM(oi.quantity) FROM order_item oi JOIN `order` o2 ON oi.order_id=o2.order_id WHERE DATE(o2.order_date)=CURDATE()),0) as items
             FROM `order` o WHERE DATE(o.order_date) = CURDATE()"
        );

        $todays_menu = $this->db->fetchAll(
            "SELECT p.product_id, p.product_name, p.price, pc.category_name,
             COALESCE((SELECT COUNT(*) FROM order_item oi JOIN `order` o2 ON oi.order_id = o2.order_id
                       WHERE oi.product_id = p.product_id AND DATE(o2.order_date) = CURDATE()), 0) as ordered_today,
             (SELECT MAX(o3.order_date) FROM order_item oi2 JOIN `order` o3 ON oi2.order_id = o3.order_id
              WHERE oi2.product_id = p.product_id AND DATE(o3.order_date) = CURDATE()) as last_ordered_at
             FROM product p
             LEFT JOIN product_category pc ON p.category_id = pc.category_id
             WHERE p.is_available = 1
             ORDER BY pc.category_name, p.product_name"
        );
        require_once __DIR__ . '/../views/admin/orders.php';
    }

    public function orderDetail($id) {
        $order = $this->db->fetchOne("SELECT o.*, t.table_number, w.waiter_name FROM `order` o LEFT JOIN restaurant_table t ON o.table_id = t.table_id LEFT JOIN waiter w ON o.waiter_id = w.waiter_id WHERE o.order_id = ?", [$id], 'i');
        $items = $this->db->fetchAll("SELECT oi.*, p.product_name, p.image FROM order_item oi LEFT JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id = ?", [$id], 'i');
        $history = $this->db->fetchAll("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY timestamp DESC", [$id], 'i');
        $payment = $this->db->fetchOne("SELECT * FROM payment WHERE order_id = ?", [$id], 'i');
        require_once __DIR__ . '/../views/admin/order_detail.php';
    }

    public function addOrder() {
        $tables = $this->db->fetchAll("SELECT * FROM restaurant_table WHERE is_occupied = 0 ORDER BY table_number");
        $waiters = $this->db->fetchAll("SELECT * FROM waiter WHERE status = 'active' ORDER BY waiter_name");
        $categories = $this->db->fetchAll("SELECT * FROM product_category ORDER BY category_name");
        $products = $this->db->fetchAll("SELECT p.*, pc.category_name FROM product p LEFT JOIN product_category pc ON p.category_id = pc.category_id WHERE p.is_available = 1 ORDER BY pc.category_name, p.product_name");
        require_once __DIR__ . '/../views/admin/add_order.php';
    }

    public function saveOrder() {
        $this->ensureInventoryHistoryTable();
        $this->ensureProductRecipeTable();
        $table_id  = intval($_POST['table_id']);
        $waiter_id = intval($_POST['waiter_id']);
        $notes     = $_POST['notes'] ?? '';
        $items     = $_POST['items'] ?? [];

        if (empty($items)) {
            $_SESSION['error'] = 'No items in order.';
            App::redirect('/admin/add-order');
        }

        $total = 0;
        foreach ($items as $item) {
            $product = $this->db->fetchOne("SELECT price FROM product WHERE product_id = ?", [intval($item['product_id'])], 'i');
            $total  += $product['price'] * intval($item['quantity']);
        }

        $order_id = $this->db->insert(
            "INSERT INTO `order` (table_id, waiter_id, order_status, total_amount, payment_status, notes) VALUES (?,?,'pending',?,'unpaid',?)",
            [$table_id, $waiter_id, $total, $notes], 'iisd'
        );
        $this->db->execute("UPDATE restaurant_table SET is_occupied = 1 WHERE table_id = ?", [$table_id], 'i');

        foreach ($items as $item) {
            $product_id = intval($item['product_id']);
            $qty        = intval($item['quantity']);
            $product    = $this->db->fetchOne("SELECT price FROM product WHERE product_id = ?", [$product_id], 'i');
            $total_item = $product['price'] * $qty;
            $this->db->execute(
                "INSERT INTO order_item (order_id, product_id, quantity, item_price, total_item_price, special_request) VALUES (?,?,?,?,?,?)",
                [$order_id, $product_id, $qty, $product['price'], $total_item, $item['special_request'] ?? ''],
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
                        $_SESSION['admin_id'],
                        "Auto-deduct: Order #$order_id x{$qty} (used {$deduct} {$recipe['unit']})"
                    ],
                    'isiiiiss'
                );
            }
            // ─────────────────────────────────────────────────────────────────
        }

        $this->db->execute(
            "INSERT INTO order_status_history (order_id, status, changed_by_role, changed_by_id) VALUES (?,'pending','admin',?)",
            [$order_id, $_SESSION['admin_id']], 'ii'
        );
        App::redirect('/admin/orders');
    }

    public function tables() {
        $tables = $this->db->fetchAll("SELECT t.*, o.order_id, o.order_status, o.total_amount, o.payment_status as order_payment_status FROM restaurant_table t LEFT JOIN `order` o ON t.table_id = o.table_id AND o.order_status NOT IN ('completed','cancelled') ORDER BY t.table_number");
        require_once __DIR__ . '/../views/admin/tables.php';
    }

    public function saveTable() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['table_id'] ?? 0);
            $number = $_POST['table_number'];
            $capacity = intval($_POST['capacity']);
            $location = $_POST['location'];
            if ($id) {
                $this->db->execute("UPDATE restaurant_table SET table_number=?, capacity=?, location=? WHERE table_id=?", [$number, $capacity, $location, $id], 'sisi');
            } else {
                $this->db->execute("INSERT INTO restaurant_table (table_number, capacity, location) VALUES (?,?,?)", [$number, $capacity, $location], 'sis');
            }
        }
        App::redirect('/admin/tables');
    }

    public function releaseTable($id) {
        $activeOrder = $this->db->fetchOne(
            "SELECT order_id, payment_status FROM `order` WHERE table_id = ? AND order_status NOT IN ('completed','cancelled') LIMIT 1",
            [$id], 'i'
        );
        if ($activeOrder) {
            if ($activeOrder['payment_status'] !== 'paid') {
                $_SESSION['error'] = 'Cannot release table: payment must be completed first.';
                App::redirect('/admin/tables');
                return;
            }
        }
        $this->db->execute("UPDATE restaurant_table SET is_occupied = 0 WHERE table_id = ?", [$id], 'i');
        $_SESSION['success'] = 'Table released successfully.';
        App::redirect('/admin/tables');
    }

    public function forceCloseTable($id) {
        // Admin override: mark table available regardless of order/payment status
        $this->db->execute("UPDATE restaurant_table SET is_occupied = 0 WHERE table_id = ?", [$id], 'i');
        $_SESSION['success'] = 'Table force-closed by admin.';
        App::redirect('/admin/tables');
    }

    public function deleteTable($id) {
        $this->db->execute("DELETE FROM restaurant_table WHERE table_id = ?", [$id], 'i');
        App::redirect('/admin/tables');
    }

    public function payments() {
        $payments = $this->db->fetchAll("SELECT p.*, o.total_amount, o.order_status, t.table_number, w.waiter_name FROM payment p JOIN `order` o ON p.order_id = o.order_id LEFT JOIN restaurant_table t ON o.table_id = t.table_id LEFT JOIN waiter w ON o.waiter_id = w.waiter_id ORDER BY p.payment_date DESC");
        $unpaid = $this->db->fetchAll("SELECT o.*, t.table_number, w.waiter_name FROM `order` o LEFT JOIN restaurant_table t ON o.table_id = t.table_id LEFT JOIN waiter w ON o.waiter_id = w.waiter_id WHERE o.payment_status = 'unpaid' AND o.order_status IN ('served','completed') ORDER BY o.order_date DESC");
        require_once __DIR__ . '/../views/admin/payments.php';
    }

    public function processPayment() {
        $order_id = intval($_POST['order_id']);
        $method = $_POST['payment_method'];
        $amount = floatval($_POST['amount_paid']);
        $this->db->execute("INSERT INTO payment (order_id, payment_method, amount_paid, payment_status) VALUES (?,?,?,'completed')", [$order_id, $method, $amount], 'isd');
        $this->db->execute("UPDATE `order` SET payment_status='paid', order_status='completed' WHERE order_id=?", [$order_id], 'i');
        $order = $this->db->fetchOne("SELECT table_id FROM `order` WHERE order_id=?", [$order_id], 'i');
        $this->db->execute("UPDATE restaurant_table SET is_occupied=0 WHERE table_id=?", [$order['table_id']], 'i');
        App::redirect('/admin/payments');
    }

    public function products() {
        $categories = $this->db->fetchAll("SELECT * FROM product_category ORDER BY category_name");
        $products = $this->db->fetchAll("SELECT p.*, pc.category_name FROM product p LEFT JOIN product_category pc ON p.category_id = pc.category_id ORDER BY pc.category_name, p.product_name");
        require_once __DIR__ . '/../views/admin/products.php';
    }

    public function saveProduct() {
        $id = intval($_POST['product_id'] ?? 0);
        $name = $_POST['product_name'];
        $desc = $_POST['description'];
        $price = floatval($_POST['price']);
        $cat = intval($_POST['category_id']);
        $avail = isset($_POST['is_available']) ? 1 : 0;
        $removeImage = intval($_POST['remove_image'] ?? 0);

        // Handle image upload
        $imagePath = null;
        $hasNewImage = false;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            if (in_array($file['type'], $allowed) && $file['size'] <= $maxSize) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . time() . '_' . uniqid() . '.' . $ext;
                $uploadDir = __DIR__ . '/../../public/uploads/products/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $imagePath = 'uploads/products/' . $filename;
                    $hasNewImage = true;
                    // Delete old image file if replacing
                    if ($id) {
                        $old = $this->db->fetchOne("SELECT image FROM product WHERE product_id=?", [$id], 'i');
                        if ($old && $old['image'] && file_exists(__DIR__ . '/../../public/' . $old['image'])) {
                            @unlink(__DIR__ . '/../../public/' . $old['image']);
                        }
                    }
                }
            }
        }

        // Handle remove image request (no new image uploaded)
        if (!$hasNewImage && $removeImage && $id) {
            $old = $this->db->fetchOne("SELECT image FROM product WHERE product_id=?", [$id], 'i');
            if ($old && $old['image'] && file_exists(__DIR__ . '/../../public/' . $old['image'])) {
                @unlink(__DIR__ . '/../../public/' . $old['image']);
            }
            // Set image to NULL in DB
            $this->db->execute("UPDATE product SET product_name=?, description=?, price=?, category_id=?, is_available=?, image=NULL WHERE product_id=?", [$name, $desc, $price, $cat, $avail, $id], 'ssdiii');
            App::redirect('/admin/products');
            return;
        }

        if ($id) {
            if ($hasNewImage) {
                // name, desc, price, cat, avail, image, id → ssdiisi (7 params)
                $this->db->execute("UPDATE product SET product_name=?, description=?, price=?, category_id=?, is_available=?, image=? WHERE product_id=?", [$name, $desc, $price, $cat, $avail, $imagePath, $id], 'ssdiisi');
            } else {
                // name, desc, price, cat, avail, id → ssdiii (6 params)
                $this->db->execute("UPDATE product SET product_name=?, description=?, price=?, category_id=?, is_available=? WHERE product_id=?", [$name, $desc, $price, $cat, $avail, $id], 'ssdiii');
            }
        } else {
            // name, desc, price, cat, avail, image → ssdiis (6 params)
            $this->db->execute("INSERT INTO product (product_name, description, price, category_id, is_available, image) VALUES (?,?,?,?,?,?)", [$name, $desc, $price, $cat, $avail, $imagePath], 'ssdiis');
        }
        App::redirect('/admin/products');
    }

    public function deleteProduct($id) {
        $this->db->execute("UPDATE product SET is_available=0 WHERE product_id=?", [$id], 'i');
        App::redirect('/admin/products');
    }

    public function toggleProduct($id) {
        // Flip is_available between 1 and 0
        $this->db->execute("UPDATE product SET is_available = 1 - is_available WHERE product_id=?", [$id], 'i');
        App::redirect('/admin/products');
    }

    public function saveCategory() {
        $id = intval($_POST['category_id'] ?? 0);
        $name = $_POST['category_name'];
        $desc = $_POST['description'] ?? '';
        if ($id) {
            $this->db->execute("UPDATE product_category SET category_name=?, description=? WHERE category_id=?", [$name, $desc, $id], 'ssi');
        } else {
            $this->db->execute("INSERT INTO product_category (category_name, description) VALUES (?,?)", [$name, $desc], 'ss');
        }
        App::redirect('/admin/products');
    }

    private function ensureProductRecipeTable() {
        $this->db->getConnection()->query("CREATE TABLE IF NOT EXISTS product_recipe (
            recipe_id     INT PRIMARY KEY AUTO_INCREMENT,
            product_id    INT NOT NULL,
            inventory_id  INT NOT NULL,
            quantity_used DECIMAL(10,3) NOT NULL DEFAULT 1,
            unit          VARCHAR(20) DEFAULT 'pcs',
            FOREIGN KEY (product_id)   REFERENCES product(product_id)     ON DELETE CASCADE,
            FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE,
            UNIQUE KEY uq_recipe (product_id, inventory_id)
        )");
    }

    private function ensureInventoryHistoryTable() {
        $conn = $this->db->getConnection();
        // Create table with full schema
        $conn->query("CREATE TABLE IF NOT EXISTS inventory_history (
            history_id    INT PRIMARY KEY AUTO_INCREMENT,
            inventory_id  INT NOT NULL,
            ingredient_name VARCHAR(100) NOT NULL,
            quantity_before INT NOT NULL DEFAULT 0,
            quantity_after  INT NOT NULL DEFAULT 0,
            reorder_before  INT DEFAULT NULL,
            reorder_after   INT DEFAULT NULL,
            changed_field   VARCHAR(50) DEFAULT 'quantity_in_stock',
            change_type     VARCHAR(20) DEFAULT 'manual',
            changed_by_id   INT,
            change_note     VARCHAR(255),
            changed_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE
        )");
        // Migration: add new columns if they don't exist yet
        @$conn->query("ALTER TABLE inventory_history ADD COLUMN IF NOT EXISTS reorder_before INT DEFAULT NULL");
        @$conn->query("ALTER TABLE inventory_history ADD COLUMN IF NOT EXISTS reorder_after  INT DEFAULT NULL");
        @$conn->query("ALTER TABLE inventory_history ADD COLUMN IF NOT EXISTS changed_field  VARCHAR(50) DEFAULT 'quantity_in_stock'");
        @$conn->query("ALTER TABLE inventory_history ADD COLUMN IF NOT EXISTS change_type    VARCHAR(20) DEFAULT 'manual'");
    }

    public function inventory() {
        $this->ensureInventoryHistoryTable();
        $this->ensureProductRecipeTable();
        $inventory = $this->db->fetchAll("SELECT i.*, p.product_name FROM inventory i LEFT JOIN product p ON i.product_id = p.product_id ORDER BY i.quantity_in_stock ASC");
        $history   = $this->db->fetchAll("SELECT ih.*, DATE(ih.changed_at) as change_date FROM inventory_history ih ORDER BY ih.changed_at DESC LIMIT 100");
        $products  = $this->db->fetchAll("SELECT product_id, product_name FROM product WHERE is_available=1 ORDER BY product_name");
        $recipes   = $this->db->fetchAll(
            "SELECT pr.*, p.product_name, i.ingredient_name, i.unit as inv_unit
             FROM product_recipe pr
             JOIN product   p ON pr.product_id   = p.product_id
             JOIN inventory i ON pr.inventory_id = i.inventory_id
             ORDER BY p.product_name, i.ingredient_name"
        );
        require_once __DIR__ . '/../views/admin/inventory.php';
    }

    public function saveRecipe() {
        $this->ensureProductRecipeTable();
        $product_id   = intval($_POST['product_id']);
        $inventory_id = intval($_POST['inventory_id']);
        $qty_used     = floatval($_POST['quantity_used']);
        $unit         = $_POST['unit'] ?? 'pcs';
        if ($product_id && $inventory_id && $qty_used > 0) {
            $this->db->execute(
                "INSERT INTO product_recipe (product_id, inventory_id, quantity_used, unit)
                 VALUES (?,?,?,?)
                 ON DUPLICATE KEY UPDATE quantity_used=VALUES(quantity_used), unit=VALUES(unit)",
                [$product_id, $inventory_id, $qty_used, $unit], 'iidd'
            );
        }
        App::redirect('/admin/inventory');
    }

    public function deleteRecipe($id) {
        $this->ensureProductRecipeTable();
        $this->db->execute("DELETE FROM product_recipe WHERE recipe_id=?", [$id], 'i');
        App::redirect('/admin/inventory');
    }


    public function updateInventory() {
        $this->ensureInventoryHistoryTable();
        $id    = intval($_POST['inventory_id'] ?? 0);
        $qty   = intval($_POST['quantity_in_stock']);
        $level = intval($_POST['reorder_level']);

        if ($id) {
            $old = $this->db->fetchOne(
                "SELECT quantity_in_stock, reorder_level, ingredient_name FROM inventory WHERE inventory_id=?",
                [$id], 'i'
            );
            $this->db->execute(
                "UPDATE inventory SET quantity_in_stock=?, reorder_level=? WHERE inventory_id=?",
                [$qty, $level, $id], 'iii'
            );

            $qtyChanged   = ((int)$old['quantity_in_stock'] !== $qty);
            $levelChanged = ((int)$old['reorder_level']     !== $level);

            if ($qtyChanged || $levelChanged) {
                if ($qtyChanged && $levelChanged)  $changedField = 'both';
                elseif ($qtyChanged)               $changedField = 'quantity_in_stock';
                else                               $changedField = 'reorder_level';

                $this->db->execute(
                    "INSERT INTO inventory_history
                        (inventory_id, ingredient_name,
                         quantity_before, quantity_after,
                         reorder_before,  reorder_after,
                         changed_field, change_type, changed_by_id)
                     VALUES (?,?, ?,?, ?,?, ?,'manual',?)",
                    [
                        $id, $old['ingredient_name'],
                        (int)$old['quantity_in_stock'], $qty,
                        (int)$old['reorder_level'],     $level,
                        $changedField, $_SESSION['admin_id']
                    ],
                    'isiiiisi'
                );
            }
        } else {
            $name = $_POST['ingredient_name'];
            $unit = $_POST['unit'];
            $new_id = $this->db->insert(
                "INSERT INTO inventory (ingredient_name, quantity_in_stock, reorder_level, unit) VALUES (?,?,?,?)",
                [$name, $qty, $level, $unit], 'siis'
            );
            $this->db->execute(
                "INSERT INTO inventory_history
                    (inventory_id, ingredient_name,
                     quantity_before, quantity_after,
                     reorder_before,  reorder_after,
                     changed_field, change_type, changed_by_id, change_note)
                 VALUES (?,?, 0,?, 0,?, 'both','initial',?, 'Initial stock')",
                [$new_id, $name, $qty, $level, $_SESSION['admin_id']],
                'isiii'
            );
        }
        App::redirect('/admin/inventory');
    }

    public function waiters() {
        $waiters = $this->db->fetchAll("SELECT w.*, COUNT(o.order_id) as total_orders FROM waiter w LEFT JOIN `order` o ON w.waiter_id = o.waiter_id GROUP BY w.waiter_id ORDER BY w.waiter_name");
        require_once __DIR__ . '/../views/admin/waiters.php';
    }

    public function saveWaiter() {
        $id = intval($_POST['waiter_id'] ?? 0);
        $name = $_POST['waiter_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone_number'];
        $status = $_POST['status'];
        if ($id) {
            $this->db->execute("UPDATE waiter SET waiter_name=?, email=?, phone_number=?, status=? WHERE waiter_id=?", [$name, $email, $phone, $status, $id], 'ssssi');
        } else {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $this->db->execute("INSERT INTO waiter (waiter_name, email, phone_number, password, status) VALUES (?,?,?,?,?)", [$name, $email, $phone, $password, $status], 'sssss');
        }
        App::redirect('/admin/waiters');
    }

    public function deleteWaiter($id) {
        $this->db->execute("UPDATE waiter SET status='inactive' WHERE waiter_id=?", [$id], 'i');
        App::redirect('/admin/waiters');
    }

    public function kitchen() {
        $orders = $this->db->fetchAll("SELECT o.*, t.table_number, w.waiter_name, COUNT(oi.order_item_id) as item_count FROM `order` o LEFT JOIN restaurant_table t ON o.table_id = t.table_id LEFT JOIN waiter w ON o.waiter_id = w.waiter_id LEFT JOIN order_item oi ON o.order_id = oi.order_id WHERE o.order_status IN ('pending','preparing') GROUP BY o.order_id ORDER BY o.order_date ASC");
        require_once __DIR__ . '/../views/admin/kitchen.php';
    }

    public function reports() {
        $month = $_GET['month'] ?? date('Y-m');
        [$year, $mon] = explode('-', $month);
        $revenue = $this->db->fetchOne("SELECT COALESCE(SUM(amount_paid),0) as total FROM payment WHERE MONTH(payment_date)=? AND YEAR(payment_date)=?", [$mon, $year], 'ss');
        $top_products = $this->db->fetchAll("SELECT p.product_name, SUM(oi.quantity) as qty, SUM(oi.total_item_price) as revenue FROM order_item oi JOIN product p ON oi.product_id=p.product_id JOIN `order` o ON oi.order_id=o.order_id WHERE MONTH(o.order_date)=? AND YEAR(o.order_date)=? GROUP BY oi.product_id ORDER BY qty DESC LIMIT 10", [$mon, $year], 'ss');
        $daily = $this->db->fetchAll("SELECT DATE(payment_date) as day, SUM(amount_paid) as revenue, COUNT(*) as orders FROM payment WHERE MONTH(payment_date)=? AND YEAR(payment_date)=? GROUP BY DATE(payment_date) ORDER BY day", [$mon, $year], 'ss');
        $waiter_stats = $this->db->fetchAll("SELECT w.waiter_name, COUNT(o.order_id) as orders, COALESCE(SUM(o.total_amount),0) as revenue FROM waiter w LEFT JOIN `order` o ON w.waiter_id=o.waiter_id AND MONTH(o.order_date)=? AND YEAR(o.order_date)=? GROUP BY w.waiter_id ORDER BY revenue DESC", [$mon, $year], 'ss');
        // Per-day order breakdown (from order table, so includes unpaid orders too)
        $daily_orders = $this->db->fetchAll(
            "SELECT DATE(o.order_date) as day,
                    COUNT(o.order_id) as total_orders,
                    COALESCE(SUM(o.total_amount),0) as gross_amount,
                    SUM(CASE WHEN o.payment_status='paid' THEN 1 ELSE 0 END) as paid_orders,
                    SUM(CASE WHEN o.payment_status='unpaid' THEN 1 ELSE 0 END) as unpaid_orders,
                    SUM(CASE WHEN o.order_status='cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    COALESCE((SELECT SUM(oi.quantity) FROM order_item oi WHERE oi.order_id IN (SELECT o2.order_id FROM `order` o2 WHERE DATE(o2.order_date)=DATE(o.order_date) AND MONTH(o2.order_date)=? AND YEAR(o2.order_date)=?)),0) as items_sold
             FROM `order` o
             WHERE MONTH(o.order_date)=? AND YEAR(o.order_date)=?
             GROUP BY DATE(o.order_date)
             ORDER BY day DESC",
            [$mon, $year, $mon, $year], 'ssss'
        );

        // Monthly menu report: only sold products this month
        $menu_report = $this->db->fetchAll(
            "SELECT pc.category_name,
                    p.product_id,
                    p.product_name,
                    p.price as unit_price,
                    SUM(oi.quantity) as total_qty,
                    SUM(oi.total_item_price) as total_revenue,
                    COUNT(DISTINCT o.order_id) as order_count
             FROM order_item oi
             JOIN `order` o ON oi.order_id = o.order_id
             JOIN product p ON oi.product_id = p.product_id
             LEFT JOIN product_category pc ON p.category_id = pc.category_id
             WHERE MONTH(o.order_date) = ?
               AND YEAR(o.order_date) = ?
               AND o.order_status != 'cancelled'
             GROUP BY p.product_id, p.product_name, p.price, pc.category_name
             HAVING total_qty > 0
             ORDER BY pc.category_name, total_qty DESC",
            [$mon, $year], 'ss'
        );

        // Group menu report by category
        $menu_by_category = [];
        foreach ($menu_report as $item) {
            $cat = $item['category_name'] ?? 'Uncategorized';
            if (!isset($menu_by_category[$cat])) {
                $menu_by_category[$cat] = ['items' => [], 'total_qty' => 0, 'total_revenue' => 0];
            }
            $menu_by_category[$cat]['items'][] = $item;
            $menu_by_category[$cat]['total_qty'] += $item['total_qty'];
            $menu_by_category[$cat]['total_revenue'] += $item['total_revenue'];
        }

        require_once __DIR__ . '/../views/admin/reports.php';
    }

    public function profile() {
        $admin = $this->db->fetchOne("SELECT * FROM admin WHERE admin_id=?", [$_SESSION['admin_id']], 'i');
        require_once __DIR__ . '/../views/admin/profile.php';
    }

    public function updateProfile() {
        $id = $_SESSION['admin_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone_number'];
        $this->db->execute("UPDATE admin SET name=?, email=?, phone_number=? WHERE admin_id=?", [$name, $email, $phone, $id], 'sssi');
        if (!empty($_POST['password'])) {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $this->db->execute("UPDATE admin SET password=? WHERE admin_id=?", [$hash, $id], 'si');
        }
        $_SESSION['admin'] = $this->db->fetchOne("SELECT * FROM admin WHERE admin_id=?", [$id], 'i');
        App::redirect('/admin/profile');
    }

    public function updateOrderStatus() {
        $order_id = intval($_POST['order_id']);
        $status = $_POST['status'];
        $this->db->execute("UPDATE `order` SET order_status=? WHERE order_id=?", [$status, $order_id], 'si');
        $this->db->execute("INSERT INTO order_status_history (order_id, status, changed_by_role, changed_by_id) VALUES (?,?,'admin',?)", [$order_id, $status, $_SESSION['admin_id']], 'isi');
        if ($status === 'completed') {
            $order = $this->db->fetchOne("SELECT table_id FROM `order` WHERE order_id=?", [$order_id], 'i');
            $this->db->execute("UPDATE restaurant_table SET is_occupied=0 WHERE table_id=?", [$order['table_id']], 'i');
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }
}
