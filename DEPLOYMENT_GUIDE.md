# CineCraze Deployment Guide

This guide covers deployment on various hosting platforms including free and paid hosting.

## Quick Start

### Option 1: Web Installer (Recommended)
1. Upload all files to your hosting
2. Navigate to `http://yourdomain.com/install.php`
3. Follow the step-by-step wizard
4. Delete `install.php` after installation

### Option 2: Manual Installation
1. Upload all files to your hosting
2. Create a MySQL database
3. Import `database.sql`
4. Copy `includes/config.sample.php` to `includes/config.php`
5. Edit `includes/config.php` with your database credentials
6. Access your site

## Deployment on Different Hosting Types

### 1. Free Hosting (InfinityFree, 000webhost, Freehostia, etc.)

**Requirements:**
- PHP 7.4+
- MySQL 5.7+
- 50MB+ storage

**Steps:**
1. **Sign up** for free hosting account
2. **Create MySQL database** in control panel
   - Note down: database name, username, password
3. **Upload files:**
   - Use File Manager or FTP
   - Upload to `public_html` or `htdocs` directory
4. **Run installer:**
   - Go to `http://yourusername.freehosting.com/install.php`
   - Enter database credentials
   - Complete installation
5. **Delete install.php** via File Manager

**Free Hosting Tips:**
- Use `.htaccess` for configuration (already included)
- Enable error logging: Check control panel
- Storage limits: Clean old content regularly
- Free hosting may have ads: Upgrade if needed

**Recommended Free Hosts:**
- InfinityFree (unlimited, no ads on files)
- 000webhost (decent PHP support)
- Freehostia (5 MySQL databases)

### 2. Shared Hosting (HostGator, Bluehost, Namecheap, etc.)

**Steps:**
1. **Login to cPanel**
2. **Create Database:**
   - Go to MySQL Databases
   - Create new database (e.g., `username_cinecraze`)
   - Create user and set password
   - Add user to database with ALL PRIVILEGES
3. **Upload Files:**
   - Use cPanel File Manager or FTP client
   - Upload to `public_html/cinecraze` or subdomain folder
   - Extract zip if uploaded as archive
4. **Set Permissions:**
   ```
   chmod 755 uploads/
   chmod 755 includes/
   ```
5. **Run Installer:**
   - Navigate to `http://yourdomain.com/install.php`
   - Use database credentials from step 2
6. **Configure Domain:**
   - If using subdomain, update in admin settings

