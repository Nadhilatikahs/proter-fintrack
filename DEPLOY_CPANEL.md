# 🚀 FinTrack - cPanel Deployment Tutorial (Using Git Repository)

## 📋 Prerequisites

- ✅ Git repository (GitHub, GitLab, Bitbucket, or cPanel Git)
- ✅ cPanel access with Git support
- ✅ SSH access to cPanel (recommended)
- ✅ Database created in cPanel
- ✅ Subdomain configured: `fintrack.poyekterapan1.com`

## 📁 Folder Structure

- **Repository Path**: `/home/xxxsuzqm/Repositories/proter-fintrack`
- **Application Path**: `/home/xxxsuzqm/fintrack.poyekterapan.com`
- **Document Root**: `/home/xxxsuzqm/fintrack.poyekterapan.com/public`

---

## 📝 STEP 1: Prepare Local Environment

### 1.1 Build Production Assets (CRITICAL - Do this FIRST!)

```bash
# Install dependencies (if not already done)
npm install

# Build production assets - THIS IS REQUIRED!
npm run build

# Verify build was successful
ls -la public/build
# You should see:
# - manifest.json
# - assets/ directory with CSS and JS files

# IMPORTANT: The public/build directory will be deployed to server
# This allows the app to work WITHOUT running npm on the server
```

### 1.2 Generate Application Key

```bash
# Generate APP_KEY for production
php artisan key:generate

# Copy the generated key from .env file
# Example: APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### 1.3 Update prod.env File

Edit `prod.env` and fill in all required values:

```bash
# Open prod.env and update:
APP_KEY=<paste_generated_key_here>
DB_PASSWORD=<your_database_password>
MAIL_PASSWORD=<your_email_password>
OPENAI_API_KEY=<your_openai_key_or_leave_empty>
```

### 1.4 Commit All Changes to Git (Including Built Assets)

```bash
# Stage all changes (including public/build directory)
git add .

# Commit with message
git commit -m "Prepare for production deployment - assets built"

# Push to repository
git push origin main
# or
git push origin master

# ⚠️ IMPORTANT: Make sure public/build directory is committed!
# Check with: git status
# If public/build is ignored, temporarily remove it from .gitignore
```

---

## 📝 STEP 2: Setup Database in cPanel

### 2.1 Create MySQL Database

1. Login to cPanel
2. Go to **MySQL Databases**
3. Create new database:
   - Database name: `xxxsuzqm_fintrack` (or your preferred name)
   - Click **Create Database**

### 2.2 Create Database User

1. In **MySQL Databases** section
2. Create new user:
   - Username: `xxxsuzqm_fintrack` (or match your database name)
   - Password: **Generate strong password** (save it!)
   - Click **Create User**

### 2.3 Assign User to Database

1. Scroll to **Add User To Database**
2. Select user: `xxxsuzqm_fintrack`
3. Select database: `xxxsuzqm_fintrack`
4. Click **Add**
5. Check **ALL PRIVILEGES**
6. Click **Make Changes**

**✅ Save these credentials - you'll need them for .env file!**

---

## 📝 STEP 3: Setup Git Repository in cPanel

### 3.1 Access Git Version Control

1. Login to cPanel
2. Find **Git Version Control** (in Files section)
3. Click **Create**

### 3.2 Configure Git Repository

Fill in the form:

- **Repository Name**: `proter-fintrack` (or your preferred name)
- **Repository URL**: 
  - GitHub: `https://github.com/yourusername/proter-fintrack.git`
  - GitLab: `https://gitlab.com/yourusername/proter-fintrack.git`
  - Bitbucket: `https://bitbucket.org/yourusername/proter-fintrack.git`
- **Repository Branch**: `main` or `master` (match your default branch)
- **Repository Path**: `/home/xxxsuzqm/Repositories/proter-fintrack`
- **Checkout Directory**: Leave empty (will use repository path)

Click **Create**

### 3.3 Clone Repository (Alternative Method via SSH)

If you prefer SSH access:

```bash
# SSH into your cPanel server
ssh xxxsuzqm@poyekterapan1.com

# Create Repositories directory if it doesn't exist
mkdir -p ~/Repositories

# Navigate to Repositories directory
cd ~/Repositories

# Clone your repository
git clone https://github.com/yourusername/proter-fintrack.git proter-fintrack

# Or if using SSH key:
git clone git@github.com:yourusername/proter-fintrack.git proter-fintrack
```

---

## 📝 STEP 4: Configure Subdomain in cPanel

### 4.1 Create Subdomain

