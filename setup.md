# HCM Lab — Setup & Design

Laravel 12 + MySQL 8 + Docker + Tailwind. Breeze for auth, Spatie for roles.

## 1. ERD (Mermaid)

```mermaid
erDiagram
    users ||--|| profiles : has
    users ||--o{ model_has_roles : has
    roles ||--o{ model_has_roles : assigned
    profiles ||--o{ documents : uploads
    users ||--o{ projects : created
    projects ||--o{ project_images : has
    users ||--o{ gallery_items : uploaded
    users {
        id bigint PK
        name string
        email string UK
        password string
        email_verified_at timestamp
        timestamps
    }
    profiles {
        id bigint PK
        user_id bigint FK
        photo_path string
        nim string
        prodi string
        angkatan year
        phone string
        bio text
        skills json
        youtube_url string
        github_url string
        portfolio_url string
        timestamps
    }
    documents {
        id bigint PK
        profile_id bigint FK
        label string
        file_path string
        timestamps
    }
    projects {
        id bigint PK
        user_id bigint FK
        title string
        slug string UK
        description text
        youtube_url string
        github_url string
        published boolean
        timestamps
    }
    project_images {
        id bigint PK
        project_id bigint FK
        path string
        sort_order int
    }
    gallery_items {
        id bigint PK
        user_id bigint FK
        title string
        caption text
        image_path string
        taken_at date
        timestamps
    }
    roles {
        id bigint PK
        name string
        guard_name string
    }
```

## 2. Schema summary

- `users` — Breeze default (id, name, email, password, ...)
- `profiles` — 1:1 with user. Holds all lab-specific fields. `skills` as JSON (no join table needed).
- `documents` — PDFs attached to a profile.
- `projects` + `project_images` — lab projects with multi-image gallery.
- `gallery_items` — activity photos.
- Spatie tables (`roles`, `permissions`, `model_has_roles`, ...) via `php artisan vendor:publish` + migration.
- Roles seeded: `admin`, `member`, `alumni`.

Role determines cards visible on `/members` vs `/alumni`. Same user table.

## 3. Folder structure (custom overlay on Breeze)

```
src/app/
├── Http/Controllers/
│   ├── Admin/{DashboardController, MemberController, AlumniController, ProjectController, GalleryController}.php
│   ├── PublicController.php          # home, about, contact
│   ├── DirectoryController.php       # /members /alumni w/ filters
│   ├── ProjectController.php         # public projects
│   ├── GalleryController.php         # public gallery
│   └── ProfileController.php         # extends Breeze — self-edit
├── Http/Middleware/EnsureRole.php    # ponytail: use spatie middleware instead
├── Models/{User, Profile, Document, Project, ProjectImage, GalleryItem}.php
resources/views/
├── layouts/{app, admin, public}.blade.php
├── public/{home, about, contact, members, alumni, projects, gallery}.blade.php
├── profile/edit.blade.php             # overrides Breeze
├── admin/{dashboard, members, alumni, projects, gallery}/*.blade.php
├── components/{card, nav, footer, stat}.blade.php
routes/web.php
database/{migrations, seeders/RoleSeeder.php}
```

## 4. Step-by-step

Run these **once** on a clean host (Docker Desktop installed):

```bash
# 1. From C:\Users\LENOVO\Documents\labHCM
docker compose up -d mysql
docker compose run --rm app composer create-project laravel/laravel:^12.0 .
docker compose run --rm app composer require laravel/breeze --dev
docker compose run --rm app composer require spatie/laravel-permission
docker compose run --rm app php artisan breeze:install blade
docker compose run --rm app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 2. Copy custom files from ./custom/ into ./src/ (see files in this repo)
#    (or apply this repo's patches — the files under src/ in this scaffold are the overlay)

# 3. Env
cp src/.env.example src/.env
# edit: DB_HOST=mysql DB_DATABASE=hcm_lab DB_USERNAME=hcm DB_PASSWORD=hcm
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan storage:link

# 4. DB
docker compose up -d
docker compose exec app php artisan migrate --seed

# 5. Assets
docker compose up node   # keeps vite dev server running on :5173

# 6. Open http://localhost:8080
#    Default admin: admin@hcm.test / password
```

Default seeded admin credentials in `RoleSeeder.php`.
