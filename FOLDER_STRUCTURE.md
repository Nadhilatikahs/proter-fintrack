# 📁 FinTrack - Server Folder Structure

## Directory Layout

```
/home/xxxsuzqm/
├── Repositories/
│   └── proter-fintrack/          # Git repository location
│       ├── .git/
│       ├── app/
│       ├── public/
│       ├── vendor/                # Will be installed here
│       └── ...
│
└── fintrack.poyekterapan.com/    # Application deployment location
    ├── app/
    ├── public/                    # Document root points here
    │   ├── index.php
    │   ├── .htaccess
    │   └── build/                 # Production assets
    ├── storage/
    ├── vendor/                    # Production dependencies
    ├── .env                       # Production environment file
    └── ...
```

## Important Paths

- **Repository**: `/home/xxxsuzqm/Repositories/proter-fintrack`
- **Application**: `/home/xxxsuzqm/fintrack.poyekterapan.com`
- **Document Root**: `/home/xxxsuzqm/fintrack.poyekterapan.com/public`

## Deployment Workflow

1. **Update Repository** (in `/home/xxxsuzqm/Repositories/proter-fintrack`)
   ```bash
   cd ~/Repositories/proter-fintrack
   git pull origin main
   ```

2. **Copy to Application** (from repository to application directory)
   ```bash
   rsync -av --exclude='.git' \
             --exclude='node_modules' \
             --exclude='vendor' \
             --exclude='.env' \
             ~/Repositories/proter-fintrack/ \
             ~/fintrack.poyekterapan.com/
   ```

3. **Install Dependencies** (in application directory)
   ```bash
   cd ~/fintrack.poyekterapan.com
   composer install --no-dev --optimize-autoloader
   ```

4. **Run Laravel Commands** (in application directory)
   ```bash
   cd ~/fintrack.poyekterapan.com
   php artisan migrate --force
   php artisan optimize
   ```

## Notes

- Repository is for Git operations only
- Application directory is what serves the website
- Always work in application directory for Laravel commands
- Use rsync to sync files from repository to application

