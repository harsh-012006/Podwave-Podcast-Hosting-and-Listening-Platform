# Render Deployment Guide - PodWave

## Prerequisites Completed ✅

All Docker configuration files have been created for Render deployment without needing Docker installed locally.

### Files Created:

1. **Dockerfile** - Multi-stage build for PHP, Node.js, and production image
2. **.dockerignore** - Excludes unnecessary files from Docker image
3. **render.yaml** - Render deployment configuration
4. **docker/entrypoint.sh** - Startup script for migrations and caching
5. **docker/nginx.conf** - Nginx configuration
6. **docker/default.conf** - Nginx virtual host configuration
7. **docker/supervisord.conf** - Process manager for PHP-FPM and Nginx
8. **docker/php.ini** - PHP production settings

## Deployment Steps on Render:

### Step 1: Create a Render Account
- Go to https://render.com
- Sign up and connect your GitHub account

### Step 2: Create a New Web Service
1. Click "New +" → "Web Service"
2. Select your podwave GitHub repository
3. Configure the following:
   - **Name**: podwave
   - **Environment**: Docker
   - **Region**: Choose closest to your users
   - **Branch**: master

### Step 3: Set Environment Variables
In the Render dashboard, set these environment variables:

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY
APP_URL=https://your-app.onrender.com
DB_CONNECTION=sqlite
DB_DATABASE=database.sqlite
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=YOUR_MAILTRAP_USER
MAIL_PASSWORD=YOUR_MAILTRAP_PASS
MAIL_ENCRYPTION=tls
GOOGLE_CLIENT_ID=YOUR_GOOGLE_CLIENT_ID
GOOGLE_CLIENT_SECRET=YOUR_GOOGLE_CLIENT_SECRET
GOOGLE_REDIRECT_URL=https://your-app.onrender.com/auth/google/callback
```

### Step 4: Update Storage Configuration
For file uploads, add to `config/app.php`:

```php
'storage_path' => env('APP_STORAGE', storage_path()),
```

### Step 5: Deploy
1. Click "Deploy"
2. Monitor the logs in Render dashboard
3. Database migrations will run automatically

## Important Notes:

✅ **SQLite will work** for initial deployment (stored in /var/www/html/database.sqlite)
✅ **All assets** will be built during Docker image creation
✅ **Logs** can be viewed in real-time in Render dashboard
✅ **No local Docker needed** - Render handles everything

## Production Checklist:

- [ ] Set `APP_DEBUG=false`
- [ ] Generate strong `APP_KEY` using `php artisan key:generate`
- [ ] Configure CORS for frontend domain
- [ ] Set up proper MAIL credentials
- [ ] Add OAuth credentials for Google
- [ ] Update `APP_URL` with your actual domain
- [ ] Configure backup solution for SQLite database
- [ ] Set up monitoring and error tracking (Sentry recommended)

## After Deployment:

```bash
# If you need to run commands on the live app:
# Use Render's Shell or SSH feature in the dashboard

# View logs:
# Dashboard → Logs tab
```

## Troubleshooting:

- **Build fails**: Check Docker image size in Render logs
- **Migration errors**: Ensure database permissions are correct
- **File uploads fail**: Check storage directory permissions
- **Slow performance**: Use caching strategies or upgrade plan

## Next Steps:

1. Commit all files to GitHub:
   ```bash
   git add .
   git commit -m "Add Docker configuration for Render deployment"
   git push origin master
   ```

2. Connect to Render and deploy
3. Monitor logs for any issues
