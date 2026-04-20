# NextClient

NextClient is a Laravel 13 + Vue 3 + Inertia.js CRM for freelancers and small service businesses. It ships with custom Fortify-based authentication, optional Socialite login, a polished SaaS-style dashboard, client management, searchable records, client notes, archive workflows, reminder tooling, and production-safe defaults.

## Stack

- PHP 8.3
- Laravel 13
- SQLite by default
- Vue 3 + Inertia.js
- Tailwind CSS v4
- Laravel Fortify
- Laravel Socialite
- PHPUnit

## Core Features

- Custom login, registration, password reset, remember me, logout, and email verification
- Optional social login with Google, Facebook, and GitHub
- Dashboard stats for total clients, active clients, leads, due follow-ups, recent clients, and recent notes
- Workspace-aware dashboard widgets for open tasks and recent communications
- Follow-up reminder panels for overdue and upcoming clients
- Daily reminder email preferences plus CLI reminder commands for local development
- Personal and shared workspaces with member roles, workspace switching, and invite-by-email membership
- Client create, edit, show, archive, restore, and permanent delete after archive
- Admin/user roles with a simple admin workspace screen
- CSV export with current filters applied
- CSV import with validation and safe email-based updates
- Client notes timeline
- Client task tracking with priorities, due dates, and completion states
- Client communication logging for calls, emails, meetings, and messages
- Proposal and invoice lite tracking tied to each client
- Secure token-based client portal links for shared client-facing document views
- Private client attachments with upload, download, and cleanup on delete
- Client tags, smart views, and saved views for repeat filtering
- Client activity timeline and dashboard activity feed
- Quick contact actions for email, phone, and marking a client as contacted
- Search by name, company, email, and phone
- Status filtering and archived filtering
- Authorization policies to keep users scoped to their own data
- Friendly 403, 404, 419, 500, and 503 behavior

## Local Setup

1. Install PHP, Composer, Node.js, and npm.
2. Install dependencies:

```bash
composer install
npm install
```

3. Create your environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

4. Create the SQLite database file:

```bash
touch database/database.sqlite
```

5. Run migrations:

```bash
php artisan migrate
```

6. Optionally seed local sample CRM data:

```bash
php artisan db:seed
```

7. Start the app for development:

```bash
composer run dev
```

If you prefer separate processes:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

## Social Login Setup

Social sign-in buttons are only shown when the matching provider keys are configured.

Set these values in `.env` as needed:

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI="${APP_URL}/auth/facebook/callback"

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"
```

If a provider is missing credentials, NextClient hides that button and fails gracefully if its callback is hit directly.

## Demo Accounts

If you run the local sample seeder, NextClient creates these accounts:

- `owner@example.com` / `password`
  Role: `admin`
  Use case: primary administrator with the largest demo dataset, useful for testing the dashboard, admin panel, imports, exports, reminders, and social login linking.
- `admin@example.com` / `password`
  Role: `admin`
  Use case: second administrator so you can safely test admin-to-user role changes without removing the last admin from the system.
- `freelancer@example.com` / `password`
  Role: `user`
  Use case: normal solo freelancer account for testing the everyday CRM flow without admin access.
- `user@example.com` / `password`
  Role: `user`
  Use case: second regular user with separate client records, useful for confirming that users cannot see or change each other’s data.

Each seeded account has its own client records and notes so you can test data ownership properly.

The sample seeder also creates a shared workspace called `Acme Collaborative` with all four demo users added as members. Use the Workspace settings page to switch into it and test the multi-user workspace flow, shared clients, tasks, communications, proposals, and portal links.

## User Types and What They Can Do

- `admin`
  Main use case: manage the workspace itself.
  Can: open the Admin screen, review users, and change user roles.
  Cannot: see other users’ clients, notes, attachments, or activity just because they are an admin. Data ownership rules still apply.
- `user`
  Main use case: run their own CRM workspace.
  Can: manage only their own clients, notes, attachments, reminders, imports, exports, and saved views.
  Cannot: open the Admin screen or change roles.

## Useful Commands

Build frontend assets:

```bash
npm run build
```

Run the test suite:

```bash
php artisan test
```

Preview reminder emails without sending anything:

```bash
php artisan crm:preview-followup-reminders
```

Preview reminders for one user on a specific date:

```bash
php artisan crm:preview-followup-reminders --user=owner@example.com --date=2026-03-30
```

Queue reminder emails manually:

```bash
php artisan crm:send-followup-reminders
```

Queue a reminder digest for one user right away:

```bash
php artisan crm:send-followup-reminders --user=owner@example.com --force
```

Create a timestamped SQLite backup:

```bash
php artisan crm:backup:sqlite
```

Create a backup in a custom folder:

```bash
php artisan crm:backup:sqlite --path=/absolute/path/to/backups
```

Run formatting and static checks:

```bash
composer run lint:check
npm run types:check
npm run format:check
```

## SQLite and Writable Paths

Make sure these paths are writable by the web server user in local or production:

- `storage/`
- `bootstrap/cache/`
- `database/database.sqlite`

The default drivers are chosen for simple VPS deployment:

- `DB_CONNECTION=sqlite`
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`

For SQLite-specific backups, NextClient includes:

```bash
php artisan crm:backup:sqlite
```

That command copies your active SQLite file into `storage/app/backups/sqlite` with a timestamped filename.

Reminder emails are queued, so for local testing you should keep a worker running:

```bash
php artisan queue:work --tries=1 --timeout=90
```

## Production Checklist

1. Set a real `APP_URL`.
2. Set `APP_ENV=production`.
3. Set `APP_DEBUG=false`.
4. Configure mail, session domain, and HTTPS at the web server level.
5. Create `database/database.sqlite` if you are deploying with SQLite.
6. Ensure `storage`, `bootstrap/cache`, and the SQLite file are writable.
7. Install dependencies and build assets:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

8. Run database and cache steps:

```bash
php artisan migrate --force
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

9. Run a queue worker:

```bash
php artisan queue:work --tries=1 --timeout=90
```

## Notes

- Archive is separate from client status.
- Permanent delete is only allowed after a client has been archived.
- Client attachments are stored privately on the local disk by default and are removed from storage when an attachment or archived client is permanently deleted.
- Tags are user-owned, so each account keeps its own labeling system and saved views.
- The admin screen manages elevated access only. Users still only see their own clients, notes, attachments, and activity.
- OAuth access tokens are not persisted.
- CSV imports create new clients or update existing ones when the email already belongs to one of your clients.
- The dashboard now includes reminder and activity sections so follow-up work is easier to prioritize.
- The database schema stays Eloquent-first and database-agnostic so moving to PostgreSQL or MySQL later is straightforward.
