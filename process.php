<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$cfg = pixmora_cfg();
$pdo = get_pdo();

$action = $_REQUEST['action'] ?? '';

header('Content-Type: application/json; charset=utf-8');

function j($d) { echo json_encode($d); exit; }

switch ($action) {
    case 'register':
        require_csrf_or_die();
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            j(['error' => 'Invalid email']);
        }
        if (strlen($password) < 8) j(['error' => 'Password must be at least 8 characters']);
        if (empty($name)) j(['error' => 'Name is required']);

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) j(['error' => 'Email already registered']);

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, is_admin, credits, plan, created_at) VALUES (:name, :email, :hash, 0, 0, 'free', datetime('now'))");
        $stmt->execute([':name' => $name, ':email' => $email, ':hash' => $hash]);
        $user_id = $pdo->lastInsertId();
        $_SESSION['user_id'] = (int)$user_id;
        j(['ok' => true, 'redirect' => 'dashboard.php']);
        break;

    case 'login':
        require_csrf_or_die();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        if (login_user($email, $password)) {
            j(['ok' => true, 'redirect' => 'dashboard.php']);
        } else {
            j(['error' => 'Invalid credentials']);
        }
        break;

    case 'logout':
        require_csrf_or_die();
        logout_user();
        j(['ok' => true, 'redirect' => 'index.php']);
        break;

    case 'upload_and_process':
        require_auth();
        require_csrf_or_die();
        $user = current_user();
        $engine = $_POST['engine'] ?? 'imgly_free';
        
        if ($engine === 'imgly_free') {
            $dataUrl = $_POST['data_url'] ?? '';
            if (empty($dataUrl) || !preg_match('#^data:image/(png|webp);base64,#', $dataUrl, $m)) {
                j(['error' => 'No valid processed image provided']);
            }
            $ext = ($m[1] === 'png') ? 'png' : 'webp';
            $data = preg_replace('#^data:image/[^;]+;base64,#', '', $dataUrl);
            $decoded = base64_decode($data);
            if ($decoded === false) j(['error' => 'Failed to decode image']);
            
            $userDir = UPLOAD_DIR . '/' . (int)$user['id'];
            if (!is_dir($userDir)) mkdir($userDir, 0755, true);
            $resFilename = 'res_free_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $resPath = $userDir . '/' . $resFilename;
            if (file_put_contents($resPath, $decoded) === false) {
                j(['error' => 'Failed to save file']);
            }
            $stmt = $pdo->prepare("INSERT INTO usage_history (user_id, original_path, result_path, engine, status, meta, created_at) VALUES (:uid,:orig,:res,:eng,:status,:meta,:now)");
            $stmt->execute([':uid' => $user['id'], ':orig' => '', ':res' => $resPath, ':eng' => 'imgly_free', ':status' => 'done', ':meta' => json_encode(['source' => 'client']), ':now' => date('c')]);
            j(['ok' => true, 'result_url' => str_replace($_SERVER['DOCUMENT_ROOT'] ?? '', '', $resPath), 'result_path' => $resPath]);
        }
        j(['error' => 'Unknown engine']);
        break;

    case 'get_usage':
        require_auth();
        $user = current_user();
        $stmt = $pdo->prepare("SELECT id, original_path, result_path, engine, status, meta, created_at FROM usage_history WHERE user_id = :uid ORDER BY id DESC LIMIT 50");
        $stmt->execute([':uid' => $user['id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        j(['ok' => true, 'data' => $rows]);
        break;

    default:
        http_response_code(400);
        j(['error' => 'Unknown action']);
}
