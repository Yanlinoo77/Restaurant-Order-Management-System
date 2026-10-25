# RestaurantOS — PHP MVC Order Management System

## 🍽️ System Overview

A full-featured restaurant order management system with three distinct portals:
- **Admin Portal** — Full control dashboard
- **Waiter Portal** — Order-taking and table management
- **Kitchen Display** — Real-time order board with Kanban interface

---

## 📁 Project Structure

```
restaurant/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── AdminController.php
│   │   ├── WaiterController.php
│   │   └── KitchenController.php
│   └── views/
│       ├── admin/       (dashboard, orders, tables, products, inventory, waiters, payments, reports, profile, kitchen)
│       ├── waiter/      (dashboard, orders, new_order, tables, payment, kitchen, order_detail)
│       ├── kitchen/     (dashboard)
│       ├── auth/        (login)
│       └── shared/      (admin/waiter/kitchen layout partials)
├── config/
│   ├── database.php     (DB connection & query helpers)
│   └── schema.sql       (Full database schema + seed data)
├── core/
│   └── App.php          (Router, auth helpers)
├── public/
│   ├── index.php        (Front controller)
│   ├── .htaccess        (URL rewriting)
│   ├── css/style.css    (All styles + animations)
│   └── js/app.js        (Charts, cart, modals, toasts)
└── .htaccess
```

---

## ⚙️ Setup Instructions

### Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with mod_rewrite enabled (or Nginx)
- XAMPP / WAMP / Laragon (recommended for local)

### Steps

1. **Copy the `restaurant/` folder** to your web server root (e.g., `htdocs/` for XAMPP)

2. **Create the database:**
   ```bash
   mysql -u root -p < restaurant/config/schema.sql
   ```
   Or open phpMyAdmin and import `config/schema.sql`

3. **Configure DB credentials** in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'restaurant_db');
   define('BASE_URL', 'http://localhost/restaurant/public');
   ```

4. **Enable mod_rewrite** in Apache (uncomment in httpd.conf):
   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
   Also ensure `AllowOverride All` is set for your directory.

5. **Visit:** `http://localhost/restaurant/public/`

---

## 🔐 Demo Login Credentials
> Password for ALL accounts: **password**

| Role    | Email                     |
|---------|---------------------------|
| Admin   | admin@restaurant.com      |
| Waiter  | alice@restaurant.com      |
| Kitchen | kitchen@restaurant.com    |

---

## ✨ Features

### Admin Portal
- 📊 Dashboard with live stats & charts
- 📋 Order management (view, add, filter by status)
- 🔥 Kitchen management view
- 🪑 Table management (add, edit, release)
- 💳 Payment processing & history
- 🍕 Product & category management
- 📦 Inventory tracking with stock alerts
- 👨‍🍳 Waiter management
- 📈 Monthly revenue reports with charts
- 👤 Profile management

### Waiter Portal
- 📊 Personal dashboard with my orders
- 📋 All orders with status filtering
- ➕ New order with menu grid & cart
- 🪑 Table availability view
- 💳 Payment tracking
- 🔥 Kitchen display (read-only)

### Kitchen Display
- 🎯 Dark-themed Kanban board (Pending → Preparing → Served)
- ⏱️ Elapsed time timers with urgency alerts
- 🔍 View order items in modal
- 🔥 One-click status updates
- 🔄 Auto-refresh every 20 seconds

---

## 🎨 Design Highlights
- Animated stat counters
- Canvas-based charts (no dependencies)
- Interactive shopping cart with real-time updates
- Toast notifications
- Smooth modal animations
- Responsive mobile layout
- Dark-mode kitchen display
- Progress bars for inventory levels
- Ripple button effects
