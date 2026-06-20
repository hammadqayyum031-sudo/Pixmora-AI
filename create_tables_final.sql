PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  is_admin INTEGER NOT NULL DEFAULT 0,
  credits INTEGER NOT NULL DEFAULT 0,
  plan TEXT NOT NULL DEFAULT 'free',
  created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS usage_history (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  original_path TEXT NOT NULL,
  result_path TEXT,
  engine TEXT NOT NULL,
  status TEXT NOT NULL,
  meta TEXT,
  created_at TEXT DEFAULT (datetime('now')),
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_usage_user ON usage_history(user_id);

CREATE TABLE IF NOT EXISTS settings (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  site_name TEXT DEFAULT 'PIXMORA AI',
  site_tagline TEXT DEFAULT 'Smart AI Background Removal in Seconds',
  logo_path TEXT DEFAULT 'assets/images/logo.svg',
  theme_primary TEXT DEFAULT '#5ee7df',
  allow_registration INTEGER DEFAULT 1,
  max_upload_bytes INTEGER DEFAULT 10485760,
  maintenance_mode INTEGER DEFAULT 0,
  default_engine TEXT DEFAULT 'imgly_free',
  fallback_engine TEXT DEFAULT 'imgly_free',
  allow_engine_selection INTEGER DEFAULT 1,
  created_at TEXT DEFAULT (datetime('now')),
  updated_at TEXT
);
INSERT INTO settings (site_name, site_tagline, logo_path) SELECT 'PIXMORA AI','Smart AI Background Removal in Seconds','assets/images/logo.svg' WHERE NOT EXISTS (SELECT 1 FROM settings);

CREATE TABLE IF NOT EXISTS api_keys (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  key TEXT NOT NULL,
  provider TEXT,
  notes TEXT,
  active INTEGER DEFAULT 1,
  created_at TEXT DEFAULT (datetime('now')),
  last_used_at TEXT
);

CREATE TABLE IF NOT EXISTS licenses (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  purchase_code TEXT NOT NULL UNIQUE,
  domain TEXT,
  status TEXT NOT NULL DEFAULT 'inactive',
  metadata TEXT,
  activated_at TEXT,
  expires_at TEXT,
  created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS admin_logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  admin_user_id INTEGER,
  action TEXT NOT NULL,
  ip TEXT,
  meta TEXT,
  created_at TEXT DEFAULT (datetime('now')),
  FOREIGN KEY(admin_user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS error_logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  severity TEXT DEFAULT 'error',
  category TEXT,
  message TEXT,
  data TEXT,
  created_at TEXT DEFAULT (datetime('now'))
);
