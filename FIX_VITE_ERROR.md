# 🔧 Fix Vite Error - Quick Solution

## Current Error
```
Unable to locate file in Vite manifest: resources/css/filament/theme.css
```

## Immediate Fix (On Server)

### Option 1: Build Assets on Server (Quick Fix)

```bash
# SSH into server
ssh xxxsuzqm@poyekterapan1.com

# Navigate to application
cd ~/fintrack.poyekterapan.com

# Install Node.js dependencies (if Node.js is available)
npm install

# Build assets
npm run build

# Clear Laravel cache
php artisan config:clear
php artisan cache:clear
```

### Option 2: Copy Built Assets from Local (Recommended)

**On Your Local Machine:**

```bash
# 1. Build assets locally
npm run build

# 2. Verify build exists
ls -la public/build

# 3. Upload build directory to server
# Using SCP or SFTP, copy public/build to server:
scp -r public/build xxxsuzqm@poyekterapan1.com:~/fintrack.poyekterapan.com/public/
```

**Or via cPanel File Manager:**
1. Go to File Manager
2. Navigate to `fintrack.poyekterapan.com/public/`
3. Upload the `build` folder from your local `public/build` directory

### Option 3: Rebuild and Redeploy via Git

**On Your Local Machine:**

```bash
# 1. Build assets
npm run build

# 2. Force add build directory to Git
git add -f public/build

# 3. Commit
git commit -m "Add built assets - fix Vite error"

# 4. Push
git push origin main
```

**On Server:**

```bash
# 1. Pull changes
cd ~/Repositories/proter-fintrack
git pull origin main

# 2. Copy to application (including build directory)
rsync -av --exclude='.git' \
          --exclude='node_modules' \
          --exclude='vendor' \
          --include='public/build' \
          --include='public/build/**' \
          ~/Repositories/proter-fintrack/ \
          ~/fintrack.poyekterapan.com/

# 3. Clear cache
cd ~/fintrack.poyekterapan.com
php artisan config:clear
php artisan cache:clear
```

---

## Verify Fix

After applying the fix:

1. **Check build directory exists:**
   ```bash
   ls -la ~/fintrack.poyekterapan.com/public/build
   # Should show: manifest.json and assets/ directory
   ```

2. **Clear Laravel cache:**
   ```bash
   cd ~/fintrack.poyekterapan.com
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Test in browser:**
   - Visit: https://fintrack.poyekterapan1.com/admin
   - Error should be gone!

---

## Prevention for Future

Always build assets **before** deploying:

```bash
# On local machine, before git push
npm run build
git add public/build  # If you're including it in Git
git commit -m "Rebuild assets"
git push origin main
```

See `BUILD_ASSETS.md` for detailed guide.