**cPanel Tips:**
- Use cPanel File Manager (faster than FTP)
- PHP version: Set to 7.4+ in "Select PHP Version"
- SSL: Enable free SSL in cPanel (Let's Encrypt)
- Email: Set up admin email for notifications

### 3. VPS / Cloud Hosting (DigitalOcean, Linode, AWS, etc.)

**Prerequisites:**
```bash
sudo apt update
sudo apt install apache2 php8.1 mysql-server
sudo apt install php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl
```

**Steps:**
1. **Create Database:**
```bash
sudo mysql -u root -p
CREATE DATABASE cinecraze CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cinecraze_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON cinecraze.* TO 'cinecraze_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

2. **Upload Files:**
```bash
cd /var/www/html
sudo git clone your-repo cinecraze
cd cinecraze
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
```

3. **Import Database:**
```bash
mysql -u cinecraze_user -p cinecraze < database.sql
```

4. **Configure Apache:**
```bash
sudo nano /etc/apache2/sites-available/cinecraze.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/cinecraze
    
    <Directory /var/www/html/cinecraze>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/cinecraze_error.log
    CustomLog ${APACHE_LOG_DIR}/cinecraze_access.log combined
</VirtualHost>
```

5. **Enable Site:**
```bash
sudo a2enmod rewrite
sudo a2ensite cinecraze
sudo systemctl restart apache2
```

6. **SSL Setup:**
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

### 4. Nginx VPS

**Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/cinecraze;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    location ~* \.(jpg|jpeg|png|gif|css|js|ico|xml)$ {
        expires 1y;
        access_log off;
        log_not_found off;
    }
}
```

### 5. Docker Deployment

**docker-compose.yml:**
```yaml
version: '3.8'

services:
  web:
    image: php:8.1-apache
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html
    depends_on:
      - db
    environment:
      DB_HOST: db
      DB_NAME: cinecraze
      DB_USER: cinecraze
      DB_PASS: password
  
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: cinecraze
      MYSQL_USER: cinecraze
      MYSQL_PASSWORD: password
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

Deploy:
```bash
docker-compose up -d
```

## Post-Deployment Checklist

### Security
- [ ] Delete `install.php`
- [ ] Change default admin password
- [ ] Set `error_reporting(0)` in config.php
- [ ] Enable HTTPS/SSL
- [ ] Update `SITE_URL` in config to use https://
- [ ] Restrict file permissions (755 for directories, 644 for files)
- [ ] Keep `includes/config.php` outside web root if possible

### Configuration
- [ ] Add TMDB API key in Admin > Settings
- [ ] Configure embed servers
- [ ] Test user registration
- [ ] Test content API
- [ ] Enable compression (already in .htaccess)
- [ ] Set up cron jobs if needed

### Optimization
- [ ] Enable OPcache in PHP
- [ ] Configure MySQL query cache
- [ ] Use CDN for static assets
- [ ] Enable browser caching (already in .htaccess)
- [ ] Optimize images before upload
- [ ] Consider using Redis for sessions (optional)

### Monitoring
- [ ] Set up error logging
- [ ] Monitor disk space usage
- [ ] Check database size regularly
- [ ] Set up backup schedule
- [ ] Monitor API rate limits (TMDB)

## Database Backup

### Manual Backup (cPanel)
1. Go to phpMyAdmin
2. Select database
3. Click "Export"
4. Choose "Quick" method
5. Download SQL file

### Command Line Backup
```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

### Automated Backup (Cron)
```bash
crontab -e
```

Add:
```
0 2 * * * mysqldump -u username -p'password' database_name | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz
```

## Troubleshooting

### "500 Internal Server Error"
- Check PHP error logs
- Verify .htaccess syntax
- Check file permissions (755/644)
- Ensure PHP version is 7.4+

### "Database Connection Failed"
- Verify credentials in config.php
- Check if MySQL service is running
- Ensure user has proper privileges
- Try 'localhost' or '127.0.0.1'

### "Cannot write to config.php"
- Check file permissions
- On shared hosting: Use File Manager to edit
- Ensure includes/ directory is writable

### API Not Working
- Verify mod_rewrite is enabled
- Check .htaccess is present
- Test direct access: /api/auth.php?action=verify
- Check for trailing slashes in URLs

### Upload Errors
- Check PHP upload_max_filesize
- Verify uploads/ directory exists
- Ensure uploads/ is writable (755)
- Check disk space

### Slow Performance
- Enable OPcache in PHP
- Add database indexes (already included)
- Use CDN for images
- Enable gzip compression (already in .htaccess)
- Consider upgrading hosting plan

## Free Hosting Limitations

### Common Issues:
1. **Resource Limits**: CPU/Memory restrictions
   - Solution: Optimize queries, use caching
2. **No .htaccess Support**: Some free hosts
   - Solution: Use index.php for routing
3. **Ads Injection**: Free hosts add ads
   - Solution: Upgrade or use InfinityFree (no ads)
4. **MySQL Limits**: Limited databases/size
   - Solution: Clean old data, optimize tables
5. **No SSL**: Some free hosts
   - Solution: Use Cloudflare free SSL

### Workarounds:
- Use Cloudflare for caching and SSL
- Optimize images before upload
- Limit content to essential items
- Use external image hosting (Imgur, etc.)
- Schedule heavy operations during low traffic

## Upgrading from Free to Paid Hosting

### Migration Steps:
1. **Backup** current database
2. **Export** database from old host
3. **Upload files** to new host
4. **Create database** on new host
5. **Import** database backup
6. **Update** config.php with new credentials
7. **Update** DNS to point to new host
8. **Test** thoroughly
9. **Enable SSL** on new host

## Best Practices

### For Free Hosting:
- Keep content minimal
- Use external CDN for images
- Regular backups
- Monitor storage usage
- Be prepared to migrate

### For Paid Hosting:
- Enable all security features
- Use SSL certificates
- Set up automated backups
- Monitor performance
- Keep PHP/MySQL updated
- Use caching (Redis/Memcached)

## Support Resources

- **Documentation**: README_SETUP.md
- **Conversion Guide**: CONVERSION_GUIDE.md
- **Community**: Create GitHub Issues
- **Hosting Support**: Contact your host

## Recommended Hosting Providers

### Free:
- InfinityFree (best free option)
- 000webhost
- Freehostia

### Shared ($3-10/month):
- Namecheap
- HostGator
- Bluehost
- SiteGround

### VPS ($5-20/month):
- DigitalOcean
- Linode
- Vultr
- AWS Lightsail

### Managed ($20+/month):
- Cloudways
- Kinsta
- WP Engine (WordPress only)
