<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (current_user() && current_user()['is_admin']) {
    header('Location: index.php'); exit;
}

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'CSRF token mismatch';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (login_user($email, $password)) {
            $u = current_user();
            if (empty($u['is_admin'])) {
                logout_user();
                $err = 'Account is not an admin';
            } else {
                header('Location: index.php'); exit;
            }
        } else {
            $err = 'Invalid credentials';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — PIXMORA AI</title>
<link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="auth-page">
  <main class="auth-container card glass" style="max-width:720px;">
    <div class="auth-grid">
      <div class="auth-visual">
        <h2>Admin Control</h2>
        <p class="muted">Sign in with your admin account to manage the system.</p>
      </div>
      <div class="auth-form">
        <?php if ($err): ?><div class="card" style="background:#ffecec;color:#8b0000;padding:10px;border-radius:8px;"><?php echo h($err); ?></div><?php endif; ?>
        <form method="post">
          <?php echo csrf_input_field(); ?>
          <label>Email</label>
          <input type="email" name="email" required>
          <label>Password</label>
          <input type="password" name="password" required>
          <button class="btn btn-primary" type="submit">Sign in</button>
        </form>
      </div>
    </div>
  </main>
</body>
</html>
