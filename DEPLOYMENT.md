# ERP System - Production Deployment Guide

## Ubuntu VPS এ Deployment করার পদ্ধতি

### 1️⃣ Server Requirements

```bash
# System Update
sudo apt update && sudo apt upgrade -y

# Required Packages Install
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath nginx mysql-server git composer unzip
```

### 2️⃣ MySQL Database Setup

```bash
# MySQL Service Start
sudo systemctl start mysql
sudo systemctl enable mysql

# MySQL Secure Installation
sudo mysql_secure_installation
```

**Database তৈরি করুন:**
```bash
sudo mysql -u root -p
```

MySQL তে:
```sql
CREATE DATABASE erp_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'erp_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON erp_database.* TO 'erp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3️⃣ Project Setup

```bash
# Project Directory তৈরি
sudo mkdir -p /var/www/erp
cd /var/www/erp

# Git Clone (অথবা আপনার code upload করুন)
git clone https://github.com/gsagg03-cmyk/ERP.git .

# Permissions সেট করুন
sudo chown -R www-data:www-data /var/www/erp
sudo chmod -R 755 /var/www/erp
sudo chmod -R 775 /var/www/erp/storage
sudo chmod -R 775 /var/www/erp/bootstrap/cache
```

### 4️⃣ Environment Configuration

```bash
# .env file তৈরি
cp .env.example .env
nano .env
```

**.env Configuration:**
```env
APP_NAME="ERP System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-ip-or-domain:8888

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_database
DB_USERNAME=erp_user
DB_PASSWORD=YourStrongPassword123!

SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 5️⃣ Laravel Setup

```bash
# Composer Install
composer install --optimize-autoloader --no-dev

# Generate Application Key
php artisan key:generate

# Run Migrations
php artisan migrate:fresh --seed

# Cache Configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage Link
php artisan storage:link
```

### 6️⃣ Nginx Configuration (Port 8888)

```bash
sudo nano /etc/nginx/sites-available/erp
```

**Nginx Config:**
```nginx
server {
    listen 8888;
    server_name your-ip-or-domain;
    root /var/www/erp/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable Site
sudo ln -s /etc/nginx/sites-available/erp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7️⃣ Firewall Configuration

```bash
# Port 8888 খুলুন
sudo ufw allow 8888/tcp
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 8️⃣ PHP-FPM Configuration

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

নিশ্চিত করুন:
```ini
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
```

```bash
sudo systemctl restart php8.2-fpm
```

### 9️⃣ Test করুন

আপনার browser এ যান:
```
http://your-server-ip:8888
```

### 🔟 Demo Login Credentials

**Owner:**
- Phone: `01711111111`
- Password: `password`

**Manager:**
- Phone: `01722222222`
- Password: `password`

**Salesman 1:**
- Phone: `01733333333`
- Password: `password`

**Salesman 2:**
- Phone: `01744444444`
- Password: `password`

---

## 🔒 Production Security Tips

1. **Strong Passwords ব্যবহার করুন**
2. **APP_KEY অবশ্যই generate করুন**
3. **APP_DEBUG=false রাখুন**
4. **HTTPS setup করুন** (Let's Encrypt free SSL)
5. **Regular backup নিন**

---

## 🚀 Systemd Service (Optional - Auto-start)

Port 8888 তে Laravel Octane ব্যবহার করতে চাইলে:

```bash
sudo nano /etc/systemd/system/erp.service
```

```ini
[Unit]
Description=ERP Laravel Application
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/erp
ExecStart=/usr/bin/php /var/www/erp/artisan serve --host=0.0.0.0 --port=8888
Restart=always

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable erp
sudo systemctl start erp
```

---

## 📝 Maintenance Commands

```bash
# Cache Clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize

# Database Backup
mysqldump -u erp_user -p erp_database > backup_$(date +%Y%m%d).sql
```

---

## ⚠️ Troubleshooting

**Permission Issues:**
```bash
sudo chown -R www-data:www-data /var/www/erp
sudo chmod -R 755 /var/www/erp
sudo chmod -R 775 /var/www/erp/storage
sudo chmod -R 775 /var/www/erp/bootstrap/cache
```

**Nginx Error:**
```bash
sudo nginx -t
sudo tail -f /var/log/nginx/error.log
```

**PHP-FPM Error:**
```bash
sudo tail -f /var/log/php8.2-fpm.log
```

**Laravel Log:**
```bash
tail -f /var/www/erp/storage/logs/laravel.log
```

---

✅ **Deployment সম্পন্ন!** আপনার ERP System এখন production ready!
