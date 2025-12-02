# Club Manager

A professional subscription management system for clubs and fitness centers built with PHP and MySQL.

## Quick Setup (No Build Process Required)

This version uses CDN assets and doesn't require Node.js/npm - perfect for shared hosting!

### Installation Steps

1. **Upload Files**
   - Upload all files to your web server
   - Ensure document root points to `/public` folder

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` with your settings:
   ```env
   APP_NAME="Your Club Name"
   APP_URL=https://yourdomain.com
   DB_HOST=localhost
   DB_DATABASE=your_database
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```

4. **Import Database**
   ```bash
   mysql -u username -p database_name < database_schema.sql
   ```

5. **Set Permissions**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 public/assets/
   ```

6. **Access Your Application**
   - Member Portal: `https://yourdomain.com/`
   - Admin Portal: `https://yourdomain.com/admin`

## File Structure (Simplified)

```
club-manager/
├── public/
│   ├── index.php           # Entry point
│   ├── .htaccess          # URL rewriting
│   └── assets/js/app.js   # Simple JavaScript (no build required)
├── app/                   # Application code
├── storage/              # Logs and cache
├── .env                  # Environment config
└── composer.json         # PHP dependencies only
```

## Features

✅ **No Build Process** - Uses Tailwind CDN and simple JavaScript
✅ **Shared Hosting Compatible** - No Node.js required
✅ **Professional UI** - Responsive design with collapsible sidebar
✅ **Alert System** - Auto-dismiss success, manual dismiss errors
✅ **Complete Backend** - All subscription management features

## Hosting Requirements

- PHP 8.1+
- MySQL 8.0+ or MariaDB 10.5+
- Apache with mod_rewrite (or Nginx)
- **No Node.js/npm required!**

Perfect for shared hosting environments!