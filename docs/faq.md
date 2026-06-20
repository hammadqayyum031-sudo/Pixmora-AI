# FAQ — PIXMORA AI

## Q: Installer says cannot write config.local.php
A: Ensure PHP user has write permission to project root.

## Q: Remove.bg processing fails
A: Verify your API key in Admin > API Keys.

## Q: How to move from SQLite to MySQL
A: Export SQLite data and import into MySQL. Update config.local.php.

## Q: How do I re-run the installer
A: Remove `data/install.lock` and `config.local.php`. Back up your DB first.
