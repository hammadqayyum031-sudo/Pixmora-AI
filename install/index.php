<?php
// install/index.php - PIXMORA AI Installer Wizard
session_start();
$lockFile = __DIR__ . '/../data/install.lock';
if (file_exists($lockFile)) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h2>PIXMORA AI - Installer</h2><p>Installation is locked. To reinstall, remove <code>data/install.lock</code> and try again.</p>";
    exit;
}

if (empty($_SESSION['install_csrf'])) $_SESSION['install_csrf'] = bin2hex(random_bytes(24));
function install_csrf_input() {
    return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars($_SESSION['install_csrf'])."\">';
}
function verify_install_csrf($token) {
    return hash_equals($_SESSION['install_csrf'] ?? '', (string)$token);
}

$step = (int)($_GET['step'] ?? 1);
$errors = [];
$ok = [];

function write_config_local($cfgArray) {
    $path = __DIR__ . '/../config.local.php';
    $content = "<?php\nreturn " . var_export($cfgArray, true) . ";\n";
    $bytes = file_put_contents($path, $content, LOCK_EX);
    @chmod($path, 0600);
    return $bytes !== false;
}
function create_lock() {
    $p = __DIR__ . '/../data';
    if (!is_dir($p)) mkdir($p, 0755, true);
    file_put_contents(__DIR__ . '/../data/install.lock', date('c') . " installed\n", LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_install_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'CSRF validation failed.';
    } else {
        if ($_POST['action'] === 'step2_save') {
            $_SESSION['installer_db'] = [
                'type' => $_POST['db_type'] ?? 'sqlite',
                'sqlite_path' => trim($_POST['sqlite_path'] ?? ''),
                'mysql_host' => trim($_POST['mysql_host'] ?? ''),
                'mysql_port' => trim($_POST['mysql_port'] ?? '3306'),
                'mysql_db' => trim($_POST['mysql_db'] ?? ''),
                'mysql_user' => trim($_POST['mysql_user'] ?? ''),
                'mysql_pass' => trim($_POST['mysql_pass'] ?? ''),
            ];
            header('Location: ?step=3'); exit;
        }

        if ($_POST['action'] === 'step3_import') {
            $cfg = $_SESSION['installer_db'] ?? null;
            if (!$cfg) { $errors[] = 'Database settings are missing.'; }
            else {
                try {
                    if ($cfg['type'] === 'sqlite') {
                        $path = $cfg['sqlite_path'] ?: __DIR__ . '/../data/database.sqlite';
                        $dir = dirname($path);
                        if (!is_dir($dir)) mkdir($dir, 0755, true);
                        $pdo = new PDO('sqlite:' . $path);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $_SESSION['installer_db']['resolved_path'] = $path;
                    } else {
                        $dsn = "mysql:host={$cfg['mysql_host']};port={$cfg['mysql_port']};charset=utf8mb4";
                        $pdo = new PDO($dsn, $cfg['mysql_user'], $cfg['mysql_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                        $dbName = $cfg['mysql_db'];
                        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        $pdo->exec("USE `{$dbName}`");
                        $_SESSION['installer_db']['resolved_dbname'] = $dbName;
                    }

                    $sqlFile = __DIR__ . '/../create_tables_final.sql';
                    if (!file_exists($sqlFile)) throw new Exception('SQL schema file not found');
                    $sql = file_get_contents($sqlFile);
                    $stmts = array_filter(array_map('trim', preg_split('/;[ \t]*\n/', $sql)));
                    foreach ($stmts as $s) {
                        if ($s) $pdo->exec($s);
                    }

                    $_SESSION['installer_db']['imported'] = true;
                    $ok[] = 'Database imported successfully.';
                    header('Location: ?step=4'); exit;
                } catch (Exception $e) {
                    $errors[] = 'Database import failed: ' . $e->getMessage();
                }
            }
        }

        if ($_POST['action'] === 'step4_admin') {
            $name = trim($_POST['admin_name'] ?? '');
            $email = trim($_POST['admin_email'] ?? '');
            $pass = $_POST['admin_password'] ?? '';
            $pass2 = $_POST['admin_password_confirm'] ?? '';
            if (!$name || !$email || !$pass) $errors[] = 'All fields are required.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
            if ($pass !== $pass2) $errors[] = 'Passwords do not match.';
            if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
            if (empty($errors)) {
                $cfg = $_SESSION['installer_db'] ?? null;
                try {
                    if ($cfg['type'] === 'sqlite') {
                        $pdo = new PDO('sqlite:' . $cfg['resolved_path']);
                    } else {
                        $dsn = "mysql:host={$cfg['mysql_host']};port={$cfg['mysql_port']};dbname={$cfg['resolved_dbname']};charset=utf8mb4";
                        $pdo = new PDO($dsn, $cfg['mysql_user'], $cfg['mysql_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    }
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, is_admin, credits, plan, created_at) VALUES (:n,:e,:ph,1,0,'pro', datetime('now'))");
                    $stmt->execute([':n'=>$name,':e'=>$email,':ph'=>$hash]);
                    $_SESSION['installer_admin_created'] = true;
                    $ok[] = 'Admin user created.';
                    header('Location: ?step=5'); exit;
                } catch (Exception $e) {
                    $errors[] = 'Failed to create admin user: ' . $e->getMessage();
                }
            }
        }

        if ($_POST['action'] === 'step5_license') {
            $cfg = $_SESSION['installer_db'] ?? null;
            if ($cfg) {
                try {
                    $finalCfg = ['app_env' => 'production', 'db' => ['type' => $cfg['type']]];
                    if ($cfg['type'] === 'sqlite') {
                        $finalCfg['db']['sqlite_path'] = $cfg['resolved_path'];
                    } else {
                        $finalCfg['db']['mysql_host'] = $cfg['mysql_host'];
                        $finalCfg['db']['mysql_port'] = $cfg['mysql_port'];
                        $finalCfg['db']['mysql_db'] = $cfg['resolved_dbname'];
                        $finalCfg['db']['mysql_user'] = $cfg['mysql_user'];
                        $finalCfg['db']['mysql_pass'] = $cfg['mysql_pass'];
                    }
                    $finalCfg['services'] = ['removebg_api_key' => ''];
                    if (write_config_local($finalCfg)) {
                        create_lock();
                        $ok[] = 'Installation complete!';
                        header('Location: ?step=6'); exit;
                    } else {
                        $errors[] = 'Failed to write config.local.php';
                    }
                } catch (Exception $e) {
                    $errors[] = 'License step failed: ' . $e->getMessage();
                }
            }
        }
    }
}

$requirements = [
    'php_version' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'pdo' => extension_loaded('pdo'),
    'curl' => extension_loaded('curl'),
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><title>PIXMORA AI - Installer</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <style>
    body{background:#f6f8fb;color:#071127;font-family:Inter,system-ui,Arial;padding:20px}
    .install-wrap{max-width:980px;margin:20px auto}
    .card{background:#fff;border-radius:12px;padding:18px;box-shadow:0 6px 30px rgba(2,6,23,0.06)}
    .err{color:#b91c1c}
  </style>
</head>
<body>
  <div class="install-wrap">
    <div class="card">
      <h2>PIXMORA AI — Installer</h2>
      <?php if ($errors): ?><div style="padding:10px;background:#fff1f2;border-radius:8px;margin-bottom:12px;"><strong class="err">Errors:</strong><ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      <?php if ($ok): ?><div style="padding:10px;background:#ecfdf5;border-radius:8px;margin-bottom:12px;"><ul><?php foreach($ok as $o): ?><li><?php echo htmlspecialchars($o); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      
      <?php if ($step === 1): ?>
        <h3>Requirements</h3>
        <ul>
          <li>PHP >= 7.4: <?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? '✓' : '✗'; ?></li>
          <li>PDO: <?php echo extension_loaded('pdo') ? '✓' : '✗'; ?></li>
          <li>cURL: <?php echo extension_loaded('curl') ? '✓' : '✗'; ?></li>
        </ul>
        <a class="btn btn-primary" href="?step=2">Continue</a>
      <?php elseif ($step === 2): ?>
        <h3>Database</h3>
        <form method="post">
          <?php echo install_csrf_input(); ?>
          <input type="hidden" name="action" value="step2_save">
          <label>DB Type</label>
          <select name="db_type"><option value="sqlite">SQLite</option><option value="mysql">MySQL</option></select>
          <label>SQLite path (optional)</label>
          <input type="text" name="sqlite_path" placeholder="/path/to/database.sqlite">
          <button class="btn btn-primary" type="submit">Save</button>
        </form>
      <?php elseif ($step === 3): ?>
        <h3>Import Database</h3>
        <form method="post">
          <?php echo install_csrf_input(); ?>
          <input type="hidden" name="action" value="step3_import">
          <button class="btn btn-primary" type="submit">Import SQL</button>
        </form>
      <?php elseif ($step === 4): ?>
        <h3>Admin Account</h3>
        <form method="post">
          <?php echo install_csrf_input(); ?>
          <input type="hidden" name="action" value="step4_admin">
          <label>Full Name</label><input type="text" name="admin_name" required>
          <label>Email</label><input type="email" name="admin_email" required>
          <label>Password</label><input type="password" name="admin_password" required>
          <label>Confirm</label><input type="password" name="admin_password_confirm" required>
          <button class="btn btn-primary" type="submit">Create</button>
        </form>
      <?php elseif ($step === 5): ?>
        <h3>Finalize</h3>
        <form method="post">
          <?php echo install_csrf_input(); ?>
          <input type="hidden" name="action" value="step5_license">
          <button class="btn btn-primary" type="submit">Complete Installation</button>
        </form>
      <?php elseif ($step === 6): ?>
        <h3>Installation Complete!</h3>
        <a class="btn btn-primary" href="../admin/login.php">Go to Admin</a>
        <a class="btn btn-ghost" href="../index.php">Open Site</a>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
