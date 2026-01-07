# Telegram Database Backup Setup

## Overview
Automated daily PostgreSQL database backups sent to Telegram channel at 2:00 AM (Bangladesh Time).

## Setup Instructions

### 1. Create Telegram Bot
1. Open Telegram and search for [@BotFather](https://t.me/BotFather)
2. Send `/newbot` command
3. Follow instructions to create your bot
4. Copy the **Bot Token** (looks like: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)

### 2. Get Chat ID

#### For Private Channel:
1. Create a new Telegram channel
2. Add your bot as administrator to the channel
3. Send a message to the channel
4. Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
5. Find `"chat":{"id":-100xxxxxxxxx}` in the response
6. Copy the chat ID (including the minus sign)

#### For Private Chat:
1. Start a chat with your bot
2. Send any message
3. Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
4. Find your chat ID in the response

### 3. Configure VPS

Add these to your VPS `.env` file:

```bash
# Telegram Backup Configuration
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_CHAT_ID=-100xxxxxxxxx
```

### 4. Test Backup

Run manual backup test:

```bash
cd /var/www/erp
php artisan db:backup --telegram
```

You should receive a compressed database backup file in your Telegram channel.

### 5. Enable Scheduler (Cron Job)

Add Laravel scheduler to crontab:

```bash
crontab -e
```

Add this line:

```bash
* * * * * cd /var/www/erp && php artisan schedule:run >> /dev/null 2>&1
```

## Backup Schedule

- **Time:** Daily at 2:00 AM (Bangladesh Time)
- **Format:** Compressed SQL (.sql.gz)
- **Retention:** Last 7 days kept automatically
- **Location:** `/var/www/erp/storage/app/backups/`

## Backup Features

✅ Automatic daily backups
✅ Compressed files (gzip)
✅ Sent to Telegram automatically
✅ Old backups auto-deleted (7 days retention)
✅ Supports both MySQL and PostgreSQL
✅ Error logging
✅ File size information

## Manual Commands

```bash
# Manual backup (saved locally only)
php artisan db:backup

# Manual backup + send to Telegram
php artisan db:backup --telegram

# View scheduled tasks
php artisan schedule:list

# Test scheduler
php artisan schedule:test
```

## Troubleshooting

### Backup not sending to Telegram?

Check logs:
```bash
tail -f /var/www/erp/storage/logs/laravel.log
```

### Verify Telegram credentials:
```bash
cd /var/www/erp
php artisan tinker --execute="
\$service = new App\Services\TelegramService();
echo \$service->sendMessage('Test message from ERP backup system');
"
```

### Check if pg_dump is installed:
```bash
which pg_dump
```

If not installed:
```bash
apt-get install postgresql-client
```

## File Sizes

Typical backup sizes:
- Small database (1000 records): ~500 KB compressed
- Medium database (10,000 records): ~2-5 MB compressed
- Large database (100,000 records): ~20-50 MB compressed

Telegram file size limit: 50 MB per file

## Security Notes

⚠️ **Important:**
- Keep your Bot Token secret
- Use a private Telegram channel
- Never share backup files publicly
- Backups contain sensitive data
- Secure your VPS server access

## Support

If backups fail, check:
1. Database credentials in `.env`
2. Telegram Bot Token is valid
3. Chat ID is correct (with minus sign for channels)
4. Bot is admin in the channel
5. PostgreSQL client tools installed
6. Storage directory is writable
