# VPS Deploy Checklist (Admin + Phase 4 API)

Use this after merging `main` (Admin control panel rebuild + Phase 4 driver API) to your production server.

## Pre-deploy

- [ ] Backup database (if you run manual backups).
- [ ] **Do not** overwrite `storage/oauth-private.key` or `storage/oauth-public.key` on the server with local files. If you use deploy scripts, exclude these or use `git update-index --skip-worktree` for them locally and keep server keys as-is.

## Steps on the VPS

1. **Pull latest**
   ```bash
   cd /path/to/1way-backend
   git fetch origin
   git checkout main
   git pull origin main
   ```

2. **Composer (no dev in production)**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Migrations**
   - These changes did **not** add new migrations for Admin or Phase 4.
   - Run anyway to be safe:
   ```bash
   php artisan migrate --force
   ```
   - If nothing to migrate, you'll see "Nothing to migrate."

4. **Cache clear**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   ```

5. **Optional: cache for production**
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```

6. **Restart queue workers** (if you use them)
   ```bash
   php artisan queue:restart
   ```

7. **Restart PHP-FPM / app server** (per your setup)
   ```bash
   sudo systemctl reload php8.2-fpm   # or your PHP version
   # or restart your PHP app service
   ```

## Verify

- [ ] `php artisan route:list | grep -E "api/user/profile|api/driver/earnings|api/driver/expenses|api/driver/mileage"` shows the Phase 4 routes.
- [ ] Admin panel loads (e.g. `/admin` or your admin URL); login redirect is OK.
- [ ] Driver app can login and call `GET /api/user/profile`, `GET /api/driver/earnings`, etc.

## Rollback

If something breaks:

- `git checkout main~1` (or the previous commit), then repeat composer/cache steps.
- Restore DB backup only if you had run new migrations and need to revert.