1. In cPanel, go to **Subdomains**
2. Create subdomain:
   - **Subdomain**: `fintrack`
   - **Domain**: `poyekterapan1.com`
   - **Document Root**: `/home/xxxsuzqm/fintrack.poyekterapan.com/public`
   - Click **Create**

**⚠️ Important**: Document root must point to `/public` folder of Laravel!

---

## 📝 STEP 5: Upload and Configure Application

### 5.1 Method A: Using cPanel Git (Recommended)

1. In cPanel Git Version Control, find your repository
2. Click **Pull or Deploy**
3. Select branch: `main` or `master`
4. Click **Update from Remote**

### 5.2 Method B: Using SSH

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Navigate to repository directory
cd ~/Repositories/proter-fintrack

# Pull latest changes
git pull origin main

# Copy files to application directory
# First time setup - copy all files
rsync -av --exclude='.git' --exclude='node_modules' --exclude='vendor' ~/Repositories/proter-fintrack/ ~/fintrack.poyekterapan.com/

# Or use symlink method (see Step 5.4)
```

### 5.3 Copy Files to Application Directory

**Option A: Copy Files (Recommended for first deployment)**

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Navigate to repository
cd ~/Repositories/proter-fintrack

# Pull latest changes
git pull origin main

# Copy files to application directory
# IMPORTANT: Include public/build directory (built assets)
rsync -av --exclude='.git' \
          --exclude='node_modules' \
          --exclude='vendor' \
          --exclude='.env' \
          --exclude='storage/logs/*' \
          --include='public/build' \
          --include='public/build/**' \
          ~/Repositories/proter-fintrack/ \
          ~/fintrack.poyekterapan.com/

# Navigate to application directory
cd ~/fintrack.poyekterapan.com

# Verify build directory exists
ls -la public/build
# Should show manifest.json and assets/ directory
```

**Option B: Use Symlink (For easier updates)**

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Create application directory if it doesn't exist
mkdir -p ~/fintrack.poyekterapan.com

# Navigate to repository
cd ~/Repositories/proter-fintrack

# Pull latest changes
git pull origin main

# Copy files first time (symlink doesn't work well with Laravel)
rsync -av --exclude='.git' ~/Repositories/proter-fintrack/ ~/fintrack.poyekterapan.com/

# Navigate to application directory
cd ~/fintrack.poyekterapan.com
```

### 5.4 Install Dependencies

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Navigate to application directory
cd ~/fintrack.poyekterapan.com

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# ⚠️ NOTE: You DON'T need to run npm install or npm run build on server
# Assets are already built locally and included in public/build directory
# This is why we build assets BEFORE pushing to Git (Step 1.1)
```

---

## 📝 STEP 6: Configure Environment File

### 6.1 Create .env File

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Navigate to application directory
cd ~/fintrack.poyekterapan.com

# Copy prod.env to .env
cp prod.env .env

# Or create .env manually via cPanel File Manager
```

### 6.2 Edit .env File

**Option A: Via cPanel File Manager**
1. Go to **File Manager**
2. Navigate to `fintrack.poyekterapan.com` (in home directory)
3. Find `.env` file (or create it)
4. Right-click → **Edit**
5. Paste content from `prod.env` and update:
   - `APP_KEY` - paste generated key
   - `DB_PASSWORD` - your database password
   - `MAIL_PASSWORD` - your email password
   - `OPENAI_API_KEY` - your API key (or leave empty)
6. Save

**Option B: Via SSH**
```bash
# Edit .env file
nano .env

# Or use vi
vi .env

# Paste content from prod.env and update values
# Save and exit (Ctrl+X, then Y, then Enter for nano)
```

### 6.3 Verify .env Configuration

```bash
# Check .env file exists
ls -la .env

# Verify it has correct values (don't show sensitive data)
grep -E "^APP_KEY=|^DB_|^APP_URL=" .env
```

---

## 📝 STEP 7: Set File Permissions

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Navigate to application directory
cd ~/fintrack.poyekterapan.com

# Set storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Set ownership (adjust username if different)
chown -R xxxsuzqm:xxxsuzqm storage
chown -R xxxsuzqm:xxxsuzqm bootstrap/cache

# If using shared hosting, you might need:
find storage -type f -exec chmod 664 {} \;
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;
```

---

## 📝 STEP 8: Run Laravel Setup Commands

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Navigate to application directory
cd ~/fintrack.poyekterapan.com

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Generate application key (if not set in .env)
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📝 STEP 9: Configure .htaccess (if needed)

### 9.1 Check public/.htaccess

The file should already exist, but verify it has:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 9.2 Update Document Root in cPanel

