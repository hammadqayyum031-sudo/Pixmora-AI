<?php
// includes/admin.php - admin authorization helper

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

function require_admin() {
    $user = current_user();
    if (!$user || empty($user['is_admin'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

function admin_user() {
    return current_user();
}

function get_settings() {
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT * FROM settings ORDER BY id LIMIT 1");
    $s = $stmt->fetch(PDO::FETCH_ASSOC);
    return $s ?: [];
}

function update_settings($data) {
    $pdo = get_pdo();
    $allowed = ['site_name','site_tagline','logo_path','theme_primary','allow_registration','max_upload_bytes','maintenance_mode','default_engine','fallback_engine','allow_engine_selection'];
    $set = [];
    $params = [];
    foreach ($allowed as $k) {
        if (array_key_exists($k, $data)) {
            $set[] = "$k = :$k";
            $params[":$k"] = $data[$k];
        }
    }
    if (empty($set)) return false;
    $params[':now'] = date('c');
    $sql = "UPDATE settings SET " . implode(',', $set) . ", updated_at = :now WHERE id = (SELECT id FROM settings ORDER BY id LIMIT 1)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}
