<?php
// includes/helpers.php - utility helpers

require_once __DIR__ . '/db.php';

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function save_uploaded_file($fileInput, $destDir, $allowedExt = ['png','jpg','jpeg','svg']) {
    if (!isset($_FILES[$fileInput])) return ['error' => 'No file uploaded'];
    $f = $_FILES[$fileInput];
    if ($f['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload error code ' . $f['error']];
    $origName = basename($f['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) return ['error' => 'Unsupported file type'];
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    $target = $destDir . '/' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $target)) return ['error' => 'Failed to move uploaded file'];
    return ['path' => $target];
}

function admin_log($adminUserId, $action, $meta = null) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_user_id, action, ip, meta) VALUES (:uid,:act,:ip,:meta)");
    $stmt->execute([
        ':uid' => $adminUserId,
        ':act' => $action,
        ':ip'  => $_SERVER['REMOTE_ADDR'] ?? '',
        ':meta' => $meta ? json_encode($meta) : null
    ]);
}

function error_log_insert($severity, $category, $message, $data = null) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("INSERT INTO error_logs (severity, category, message, data) VALUES (:sev,:cat,:msg,:data)");
    $stmt->execute([
        ':sev' => $severity,
        ':cat' => $category,
        ':msg' => $message,
        ':data' => $data ? json_encode($data) : null
    ]);
}
