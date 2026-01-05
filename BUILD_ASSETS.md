# 🔨 Building Assets for Production Deployment

## Problem
Laravel Vite requires built assets (`public/build` directory) to work in production. Without these, you'll get errors like:
```
Unable to locate file in Vite manifest: resources/css/filament/theme.css
```

## Solution: Build Locally, Deploy Built Assets

Since you don't want to run `npm` on the cPanel server, build assets **locally** and include them in your Git repository.

---

## Quick Steps

### 1. Build Assets Locally

```bash
# On your local machine
npm install          # If not already done
npm run build       # Build production assets
```

### 2. Verify Build

```bash
# Check that public/build exists
ls -la public/build

# You should see:
# - manifest.json
# - assets/ directory with CSS and JS files
```

### 3. Include Built Assets in Git

**Option A: Temporarily Allow Build Directory**

```bash
# Remove public/build from .gitignore temporarily
# Edit .gitignore and comment out: /public/build

# Add and commit
git add public/build
git add .gitignore
git commit -m "Add built assets for production deployment"
git push origin main
```

**Option B: Force Add (One-time)**

```bash
# Force add the build directory
git add -f public/build
git commit -m "Add built assets for production"
git push origin main
```

### 4. Deploy to Server

```bash
# On server via SSH
cd ~/Repositories/proter-fintrack
git pull origin main

# Copy to application (build directory will be included)
rsync -av --exclude='.git' \
          --exclude='node_modules' \
          --exclude='vendor' \
          --exclude='.env' \
          ~/Repositories/proter-fintrack/ \
          ~/fintrack.poyekterapan.com/
```

---

## For Future Updates

### When You Make Changes:

1. **Make code changes**
2. **Rebuild assets** (if CSS/JS changed):
   ```bash
   npm run build
   ```
3. **Commit everything**:
   ```bash
   git add .
   git commit -m "Update code and rebuild assets"
   git push origin main
   ```
4. **Deploy to server** (as above)

---

## Alternative: Keep Build in .gitignore

If you prefer to keep `/public/build` in `.gitignore`:

1. Build assets locally
2. Use `rsync` with `--include` to copy build directory:
   ```bash
   rsync -av \
     --exclude='.git' \
     --exclude='node_modules' \
     --exclude='vendor' \
     --include='public/build' \
     --include='public/build/**' \
     ~/Repositories/proter-fintrack/ \
     ~/fintrack.poyekterapan.com/
   ```

---

## Troubleshooting

### Error: "Unable to locate file in Vite manifest"

**Solution:**
- Make sure `public/build` directory exists on server
- Verify `public/build/manifest.json` exists
- Rebuild assets: `npm run build`
- Redeploy the `public/build` directory

### Assets Not Updating

**Solution:**
- Clear Laravel cache: `php artisan config:clear`
- Rebuild assets: `npm run build`
- Redeploy

---

## ✅ Best Practice

**Recommended approach:**
1. Keep `/public/build` in `.gitignore` for development
2. Build assets locally before each deployment
3. Use `rsync` with `--include` to copy build directory to server
4. This keeps your repo clean while ensuring production has assets

---

## Quick Reference

```bash
# Build assets
npm run build

# Deploy (from local to server via rsync)
rsync -av \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --include='public/build' \
  --include='public/build/**' \
  ./ \
  user@server:/path/to/application/
```

