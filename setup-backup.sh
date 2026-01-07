#!/bin/bash

# ERP Telegram Backup Auto Setup Script
# This script automatically configures the backup system after git pull

echo "=========================================="
echo "ERP Telegram Backup Setup"
echo "=========================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "⚠️  Please run as root (use: sudo bash setup-backup.sh)"
    exit 1
fi

# Get application directory
APP_DIR="/var/www/erp"
if [ ! -d "$APP_DIR" ]; then
    echo "❌ Error: Application directory not found at $APP_DIR"
    exit 1
fi

cd $APP_DIR

# Check if .env exists
if [ ! -f "$APP_DIR/.env" ]; then
    echo "❌ Error: .env file not found. Please copy .env.example to .env first"
    exit 1
fi

echo "✅ Application directory found: $APP_DIR"
echo ""

# Install PostgreSQL client if not exists
echo "📦 Checking PostgreSQL client..."
if ! command -v pg_dump &> /dev/null; then
    echo "Installing postgresql-client..."
    apt-get update -qq
    apt-get install -y postgresql-client > /dev/null 2>&1
    echo "✅ PostgreSQL client installed"
else
    echo "✅ PostgreSQL client already installed"
fi
echo ""

# Check for gzip
if ! command -v gzip &> /dev/null; then
    echo "Installing gzip..."
    apt-get install -y gzip > /dev/null 2>&1
    echo "✅ gzip installed"
fi

# Create backup directory
BACKUP_DIR="$APP_DIR/storage/app/backups"
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
    chown -R www-data:www-data "$BACKUP_DIR"
    chmod -R 775 "$BACKUP_DIR"
    echo "✅ Backup directory created: $BACKUP_DIR"
else
    echo "✅ Backup directory exists"
fi
echo ""

# Check if Telegram credentials are set
echo "🔍 Checking Telegram configuration..."
BOT_TOKEN=$(grep "^TELEGRAM_BOT_TOKEN=" $APP_DIR/.env | cut -d '=' -f2)
CHAT_ID=$(grep "^TELEGRAM_CHAT_ID=" $APP_DIR/.env | cut -d '=' -f2)

if [ -z "$BOT_TOKEN" ] || [ "$BOT_TOKEN" == "your_bot_token_here" ]; then
    echo ""
    echo "⚠️  Telegram Bot Token not configured!"
    echo ""
    echo "Please add to .env file:"
    echo "TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz"
    echo ""
    read -p "Enter Bot Token (or press Enter to skip): " INPUT_TOKEN
    if [ ! -z "$INPUT_TOKEN" ]; then
        sed -i "s|TELEGRAM_BOT_TOKEN=.*|TELEGRAM_BOT_TOKEN=$INPUT_TOKEN|" $APP_DIR/.env
        echo "✅ Bot Token saved"
    fi
fi

if [ -z "$CHAT_ID" ] || [ "$CHAT_ID" == "your_chat_id_here" ]; then
    echo ""
    echo "⚠️  Telegram Chat ID not configured!"
    echo ""
    echo "Please add to .env file:"
    echo "TELEGRAM_CHAT_ID=-100xxxxxxxxx"
    echo ""
    read -p "Enter Chat ID (or press Enter to skip): " INPUT_CHAT
    if [ ! -z "$INPUT_CHAT" ]; then
        sed -i "s|TELEGRAM_CHAT_ID=.*|TELEGRAM_CHAT_ID=$INPUT_CHAT|" $APP_DIR/.env
        echo "✅ Chat ID saved"
    fi
fi
echo ""

# Setup Laravel Scheduler Cron
echo "⏰ Setting up Laravel Scheduler..."
CRON_CMD="* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"

# Check if cron already exists
if crontab -l 2>/dev/null | grep -q "artisan schedule:run"; then
    echo "✅ Cron job already exists"
else
    # Add cron job
    (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
    echo "✅ Cron job added for Laravel Scheduler"
fi
echo ""

# Clear Laravel cache
echo "🧹 Clearing Laravel cache..."
cd $APP_DIR
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
echo "✅ Cache cleared"
echo ""

# Test backup (optional)
echo "=========================================="
echo "Setup Complete! 🎉"
echo "=========================================="
echo ""
echo "Backup Schedule:"
echo "  - Time: Daily at 2:00 AM (Bangladesh Time)"
echo "  - Format: Compressed SQL (.sql.gz)"
echo "  - Retention: 7 days"
echo ""
echo "Backup Location: $BACKUP_DIR"
echo ""

# Ask to test
read -p "Do you want to test the backup now? (y/n): " TEST_BACKUP
if [ "$TEST_BACKUP" == "y" ] || [ "$TEST_BACKUP" == "Y" ]; then
    echo ""
    echo "Running test backup..."
    cd $APP_DIR
    php artisan db:backup --telegram
    echo ""
    echo "Check your Telegram channel for the backup file!"
fi

echo ""
echo "=========================================="
echo "Useful Commands:"
echo "=========================================="
echo "Manual backup:           php artisan db:backup --telegram"
echo "View schedule:           php artisan schedule:list"
echo "Test schedule:           php artisan schedule:test"
echo "View backup files:       ls -lh $BACKUP_DIR"
echo ""
echo "For more details, see: TELEGRAM_BACKUP_SETUP.md"
echo ""