1. Go to **Subdomains**
2. Edit `fintrack` subdomain
3. Ensure **Document Root** is: `/home/xxxsuzqm/fintrack.poyekterapan.com/public`
4. Save

---

## 📝 STEP 10: Test Application

### 10.1 Access Application

Open browser and visit:
```
https://fintrack.poyekterapan1.com
```

### 10.2 Check for Errors

1. **If you see 500 error:**
   - Check Laravel logs: `storage/logs/laravel.log`
   - Verify file permissions
   - Check .env file configuration

2. **If you see database error:**
   - Verify database credentials in .env
   - Check database user has proper permissions
   - Ensure database exists

3. **If assets not loading:**
   - Verify `public/build` directory exists
   - Check file permissions on `public` directory
   - Clear browser cache

### 10.3 Verify Application

- ✅ Homepage loads
- ✅ Login/Register works
- ✅ Database connection works
- ✅ Assets (CSS/JS) load correctly
- ✅ No console errors

---

## 📝 STEP 11: Setup Auto-Deployment (Optional)

### 11.1 Using cPanel Git Webhook

1. In cPanel Git Version Control
2. Click **Manage** on your repository
3. Setup **Webhook** (if available)
4. Configure to auto-pull on push

### 11.2 Using SSH Script

Create deployment script:

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Create deploy script
nano ~/deploy-fintrack.sh
```

Add this content:

```bash
#!/bin/bash
# Update repository
cd ~/Repositories/proter-fintrack
git pull origin main

# Copy files to application directory
# Include public/build (built assets) - no npm needed on server!
rsync -av --exclude='.git' \
          --exclude='node_modules' \
          --exclude='vendor' \
          --exclude='.env' \
          --exclude='storage/logs/*' \
          --include='public/build' \
          --include='public/build/**' \
          ~/Repositories/proter-fintrack/ \
          ~/fintrack.poyekterapan.com/

# Navigate to application directory
cd ~/fintrack.poyekterapan.com

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations and optimize
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Make executable:
```bash
chmod +x ~/deploy-fintrack.sh
```

Run manually when needed:
```bash
~/deploy-fintrack.sh
```

---

## 📝 STEP 12: Post-Deployment Checklist

- [ ] Application accessible at `https://fintrack.poyekterapan1.com`
- [ ] Database migrations completed
- [ ] User can register/login
- [ ] All pages load correctly
- [ ] Assets (CSS/JS/images) load
- [ ] No errors in browser console
- [ ] Email sending works (if configured)
- [ ] File uploads work (if applicable)
- [ ] Storage link created
- [ ] Logs directory writable
- [ ] APP_DEBUG=false in production
- [ ] HTTPS working correctly

---

## 🔧 Troubleshooting

### Issue: 500 Internal Server Error

**Solution:**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Verify permissions
chmod -R 775 storage bootstrap/cache

# Clear caches
php artisan config:clear
php artisan cache:clear
```

### Issue: Database Connection Error

**Solution:**
- Verify database credentials in .env
- Check database exists in cPanel
- Verify database user has permissions
- Test connection via cPanel MySQL Databases

### Issue: Assets Not Loading

**Solution:**
```bash
# Rebuild assets
npm run build

# Verify public/build exists
ls -la public/build

# Check file permissions
chmod -R 755 public
```

### Issue: Permission Denied

**Solution:**
```bash
# Fix storage permissions
chmod -R 775 storage
chown -R xxxsuzqm:xxxsuzqm storage

# Fix bootstrap cache
chmod -R 775 bootstrap/cache
chown -R xxxsuzqm:xxxsuzqm bootstrap/cache
```

### Issue: Route Not Found

**Solution:**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Verify .htaccess exists in public folder
```

---

## 📚 Additional Resources

- Laravel Deployment: https://laravel.com/docs/deployment
- cPanel Documentation: https://docs.cpanel.net
- Git Documentation: https://git-scm.com/doc

---

## ✅ Success!

Your FinTrack application should now be live at:
**https://fintrack.poyekterapan1.com**

For future updates:
1. Make changes locally
2. Commit and push to Git
3. SSH into server and run:
   ```bash
   cd ~/Repositories/proter-fintrack
   git pull origin main
   rsync -av --exclude='.git' --exclude='node_modules' --exclude='vendor' --exclude='.env' ~/Repositories/proter-fintrack/ ~/fintrack.poyekterapan.com/
   cd ~/fintrack.poyekterapan.com
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan optimize
   ```
   
   Or simply run: `~/deploy-fintrack.sh`

---

**Need Help?** Check Laravel logs: `storage/logs/laravel.log`

