# SIM-BUMDes AI Agent Handoff

## Mission

Continue the SIM-BUMDes implementation incrementally in `D:\Portofolio\bumdes`.
The application is a Laravel 11 modular monolith for BUMDes operations. Backend foundations are implemented first; frontend Blade + Livewire follows after the domain model and business rules are stable.

## Source Of Truth

Read these files before each checkpoint:

1. `docs/PRD.md` for product behavior and user flows.
2. `docs/architecture.md` for Laravel structure, auth, RBAC, services, Livewire, and Reverb decisions.
3. `docs/database-schema.dbml` for tables, columns, keys, and relationships.
4. `docs/api-spec.json` for public data shape and API naming.
5. `docs/progress.md` for completed checkpoints and the next checkpoint.

Do not invent a competing design. Preserve existing model names, table names, ID formats, relationship names, and local conventions. Check files that may have changed before editing them.

## Current Stack

- Laravel 11, PHP 8.3, MySQL through Laragon on Windows.
- Blade + Livewire 3, Tailwind CSS.
- Spatie Permission and Activitylog.
- Laravel Excel and Reverb are installed.
- Do not add Filament.
- Do not introduce Docker at this stage.
- Development `.env` uses database `bumdes` and database-backed sessions.

The current scheduler pattern uses Laravel 11 `withSchedule` in `bootstrap/app.php`, not a static `Schedule::command()` call in `routes/console.php`. The monthly iuran command is `iuran:generate-bulanan`; it accepts `--bulan=YYYY-MM` for deterministic tests and creates Rp50.000 records only for active BUMDes.

Referral automation uses `referral:expire-check` hourly and `referral:verify-check` daily. Expired active codes receive a replacement active code; pending referrals past 15 days become `cair` when the recipient has transaction activity, which also creates a Rp10.000 incoming referral mutation in the recipient cash ledger, otherwise they become `gagal`.

Authentication uses `AuthController` with `username` + `password`, `EnsureAccountActive` after `auth`, and `RateLimiter::for('login')` at five requests per minute per IP+username. The login page is `resources/views/auth/login.blade.php`; protected panel routes use `auth` and `account.active` middleware.

## Checkpoint Pattern

For one checkpoint only:

1. Read the five source-of-truth documents and the nearest existing models/seeders/tests.
2. State a local hypothesis and the cheapest check that can disconfirm it.
3. Add the smallest backend slice: migration, model, seeder, relationships, and focused feature test.
4. Use explicit `use ...;` imports for every application class, seeder, model, relation, facade, and test type. Prefer aliases such as `BumdesModel` when a same-namespace model would otherwise be implicit or Pint reports an unused import.
5. Keep seeders deterministic and idempotent with `updateOrCreate`; use fixed dates/IDs for development fixtures.
6. Run normal migrations only. Never use `migrate:fresh`, `migrate:refresh`, `--force`, `db:wipe`, or destructive reset commands during this incremental workflow.
7. Run diagnostics, `php -l`, focused test, Laravel Pint, then the full test suite.
8. Seed development data with `php artisan db:seed --class=DatabaseSeeder` and inspect the result with Tinker.
9. Update `docs/progress.md` only after executable validation passes.

## PHP Rules

- Every model with a prefixed string key must set `$incrementing = false` and `$keyType = 'string'`.
- Match foreign-key lengths exactly with the referenced column. Existing `pelanggan.id_pelanggan` is 40 characters because its documented ID format exceeds 30.
- Check method names, return types, relation types, and argument counts against the target class.
- For `Akun`, authentication is username-based and the password column is `password_hash`.
- Use `Carbon` or fixed date objects for deterministic fixtures, not `now()` when IDs or assertions depend on dates.
- Do not silently change existing user-facing or database contracts to make a test pass.

## Validation Commands

Run from the project root:

```powershell
php artisan migrate
php artisan test
vendor\bin\pint --test <touched-files>
php -l <touched-file>
composer dump-autoload
php artisan db:seed --class=DatabaseSeeder
```

If `php` is missing from PATH, use the PHP executable selected by Laragon. If MySQL refuses `127.0.0.1:3306`, do not mark the checkpoint complete; report the environment blocker and retry after Laragon MySQL is started.

## Progress Update Format

Add a concise checkpoint entry to `docs/progress.md` containing:

- checkpoint number and date;
- migration, model, seeder, relationships, and behavior implemented;
- diagnostics, syntax, Pint, migration, and test results;
- whether migration ran without `--force`;
- a short Conventional Commit message;
- the next checkpoint.

Use commit messages such as:

```text
feat(database): tambah feedback dan status tindak lanjut
```

Do not create commits or push branches unless the user explicitly asks. The developer pushes manually.

## Backend Then Frontend

Finish domain migrations and focused backend behavior before building panel or portal UI. When frontend work begins, follow the architecture structure: custom Blade + Livewire, role-scoped routes, reusable search/filter/pagination patterns, clear loading states, responsive layouts, and Reverb only after channel authorization and notification contracts are defined.
