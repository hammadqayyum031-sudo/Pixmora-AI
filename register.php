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
  <title>Get Started — PIXMORA AI</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="auth-page">
  <main class="auth-container card glass">
    <div class="auth-grid">
      <div class="auth-visual">
        <h2>Create your account</h2>
        <p class="muted">Start with a free plan — upgrade for HD exports.</p>
        <img src="assets/images/after.svg" alt="visual" class="auth-img">
      </div>

      <div class="auth-form">
        <h3>Sign up to PIXMORA AI</h3>
        <form id="registerForm" method="post" action="process.php">
          <?php echo csrf_input_field(); ?>
          <input type="hidden" name="action" value="register">
          <label>Full name</label>
          <input type="text" name="name" placeholder="Your name" required>

          <label>Email</label>
          <input type="email" name="email" placeholder="you@company.com" required>

          <label>Password</label>
          <input type="password" name="password" placeholder="Create a password (min 8 chars)" required>

          <button class="btn btn-primary" type="submit">Create account</button>
        </form>

        <p class="muted">By signing up you agree to our <a href="#">Terms & Privacy</a>.</p>

        <p class="muted">Already have an account? <a href="login.php">Log in</a></p>
      </div>
    </div>
  </main>
  <script src="assets/js/app.js"></script>
</body>
</html>