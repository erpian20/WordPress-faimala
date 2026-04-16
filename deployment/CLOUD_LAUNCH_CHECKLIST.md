# PowerUp Cloud Launch Checklist

This checklist is tailored for this workspace and should be completed before commercial launch.

## 1) Server Baseline

- OS: Ubuntu 22.04 LTS (recommended)
- Web: Nginx 1.24+
- PHP: 8.1 or 8.2 with php-fpm
- DB: MySQL 8.0+ or MariaDB 10.6+
- TLS: Let's Encrypt certificate

Install packages (Ubuntu example):

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-mysql php8.2-curl php8.2-gd php8.2-xml php8.2-mbstring php8.2-zip php8.2-intl unzip
```

## 2) Deploy Files

- Upload the WordPress project to: /var/www/powerup
- Ensure writable folders:
  - /var/www/powerup/wp-content/uploads
  - /var/www/powerup/wp-content/cache (if used)

Set ownership and permissions:

```bash
sudo chown -R www-data:www-data /var/www/powerup
sudo find /var/www/powerup -type d -exec chmod 755 {} \;
sudo find /var/www/powerup -type f -exec chmod 644 {} \;
```

## 3) Database Setup

Create production DB and user (replace placeholders):

```sql
CREATE DATABASE powerup_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'powerup_user'@'localhost' IDENTIFIED BY 'CHANGE_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON powerup_prod.* TO 'powerup_user'@'localhost';
FLUSH PRIVILEGES;
```

Import data dump:

```bash
mysql -u powerup_user -p powerup_prod < /path/to/powerup.sql
```

## 4) Critical wp-config.php Changes

Current local values in this repo must be changed for production:
- DB_NAME, DB_USER, DB_PASSWORD, DB_HOST
- WP_ENVIRONMENT_TYPE from local to production

Recommended production block in wp-config.php:

```php
define( 'DB_NAME', 'powerup_prod' );
define( 'DB_USER', 'powerup_user' );
define( 'DB_PASSWORD', 'CHANGE_STRONG_PASSWORD' );
define( 'DB_HOST', '127.0.0.1' );

define( 'WP_ENVIRONMENT_TYPE', 'production' );
define( 'WP_DEBUG', false );
define( 'DISALLOW_FILE_EDIT', true );
define( 'FORCE_SSL_ADMIN', true );
```

Set site URLs explicitly (replace with your real domain):

```php
define( 'WP_HOME', 'https://YOUR_DOMAIN' );
define( 'WP_SITEURL', 'https://YOUR_DOMAIN' );
```

## 5) Nginx Virtual Host

- Use the template file: deployment/nginx-powerup.conf
- Replace YOUR_DOMAIN and certificate paths
- Enable site and reload Nginx

```bash
sudo ln -s /var/www/powerup/deployment/nginx-powerup.conf /etc/nginx/sites-available/powerup
sudo ln -s /etc/nginx/sites-available/powerup /etc/nginx/sites-enabled/powerup
sudo nginx -t && sudo systemctl reload nginx
```

## 6) HTTPS Certificate

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d YOUR_DOMAIN -d www.YOUR_DOMAIN
```

## 7) WordPress URL Replacement (if migrated from local)

```bash
wp search-replace 'http://powerup.local' 'https://YOUR_DOMAIN' --all-tables
wp search-replace 'https://powerup.local' 'https://YOUR_DOMAIN' --all-tables
```

## 8) WooCommerce Production Validation

- Payment gateway in live mode
- Shipping zones and methods verified
- Tax rules verified
- Transactional emails verified
- Order creation and refund test passed

## 9) Theme Runtime Config Validation

In WordPress Admin:
- Appearance -> PowerUp Config
- Ensure contact email is a real business mailbox
- Ensure generated image base URL is either empty or your stable service endpoint

In WordPress Admin:
- Appearance -> PowerUp Health
- No launch warnings except expected environment-specific notes

## 10) Performance and Backups

- Enable page cache and object cache (if available)
- Enable CDN for static assets (optional)
- Configure daily DB backup + weekly full backup
- Verify restore process once

FastCGI cache operations (after deployment):

```bash
# Clear Nginx FastCGI cache directory
sudo /var/www/powerup/deployment/clear-fastcgi-cache.sh /tmp/nginx-fastcgi-cache

# Check cache HIT/MISS behavior on a public page
/var/www/powerup/deployment/check-fastcgi-cache.sh https://YOUR_DOMAIN/ 4
```

Expected behavior:
- First request: usually `X-FastCGI-Cache: MISS`
- Subsequent anonymous requests: should become `X-FastCGI-Cache: HIT`
- Logged-in/admin/checkout related traffic: should be `BYPASS`

SEO baseline quick-check:

```bash
/var/www/powerup/deployment/check-seo-basics.sh https://YOUR_DOMAIN
```

Expected behavior:
- `/robots.txt` returns `200`
- `/wp-sitemap.xml` returns `200`
- `Cache-Control` headers exist for robots/sitemap responses

wp-login rate limit quick-check:

```bash
/var/www/powerup/deployment/check-login-rate-limit.sh https://YOUR_DOMAIN 30
```

Expected behavior:
- Part of high-frequency login attempts should return `429`
- Script summary should show `429` greater than `0`

Integrated one-command health check:

```bash
/var/www/powerup/deployment/check-site-health.sh https://YOUR_DOMAIN
```

Expected behavior:
- Exit code is `0`
- Final summary reports `FAIL: 0`

## 11) Security Baseline

- Remove unused themes/plugins
- Enable WAF or fail2ban
- Restrict wp-admin by IP if possible
- Keep wp-login POST rate limiting enabled at Nginx layer
- Use strong admin password + 2FA

## 12) Final Go-Live Gate

Commercial launch is approved only if all are true:
- WP_ENVIRONMENT_TYPE is production
- DB account is not root
- HTTPS is valid and forced
- Checkout end-to-end test passed
- Email deliverability test passed
- Backup restore test passed
