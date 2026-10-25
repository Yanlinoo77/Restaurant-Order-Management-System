<?php
require_once __DIR__ . '/../config/database.php';

class Router {
    private $routes = [];

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    public function resolve($url, $method) {
        $route = $this->routes[$method][$url] ?? null;
        if ($route) {
            return call_user_func($route);
        }
        http_response_code(404);
        echo "404 Not Found";
    }
}

class App {
    public static function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit;
    }

    public static function isLoggedIn($role) {
        session_start_if_not_started();
        return isset($_SESSION[$role . '_id']);
    }

    public static function requireLogin($role) {
        session_start_if_not_started();
        if (!isset($_SESSION[$role . '_id'])) {
            self::redirect('/login/' . $role);
        }
    }

    public static function getCurrentUser($role) {
        session_start_if_not_started();
        return $_SESSION[$role] ?? null;
    }
}

function session_start_if_not_started() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
