# MyBlog: Laravel Blog Platform with Role-Based Admin Panel

A multi-author blog built with **Laravel 12**. Authors write posts, editors approve them, visitors read, comment and react, and admins control everything from a role-based admin panel with a built-in activity log.

[[_TOC_]]

## Features

### Public site
- Home page, post pages, category pages, tag pages and author profile pages
- Full-text style search across posts
- Comments with moderation (pending, approved, spam)
- Anonymous emoji reactions on posts: 👍 ❤️ 😂 😮 😢 (one per visitor, click again to remove)
- Popular posts sidebar (by views), category and tag navigation
- Light / dark mode toggle
- Auto-generated SEO meta title, description, keywords and image for every post

### Writing and approval workflow
- Authors write and submit posts; new posts from authors wait as **pending**
- Users with approve permission can **approve or reject** posts (their own posts publish instantly)
- Authors get an in-app **notification** when their post is approved
- **Trash**: soft-deleted posts can be restored or deleted forever
- Author profile page with avatar and bio

### Admin panel
- Dashboard with pending posts and pending comments counters
- Manage posts, categories, tags, comments and users
- **Roles & Permissions**: create custom roles and tick exactly which permissions each role gets
- **Activity Log**: who did what, when and from which IP (see below)
- **Mail settings**: configure SMTP from the browser (overrides `.env`) and send a test email

### Activity log
Every important action is recorded: login, logout, failed login, post created / updated / trashed / approved / rejected / restored / deleted, comment approved / spam / deleted, user created / role changed / deleted, role changes, category and tag changes, and mail settings updates.

The log page supports search, filtering by user, action type and date range, and pagination. It is append-only (no edit or delete) and never stores passwords or post content.

### Scheduled tasks
- **Pending posts reminder**: every day at 09:00 (Asia/Colombo) approvers receive an email listing posts that have been waiting for approval for 2 or more days.

## Tech stack

| Area | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 12, Eloquent ORM |
| Authentication | Laravel Breeze |
| Authorization | Laravel Gates and Policies with database-driven roles and permissions |
| Database | SQLite by default (MySQL also works) |
| Frontend | Blade templates, Bootstrap 5, Bootstrap Icons, custom CSS (`admin.css`, `blog.css`, `dark.css`, `auth.css`) |
| Build tools | Vite, Composer, npm |
| Mail | Laravel Mail with SMTP settings stored in the database |
| Scheduling | Laravel Scheduler (`routes/console.php`) |

## Requirements

- PHP 8.2 or higher (with the `sqlite` and `mbstring` extensions)
- Composer 2
- Node.js 18+ and npm
- (Optional) MySQL if you do not want SQLite

## Installation

```bash
# 1. Clone the project
git clone <your-gitlab-repository-url>
cd <project-folder>

# 2. Install PHP and JS dependencies
composer install
npm install
npm run build

# 3. Environment file and app key
cp .env.example .env          # Windows (PowerShell): copy .env.example .env
php artisan key:generate

# 4. Create the tables (answer "yes" if Laravel offers to create the SQLite file)
php artisan migrate

# 5. Make uploaded images (avatars, post images) available
php artisan storage:link

# 6. Start the app
php artisan serve
```

Open **http://127.0.0.1:8000**.

### Create the first admin

1. Register a new account at `/register`.
2. Promote it to admin:

```bash
php artisan tinker
>>> App\Models\User::first()->forceFill(['role' => 'admin'])->save();
>>> exit
```

3. Log in and open **http://127.0.0.1:8000/admin**.

## Configuration

Edit `.env`:

| Key | Purpose |
|---|---|
| `APP_NAME` | Site name shown in the layout and emails |
| `APP_URL` | Base URL (used in email links) |
| `DB_CONNECTION` | `sqlite` (default) or `mysql` |
| `ADMIN_PATH` | URL prefix of the admin panel (default `admin`) |
| `USER_PATH` | URL prefix of the author panel (default `user`) |
| `MAIL_*` | SMTP settings used when the Mail settings page is off |

**Mail:** you can either fill the `MAIL_*` keys in `.env`, or log in as admin and open **Mail settings**, switch on *use this setting*, fill in the SMTP details and save. Use *Send test email* to confirm that it works.

## Roles and permissions

Access is controlled by permissions, and roles are just named groups of permissions. Admins always have full access.

| Permission | What it allows |
|---|---|
| `posts.create` | Write and submit posts |
| `posts.view_all` | See every author's posts |
| `posts.edit_any` | Edit or trash any post |
| `posts.approve` | Approve or reject posts |
| `posts.trash` | Manage the Trash |
| `comments.moderate` | Approve, mark as spam or delete comments |
| `categories.manage` | Manage categories |
| `tags.manage` | Manage tags |
| `users.manage` | Manage users and assign roles |
| `settings.manage` | Change mail (SMTP) settings |
| `activity.view` | View the activity log |

Create or edit roles from **Roles & Permissions** in the admin panel.

## Artisan commands and scheduler

| Command | Description |
|---|---|
| `php artisan seo:generate` | Auto-generate post meta title, description, keywords and image (`--force` regenerates existing ones) |
| `php artisan posts:remind-pending --days=2` | Email approvers about posts pending for 2 or more days |
| `php artisan schedule:list` | Show registered scheduled tasks |
| `php artisan schedule:work` | Run the scheduler locally (keep it open in a terminal) |

On a server, run the scheduler every minute with cron:

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

On Windows, create a Task Scheduler job that runs `php artisan schedule:run` every minute from the project folder.

## Project structure

```
app/
├── Console/Commands/      seo:generate, posts:remind-pending
├── Http/Controllers/      Post, Comment, User, Role, Category, Tag, Reaction, ActivityLog ...
├── Mail/                  Email classes (test email, pending posts reminder)
├── Models/                Post, Comment, Reaction, Role, Permission, ActivityLog ...
├── Notifications/         PostApproved
├── Policies/              PostPolicy
├── Providers/             Gates and login / logout event listeners
└── Support/               Permissions, Panel, MailSettings, Activity (log helper)
config/                    admin.php, permissions.php
database/migrations/       Database structure
resources/views/           frontend, admin, auth, emails, layouts
routes/                    web.php, auth.php, console.php
public/css, public/js      Stylesheets and scripts
```

## Author

Developed by **<your name>**.