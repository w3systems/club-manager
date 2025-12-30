# Club Manager

A professional subscription management system for clubs and fitness centers built with PHP and MySQL.

## Quick Setup (No Build Process Required)

This version uses CDN assets and doesn't require Node.js/npm - perfect for shared hosting!

### Installation Steps

1. **Upload Files**
   * Upload all files to your web server.
   * Ensure document root points to the `/public_html` folder.

2. **Install PHP Dependencies**
   ```bash
   composer install

3. **Configure Environment**
   . Create a .env file in the root directory (or rename .env.example if available).
   . Add the following settings to .env:

4. **Import Database**
   . Import the database schema into your MySQL database (ensure you have the .sql file available):

5. **Set Permissions**
   . Ensure the server can write to the storage directory:

6. **Access Your Application**
   . Member Portal: https://yourdomain.com/
   . Admin Portal: https://yourdomain.com/admin

**File Structure**

club-manager/
├── public_html/
│   ├── index.php           # Entry point
│   ├── .htaccess          # URL rewriting
│   └── assets/js/app.js   # Simple JavaScript (no build required)
├── app/                   # Application code
├── storage/              # Logs and cache
├── .env                  # Environment config
└── composer.json         # PHP dependencies only

**Features**
✅ No Build Process - Uses Tailwind CDN and simple JavaScript 
✅ Shared Hosting Compatible - No Node.js required 
✅ Professional UI - Responsive design with collapsible sidebar 
✅ Alert System - Auto-dismiss success, manual dismiss errors 
✅ Complete Backend - All subscription management features

**Hosting Requirements**
PHP 8.1+
MySQL 8.0+ or MariaDB 10.5+
Apache with mod_rewrite (or Nginx/Litespeed)

**Changelog**
Please see for details on recent updates and changes.
