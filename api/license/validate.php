<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/license.php';

$code = trim($_POST['code'] ?? '');
$domain = trim($_POST['domain'] ?? '');
if (!$code) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'missing_code']);
    exit;
}

$lic = get_license($code);
if (!$lic) {
    echo json_encode(['ok'=>false,'status'=>'not_found']);
    exit;
}

$valid = is_license_valid_for_domain($code, $domain);
$out = [
    'ok' => true,
    'purchase_code' => $lic['purchase_code'],
    'status' => $lic['status'],
    'domain' => $lic['domain'],
    'activated_at' => $lic['activated_at'],
    'expires_at' => $lic['expires_at'],
    'valid_for_domain' => $valid,
];
echo json_encode($out);
