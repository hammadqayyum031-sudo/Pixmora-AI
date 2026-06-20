<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/db.php';
require_auth();
$user = current_user();
$cfg = pixmora_cfg();
$pdo = get_pdo();
$engineFlagsFile = DATA_DIR . '/engine_flags.json';
$engineFlags = [];
if (file_exists($engineFlagsFile)) {
    $engineFlags = json_decode(file_get_contents($engineFlagsFile), true) ?: [];
    foreach ($engineFlags as $k => $v) {
        if (array_key_exists($k, $cfg['engines'] ?? [])) $cfg['engines'][$k] = (bool)$v;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard — PIXMORA AI</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .engine-toggle { display:flex; gap:8px; align-items:center; }
  </style>
</head>
<body>
  <header class="app-header">
    <div class="container header-inner">
      <a class="brand" href="index.php">
        <?php include 'assets/images/logo.svg'; ?>
        <span class="brand-text">PIXMORA AI</span>
      </a>
      <nav class="nav">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="#">Projects</a>
        <a href="#">Billing</a>
        <form method="post" action="logout.php" style="display:inline;">
          <?php echo csrf_input_field(); ?>
          <button class="btn btn-ghost" type="submit">Log out</button>
        </form>
      </nav>
    </div>
  </header>

  <main class="container dashboard" style="padding-top:36px;display:flex;gap:20px;">
    <aside class="sidebar card glass" style="width:240px;padding:18px;">
      <div style="margin-bottom:12px">
        <strong><?php echo h($user['name']); ?></strong><br>
        <span class="muted"><?php echo h($user['email']); ?></span>
      </div>
      <ul style="list-style:none;padding:0;margin:0;">
        <li style="padding:10px 8px;border-radius:8px;background:rgba(79,70,229,0.06);font-weight:700;">My Images</li>
      </ul>
    </aside>

    <section class="dash-main" style="flex:1;">
      <div class="dash-top">
        <h1>Welcome back, <?php echo h($user['name']); ?></h1>
        <p class="muted">Quick actions & recent uploads</p>
      </div>

      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:18px;">
        <div class="card glass">
          <h4>Upload & Remove Background</h4>
          <p class="muted">Choose engine and upload an image.</p>
          <div style="margin:10px 0;">
            <label for="engineSelect">Engine</label>
            <select id="engineSelect">
              <option value="imgly_free">IMG.LY — Free (Browser)</option>
              <option value="removebg">Remove.bg — Premium (Server)</option>
            </select>
          </div>
          <div>
            <input id="imageFile" type="file" accept="image/*">
          </div>
          <div style="margin-top:10px; display:flex; gap:10px">
            <button id="processBtn" class="btn btn-primary">Process</button>
            <a id="downloadLink" class="btn btn-outline" style="display:none" download>Download Result</a>
          </div>
          <div id="processingStatus" style="margin-top:12px" class="muted"></div>
          <div id="previewResult" style="margin-top:12px"></div>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <div class="muted">© <?php echo date('Y'); ?> PIXMORA AI</div>
    </div>
  </footer>

  <script>
    const USER_ID = <?php echo (int)$user['id']; ?>;
    const CSRF_TOKEN = "<?php echo h(generate_csrf_token()); ?>";
    const ENGINE_FLAGS = <?php echo json_encode($cfg['engines'] ?? []); ?>;
  </script>
  <script src="assets/js/app.js"></script>
</body>
</html>