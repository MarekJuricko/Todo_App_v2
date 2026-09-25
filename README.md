<h1 align="center">ToDo App</h1>

<p align="center">
  A simple task manager built with <b>Laravel 13</b>, featuring a REST API (Sanctum) and a web UI (Inertia.js + Vue 3).
</p>

---

## Features

- Register, log in, log out
- Create, edit, delete, and complete tasks
- Attach / detach tags, filter tasks by tag
- Search by title or description
- Filter by status (completed / pending)
- Pagination
- REST API with token auth (Sanctum)
- Web UI with Inertia.js + Vue 3
- Pest tests

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+ & npm

## Setup

```bash
git clone https://github.com/MarekJuricko/Todo_App_v2
cd Todo_App_v2

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite   # Windows: ni database\database.sqlite

php artisan migrate --seed
```

Run both dev servers:

```bash
php artisan serve   # terminal 1
npm run dev          # terminal 2
```

- Web UI: **http://127.0.0.1:8000**
- API: **http://127.0.0.1:8000/api**

## Demo Login

| Email | Password | Notes |
|---|---|---|
| `test@example.com` | `password` | 20 tasks, 5 tags |
| `other@example.com` | `password` | 3 tasks (for testing user isolation) |

Reset the database anytime:

```bash
php artisan migrate:fresh --seed
```

## Tests

```bash
php artisan test
```

## API Reference

All API requests require `Accept: application/json`. Authenticated routes require:

```
Authorization: Bearer <token>
```

**Auth**

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/register` | Register |
| POST | `/api/login` | Log in, returns a token |
| POST | `/api/logout` | Log out |

**Tasks**

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/tasks` | List tasks (`search`, `status`, `tag`, `page` query params) |
| POST | `/api/tasks` | Create a task |
| GET | `/api/tasks/{id}` | Task detail |
| PUT | `/api/tasks/{id}` | Update a task |
| DELETE | `/api/tasks/{id}` | Delete a task |
| PATCH | `/api/tasks/{id}/complete` | Toggle completion |

**Tags**

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/tags` | List your tags |
| POST | `/api/tasks/{id}/tags` | Attach a tag (creates it if new) |
| DELETE | `/api/tasks/{id}/tags/{tagId}` | Detach a tag |

**Example**

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

## Project Structure

```
app/Http/Controllers/Api/   REST API controllers (token auth)
app/Http/Controllers/       Web controllers (session auth)
app/Http/Requests/          Form Request validation (shared by API & web)
app/Http/Resources/         API JSON response formatting
app/Policies/               Authorization (users only access their own tasks)
resources/js/Pages/         Vue pages
resources/js/Layouts/       Shared Vue layout
routes/api.php              API routes
routes/web.php              Web routes
tests/Feature/              Pest tests
```

## License

Open-sourced under the [MIT license](https://opensource.org/licenses/MIT).