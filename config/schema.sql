-- Restaurant Order Management System Database
CREATE DATABASE IF NOT EXISTS restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_db;

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Waiter table
CREATE TABLE IF NOT EXISTS waiter (
    waiter_id INT PRIMARY KEY AUTO_INCREMENT,
    waiter_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Kitchen table
CREATE TABLE IF NOT EXISTS kitchen (
    kitchen_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ProductCategory table
CREATE TABLE IF NOT EXISTS product_category (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product table
CREATE TABLE IF NOT EXISTS product (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image VARCHAR(255) DEFAULT NULL,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_category(category_id) ON DELETE SET NULL
);

-- ====================== TABLES ======================

-- Inventory table
CREATE TABLE IF NOT EXISTS inventory (
    inventory_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    ingredient_name VARCHAR(100) NOT NULL,
    quantity_in_stock INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 10,
    unit VARCHAR(50) DEFAULT 'pcs',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE SET NULL
);

-- Product Recipe table (menu item <-> ingredient many-to-many)
CREATE TABLE IF NOT EXISTS product_recipe (
    recipe_id     INT PRIMARY KEY AUTO_INCREMENT,
    product_id    INT NOT NULL,
    inventory_id  INT NOT NULL,
    quantity_used DECIMAL(10,3) NOT NULL DEFAULT 1,
    unit          VARCHAR(20) DEFAULT 'pcs',
    FOREIGN KEY (product_id)   REFERENCES product(product_id)     ON DELETE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE,
    UNIQUE KEY uq_recipe (product_id, inventory_id)
);

-- Table management
CREATE TABLE IF NOT EXISTS restaurant_table (
    table_id INT PRIMARY KEY AUTO_INCREMENT,
    table_number VARCHAR(50) NOT NULL UNIQUE,
    capacity INT DEFAULT 4,
    is_occupied BOOLEAN DEFAULT FALSE,
    location VARCHAR(100) DEFAULT 'Main Hall',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Order table
CREATE TABLE IF NOT EXISTS `order` (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    table_id INT,
    waiter_id INT,
    order_status VARCHAR(50) DEFAULT 'pending',
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_status VARCHAR(50) DEFAULT 'unpaid',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (table_id) REFERENCES restaurant_table(table_id) ON DELETE SET NULL,
    FOREIGN KEY (waiter_id) REFERENCES waiter(waiter_id) ON DELETE SET NULL
);

-- OrderItem table
CREATE TABLE IF NOT EXISTS order_item (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    item_price DECIMAL(10,2) NOT NULL,
    total_item_price DECIMAL(10,2) NOT NULL,
    special_request TEXT,
    FOREIGN KEY (order_id) REFERENCES `order`(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE SET NULL
);

-- Payment table
CREATE TABLE IF NOT EXISTS payment (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(50) DEFAULT 'completed',
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    transaction_ref VARCHAR(100),
    FOREIGN KEY (order_id) REFERENCES `order`(order_id) ON DELETE CASCADE
);

-- OrderStatusHistory table
CREATE TABLE IF NOT EXISTS order_status_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    changed_by_role VARCHAR(50),
    changed_by_id INT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (order_id) REFERENCES `order`(order_id) ON DELETE CASCADE
);

-- ====================== SEED DATA ======================

-- Admin
INSERT INTO admin (name, email, phone_number, password) VALUES
('Super Admin', 'admin@restaurant.com', '+1-555-0100', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Manager John', 'john@restaurant.com', '+1-555-0101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Default password: password

-- Waiters
INSERT INTO waiter (waiter_name, email, phone_number, password) VALUES
('Alice Smith', 'alice@restaurant.com', '+1-555-0201', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Bob Johnson', 'bob@restaurant.com', '+1-555-0202', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Carol Davis', 'carol@restaurant.com', '+1-555-0203', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Kitchen
INSERT INTO kitchen (name, email, password) VALUES
('Kitchen Staff', 'kitchen@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Chef Mario', 'mario@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- European Restaurant Categories
INSERT INTO product_category (category_name, description) VALUES
('Amuse-Bouche & Canapés', 'Elegant bite-sized starters'),
('Plats Principaux', 'Main courses and signature dishes'),
('Desserts & Pâtisserie', 'French and European pastries and sweets'),
('Vins & Boissons', 'Wines, spirits and beverages'),
('Soupes & Salades', 'Soups and fresh salads'),
('Fromages', 'European cheese selections'),
('Charcuterie', 'Cured meats and pâtés');

-- European Restaurant Products
INSERT INTO product (product_name, description, price, category_id) VALUES
-- Amuse-Bouche & Canapés
('Escargots de Bourgogne', 'Classic French snails baked in garlic-herb butter, served in shell', 16.50, 1),
('Foie Gras Torchon', 'Duck foie gras with brioche toast, fig compote and Sauternes jelly', 24.00, 1),
('Tartare de Saumon', 'Norwegian salmon tartare with crème fraîche and caviar blinis', 18.50, 1),
('Bruschetta Tricolore', 'Toasted sourdough with heirloom tomatoes, fresh burrata and basil oil', 12.00, 1),
('Vitello Tonnato', 'Thinly sliced veal with tuna-caper sauce, capers and anchovies', 17.00, 1),
-- Plats Principaux
('Boeuf Bourguignon', 'Slow-braised beef in Burgundy wine with pearl onions and lardons', 34.00, 2),
('Sole Meunière', 'Dover sole fillet in brown butter, lemon and fresh parsley', 38.00, 2),
('Rack of Lamb Provençal', 'Herb-crusted rack of lamb with ratatouille and rosemary jus', 42.00, 2),
('Coq au Vin', 'Free-range chicken braised in red wine with mushrooms and thyme', 28.00, 2),
('Risotto al Tartufo', 'Arborio rice with black truffle, aged Parmigiano and truffle oil', 32.00, 2),
('Wiener Schnitzel', 'Classic Austrian veal escalope with potato salad and lingonberries', 29.00, 2),
('Bouillabaisse Marseillaise', 'Traditional Provençal fish stew with saffron, rouille and croutons', 36.00, 2),
('Duck Confit', 'Slow-cooked duck leg with Sarladaise potatoes and green lentils', 31.00, 2),
-- Desserts & Pâtisserie
('Crème Brûlée Vanille', 'Classic vanilla custard with caramelized sugar crust', 10.00, 3),
('Tarte Tatin', 'Upside-down caramelized apple tart with Normandy crème fraîche', 11.50, 3),
('Soufflé au Chocolat', 'Warm dark chocolate soufflé with vanilla crème anglaise', 13.00, 3),
('Profiteroles', 'Choux pastry filled with vanilla ice cream, warm chocolate sauce', 10.50, 3),
('Opera Cake', 'Classic French almond sponge with coffee buttercream and ganache', 12.00, 3),
('Panna Cotta aux Fruits Rouges', 'Silky Italian cream dessert with mixed berry coulis', 9.50, 3),
-- Soupes & Salades
("Soupe à l'Oignon Gratinée", 'Classic French onion soup with Gruyère croûton', 13.00, 5),
('Salade Niçoise', 'Provençal salad with tuna, olives, eggs and haricots verts', 15.50, 5),
('Vichyssoise', 'Chilled leek and potato cream soup with chives', 11.00, 5),
('Salade César Maison', 'Romaine, house-made Caesar dressing, Parmigiano and croutons', 13.50, 5),
-- Vins & Boissons
('Bordeaux Rouge (Glass)', 'Château Pichon Baron 2018 – Pauillac, full-bodied red', 14.00, 4),
('Champagne (Glass)', 'Moët & Chandon Impérial Brut, classic champagne', 16.00, 4),
('Côtes du Rhône Blanc', 'Crisp white wine with floral notes, Viognier blend', 11.00, 4),
('Perrier Sparkling Water', 'Premium French sparkling mineral water 330ml', 4.50, 4),
('Café Français', 'Double espresso or café au lait, served with a petit four', 5.50, 4),
('Thé des Alpes', 'Alpine herbal tea selection with honey and lemon', 4.50, 4),
-- Fromages
('Plateau de Fromages', 'Selection of 5 European cheeses with walnuts, grapes and honey', 22.00, 6),
('Camembert Chaud', 'Baked whole Camembert with rosemary, served with rustic bread', 16.00, 6),
-- Charcuterie
('Planche Charcuterie', 'Selection of jambon, saucisson, pâté de campagne and cornichons', 20.00, 7),
('Pâté en Croûte', 'House-made pork and pistachio pâté en croûte with mustard', 15.00, 7);

-- Inventory (European restaurant ingredients)
INSERT INTO inventory (inventory_id, ingredient_name, quantity_in_stock, reorder_level, unit) VALUES
(1,  'Butter',               2000, 200,  'g'),
(2,  'Olive Oil',            3000, 300,  'ml'),
(3,  'All-Purpose Flour',   10000, 1000, 'g'),
(4,  'Eggs',                  100, 20,   'pcs'),
(5,  'Heavy Cream',          2000, 200,  'ml'),
(6,  'Parmesan Cheese',      1500, 150,  'g'),
(7,  'Chicken (whole leg)',    30,  8,   'pcs'),
(8,  'Beef Chuck',             20,  5,   'kg'),
(9,  'Duck Leg',               25,  6,   'pcs'),
(10, 'Salmon Fillet',          20,  5,   'pcs'),
(11, 'Sole Fillet',            15,  4,   'pcs'),
(12, 'Lamb Rack',              10,  3,   'pcs'),
(13, 'Veal Escalope',          12,  3,   'pcs'),
(14, 'Escargots (snails)',     200, 30,   'pcs'),
(15, 'Foie Gras',               8,  2,   'kg'),
(16, 'Salmon (tartare cut)',   15,  4,   'pcs'),
(17, 'Sourdough Bread',        30,  8,   'loaves'),
(18, 'Veal (thinly sliced)',   15,  4,   'pcs'),
(19, 'Arborio Rice',          5000, 500, 'g'),
(20, 'Black Truffle',          500, 50,  'g'),
(21, 'Camembert',              15,  4,   'pcs'),
(22, 'Mixed Cheese (5 types)', 10,  3,   'kg'),
(23, 'Charcuterie Mix',        10,  3,   'kg'),
(24, 'Pork & Pistachio Pate',  10,  3,   'kg'),
(25, 'Vanilla Bean',           50,  10,  'pcs'),
(26, 'Dark Chocolate',        2000, 200, 'g'),
(27, 'Apples',                 80,  20,  'pcs'),
(28, 'Choux Pastry Mix',      2000, 200, 'g'),
(29, 'Almond Sponge',         2000, 200, 'g'),
(30, 'Panna Cotta Base',      2000, 200, 'ml'),
(31, 'Onions',                 50,  10,  'kg'),
(32, 'Gruyere Cheese',        1000, 100, 'g'),
(33, 'Potato',                 50,  10,  'kg'),
(34, 'Tuna (canned)',          30,  8,   'pcs'),
(35, 'Romaine Lettuce',        20,  5,   'pcs'),
(36, 'Bordeaux Wine (bottle)',  24,  6,   'bottles'),
(37, 'Champagne (bottle)',      12,  3,   'bottles'),
(38, 'White Wine (bottle)',     18,  4,   'bottles'),
(39, 'Sparkling Water',         48, 12,  'bottles'),
(40, 'Coffee Beans',           3000,300, 'g'),
(41, 'Herbal Tea',             100, 20,  'pcs'),
(42, 'Mixed Berries',          2000,200, 'g'),
(43, 'Sugar',                 10000,1000,'g'),
(44, 'Red Wine (Burgundy)',     24,  6,  'bottles'),
(45, 'Garlic',                 200, 30,  'pcs'),
(46, 'Saffron',                 50,  5,  'g');

-- Product Recipes (all 33 menu items -> ingredients)
INSERT INTO product_recipe (product_id, inventory_id, quantity_used, unit) VALUES
-- 1: Escargots de Bourgogne
(1, 14, 6, 'pcs'),(1, 1, 20, 'g'),(1, 45, 2, 'pcs'),
-- 2: Foie Gras Torchon
(2, 15, 0.1, 'kg'),(2, 1, 15, 'g'),(2, 43, 10, 'g'),
-- 3: Tartare de Saumon
(3, 16, 1, 'pcs'),(3, 5, 30, 'ml'),
-- 4: Bruschetta Tricolore
(4, 17, 0.2, 'loaves'),(4, 2, 10, 'ml'),(4, 1, 10, 'g'),
-- 5: Vitello Tonnato
(5, 18, 1, 'pcs'),(5, 34, 1, 'pcs'),(5, 2, 10, 'ml'),
-- 6: Boeuf Bourguignon
(6, 8, 0.3, 'kg'),(6, 44, 0.2, 'bottles'),(6, 1, 30, 'g'),(6, 31, 0.1, 'kg'),
-- 7: Sole Meuniere
(7, 11, 1, 'pcs'),(7, 1, 40, 'g'),(7, 3, 30, 'g'),
-- 8: Rack of Lamb Provencal
(8, 12, 1, 'pcs'),(8, 3, 20, 'g'),(8, 1, 20, 'g'),
-- 9: Coq au Vin
(9, 7, 1, 'pcs'),(9, 44, 0.15, 'bottles'),(9, 31, 0.1, 'kg'),
-- 10: Risotto al Tartufo
(10, 19, 100, 'g'),(10, 20, 10, 'g'),(10, 6, 30, 'g'),(10, 1, 20, 'g'),
-- 11: Wiener Schnitzel
(11, 13, 1, 'pcs'),(11, 3, 50, 'g'),(11, 4, 2, 'pcs'),(11, 1, 30, 'g'),
-- 12: Bouillabaisse Marseillaise
(12, 10, 1, 'pcs'),(12, 46, 0.5, 'g'),(12, 2, 20, 'ml'),(12, 3, 20, 'g'),
-- 13: Duck Confit
(13, 9, 1, 'pcs'),(13, 33, 0.1, 'kg'),(13, 1, 30, 'g'),
-- 14: Creme Brulee Vanille
(14, 5, 150, 'ml'),(14, 25, 1, 'pcs'),(14, 4, 3, 'pcs'),(14, 43, 30, 'g'),
-- 15: Tarte Tatin
(15, 27, 3, 'pcs'),(15, 3, 80, 'g'),(15, 1, 50, 'g'),(15, 43, 40, 'g'),
-- 16: Souffle au Chocolat
(16, 26, 60, 'g'),(16, 4, 2, 'pcs'),(16, 43, 20, 'g'),(16, 1, 15, 'g'),
-- 17: Profiteroles
(17, 28, 100, 'g'),(17, 26, 50, 'g'),(17, 5, 80, 'ml'),
-- 18: Opera Cake
(18, 29, 80, 'g'),(18, 26, 40, 'g'),(18, 40, 10, 'g'),
-- 19: Panna Cotta aux Fruits Rouges
(19, 30, 150, 'ml'),(19, 42, 60, 'g'),(19, 43, 15, 'g'),
-- 20: Soupe a l'Oignon Gratinee
(20, 31, 0.2, 'kg'),(20, 32, 40, 'g'),(20, 17, 0.1, 'loaves'),(20, 1, 20, 'g'),
-- 21: Salade Nicoise
(21, 34, 1, 'pcs'),(21, 4, 2, 'pcs'),(21, 2, 15, 'ml'),
-- 22: Vichyssoise
(22, 33, 0.15, 'kg'),(22, 5, 100, 'ml'),(22, 31, 0.1, 'kg'),(22, 1, 15, 'g'),
-- 23: Salade Cesar Maison
(23, 35, 1, 'pcs'),(23, 6, 20, 'g'),(23, 4, 1, 'pcs'),(23, 2, 20, 'ml'),
-- 24: Bordeaux Rouge (Glass)
(24, 36, 0.17, 'bottles'),
-- 25: Champagne (Glass)
(25, 37, 0.17, 'bottles'),
-- 26: Cotes du Rhone Blanc
(26, 38, 0.17, 'bottles'),
-- 27: Perrier Sparkling Water
(27, 39, 1, 'bottles'),
-- 28: Cafe Francais
(28, 40, 14, 'g'),
-- 29: The des Alpes
(29, 41, 1, 'pcs'),
-- 30: Plateau de Fromages
(30, 22, 0.15, 'kg'),
-- 31: Camembert Chaud
(31, 21, 1, 'pcs'),(31, 17, 0.2, 'loaves'),
-- 32: Planche Charcuterie
(32, 23, 0.15, 'kg'),
-- 33: Pate en Croute
(33, 24, 0.15, 'kg'),(33, 3, 50, 'g');


-- Tables
INSERT INTO restaurant_table (table_number, capacity, location) VALUES
('T-01', 2, 'Window'),
('T-02', 4, 'Main Hall'),
('T-03', 4, 'Main Hall'),
('T-04', 6, 'Private Room'),
('T-05', 4, 'Patio'),
('T-06', 2, 'Bar Area'),
('T-07', 8, 'Event Space'),
('T-08', 4, 'Main Hall'),
('T-09', 4, 'Patio'),
('T-10', 2, 'Window');

-- Sample orders
INSERT INTO `order` (table_id, waiter_id, order_status, total_amount, payment_status, order_date) VALUES
(1, 1, 'completed', 52.97, 'paid', NOW() - INTERVAL 2 HOUR),
(2, 2, 'preparing', 67.96, 'unpaid', NOW() - INTERVAL 30 MINUTE),
(3, 1, 'pending', 28.98, 'unpaid', NOW() - INTERVAL 10 MINUTE),
(4, 3, 'served', 94.95, 'unpaid', NOW() - INTERVAL 1 HOUR);

INSERT INTO order_item (order_id, product_id, quantity, item_price, total_item_price) VALUES
(1, 1, 2, 8.99, 17.98),
(1, 4, 1, 24.99, 24.99),
(1, 8, 1, 8.99, 8.99),
(2, 5, 1, 39.99, 39.99),
(2, 10, 2, 11.99, 23.98),
(3, 7, 1, 16.99, 16.99),
(3, 11, 1, 9.99, 9.99),
(4, 5, 2, 39.99, 79.98),
(4, 8, 2, 8.99, 17.97);

INSERT INTO payment (order_id, payment_method, amount_paid, payment_status) VALUES
(1, 'credit_card', 52.97, 'completed');

INSERT INTO order_status_history (order_id, status, changed_by_role, changed_by_id) VALUES
(1, 'pending', 'waiter', 1),
(1, 'preparing', 'kitchen', 1),
(1, 'served', 'kitchen', 1),
(1, 'completed', 'waiter', 1),
(2, 'pending', 'waiter', 2),
(2, 'preparing', 'kitchen', 1),
(3, 'pending', 'waiter', 1),
(4, 'pending', 'waiter', 3),
(4, 'served', 'kitchen', 1);
