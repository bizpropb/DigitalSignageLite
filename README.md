# DigitalSignageLite

## Lightweight Digital-Signage Stack

A **browser-first** signage lite network built on three moving parts:

**Laravel** builds the backbone, providing the websocket, and the content scheduler.

**Fillament** provides the admin interface for managing the content.

**React** is used for the actual player, using either X- or I-Frames to render scrolling links and playing embeddings. 


The communication between front- and backend is secured via a JWT, completely negating any access outside of the admin interface. 
`dangerouslySetInnerHTML` isnt' actually dangerous. it completely rejects javascript injections and only allows HTML/CSS.

This system is meant to be used to display embeddings of:
- https://www.youtube.com/ 
- https://www.sway.com/
- Other services that provide an embedding code
- Simple one-pagers any AI can write consisting of HTML/CSS/JS

But also the following:
- Any site you host locally or on the web.
- Most websites, but not all.

### Pros
- **Zero-install client**: open URL, done. Runs in any modern browser (smart-TV, Pi, phone, PC)
- **Global reach**: display boots over hotel Wi-Fi, receives updates from another continent
- **Near Infinitely scalable**: The server load is kept light on purpose, allowing it to support a great number of devices.
- **End-to-end security**: JWT signatures + admin-only Filament UI
- **Ultra-light server**: plain HTTP/WS, no RTMP, no GPU, no storage churn
- **Nearly Instant content swap**: Playlists and contents may change every interval, making the transition seamlessly.
- **Cheap redundancy**: any device with a display can be used and setup in minutes.
- **Link animation ready**: down-scroll, step, down&reset, etc. already built in
- **Ease-Of-Use**: Any enduser can use this software. No deeper knowledge or complicated installations.

### Cons / Hard Limits
- **YouTube/etc ads may still run** (Assuming you can't get an adblocker on the device's browser)
- **CORS & iframe sandbox** Some websites have complex security restrictions preventing the X-Frame to work fully.
- **Lack of machine-To-machine file transfer**: Browser support only very limited storage for security reasons.
- **No hardware-level control**: can't remotely turn screen on/off, can't spoof keyboard/mouse

### ...and how to get around them
If you need to get around these restrictions, I recommend you use a Raspberry Pi or similar device, which then is used as receiver for your server, remote controlled via puppeteer (or alike). This allows you to get around all the CORS issues, and it also allows you machine-to-machine-file-transfers meaning you can transfer entire videofiles to the clientsystem without being being forced to stream them, which is a constant strain for the network.


## Prerequisites

- Docker Desktop (for PostgreSQL only)
- PHP 8.3+ (or just use herd to get around the annoying config -> https://herd.laravel.com/windows)
- Composer
- Node.js 18+
- Git


## Architecture

- **Backend**: Laravel 12 with Filament 4 admin panel (local)
- **Frontend**: React with Vite (local)
- **Database**: PostgreSQL 17 (Docker)
- **WebSockets**: Laravel Reverb (local)


## Setup

**Note:** The following steps are only for local development. If you plan on using this thing in production, use Docker for the entire Stack, not just the DB, and spare yourself the headache! Also make sure to set all the services to automatically restart, just in case.

### 1. Start Database & Install Dependencies

**Start DB & Dependencies Install (once):**
```bash
docker compose down
docker compose up -d
cd backend
composer install
cd ..
cd frontend
npm install
cd ..
```

### Only once: ⚠️ Execute this command only once for the setup! 
```bash
cd backend
php artisan config:clear
php artisan migrate:fresh --seed # This drops the entire database if there is one!
php artisan make:filament-user --name=admin --email=admin@example.com --password=password
cd ..
```

⚠️ If it fails, it means a local PostgreSQL is running and blocking your port.
Run "services.msc", find "postgresql-xxx-xx", stop it. Do step 1 again, then step 3. If that didnt help, "run netstat -ano | findstr :5432" and stop these services (might require a restart afterwards)

⚠️ If you have PostgreSQL installed on your system, you need to stop the service every time you restart your computer!

--------------------------------------------

### 4. Start Applications

**⚠️ Make sure your PostgreSQL inside your Docker is running!**

**Laravel Logs (Optional) - Terminal 1:**
```bash
cd backend
Get-Content storage/logs/laravel.log -Wait -Tail 0 
# Shows Laravel logs in real-time. Only displays major events and things flagged to log. (mostly useless tbh)
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

**Content Scheduler - Terminal 5:**
```bash
cd backend
php artisan content:schedule start
```
Manages content rotation and broadcasts to live displays.

**Frontend (React) - Terminal 6:**
```bash
cd frontend
npm run dev
```
Access: http://localhost:3000

### 5. Access pgAdmin (Optional)
- URL: http://localhost:5050
- Login: admin@example.com / password
- Add server: Host=localhost, Port=5432, Database=DigitalSignageLite, User=admin, Password=password


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

## Laravel Telescope (Debugging Tool)

Laravel Telescope is a debugging tool that logs queries, events, models, cache operations, and more.

**⚠️ Telescope is DISABLED on purpose** because it causes memory leaks in long-running processes like the content scheduler. In production, Telescope will crash the scheduler after ~60 minutes by exhausting the 128MB memory limit.

**How to enable Telescope (for debugging only):**
1. Search for the file `telescope.php`, find line 19
2. Set `TELESCOPE_ENABLED=true`
3. Run `php artisan config:clear`
4. Access Telescope at http://localhost:8000/telescope

**⚠️ IMPORTANT:** Disable Telescope before deploying or running the content scheduler for extended periods. Set `TELESCOPE_ENABLED=false` and clear the config cache.

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
