# HCM Laboratory · Profile Site

Responsive Laravel 12 web app for the **Human Centric Multimedia (HCM) Laboratory**.

**Stack:** Laravel 12 · MySQL 8 · Docker · Nginx · Blade + Tailwind · Breeze auth · Spatie Permission · Vite.

**Roles:** Admin, Dosen, Member (mahasiswa aktif), Alumni.

## Features

- Single-page marketing site: Home, About, Projects, Gallery, Contact (contact scrolls to footer)
- People directory with live search (type to filter, no reload), filter by role/prodi/angkatan, sort by name/batch
- Hero carousel showing latest projects + gallery highlights (5 s auto-advance, pause on hover)
- Role-aware self-profile edit: NRP for mahasiswa/alumni, NIP for dosen
- Admin: full CRUD for Dosen/Members/Alumni, Projects (multi-image), Gallery. Admin can edit any user's profile
- Glass-morphism UI with soft gradient backdrops
- Cross-document view transitions (native, degrades to CSS fade)

## Prerequisites

- Docker & Docker Compose (Docker Desktop on Windows/macOS, `docker` + `docker compose` plugin on Linux)
- Git

Everything else (PHP, Composer, Node, MySQL) runs inside containers.

## 1. Local setup (first time)

```bash
git clone https://github.com/mulyodwi16/LabHCM-Profile.git labHCM
cd labHCM

# 1. bootstrap config
cp docker-compose.yml.example docker-compose.yml
cp src/.env.example src/.env
#    edit both files if you want to change DB passwords / APP_URL / port

# 2. bring up MySQL first (image download can take a few minutes)
docker compose up -d mysql

# 3. Laravel bootstrap: composer install, key, storage link
docker compose run --rm app sh -c "\
  composer install --no-interaction && \
  php artisan key:generate && \
  php artisan storage:link \
"

# 4. give php-fpm write access to storage
docker compose run --rm app sh -c "\
  chown -R www-data:www-data storage bootstrap/cache && \
  chmod -R ug+w storage bootstrap/cache \
"

# 5. migrate + seed sample data (roles, admin, sample users, projects, gallery)
docker compose run --rm app php artisan migrate --seed --force

# 6. build front-end assets
docker compose run --rm node sh -c "npm install && npm run build"

# 7. start the stack
docker compose up -d app nginx

# open http://localhost:8080
```

**Default admin:** `admin@hcm.test` / `password` (change immediately after first login).

## 2. Server deployment

Same flow as local, with production tweaks. The steps below assume a Linux server with Docker installed and a domain pointing at it.

### 2a. Clone + configure

```bash
ssh you@server
git clone https://github.com/mulyodwi16/LabHCM-Profile.git /opt/hcm
cd /opt/hcm

cp docker-compose.yml.example docker-compose.yml
cp src/.env.example src/.env
```

Edit `docker-compose.yml`:
- Replace both `CHANGEME_*` MySQL passwords with strong random strings
- **Remove the `- "3306:3306"` line under `mysql`** so the database is not exposed to the internet
- Change `- "8080:80"` under `nginx` to `- "127.0.0.1:8080:80"` if you're putting a reverse proxy (Caddy / Traefik / host nginx) in front

Edit `src/.env`:
```env
APP_NAME="HCM Laboratory"
APP_ENV=production
APP_KEY=            # will be generated below
APP_DEBUG=false
APP_URL=https://hcm.your-domain.tld

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=hcm_lab
DB_USERNAME=hcm
DB_PASSWORD=            # match the CHANGEME_APP_PASSWORD you set above

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
LOG_LEVEL=warning
```

### 2b. Bring it up

```bash
docker compose up -d mysql
docker compose run --rm app composer install --no-interaction --optimize-autoloader --no-dev
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan storage:link
docker compose run --rm app sh -c "chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+w storage bootstrap/cache"

# migrate WITHOUT seed on production (unless you want the demo data)
docker compose run --rm app php artisan migrate --force

# create the real admin user (interactive tinker)
docker compose run --rm app php artisan tinker
#  >>> $u = App\Models\User::create(['name'=>'HCM Admin','email'=>'you@lab.tld','password'=>bcrypt('STRONG_PASSWORD'),'email_verified_at'=>now()]);
#  >>> Spatie\Permission\Models\Role::firstOrCreate(['name'=>'admin','guard_name'=>'web']);
#  >>> Spatie\Permission\Models\Role::firstOrCreate(['name'=>'dosen','guard_name'=>'web']);
#  >>> Spatie\Permission\Models\Role::firstOrCreate(['name'=>'member','guard_name'=>'web']);
#  >>> Spatie\Permission\Models\Role::firstOrCreate(['name'=>'alumni','guard_name'=>'web']);
#  >>> $u->assignRole('admin');
#  >>> exit

# build front-end for production
docker compose run --rm node sh -c "npm install && npm run build"

# cache config for speed
docker compose run --rm app sh -c "\
  php artisan config:cache && \
  php artisan route:cache && \
  php artisan view:cache \
"

docker compose up -d app nginx
```

