<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/license.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo "Method not allowed"; exit;
}
$code = trim($_POST['purchase_code'] ?? '');
$domain = trim($_POST['domain'] ?? '');
if (!$code || !$domain) {
    http_response_code(400); echo "Missing params"; exit;
}
$lic = get_license($code);
if (!$lic) {
    create_license($code, $domain, ['auto_created' => true]);
    echo json_encode(['ok'=>false,'message'=>'License not found locally; pending approval']);
    exit;
}

if ($lic['status'] !== 'active') {
    $res = activate_license_local($code, $domain);
    if ($res) {
        echo json_encode(['ok'=>true,'message'=>'Activated', 'code'=>$code]);
    } else {
        echo json_encode(['ok'=>false,'message'=>'Failed to activate']);
    }
    exit;
}

if (is_license_valid_for_domain($code, $domain)) {
    echo json_encode(['ok'=>true,'message'=>'Valid']);
} else {
    echo json_encode(['ok'=>false,'message'=>'Not valid for this domain']);
}
