# Installer Troubleshooting Guide

## Common Installation Issues

### Issue: Stuck on Step 2 (Database Configuration)

**Symptoms:**
- After filling database credentials and clicking "Continue"
- Page reloads but goes back to Step 2
- Have to re-enter database information

**Solution (FIXED):**
This issue has been fixed in the latest version. The installer now properly maintains session state.

**If still experiencing issues:**
1. Clear browser cookies and cache
2. Close all browser tabs
3. Start fresh at `http://yourdomain.com/install.php`
4. Make sure cookies are enabled in your browser

### Issue: "Database connection failed"

**Possible Causes:**

1. **Wrong Credentials**
   - Double-check database name, username, and password
   - Copy/paste to avoid typos

2. **Database Host Wrong**
   - Most shared hosting: Use `localhost`
   - Some hosts: Use `localhost:3306` or specific host
   - Check your hosting control panel for correct host

3. **Database User Not Assigned**
   - Create database user in cPanel
   - Assign user to database
   - Give ALL PRIVILEGES

4. **Database Doesn't Exist**
   - Installer tries to create it automatically
   - If fails, manually create database in cPanel first
   - Then enter the name in installer

**Free Hosting Specific:**
- Database name format: `username_dbname` (e.g., `abc123_cinecraze`)
- Use exact format shown in control panel
- Don't use just "cinecraze" - won't work

### Issue: "500 Internal Server Error"

**Solutions:**

1. **Check PHP Version**
   ```
   Must be PHP 7.4 or higher
   Change in cPanel > Select PHP Version
   ```

2. **File Permissions**
   ```
   Set includes/ folder to 755
   Set install.php to 644
   ```

3. **Check .htaccess**
   ```
   Make sure .htaccess file exists in root
   If causing issues, temporarily rename to .htaccess.bak
   ```

4. **View Error Logs**
   ```
   cPanel > Error Log
   Look for specific error messages
   ```

### Issue: "Cannot write config.php"

**Solutions:**

1. **Set Folder Permissions**
   ```
   includes/ folder: Change to 755 or 777 (temporarily)
   After installation, change back to 755
   ```

2. **Manual Method**
   - Let installer fail at Step 5
   - Copy the error message (it shows the config content)
   - Use cPanel File Manager
   - Create `includes/config.php` manually
   - Paste the content
   - Save and close installer

### Issue: Session/Cookie Problems

**Symptoms:**
- Keeps going back to previous steps
- Data not saved between steps
- Installation restarts

**Solutions:**

1. **Enable Cookies**
   ```
   Browser Settings > Privacy > Allow Cookies
   Make sure not in Incognito/Private mode
   ```

2. **Clear Browser Data**
   ```
   Clear cookies and cache
   Close ALL browser tabs
   Restart browser
   Try again
   ```

3. **Try Different Browser**
   ```
   If Chrome fails, try Firefox
   Or Edge, Safari, etc.
   ```

4. **Check Server Sessions**
   ```
   Some free hosts disable sessions
   Contact support to enable PHP sessions
   ```

### Issue: "Database import failed"

**Solutions:**

1. **File Missing**
   ```
   Make sure database.sql exists in root folder
   Re-upload if missing
   ```

2. **File Permissions**
   ```
   database.sql should be readable (644)
   ```

3. **Large File Timeout**
   ```
   Increase PHP max_execution_time
   Or manually import via phpMyAdmin:
   - Export database.sql from phpMyAdmin
   - Go to Import tab
   - Select database.sql
   - Click Go
   ```

4. **SQL Syntax Errors**
   ```
   Check if database.sql is complete
   Re-download from GitHub
   Make sure no corruption
   ```

### Issue: Can't Create Admin Account

**Solutions:**

1. **Email Already Exists**
   ```
   Use different email address
   Or delete existing user in database first
   ```

2. **Password Too Short**
   ```
   Must be at least 6 characters
   Use stronger password
   ```

3. **Database Tables Not Created**
   ```
   Go back to Step 3
   Re-import database
   Then proceed to Step 4
   ```

## Manual Installation Method

If installer keeps failing, install manually:

### Step 1: Create Database
```sql
CREATE DATABASE cinecraze CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 2: Import Database
Via phpMyAdmin:
1. Select database
2. Click "Import"
3. Choose database.sql
4. Click "Go"

Or via command line:
```bash
mysql -u username -p cinecraze < database.sql
```

### Step 3: Create Config File
Copy `includes/config.sample.php` to `includes/config.php`

Edit with your details:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('SITE_URL', 'http://yourdomain.com');
```

### Step 4: Create Admin Account
Via phpMyAdmin:
1. Go to `users` table
2. Click "Insert"
3. Fill in:
   - username: admin
   - email: your@email.com
   - password: (generate hash first)
   - is_admin: 1
   - is_active: 1

Generate password hash:
```php
<?php
echo password_hash('your_password', PASSWORD_BCRYPT);
?>
```

### Step 5: Delete Installer
```bash
rm install.php
```

### Step 6: Create Upload Folders
```bash
mkdir -p uploads/posters uploads/backdrops
chmod 755 uploads -R
```

## Testing Installation

After installation completes:

1. **Test Public Site**
   ```
   Visit: http://yourdomain.com/
   Should see login/register modal
   ```

2. **Test Registration**
   ```
   Register a test account
   Should redirect after login
   ```

3. **Test Admin Panel**
   ```
   Visit: http://yourdomain.com/admin/
   Login with admin credentials
   Should see dashboard
   ```

4. **Test API**
   ```
   Visit: http://yourdomain.com/api/content.php?action=list
   Should see JSON response (empty array is OK)
   ```

## Getting Help

If none of these solutions work:

1. **Check Error Logs**
   - cPanel > Error Log
   - Look for PHP errors

2. **Enable Debug Mode**
   Edit `includes/config.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

3. **Test PHP Requirements**
   Create `test.php`:
   ```php
   <?php
   phpinfo();
   ?>
   ```
   Visit it to check PHP configuration

4. **Contact Support**
   - Check DEPLOYMENT_GUIDE.md
   - Create GitHub issue with:
     - Hosting provider
     - PHP version
     - Error message
     - Step where it fails

## Security Reminder

After successful installation:
- ✅ Delete install.php
- ✅ Delete test.php (if created)
- ✅ Change admin password
- ✅ Set config.php to 644 permissions
- ✅ Enable HTTPS if available
