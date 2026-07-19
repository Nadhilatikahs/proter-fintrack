# 🔄 Update Application in cPanel - Quick Guide

## Method 1: Using SSH (Recommended - Fastest)

### Step 1: SSH into Your Server

```bash
ssh xxxsuzqm@poyekterapan1.com
```

### Step 2: Update Repository

```bash
# Navigate to repository directory
cd ~/Repositories/proter-fintrack

# Pull latest changes from Git
git pull origin main
```

### Step 3: Copy Files to Application Directory

```bash
# Copy updated files to application (excluding unnecessary files)
rsync -av --exclude='.git' \
          --exclude='node_modules' \
          --exclude='vendor' \
          --exclude='.env' \
          --exclude='storage/logs/*' \
          --include='public/build' \
          --include='public/build/**' \
          ~/Repositories/proter-fintrack/ \
          ~/fintrack.poyekterapan.com/
```

### Step 4: Install Dependencies (if needed)

```bash
# Navigate to application directory
cd ~/fintrack.poyekterapan.com

# Install/update PHP dependencies (if composer.json changed)
composer install --no-dev --optimize-autoloader
```

### Step 5: Run Laravel Commands

```bash
# Clear and cache routes (IMPORTANT for route changes!)
php artisan route:clear
php artisan route:cache

# Clear config cache
php artisan config:clear
php artisan config:cache

# Clear view cache
php artisan view:clear
php artisan view:cache

# Run migrations (if database changes)
php artisan migrate --force
```

### Step 6: Set Permissions (if needed)

```bash
# Ensure storage and cache directories are writable
chmod -R 775 storage bootstrap/cache
```

### Step 7: Verify Update

```bash
# Check if files were updated
ls -la ~/fintrack.poyekterapan.com/routes/web.php

# Test the application
# Visit: https://fintrack.poyekterapan1.com
```

---

## Method 2: Using cPanel File Manager + Terminal

### Step 1: Update via Git in cPanel Terminal

1. Login to cPanel
2. Go to **Terminal** (or **SSH Access**)
3. Run the commands from Method 1 above

### Step 2: Or Use Git Version Control

1. Login to cPanel
2. Go to **Git Version Control**
3. Find your repository (`proter-fintrack`)
4. Click **Pull or Deploy**
5. Select branch: `main`
6. Click **Update from Remote**

### Step 3: Copy Files Manually (if needed)

1. Go to **File Manager**
2. Navigate to `Repositories/proter-fintrack`
3. Select all files (except `.git`, `node_modules`, `vendor`)
4. Copy to `fintrack.poyekterapan.com`

### Step 4: Run Commands via Terminal

Use cPanel Terminal to run Laravel commands (see Method 1, Steps 4-5)

---

## Quick Update Script (One Command)

Create this script for faster updates:

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Create update script
nano ~/update-fintrack.sh
```

Paste this content:

```bash
#!/bin/bash
echo "🔄 Updating FinTrack..."

# Update repository
cd ~/Repositories/proter-fintrack
git pull origin main

# Copy to application
rsync -av --exclude='.git' \
          --exclude='node_modules' \
          --exclude='vendor' \
          --exclude='.env' \
          --exclude='storage/logs/*' \
          --include='public/build' \
          --include='public/build/**' \
          ~/Repositories/proter-fintrack/ \
          ~/fintrack.poyekterapan.com/

# Navigate to application
cd ~/fintrack.poyekterapan.com

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear and cache
php artisan route:clear
php artisan route:cache
php artisan config:clear
php artisan config:cache
php artisan view:clear
php artisan view:cache

echo "✅ Update complete!"
```

Make it executable:

```bash
chmod +x ~/update-fintrack.sh
```

**Now you can update with one command:**

```bash
~/update-fintrack.sh
```

---

## For This Specific Update (Login Redirect Fix)

Since we only changed route files, you can do a minimal update:

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Update repository
cd ~/Repositories/proter-fintrack
git pull origin main

# Copy only the changed files
cp routes/web.php ~/fintrack.poyekterapan.com/routes/web.php
cp app/Http/Controllers/Auth/AuthenticatedSessionController.php ~/fintrack.poyekterapan.com/app/Http/Controllers/Auth/AuthenticatedSessionController.php

# Clear route cache (CRITICAL!)
cd ~/fintrack.poyekterapan.com
php artisan route:clear
php artisan route:cache
```

---

## Troubleshooting

### Routes Not Updating

```bash
# Clear all caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Rebuild caches
php artisan route:cache
php artisan config:cache
php artisan view:cache
```

### Permission Denied

```bash
# Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R xxxsuzqm:xxxsuzqm storage bootstrap/cache
```

### Files Not Updating

```bash
# Check if files were copied
ls -la ~/fintrack.poyekterapan.com/routes/web.php

# If not, manually copy via File Manager or rsync again
```

---

## ✅ Update Checklist

- [ ] Pulled latest changes from Git
- [ ] Copied files to application directory
- [ ] Cleared route cache (`php artisan route:clear`)
- [ ] Rebuilt route cache (`php artisan route:cache`)
- [ ] Cleared config cache (`php artisan config:clear`)
- [ ] Tested login redirect to `/dashboard`

---

## Quick Reference

**Full Update:**
```bash
cd ~/Repositories/proter-fintrack && git pull origin main && \
rsync -av --exclude='.git' --exclude='node_modules' --exclude='vendor' --exclude='.env' \
  ~/Repositories/proter-fintrack/ ~/fintrack.poyekterapan.com/ && \
cd ~/fintrack.poyekterapan.com && \
composer install --no-dev --optimize-autoloader && \
php artisan route:clear && php artisan route:cache && \
php artisan config:clear && php artisan config:cache
```

**Quick Update (routes only):**
```bash
cd ~/Repositories/proter-fintrack && git pull origin main && \
cp routes/web.php ~/fintrack.poyekterapan.com/routes/web.php && \
cp app/Http/Controllers/Auth/AuthenticatedSessionController.php ~/fintrack.poyekterapan.com/app/Http/Controllers/Auth/AuthenticatedSessionController.php && \
cd ~/fintrack.poyekterapan.com && \
php artisan route:clear && php artisan route:cache
```