### 2c. Put a reverse proxy in front (recommended)

The app container listens on port 80 inside the Docker network. On the host, bind nginx to `127.0.0.1:8080` and put Caddy (easiest) or nginx on the public interface for HTTPS.

**Caddy** (`/etc/caddy/Caddyfile`):
```
hcm.your-domain.tld {
    reverse_proxy 127.0.0.1:8080
}
```
Caddy handles Let's Encrypt automatically. Restart with `systemctl reload caddy`.

### 2d. Ongoing operations

```bash
# view logs
docker compose logs -f app nginx

# apply a code update
git pull
docker compose run --rm app composer install --no-interaction --optimize-autoloader --no-dev
docker compose run --rm app php artisan migrate --force
docker compose run --rm node sh -c "npm install && npm run build"
docker compose run --rm app sh -c "php artisan config:cache route:cache view:cache"
docker compose restart app nginx

# backup the database
docker exec hcm_mysql sh -c 'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" hcm_lab' > backup-$(date +%F).sql

# open a shell inside the app container
docker exec -it hcm_app sh
```

**Persistent data:**
- MySQL: named volume `mysql_data` (survives `docker compose down`, dies on `down -v`)
- Uploaded files: `src/storage/app/public/` (bind-mounted, so it lives on the host filesystem under `/opt/hcm/src/storage/app/public/`)

Back up both if you value the data.

## 3. Development commands

```bash
# start everything including vite HMR
docker compose up -d

# stop everything (data survives)
docker compose down

# tear down INCLUDING mysql volume (deletes all data)
docker compose down -v

# tail logs
docker compose logs -f app

# artisan
docker exec -it hcm_app php artisan <command>

# rebuild assets after touching css/js
docker compose run --rm node sh -c "npm run build"
```

## 4. Project structure

```
labHCM/
├── docker/                        # Docker build files (Dockerfile, nginx.conf, php.ini)
├── docker-compose.yml.example     # template - copy to docker-compose.yml
├── src/                           # Laravel app
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── Admin/             # DashboardController, UserController, ProjectController, GalleryController
│   │   │   ├── DirectoryController.php    # /people with live search
│   │   │   ├── ProfileController.php      # self-edit
│   │   │   ├── PublicController.php       # home
│   │   │   └── PublicProjectController.php
│   │   └── Models/                # User, Profile, Document, Project, ProjectImage, GalleryItem
│   ├── database/
│   │   ├── migrations/            # profiles, documents, projects, project_images, gallery_items
│   │   └── seeders/DatabaseSeeder.php     # dev seed - roles + sample users + demo content
│   ├── resources/
│   │   ├── css/app.css            # Tailwind + glass utilities + view-transition
│   │   └── views/
│   │       ├── layouts/           # public.blade.php, admin.blade.php
│   │       ├── public/            # home, directory, project_show, partials/people-list
│   │       ├── profile/edit.blade.php
│   │       ├── admin/             # dashboard, users/, projects/, gallery/
│   │       └── components/person-card.blade.php
│   ├── routes/web.php
│   ├── public/images/             # HCMBlue.svg, HCMWhite.svg logos
│   └── .env.example
├── setup.md                       # ERD + schema reference
├── HCMBlue.svg, HCMWhite.svg      # original logos (source of truth)
└── README.md
```

## 5. Roles & routes

Public:
- `GET /` — single-page home (Home / About / Projects / Gallery, contact = footer)
- `GET /people` — directory, live search
- `GET /projects/{slug}` — project detail

Authenticated (any role):
- `GET/PATCH /profile` — self-edit (role-aware NRP vs NIP)
- `POST /profile/documents`, `DELETE /profile/documents/{id}` — supporting PDFs

Admin (`role:admin` middleware):
- `GET /admin` — dashboard stats
- `GET /admin/{dosen|member|alumni}` — list + CRUD
- `GET /admin/projects` — project CRUD
- `GET /admin/gallery` — gallery CRUD

Breeze standard auth pages at `/login`, `/register`, `/forgot-password`, `/logout`.

## 6. Common questions

**Where do I put new logo files?**
Drop replacements at `src/public/images/HCMBlue.svg` and `src/public/images/HCMWhite.svg`. Refresh browser, no rebuild needed.

**How do I add a new dosen / member / alumni?**
Login as admin, go to Admin sidebar > Dosen/Members/Alumni > "+ New". You can also edit any user's full profile from that screen.

**Can normal users edit other users' profiles?**
No. `/profile` always resolves to the currently authenticated user, and only `role:admin` can reach `/admin/*`.

**Migration to change a column later?**
Add a new migration file under `src/database/migrations/`, then `docker exec hcm_app php artisan migrate`.

## 7. License

Internal HCM Laboratory project.
