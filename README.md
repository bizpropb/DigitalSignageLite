# Presenter V4

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

### 3. Setup Database ⚠️ Execute this command only once for the setup!
```bash
cd backend
php artisan config:clear
php artisan migrate:fresh --seed # This drops the entire database if there is one!
php artisan make:filament-user --name=admin --email=admin@example.com --password=password
cd ..
```
⚠️ If it fails, it means a local PostgreSQL is running and blocking your port.
Run "services.msc", find "postgresql-xxx-xx", stop it. Do step 1 again, then step 3.
If that didnt help, "run netstat -ano | findstr :5432" and stop these services (might require a restart afterwards)

### 4. Start Applications

**Laravel Logs (Optional) - Terminal 1:**
```bash
cd backend
Get-Content storage/logs/laravel.log -Wait -Tail 0 # Shows Laravel logs in real-time. Only displays major events and things flagged to log. (mostly useless tbh)
```

**Backend (Laravel + Filament) - Terminal 2:**
```bash
cd backend
php artisan serve
```
Access: http://localhost:8000 (Laravel) | http://localhost:8000/admin (Filament)

**WebSocket Server - Terminal 3:**
```bash
cd backend
php artisan reverb:start --port=8080 --debug # Proper Logs
```

👨‍🔧*Hint:* If Websocket ever gives you trouble, download wireshark to see all outgoing and incoming packages. it's way better than anything laravel offers.

**Queue Worker - Terminal 4:**
```bash
cd backend
php artisan queue:work
```
Processes broadcast jobs and sends them to WebSocket server.

**Frontend (React) - Terminal 5:**
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
php artisan migrate:fresh --seed # This drops the entire database if there is one!
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

⚠️Find ";opcache.enable=" in your php.ini and set it to "opcache.enable=1" for extra speed.

⚠️ Fillament hates windows defender! mark your dir as excluded like this:
```bash
# Must be Admin to do this!
Add-MpPreference -ExclusionPath "C:\dev\my-filament-app" # to exlude (change path)
Get-MpPreference | Select-Object -ExpandProperty ExclusionPath # to check if applied
```
NOTE: Filament is NOT optimized for Windows, running it inside a unix enviroment (e.g. inside docker) is recommended.
NOTE: Never ever use Docker Bindmounts for Fillament. It slows everything down to a crawl.

## Pitfalls

⚠️ Do NOT Cache things during development! it can horribly confuse you and make you think your code is not working.
```bash
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache
php artisan filament:optimize
php artisan livewire:publish --assets
```
