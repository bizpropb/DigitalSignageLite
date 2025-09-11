# Presenter V3

Digital signage system with Laravel backend (Filament admin), React frontend, and PostgreSQL database.

## Prerequisites

- Docker Desktop (for PostgreSQL only)
- PHP 8.3+
- Composer
- Node.js 18+
- Git

## Architecture

- **Backend**: Laravel 12 with Filament 4 admin panel (local)
- **Frontend**: React with Vite (local)
- **Database**: PostgreSQL 17 (Docker)
- **WebSockets**: Laravel Reverb (local)

## Setup

### 1. Start Database
```bash
# 1. start
docker compose down
docker volume rm presenter-v4_postgres_data   # optional, wipes old db
docker compose up -d
```

### 2. Install Dependencies

**Backend:**
```bash
cd backend
composer install
cd ..
```

**Frontend:**
```bash
cd frontend
npm install
cd ..
```

### 3. Setup Database
```bash
cd backend
php artisan config:clear
php artisan migrate:fresh --seed
php artisan make:filament-user --name=admin --email=admin@example.com --password=password
cd ..
```
⚠️ If it fails, it means a local PostgreSQL is running and blocking your port.
Run "services.msc", find "postgresql-xxx-xx", stop it. Do step 1 again, then step 3.
If that didnt help, "run netstat -ano | findstr :5432" and stop these services (might require a restart afterwards)

### 4. Start Applications

**Backend (Laravel + Filament) - Terminal 1:**
```bash
cd backend
php artisan serve
```

**WebSocket Server - Terminal 2:**
```bash
cd backend
php artisan reverb:start --port=8080
```

Access: http://localhost:8000 (Laravel) | http://localhost:8000/admin (Filament)

**Frontend (React) - Terminal 3:**
```bash
cd frontend
npm run dev
```
Access: http://localhost:3000

### 5. Access pgAdmin (Optional)
- URL: http://localhost:5050
- Login: admin@example.com / password
- Add server: Host=localhost, Port=5432, Database=presenter_v4, User=admin, Password=password


# TROUBLESHOOTING

## Database Refresh & Reseed
**Most common fix - run when things break:**
```bash
cd backend
php artisan migrate:fresh --seed
php artisan make:filament-user --name=admin --email=admin@example.com --password=password
```

## Cache Clear
**When Laravel/Filament acts weird try this one // this thing tends to cache lots of things:**
```bash
cd backend
php artisan config:clear
php artisan optimize:clear
php artisan filament:clear-cached-components
```

## Optimizations (make it faster)

⚠️ External icons (blade) slows down Filament under Windows OS. Use the following commands to increase speed.
```bash
# 1. Laravel config, routes, events, views
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache

# 2. Filament-specific caches (components + Blade icons)
php artisan filament:optimize        # shorthand for the next two
#   ├─ filament:cache-components
#   └─ icons:cache

# 3. Livewire JS asset (makes it a real static file Nginx/Apache can cache)
php artisan livewire:publish --assets
```
⚠️Find ";opcache.enable=" in your php.ini and set it to "opcache.enable=1" for extra speed.

⚠️ Fillament hates windows defender! mark your dir as excluded like this:
```bash
# Must be Admin to do this!
Add-MpPreference -ExclusionPath "C:\dev\my-filament-app" # to exlude (change path)
Get-MpPreference | Select-Object -ExpandProperty ExclusionPath # to check if applied
```
NOTE: Filament is NOT optimized for Windows, running it inside a unix enviroment (e.g. inside docker) is recommended.
NOTE: Never ever use Docker Bindmounts for Fillament. It slows everything down to a crawl.
