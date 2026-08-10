# AGENT.md — Project Guide for Coding Agents

## Project snapshot

**Repository:** `Tren` (monorepo)  
**Goal:** workout/training platform with a Rust API and Laravel web client.

Top level:

- `tren_api/` — Rust API (Axum + SQLx + PostgreSQL)
- `tren_webclient/example-app/` — Laravel 13 + Livewire 4 app
- `sql/` — schema/bootstrap SQL
- `docker/` — local PostgreSQL setup

## Stack

### Rust API (`tren_api`)

- Rust 2021, Axum 0.7, Tokio
- SQLx (`postgres`, `chrono`, `uuid`, `runtime-tokio-rustls`)
- Serde/serde_json, anyhow (+ thiserror), tracing, dotenv, rand

### Web client (`tren_webclient/example-app`)

- Laravel 13, Fortify, Livewire 4, Flux UI
- Tailwind 4 + Vite
- Pest/PHPUnit

### Data/infra

- PostgreSQL 16
- Docker compose: `docker/docker-compose.yml`
- Schema seed script: `sql/Drop and create, initial.sql`

## Rust architecture (current)

Layered design:

1. controllers (`src/controllers/`)
2. services (`src/services/`)
3. repositories (`src/repositories/`)

DI/abstractions:

- Traits: `WorkoutService`, `WorkoutRepository`
- Concrete wiring in `src/main.rs`
- Shared with `Arc<dyn Trait + Send + Sync>`

Model split:

- Domain/API models: `src/models.rs`
- SQL row models: `src/rows.rs`
- Row -> nested domain assembly: `src/conversions.rs` (plus repository assembly helpers)

Flow: **handler -> service trait -> repository trait -> SQLx queries**.

## API routing (Rust)

Defined in `src/main.rs` and `src/controllers/workouts_controller.rs`:

- `GET /`, `GET /health`
- `GET /workouts/user/:id`
- `POST /workouts`
- `GET /workouts/exercises/user/:id`
- `POST /workouts/user/:id` (insert workout session)
- additional `/workouts/*` utility endpoints (health/ping/random)

Errors are JSON with status plus `"error"` and `"details"`.

## Database model

From `sql/Drop and create, initial.sql`:

- `users`
- `exercise`, `general_exercises`, `user_exercises`
- `workout`, `workout_exercise`, `workout_set`
- `workout_session`, `session_exercise`, `session_set`, `set_log`

Ordering fields (`exercise_order`, `set_order`) have parent-scoped uniqueness constraints.

## Conventions and guardrails

- Keep existing structure; prefer surgical changes over broad refactors.
- Preserve controller/service/repository boundaries unless explicitly asked to redesign.
- Rust naming: `snake_case` modules/files, `PascalCase` types/traits.
- Return `Result<...>` and propagate errors with context (no silent fallbacks).
- When changing workout/session shapes, keep `models.rs`, schema/query code, conversion logic, and handler/service signatures in sync.
- Keep Rust API and Laravel concerns separated.
- Do not commit secrets; use `.env`.
- For Laravel-side edits, follow `tren_webclient/example-app/AGENTS.md`.

## Local setup and commands

PostgreSQL:

1. Copy `docker/.env.example` -> `docker/.env`
2. `docker compose -f docker/docker-compose.yml up -d`
3. Apply `sql/Drop and create, initial.sql` to DB `tren`

Rust API (`tren_api/`):

- `cargo build`
- `cargo check`
- `cargo test`
- `cargo run`

Requires root `.env` with `DATABASE_URL`.

Laravel app (`tren_webclient/example-app/`):

- `composer install && npm install`
- `composer run dev` (or `php artisan serve` + `npm run dev`)
- `php artisan test --compact`

## State awareness

The Rust API layering is in place but still evolving (temporary comments and some experimental endpoints exist). Favor incremental, correctness-first changes.
