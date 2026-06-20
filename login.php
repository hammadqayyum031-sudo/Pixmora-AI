<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
if (current_user()) {
    header('Location: dashboard.php'); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Log in — PIXMORA AI</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="auth-page">
  <main class="auth-container card glass">
    <div class="auth-grid">
      <div class="auth-visual">
        <h2>Welcome back</h2>
        <p class="muted">Log in to access your account and HD exports.</p>
        <img src="assets/images/before.svg" alt="visual" class="auth-img">
      </div>

      <div class="auth-form">
        <h3>Log in to PIXMORA AI</h3>
        <form id="loginForm" method="post" action="process.php">
          <?php echo csrf_input_field(); ?>
          <input type="hidden" name="action" value="login">
          <label>Email</label>
          <input type="email" name="email" placeholder="you@company.com" required>

          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required>

          <div class="form-row">
            <label class="checkbox"><input type="checkbox"> Remember me</label>
            <a class="muted" href="#">Forgot?</a>
          </div>

          <button class="btn btn-primary" type="submit">Log in</button>
        </form>

        <p class="muted">Don't have an account? <a href="register.php">Start free</a></p>
      </div>
    </div>
  </main>
  <script src="assets/js/app.js"></script>
</body>
</html>