# ✅ FinTrack - Quick Deployment Checklist

## Before You Start
- [✅] All code committed to Git repository
- [✅] Production assets built (`npm run build`)
- [✅] APP_KEY generated (`php artisan key:generate`)
- [✅] `prod.env` file updated with all credentials

---

## cPanel Setup
- [✅] Database created in cPanel MySQL Databases
- [✅] Database user created and assigned to database
- [✅] Subdomain `fintrack.poyekterapan1.com` created
- [ ] Document root set to `/home/xxxsuzqm/fintrack.poyekterapan.com/public`
- [✅] Git repository cloned to `/home/xxxsuzqm/Repositories/proter-fintrack`

---

## Application Deployment
- [✅] Repository cloned to `/home/xxxsuzqm/Repositories/proter-fintrack`
- [✅] Files copied to `/home/xxxsuzqm/fintrack.poyekterapan.com`
- [ ] `.env` file created from `prod.env` in application directory
- [✅] All credentials filled in `.env`:
  - [ ] `APP_KEY` - Generated key
  - [ ] `DB_PASSWORD` - Database password
  - [ ] `MAIL_PASSWORD` - Email password
  - [ ] `OPENAI_API_KEY` - API key (or empty)
- [✅] Dependencies installed (`composer install --no-dev`)
- [ ] **Built assets deployed** (`public/build` directory exists on server)
- [ ] **Verify**: `ls -la ~/fintrack.poyekterapan.com/public/build` shows manifest.json

---

## Laravel Configuration
- [ ] File permissions set:
  - [ ] `storage` - 775
  - [ ] `bootstrap/cache` - 775
- [ ] Storage link created (`php artisan storage:link`)
- [ ] Database migrated (`php artisan migrate --force`)
- [ ] Caches cleared and optimized:
  - [ ] `php artisan config:clear`
  - [ ] `php artisan cache:clear`
  - [ ] `php artisan route:cache`
  - [ ] `php artisan view:cache`

---

## Testing
- [ ] Application accessible: https://fintrack.poyekterapan1.com
- [ ] Homepage loads correctly
- [ ] Login/Register works
- [ ] Database connection works
- [ ] CSS/JS assets load
- [ ] No console errors
- [ ] File uploads work (if applicable)
- [ ] Email sending works (if configured)

---

## Security
- [ ] `APP_DEBUG=false` in production
- [ ] `.env` file not accessible via web
- [ ] File permissions correct
- [ ] HTTPS enabled

---

## ✅ Deployment Complete!

**Application URL:** https://fintrack.poyekterapan1.com

**For updates:** Pull from Git → Run migrations → Clear caches

