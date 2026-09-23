# Queue Worker Setup for Hosting

## 1. Update .env on Hosting
```env
QUEUE_CONNECTION=database
```

## 2. Run Migrations (if not done)
```bash
php artisan migrate
```

## 3. Start Queue Worker (REQUIRED for background jobs)

### Option A: Supervisor (Recommended for production)
Create `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

Then run:
```bash
supervisorctl reread
supervisorctl update
supervisorctl status laravel-worker:*
```

### Option B: Screen/Tmux (Temporary)
```bash
screen -S queue-worker
php artisan queue:work database --sleep=3 --tries=3
# Ctrl+A, D to detach
```

### Option C: Shared Hosting (cPanel)
- Use "Setup Node.js App" or "Terminal" to run worker
- Or set up cron job every minute:
```bash
* * * * * cd /home/user/project && php artisan queue:work database --once --max-time=60 >> storage/logs/cron-queue.log 2>&1
```

## 4. Monitor Queue
```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

## 5. Cache Configuration (for better performance)
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
```
Requires Redis on hosting.

## Key Changes Made to Fix 508 Errors:

1. **Caching**: Daily summaries cached for 6 hours - no recalculation on page load
2. **Queue Jobs**: Heavy recalculations run in background via `RecalculateDailySummaries` job
3. **Pagination**: Reports limited to 50 records per page
4. **Controllers**: All CRUD operations now dispatch queued jobs instead of synchronous recalculation

## Verification
After deployment:
1. Visit report page - should load instantly (uses cache)
2. Create a transaction - page redirects immediately, summary updates in background
3. Check queue: `php artisan queue:work database --once`
4. Verify summary updated after job runs