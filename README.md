# IMM Content Planner

Internal Laravel 13 application for managing brand workspaces and monthly social-content plans. It uses Blade, Vite, vanilla JavaScript, Eloquent, session authentication, private-by-default Cloudflare R2 media, and browser Print -> Save as PDF.

## Requirements

- PHP 8.3+
- Composer 2
- Node.js 20+
- SQLite for local development or MySQL for deployment
- Cloudflare R2 credentials for persistent production media

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm install
npm run build
composer run dev
```

Set `DB_CONNECTION=sqlite` and `DB_DATABASE=/absolute/path/to/database/database.sqlite` for SQLite. For MySQL, set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.

The application timezone defaults to `Asia/Jakarta`.

## Local Admin Seeder

Configure these variables before running the seeder:

```dotenv
CONTENT_PLANNER_ADMIN_NAME=
CONTENT_PLANNER_ADMIN_EMAIL=
CONTENT_PLANNER_ADMIN_PASSWORD=
```

When they are empty, the local-development-only fallback is:

- Email: `admin@imm.local`
- Password: `password`

These fallback credentials must never be used in production. Set strong deployment credentials in the environment and rotate any seeded password before exposing the application.

## Cloudflare R2 Object Storage

Persistent brand logos and content images are stored as R2 object keys through `MediaStorageService`. No uploaded binary is stored in the database, localStorage, or as base64.

1. Create an R2 bucket in Cloudflare.
2. Create an API token with minimum object read/write permissions for that bucket.
3. Copy the S3-compatible API endpoint.
4. Configure the deployment environment:

```dotenv
MEDIA_DISK=r2
MEDIA_VISIBILITY=private
R2_ACCESS_KEY_ID=replace-with-cloudflare-r2-access-key
R2_SECRET_ACCESS_KEY=replace-with-cloudflare-r2-secret-key
R2_BUCKET=imm-content-planner
R2_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
R2_URL=
R2_DEFAULT_REGION=us-east-1
R2_USE_PATH_STYLE_ENDPOINT=false
R2_THROW_EXCEPTIONS=true
```

`R2_ENDPOINT` is the Cloudflare R2 S3-compatible API endpoint. `R2_URL` is an optional public base URL or custom domain and should stay empty for private mode. Never commit real keys; production secrets belong only in the deployment environment.

Private mode streams images through authenticated, ownership-authorized Laravel routes. No `php artisan storage:link` command is required for R2 media.

Optional public mode:

```dotenv
MEDIA_VISIBILITY=public
R2_URL=https://media.example.com
```

Public mode can make objects accessible to anyone with the URL unless additional Cloudflare protection is configured.

Automated tests use `Storage::fake()` and never contact a real R2 bucket.

## Commands

```bash
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
./vendor/bin/pint
npm install
npm run build
```

## Main Routes

- `/login`, `/forgot-password`, `/reset-password/{token}`
- `/brands`
- `/brands/{brand}/workspace`
- `/contents/{contentPlan}/preview`
- `/contents/{contentPlan}/print`
- `/media/{contentImage}` for private authorized media

Registration is intentionally disabled. All workspace routes require session authentication and an active user account. Brand and content policies enforce per-user ownership.

The PDF workflow uses the browser print dialog. Open a content preview, choose **Print / PDF**, then select **Save as PDF**.
