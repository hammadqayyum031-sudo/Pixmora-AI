<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/license.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo "Method not allowed"; exit;
}
$code = trim($_POST['purchase_code'] ?? '');
if (!$code) { http_response_code(400); echo "Missing code"; exit; }
$res = deactivate_license_local($code);
echo json_encode(['ok' => (bool)$res]);
