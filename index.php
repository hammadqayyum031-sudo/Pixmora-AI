<?php
// index.php - Landing page (keeps the premium UI, minimal adjustments for auth)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>PIXMORA AI — Smart AI Background Removal in Seconds</title>
  <meta name="description" content="Remove background instantly with AI. Fast, precise, and beautiful UI for background removal." />
  <link rel="icon" href="assets/images/logo.svg" type="image/svg+xml" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="brand" href="index.php" aria-label="PIXMORA AI">
        <?php include 'assets/images/logo.svg'; ?>
        <span class="brand-text">PIXMORA AI</span>
      </a>
      <nav class="nav">
        <a href="#features">Features</a>
        <a href="#demo">Demo</a>
        <a href="#pricing">Pricing</a>
        <a href="#faq">FAQ</a>
        <?php if ($user): ?>
          <a class="btn btn-ghost" href="dashboard.php">Dashboard</a>
          <form method="post" action="logout.php" style="display:inline;">
            <?php require_once __DIR__ . '/includes/csrf.php'; echo csrf_input_field(); ?>
            <button class="btn btn-outline" type="submit">Log out</button>
          </form>
        <?php else: ?>
          <a class="btn btn-ghost" href="login.php">Log in</a>
          <a class="btn btn-primary" href="register.php">Start Free</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero">
      <div class="gradient-bg"></div>
      <div class="container hero-inner">
        <div class="hero-copy">
          <h1 class="title">Remove Background Instantly with AI</h1>
          <p class="subtitle">Smart AI-powered image cutout tool — precise results, lightning fast. Try browser mode or upgrade to HD Premium.</p>
          <div class="hero-ctas">
            <a class="btn btn-cta" href="register.php">Start Free</a>
            <a class="btn btn-ghost" href="#demo">Try Demo</a>
          </div>
        </div>
        <div class="hero-visual">
          <div class="card glass card-demo">
            <div class="badge">Browser Mode</div>
            <div class="mock-device">
              <div class="device-screen">
                <img src="assets/images/before.svg" alt="before" class="mock-img" id="mockBefore" />
                <div class="mock-overlay">
                  <div class="stat">
                    <strong>0.8s</strong>
                    <span>Processing</span>
                  </div>
                  <div class="stat">
                    <strong>HD</strong>
                    <span>Quality</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer muted">No installs. Edit in-browser, export transparent PNG.</div>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="section">
      <div class="container">
        <h2 class="section-title">Why PIXMORA AI</h2>
        <p class="section-sub">Designed for creators, e-commerce, and agencies — speed and precision in a beautiful UI.</p>
        <div class="grid features-grid">
          <div class="feature card glass">
            <div class="icon">⚡</div>
            <h3>Fast Processing</h3>
            <p>Optimized browser pipeline to remove backgrounds within seconds.</p>
          </div>
          <div class="feature card glass">
            <div class="icon">✂️</div>
            <h3>AI Precision Cutout</h3>
            <p>Edge-aware segmentation for hair, fur, and detailed shapes.</p>
          </div>
          <div class="feature card glass">
            <div class="icon">🧭</div>
            <h3>Free Browser Mode</h3>
            <p>Quick edits and exports without sign up — try a sample instantly.</p>
          </div>
          <div class="feature card glass">
            <div class="icon">📷</div>
            <h3>HD Premium Mode</h3>
            <p>Premium servers for high-resolution results and batch processing.</p>
          </div>
        </div>
      </div>
    </section>

    <section id="pricing" class="section">
      <div class="container">
        <h2 class="section-title">Pricing</h2>
        <p class="section-sub">Simple plans for hobbyists to teams. Upgrade anytime.</p>
        <div class="pricing-grid">
          <div class="card glass price-card">
            <div class="price-badge">Free</div>
            <h3>Free</h3>
            <p class="muted">Best for testing and occasional images</p>
            <div class="price-value">Free</div>
            <ul class="features-list">
              <li>Browser-mode processing</li>
              <li>Basic quality (suitable for web)</li>
              <li>1 export per minute</li>
            </ul>
            <a class="btn btn-outline" href="register.php">Get Free</a>
          </div>
          <div class="card glass price-card price-popular">
            <div class="price-badge">Popular</div>
            <h3>Pro</h3>
            <p class="muted">Creators & small teams</p>
            <div class="price-value">$9 / month</div>
            <ul class="features-list">
              <li>Up to 10 HD exports / month</li>
              <li>Priority processing</li>
              <li>Batch mode</li>
            </ul>
            <a class="btn btn-primary" href="register.php">Start Pro</a>
          </div>
          <div class="card glass price-card">
            <h3>Business</h3>
            <p class="muted">For high-volume teams & e-commerce</p>
            <div class="price-value">Contact Sales</div>
            <ul class="features-list">
              <li>Custom SLA</li>
              <li>Team seats & billing</li>
              <li>Dedicated support</li>
            </ul>
            <a class="btn btn-outline" href="register.php">Contact Sales</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <div class="footer-left">
        <a class="brand" href="index.php">
          <?php include 'assets/images/logo.svg'; ?>
          <span class="brand-text">PIXMORA AI</span>
        </a>
        <p class="muted">Smart AI Background Removal in Seconds</p>
      </div>
      <div class="footer-right">
        <div class="muted">© <?php echo date('Y'); ?> PIXMORA AI. All rights reserved.</div>
      </div>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
</body>
</html>