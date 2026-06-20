# Installation Guide — PIXMORA AI

## Server Requirements
- PHP 7.4 or higher
- Extensions: PDO, pdo_sqlite or pdo_mysql, cURL, OpenSSL, JSON
- Recommended: HTTPS, 128MB PHP memory

## Step-by-Step Install
1. Upload files to your web server
2. Ensure `data/` and `uploads/` are writable by PHP
3. Navigate to `https://your-domain/install/`
4. Follow the installer wizard

## Post-Install
- Remove or protect the `install/` directory
- Visit `/admin/login.php` to configure settings

## Troubleshooting
- If installer cannot write config: verify file permissions
- For MySQL errors: check host, port, user, and privileges
