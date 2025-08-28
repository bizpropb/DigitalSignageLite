# Presenter V3

Full-stack application with Laravel backend (with Filament admin), React frontend, and PostgreSQL database, all containerized with Docker.

## Prerequisites

- Docker Desktop
- Git

## Setup

### 1. Install dependencies

**Backend:**
```bash
cd backend
composer install
npm install
cd ..
```

**Frontend:**
```bash
cd frontend
npm install
cd ..
```

### 2. Start the application
```bash
docker compose down
docker volume rm presenter-v3_frontend_node_modules presenter-v3_backend_storage presenter-v3_backend_bootstrap_cache presenter-v3_backend_vendor # These are cache/dependency volumes, but the naming prevents regeneration. remove them before startup to prevent issues.
docker compose up --build
```

This will start:
- Laravel backend: http://localhost:8000
- React frontend: http://localhost:3000
- PostgreSQL database: localhost:5432
- Filament admin: http://localhost:8000/admin
- pgAdmin (PostgreSQL admin): http://localhost:5050
(You can find the ports in docker desktop too)

### 4. Create admin user

⚠️ **Important**: Git doesn't track database changes, so you need to create an admin user after first setup for FILAMENT:

```bash
docker exec -it presenter-backend php artisan make:filament-user
```

Use these generic credentials for development:
- **Name**: admin
- **Email**: admin@example.com
- **Password**: password

### 5. Access pgAdmin (PostgreSQL admin)

Navigate to http://localhost:5050 and login with the same credentials you defined in step 4:
- **Email**: admin@example.com
- **Password**: password

Connect your DB with pgAdmin via "add new server":
- **Name**: presenter_v3_connection
Change to the "Connections" Tab.
- **Host**: postgres
- **Port**: 5432 (should be there)
- **Maintenance database**: presenter_v3
- **Username**: postgres
- **Kerberos**: false
- **Password**: password
- **Save password**: true
(ignore the rest and save)


### Accessing containers
```bash
# Laravel backend
docker exec -it presenter-backend bash

# PostgreSQL
docker exec -it presenter-postgres psql -U postgres -d presenter_v3
```

## Architecture

- **Backend**: Laravel 12 with Filament 4 admin panel
- **Frontend**: React with Vite
- **Database**: PostgreSQL 17
- **Infrastructure**: Docker with named volumes (no bind mounts)

All dependencies are containerized using Docker volumes for optimal performance and portability.