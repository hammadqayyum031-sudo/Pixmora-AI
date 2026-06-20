<?php
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();
$me = admin_user();
$pdo = get_pdo();

$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalImages = (int)$pdo->query("SELECT COUNT(*) FROM usage_history")->fetchColumn();

$engStmt = $pdo->query("SELECT engine, COUNT(*) AS cnt FROM usage_history GROUP BY engine");
$engineStats = $engStmt->fetchAll(PDO::FETCH_ASSOC);

$apiKeysCount = (int)$pdo->query("SELECT COUNT(*) FROM api_keys")->fetchColumn();

$settings = get_settings();
$maintenance = !empty($settings['maintenance_mode']);
$defaultEngine = $settings['default_engine'] ?? 'imgly_free';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — PIXMORA AI</title>
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
  body{background:#0b1220;color:#e6eef8}
  .card{background:linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02)); color:#e6eef8; border:1px solid rgba(255,255,255,0.04)}
  .sidebar{background:transparent}
  .muted{color:#9fb0d1}
</style>
</head>
<body>
  <header class="site-header" style="background:linear-gradient(180deg,#071127,#000814);">
    <div class="container header-inner">
      <a class="brand" href="../index.php">
        <?php include '../assets/images/logo.svg'; ?>
        <span class="brand-text">PIXMORA AI Admin</span>
      </a>
      <nav class="nav">
        <a href="index.php">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="api-settings.php">API Keys</a>
        <a href="settings.php">Settings</a>
        <a href="license.php">License</a>
        <form method="post" action="logout.php" style="display:inline;">
          <?php echo csrf_input_field(); ?>
          <button class="btn btn-ghost">Log out</button>
        </form>
      </nav>
    </div>
  </header>

  <main class="container" style="padding-top:20px;">
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:18px;">
      <div class="card">
        <h3>Total Users</h3>
        <div style="font-size:28px;font-weight:700;"><?php echo (int)$totalUsers; ?></div>
      </div>
      <div class="card">
        <h3>Total Processed Images</h3>
        <div style="font-size:28px;font-weight:700;"><?php echo (int)$totalImages; ?></div>
      </div>
      <div class="card">
        <h3>API Keys</h3>
        <div style="font-size:28px;font-weight:700;"><?php echo (int)$apiKeysCount; ?></div>
      </div>
      <div class="card">
        <h3>System Status</h3>
        <div class="muted">Maintenance</div>
        <div style="font-weight:700;"><?php echo $maintenance ? 'ON' : 'OFF'; ?></div>
        <div class="muted" style="margin-top:6px;">Default engine</div>
        <div style="font-weight:700;"><?php echo h($defaultEngine); ?></div>
      </div>
    </div>

    <section style="margin-top:18px;">
      <div class="card">
        <h3>Engine Usage</h3>
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-top:12px;">
          <?php foreach ($engineStats as $es): ?>
            <div style="padding:10px; border-radius:10px; background:rgba(255,255,255,0.02)">
              <strong><?php echo h($es['engine']); ?></strong>
              <div class="muted"><?php echo (int)$es['cnt']; ?> processed</div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
