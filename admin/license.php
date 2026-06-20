<?php
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/license.php';
require_admin();
$pdo = get_pdo();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/csrf.php';
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF');

    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $code = trim($_POST['purchase_code'] ?? '');
        $domain = trim($_POST['domain'] ?? '');
        if ($code) {
            create_license($code, $domain, ['created_by'=>current_user()['id']]);
            admin_log(current_user()['id'], 'license_create', ['code'=>$code,'domain'=>$domain]);
            $msg = 'Created license';
        }
    } elseif ($action === 'activate') {
        $code = trim($_POST['purchase_code'] ?? '');
        $domain = trim($_POST['domain'] ?? '');
        if ($code) {
            activate_license_local($code, $domain);
            admin_log(current_user()['id'], 'license_activate', ['code'=>$code,'domain'=>$domain]);
            $msg = 'Activated';
        }
    } elseif ($action === 'deactivate') {
        $code = trim($_POST['purchase_code'] ?? '');
        if ($code) {
            deactivate_license_local($code);
            admin_log(current_user()['id'], 'license_deactivate', ['code'=>$code]);
            $msg = 'Deactivated';
        }
    }
}

$licenses = $pdo->query("SELECT id, purchase_code, domain, status, activated_at, expires_at, created_at FROM licenses ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>License — Admin — PIXMORA AI</title>
<link rel="stylesheet" href="../assets/css/styles.css">
<style>body{background:#0b1220;color:#e6eef8}.card{background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01))}</style>
</head>
<body>
  <header class="site-header" style="background:linear-gradient(180deg,#071127,#000814);">
    <div class="container header-inner">
      <a class="brand" href="index.php"><?php include '../assets/images/logo.svg'; ?><span class="brand-text">Admin</span></a>
      <nav class="nav"><a href="index.php">Dashboard</a><a href="license.php">License</a></nav>
    </div>
  </header>

  <main class="container" style="padding-top:20px;">
    <div class="card">
      <h3>Licenses</h3>
      <?php if ($msg): ?><div style="padding:8px;background:rgba(0,128,0,0.08);border-radius:8px;margin-bottom:8px;"><?php echo h($msg); ?></div><?php endif; ?>
      <form method="post" style="display:flex;gap:8px;margin-bottom:12px;">
        <?php echo csrf_input_field(); ?>
        <input type="hidden" name="action" value="create">
        <input type="text" name="purchase_code" placeholder="Purchase code" required>
        <input type="text" name="domain" placeholder="Domain (example.com)">
        <button class="btn btn-primary" type="submit">Create</button>
      </form>

      <table style="width:100%;border-collapse:collapse;">
        <thead class="muted"><tr><th>ID</th><th>Code</th><th>Domain</th><th>Status</th><th>Activated</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($licenses as $l): ?>
            <tr style="border-top:1px solid rgba(255,255,255,0.02);">
              <td><?php echo (int)$l['id']; ?></td>
              <td><?php echo h(substr($l['purchase_code'], 0, 20)); ?>...</td>
              <td><?php echo h($l['domain']); ?></td>
              <td><?php echo h($l['status']); ?></td>
              <td><?php echo h($l['activated_at']); ?></td>
              <td>
                <form method="post" style="display:inline;">
                  <?php echo csrf_input_field(); ?>
                  <input type="hidden" name="purchase_code" value="<?php echo h($l['purchase_code']); ?>">
                  <input type="hidden" name="domain" value="<?php echo h($l['domain']); ?>">
                  <input type="hidden" name="action" value="activate">
                  <button class="btn btn-sm">Activate</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
