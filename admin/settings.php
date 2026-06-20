<?php
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();
$settings = get_settings();
$pdo = get_pdo();

$updated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/csrf.php';
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF');

    $updateData['site_name'] = $_POST['site_name'] ?? $settings['site_name'];
    $updateData['site_tagline'] = $_POST['site_tagline'] ?? $settings['site_tagline'];
    $updateData['theme_primary'] = $_POST['theme_primary'] ?? $settings['theme_primary'];
    $updateData['allow_registration'] = !empty($_POST['allow_registration']) ? 1 : 0;
    $updateData['max_upload_bytes'] = (int)($_POST['max_upload_bytes'] ?? $settings['max_upload_bytes']);
    $updateData['maintenance_mode'] = !empty($_POST['maintenance_mode']) ? 1 : 0;
    $updateData['default_engine'] = $_POST['default_engine'] ?? $settings['default_engine'];
    $updateData['fallback_engine'] = $_POST['fallback_engine'] ?? $settings['fallback_engine'];
    $updateData['allow_engine_selection'] = !empty($_POST['allow_engine_selection']) ? 1 : 0;

    update_settings($updateData);
    admin_log(current_user()['id'], 'updated_settings', $updateData);
    $updated = true;
    $settings = get_settings();
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Settings — Admin — PIXMORA AI</title>
<link rel="stylesheet" href="../assets/css/styles.css">
<style>body{background:#0b1220;color:#e6eef8}.card{background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01))}</style>
</head>
<body>
  <header class="site-header" style="background:linear-gradient(180deg,#071127,#000814);">
    <div class="container header-inner">
      <a class="brand" href="index.php"><?php include '../assets/images/logo.svg'; ?><span class="brand-text">Admin</span></a>
      <nav class="nav"><a href="index.php">Dashboard</a><a href="settings.php">Settings</a></nav>
    </div>
  </header>

  <main class="container" style="padding-top:20px;">
    <div class="card">
      <h3>System Settings</h3>
      <?php if ($updated): ?><div style="padding:8px;background:rgba(0,128,0,0.08);border-radius:8px;margin-bottom:8px;">Settings updated.</div><?php endif; ?>
      <form method="post">
        <?php echo csrf_input_field(); ?>
        <label>Site name</label>
        <input type="text" name="site_name" value="<?php echo h($settings['site_name']); ?>">

        <label>Tagline</label>
        <input type="text" name="site_tagline" value="<?php echo h($settings['site_tagline']); ?>">

        <label>Primary theme color</label>
        <input type="text" name="theme_primary" value="<?php echo h($settings['theme_primary']); ?>">

        <label>Allow registration</label>
        <input type="checkbox" name="allow_registration" <?php echo $settings['allow_registration'] ? 'checked' : ''; ?>>

        <label>Default engine</label>
        <input type="text" name="default_engine" value="<?php echo h($settings['default_engine']); ?>">

        <label>Fallback engine</label>
        <input type="text" name="fallback_engine" value="<?php echo h($settings['fallback_engine']); ?>">

        <label>Max upload bytes</label>
        <input type="number" name="max_upload_bytes" value="<?php echo (int)$settings['max_upload_bytes']; ?>">

        <label>Maintenance mode</label>
        <input type="checkbox" name="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>

        <div style="margin-top:12px;">
          <button class="btn btn-primary" type="submit">Save settings</button>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
