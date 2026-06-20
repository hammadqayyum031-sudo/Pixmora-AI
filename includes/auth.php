<?php
// includes/auth.php - authentication helpers

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

function current_user() {
    if (!isset($_SESSION['user_id'])) return null;
    static $user = false;
    if ($user !== false) return $user;
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT id, name, email, is_admin, credits, plan, created_at FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    return $user;
}

function require_auth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_csrf_or_die() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo "CSRF token mismatch.";
            exit;
        }
    }
}

function create_user($name, $email, $password, $is_admin = 0) {
    $pdo = get_pdo();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, is_admin, credits, plan, created_at) VALUES (:name, :email, :hash, :admin, :credits, :plan, :now)");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':hash' => $hash,
        ':admin' => $is_admin,
        ':credits' => 0,
        ':plan' => 'free',
        ':now' => date('c'),
    ]);
    return $pdo->lastInsertId();
}

function login_user($email, $password) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) return false;
    if (password_verify($password, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$u['id'];
        return true;
    }
    return false;
}

function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
