# Multi-Currency Payment API

A Laravel 12 REST API for managing multi-currency payment requests across a
company with employees in different countries. Authenticated users submit
payments in their local currency, the system fetches the real-time exchange
rate and converts the amount to EUR, and the finance team approves or rejects
each request.

Built for the Buzzvel 2026 Dev Team Test.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
- [Default Users (Seeded)](#default-users-seeded)
- [API Documentation](#api-documentation)
- [API Endpoints](#api-endpoints)
- [Running Tests](#running-tests)
- [Scheduled Task (48h Expiration)](#scheduled-task-48h-expiration)
- [Architecture](#architecture)
- [Useful Commands](#useful-commands)

---

## Features

- **Token authentication** with Laravel Sanctum (register, login, logout).
- **Role-based authorization** (`employee` and `finance`) enforced by both
  middleware and policies.
- **Payment requests**: create, list (with status filter), view, approve and
  reject.
- **Real-time exchange rates** fetched from a public API on creation. The rate,
  its source and the timestamp are stored with the payment and never change.
- **Scheduled expiration**: pending requests older than 48 hours are
  automatically marked as expired.
- **Consistent JSON error responses** across the whole API.
- **Interactive API documentation** (OpenAPI / RapiDoc).
- **Full test suite** (unit + feature).

---

## Tech Stack

| Component        | Version / Tool            |
| ---------------- | ------------------------- |
| Language         | PHP 8.3                   |
| Framework        | Laravel 12                |
| Authentication   | Laravel Sanctum           |
| Database         | MySQL 8.0                 |
| Web server       | Nginx                     |
| API docs         | L5-Swagger (OpenAPI) + RapiDoc |
| Containerization | Docker + Docker Compose   |

---

## Getting Started

The entire environment runs in Docker. You only need **Docker** and **Docker
Compose** installed — no local PHP, Composer or MySQL required.

### 1. Clone the repository

```bash
git clone https://github.com/Claudio-16dv/buzzvel-payment-test.git
cd buzzvel-payment-test
```

### 2. Create the environment file

```bash
cp .env.example .env
```

The default values already point to the Dockerized MySQL service, so no changes
are needed to run locally.

### 3. Build and start the containers

```bash
docker compose up -d --build
```

This builds the PHP image (installing Composer dependencies automatically) and
starts four services:

| Service     | Description                                   | Host port |
| ----------- | --------------------------------------------- | --------- |
| `nginx`     | Web server (entry point)                      | `8000`    |
| `app`       | PHP 8.3-FPM running Laravel                   | —         |
| `db`        | MySQL 8.0                                      | `3308`    |
| `scheduler` | Runs the Laravel scheduler for expiration job | —         |

### 4. Generate the app key, run migrations and seeders

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 5. You're ready

- API base URL: `http://localhost:8000/api`
- Interactive docs: `http://localhost:8000/api-docs`

---

## Default Users (Seeded)

All seeded users share the password **`password`**.

| Name           | Email                  | Role     | Country        | Currency |
| -------------- | ---------------------- | -------- | -------------- | -------- |
| Sofia Martins  | finance@example.com    | finance  | Portugal       | EUR      |
| Ana Costa      | ana@example.com        | employee | Brazil         | BRL      |
| John Smith     | john@example.com       | employee | United States  | USD      |
| Emma Brown     | emma@example.com       | employee | United Kingdom | GBP      |
| Yuki Tanaka    | yuki@example.com       | employee | Japan          | JPY      |
| Liam Tremblay  | liam@example.com       | employee | Canada         | CAD      |

The seeder also creates sample payment requests in different statuses
(pending, approved, rejected) for each employee.

---

## API Documentation

Interactive documentation is available once the containers are running:

- **RapiDoc** (recommended): `http://localhost:8000/api-docs`
- **Swagger UI**: `http://localhost:8000/api/documentation`

### How to authenticate in the docs

1. Open the **Login** endpoint and execute it with a seeded user (for example
   `finance@example.com` / `password`).
2. Copy the `token` from the response.
3. Use the **Authentication** option in the UI and paste the token.
4. All protected endpoints are now available.

---

## API Endpoints

Base URL: `http://localhost:8000/api`

All endpoints return JSON. Protected endpoints require an
`Authorization: Bearer <token>` header.

### Authentication

| Method | Endpoint     | Auth | Description                          |
| ------ | ------------ | ---- | ------------------------------------ |
| POST   | `/login`     | No   | Authenticate and receive a token     |
| POST   | `/register`  | No   | Register a new user and get a token  |
| POST   | `/logout`    | Yes  | Revoke the current token             |
| GET    | `/me`        | Yes  | Get the authenticated user's profile |

### Payment Requests

| Method | Endpoint                              | Auth            | Description                                   |
| ------ | ------------------------------------- | --------------- | --------------------------------------------- |
| GET    | `/payment-requests`                   | Yes             | List requests (own for employees, all for finance) |
| POST   | `/payment-requests`                   | Yes             | Create a request (exchange rate fetched automatically) |
| GET    | `/payment-requests/{id}`              | Yes (owner/finance) | View a single request                     |
| PATCH  | `/payment-requests/{id}/approve`      | Yes (finance)   | Approve a pending request                     |
| PATCH  | `/payment-requests/{id}/reject`       | Yes (finance)   | Reject a pending request                      |

### Example: create a payment request

Request:

```http
POST /api/payment-requests
Authorization: Bearer <token>
Content-Type: application/json

{
  "amount": 500.00,
  "currency": "BRL",
  "description": "Office supplies"
}
```

Response (`201 Created`):

```json
{
  "data": {
    "id": 1,
    "amount": "500.00",
    "currency": "BRL",
    "exchange_rate": "5.98000000",
    "amount_in_eur": "83.61",
    "rate_source": "exchangerate-api.com",
    "rate_fetched_at": "2026-06-11T13:00:00.000000Z",
    "status": "pending",
    "description": "Office supplies",
    "reviewed_at": null,
    "user": { "id": 1, "name": "Ana Costa", "email": "ana@example.com" },
    "created_at": "2026-06-11T13:00:00.000000Z"
  }
}
```

### Error responses

The API returns consistent JSON errors with meaningful messages:

| Status | When it happens                                        |
| ------ | ------------------------------------------------------ |
| 401    | Missing or invalid token                               |
| 403    | Authenticated but not allowed (wrong role or not owner)|
| 404    | Resource not found                                     |
| 409    | Trying to approve/reject a request that is not pending |
| 422    | Validation failed (invalid input)                      |

Example (`409 Conflict`):

```json
{
  "message": "This payment request cannot be approved or rejected because it is not pending."
}
```

---

## Running Tests

The suite uses an in-memory SQLite database and does not touch the MySQL data.

```bash
docker compose exec app php artisan test
```

Tests are split into:

- **Unit** (`tests/Unit`): actions and the exchange rate service in isolation
  (the external API is faked, so no network calls are made).
- **Feature** (`tests/Feature`): full HTTP flows including authentication and
  authorization.

---

## Scheduled Task (48h Expiration)

Payment requests that stay `pending` for more than 48 hours are automatically
marked as `expired`.

- The logic lives in `App\Actions\PaymentRequest\ExpireStalePaymentRequestsAction`.
- It is triggered by the `payment-requests:expire` command, scheduled to run
  hourly in `routes/console.php`.
- The `scheduler` container runs `php artisan schedule:work`, so this happens
  automatically — no host cron setup needed.

To run it manually:

```bash
docker compose exec app php artisan payment-requests:expire
```

---

## Architecture

The project follows a layered, single-responsibility approach.

```
app/
├── Actions/              Business operations (one class per action, handle() method)
│   ├── Auth/
│   └── PaymentRequest/
├── DTO/                  Typed input objects (e.g. payment creation)
├── Enums/                PaymentStatus, Role
├── Exceptions/           Domain exceptions (exchange rate, payment state)
├── Http/
│   ├── Controllers/      Thin controllers — orchestration only
│   ├── Middleware/        Role check, force-JSON
│   ├── Requests/          Form Request validation
│   └── Resources/         API response shaping
├── Models/               Eloquent models
├── Policies/             Resource authorization (permission)
└── Services/             External integrations (exchange rate API)
```

Key decisions:

- **Controllers only orchestrate.** They validate input (via Form Requests),
  call an action, and return a resource. No business logic.
- **Actions hold business logic.** Every operation is a single-action class with
  a `handle()` method, receiving clean data (DTO / model / user) — never the
  raw request.
- **Services handle external integrations.** The exchange rate API lives behind
  `ExchangeRateService`, which makes it easy to cache and to mock in tests.
- **Layered authorization (defense in depth):**
  - Sanctum protects all non-public routes.
  - The `role` middleware blocks non-finance users at the route level.
  - Policies decide resource-level access (owner vs. finance).
  - Business state rules (e.g. only pending requests can be reviewed) live in
    the actions.
- **Exchange rate immutability.** The rate is captured at creation and the
  approval/rejection flow only touches the status and reviewer fields.

---

## Useful Commands

```bash
# Start / stop the environment
docker compose up -d
docker compose down

# Open a shell in the app container
docker compose exec app bash

# Run artisan commands
docker compose exec app php artisan <command>

# Reset the database with fresh seed data
docker compose exec app php artisan migrate:fresh --seed

# Regenerate the API documentation
docker compose exec app php artisan l5-swagger:generate

# Run the test suite
docker compose exec app php artisan test
```
