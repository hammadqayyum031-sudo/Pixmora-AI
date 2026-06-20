<?php
// includes/license.php - license management helpers

require_once __DIR__ . '/db.php';

function create_license($purchaseCode, $domain = null, $metadata = null) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("INSERT INTO licenses (purchase_code, domain, metadata, status) VALUES (:code,:domain,:meta,'inactive')");
    $stmt->execute([
        ':code' => $purchaseCode,
        ':domain' => $domain,
        ':meta' => $metadata ? json_encode($metadata) : null
    ]);
    return $pdo->lastInsertId();
}

function activate_license_local($purchaseCode, $domain = null, $expiresAt = null) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("UPDATE licenses SET status = 'active', domain = :domain, activated_at = :now, expires_at = :expires WHERE purchase_code = :code");
    $stmt->execute([
        ':domain' => $domain,
        ':now' => date('c'),
        ':expires' => $expiresAt,
        ':code' => $purchaseCode
    ]);
    return $stmt->rowCount() > 0;
}

function deactivate_license_local($purchaseCode) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("UPDATE licenses SET status = 'inactive', domain = NULL WHERE purchase_code = :code");
    $stmt->execute([':code' => $purchaseCode]);
    return $stmt->rowCount() > 0;
}

function get_license($purchaseCode) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT * FROM licenses WHERE purchase_code = :code LIMIT 1");
    $stmt->execute([':code' => $purchaseCode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['metadata']) {
        $row['metadata'] = json_decode($row['metadata'], true);
    }
    return $row ?: null;
}

function is_license_valid_for_domain($purchaseCode, $domain) {
    $lic = get_license($purchaseCode);
    if (!$lic) return false;
    if ($lic['status'] !== 'active') return false;
    if (!empty($lic['expires_at']) && strtotime($lic['expires_at']) < time()) return false;
    if (!empty($lic['domain'])) {
        $licensed = preg_quote($lic['domain'], '#');
        if (preg_match('#' . $licensed . '$#i', $domain)) {
            return true;
        }
        return false;
    }
    return true;
}
