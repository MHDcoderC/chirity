# Chirity

PHP/MySQL CMS for charity news sites. RTL layout, admin panel, jehadi sign-ups, donation card modal, and a browser-based installer.

Built by [Mohammad Sajjadi](https://mmdcode.top).

## Requirements

- PHP 8.1+ (`pdo_mysql`, `mbstring`, `json`, `gd` or `imagick`)
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite`

## Install

1. Upload the project to your host (root or subfolder).
2. Create a MySQL database and user in cPanel.
3. Open `/install.php` and complete the wizard.

Admin panel: `/admin/login.php`

## Other deployments

If you use this outside the original charity site, star the repo before install:

https://github.com/MHDcoderC/chirity

## Config

| File | Purpose |
|------|---------|
| `config/local.php` | Database credentials (created by installer) |
| `config/local.app.php` | `app_url`, environment, HTTPS |
| `.htaccess` | Rewrites and directory protection |

`config/local.php` and `config/installed.lock` are gitignored.
