<?php
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();
$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/csrf.php';
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF');
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['name'] ?? '');
        $key = trim($_POST['key'] ?? '');
        $provider = trim($_POST['provider'] ?? '');
        if ($name && $key) {
            $stmt = $pdo->prepare("INSERT INTO api_keys (name, key, provider, active) VALUES (:n,:k,:p,1)");
            $stmt->execute([':n'=>$name,':k'=>$key,':p'=>$provider]);
            admin_log(current_user()['id'], "api_key_add", ['name'=>$name,'provider'=>$provider]);
        }
    } elseif (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM api_keys WHERE id = :id");
        $stmt->execute([':id'=>$id]);
        admin_log(current_user()['id'], "api_key_delete", ['id'=>$id]);
    }
    header('Location: api-settings.php'); exit;
}

$keys = $pdo->query("SELECT id, name, provider, substr(key,1,6) as preview, active, created_at FROM api_keys ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>API Keys — Admin — PIXMORA AI</title>
<link rel="stylesheet" href="../assets/css/styles.css">
<style>body{background:#0b1220;color:#e6eef8}.card{background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01))}</style>
</head>
<body>
  <header class="site-header" style="background:linear-gradient(180deg,#071127,#000814);">
    <div class="container header-inner">
      <a class="brand" href="index.php"><?php include '../assets/images/logo.svg'; ?><span class="brand-text">Admin</span></a>
      <nav class="nav"><a href="index.php">Dashboard</a><a href="api-settings.php">API Keys</a></nav>
    </div>
  </header>

  <main class="container" style="padding-top:20px;">
    <div class="card">
      <h3>API Keys</h3>
      <form method="post" style="display:flex;gap:8px;align-items:center;margin-top:12px;">
        <?php echo csrf_input_field(); ?>
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="Name (e.g. Remove.bg)" required>
        <input type="text" name="provider" placeholder="Provider" required>
        <input type="text" name="key" placeholder="API Key" required style="min-width:300px;">
        <button class="btn btn-primary" type="submit">Add Key</button>
      </form>

      <div style="margin-top:12px;">
        <table style="width:100%;border-collapse:collapse;">
          <thead class="muted"><tr><th>ID</th><th>Name</th><th>Provider</th><th>Key</th><th>Active</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach($keys as $k): ?>
              <tr style="border-top:1px solid rgba(255,255,255,0.02);">
                <td><?php echo (int)$k['id']; ?></td>
                <td><?php echo h($k['name']); ?></td>
                <td><?php echo h($k['provider']); ?></td>
                <td><?php echo h($k['preview']); ?>••••</td>
                <td><?php echo $k['active'] ? 'Yes' : 'No'; ?></td>
                <td>
                  <form method="post" style="display:inline;">
                    <?php echo csrf_input_field(); ?>
                    <input type="hidden" name="delete_id" value="<?php echo (int)$k['id']; ?>">
                    <button class="btn btn-sm" onclick="return confirm('Delete key?')">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
