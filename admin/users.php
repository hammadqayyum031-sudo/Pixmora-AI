<?php
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();
$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/csrf.php';
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF');
    $act = $_POST['act'] ?? '';
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($act && $uid) {
        if ($act === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id'=>$uid]);
            admin_log(current_user()['id'], "deleted_user:$uid");
        } elseif ($act === 'block') {
            $stmt = $pdo->prepare("UPDATE users SET plan = 'blocked' WHERE id = :id");
            $stmt->execute([':id'=>$uid]);
            admin_log(current_user()['id'], "blocked_user:$uid");
        } elseif ($act === 'unblock') {
            $stmt = $pdo->prepare("UPDATE users SET plan = 'free' WHERE id = :id");
            $stmt->execute([':id'=>$uid]);
            admin_log(current_user()['id'], "unblocked_user:$uid");
        } elseif ($act === 'reset_usage') {
            $stmt = $pdo->prepare("DELETE FROM usage_history WHERE user_id = :id");
            $stmt->execute([':id'=>$uid]);
            admin_log(current_user()['id'], "reset_usage:$uid");
        }
    }
    header('Location: users.php'); exit;
}

$stmt = $pdo->query("SELECT id, name, email, is_admin, plan, credits, created_at FROM users ORDER BY id DESC LIMIT 200");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Users — Admin — PIXMORA AI</title>
<link rel="stylesheet" href="../assets/css/styles.css">
<style>body{background:#0b1220;color:#e6eef8}.card{background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01))}</style>
</head>
<body>
  <header class="site-header" style="background:linear-gradient(180deg,#071127,#000814);">
    <div class="container header-inner">
      <a class="brand" href="index.php"><?php include '../assets/images/logo.svg'; ?><span class="brand-text">Admin</span></a>
      <nav class="nav">
        <a href="index.php">Dashboard</a>
        <a href="users.php">Users</a>
      </nav>
    </div>
  </header>

  <main class="container" style="padding-top:20px;">
    <div class="card">
      <h3>Users</h3>
      <div class="muted">Manage registered users</div>
      <table style="width:100%; margin-top:12px; border-collapse:collapse;">
        <thead style="text-align:left;">
          <tr class="muted"><th>ID</th><th>Name</th><th>Email</th><th>Plan</th><th>Admin</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr style="border-top:1px solid rgba(255,255,255,0.02);">
              <td><?php echo (int)$u['id']; ?></td>
              <td><?php echo h($u['name']); ?></td>
              <td><?php echo h($u['email']); ?></td>
              <td><?php echo h($u['plan']); ?></td>
              <td><?php echo $u['is_admin'] ? 'Yes' : 'No'; ?></td>
              <td>
                <form method="post" style="display:inline;">
                  <?php echo csrf_input_field(); ?>
                  <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                  <button class="btn btn-sm btn-outline" name="act" value="reset_usage">Reset</button>
                  <button class="btn btn-sm" name="act" value="delete" onclick="return confirm('Delete user?')">Delete</button>
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
