# RotationIQ

RotationIQ is a volleyball rotation planning tool for coaches and players. It lets a coach
build a team roster, assign positions and roles, and lay out attack and defence rotations on
an interactive court — either as a single formation or as a full 6-rotation sequence — which
teammates can then view from their own account.

## Features

- **Teams** — a coach creates a team and gets a join code to share; players join with that
  code. Team roles (manager, assistant manager, student) control who can edit the roster,
  rename positions, or post announcements.
- **Attack & Defence rotations** — drag players onto a court to build a formation. A rotation
  can be a **single** snapshot or a **sequence** of all 6 rotations, built and edited slot by
  slot. Rotations saved to a team are visible to every team member; only the creator can edit
  or delete them.
- **Moving Players** — a separate tool for sketching a player's movement from one court
  position to another, with a simple click-to-select / click-to-set-destination animation.
- **Team chat & announcements** — a running chat for the team, plus a separate announcements
  feed that only the coach/managers can post to.
- **Responsive, touch-friendly court** — the interactive court works with a mouse on desktop
  and with touch on a phone, and scales to fit the screen either way.

## Tech stack

- **Backend**: Laravel 12, PHP 8.2+, MySQL
- **Auth**: Laravel Breeze (registration, login, password reset)
- **Frontend**: Blade, Tailwind CSS, Alpine.js, Vite
- **Testing**: Pest

## Roles

RotationIQ has two layers of roles:

- **Account role** (`users.role`): `coach` or `student`, chosen at registration. A coach can
  create teams; a student can only join one with a code.
- **Team role** (`team_user.role`, per team membership): `manager`, `assistant_manager`, or
  `student`. The team's creator (the coach) is always treated as its manager. A manager can
  change roster roles/positions/attendance and post announcements; an assistant manager can
  edit student roster data but not promote anyone; a student can only view.

## Core data model

| Table | Purpose |
|---|---|
| `users` | Accounts — name, email, password, account `role` |
| `teams` | A team — name, `coach_id`, `join_code` |
| `team_user` | Pivot: team membership — per-member `role`, `position` (`S`/`MB`/`OH`/`RS`/`L`), `attendance` |
| `team_messages` | Team chat history |
| `team_announcements` | Coach/manager announcements, separate from chat |
| `attack_rotations` / `defence_rotations` | A saved rotation — `name`, `type` (`single`/`sequence`), `players` (JSON), optional `team_id` |
| `moving_players` | A saved player-movement sketch — `name`, `players` (JSON) |

Player layouts (`players` on rotations and moving-player records) are stored as JSON rather
than normalized rows, since each entry is really a court diagram (role, position, pixel
coordinates) rather than relational data — but this means the database can't enforce things
like "no duplicate position" or "these player ids belong to this team" by itself; that's
validated in `BaseRotationController` at save time instead.

## Local setup

Requirements: PHP 8.2+, Composer, Node.js, MySQL.

```bash
git clone <this-repo>
cd RotationIQ

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` and point `DB_*` at a MySQL database you've created:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rotationiq
DB_USERNAME=root
DB_PASSWORD=
```

Then:

```bash
php artisan migrate
npm run build   # or `npm run dev` while actively developing
php artisan serve
```

Visit `http://localhost:8000`, register an account, and either create a team (as a coach) or
join one with a code (as a student).

## Running tests

```bash
php artisan test
```

## Deploying to production

A few things that matter beyond "it runs on `php artisan serve` locally":

- **`APP_DEBUG=false`** and **`APP_ENV=production`** — `APP_DEBUG=true` (the local default)
  leaks stack traces and config to visitors on any error; never deploy with it on.
- **`SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` = `database`** — already the default
  here. Most hosts (Railway, Render, etc.) use an ephemeral filesystem that's wiped on every
  deploy/restart, so file-based sessions or cache would silently reset; the database-backed
  drivers survive that.
- **Trusted proxies** — `bootstrap/app.php` already calls `$middleware->trustProxies(at:
  '*')`, which is required behind any reverse-proxy host (Railway, Render, Heroku-style
  platforms all terminate HTTPS at their own edge and forward plain HTTP internally). Without
  it, Laravel thinks every request is insecure and generates `http://` URLs even on an HTTPS
  site.
- **`APP_URL`** must match the real public URL once one exists, not `http://localhost`.
- **Build step** — the deploy needs to run both `composer install --no-dev --optimize-autoloader`
  and `npm install && npm run build` (the UI is compiled through Vite, not served raw).
- **Migrations** — run `php artisan migrate --force` once after the first deploy (and after
  any later schema change); it isn't run automatically on every deploy.
