# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

> For comprehensive code style, naming conventions, and testing guidelines, see **AGENTS.md**.

## Commands

All commands run inside the Docker container:

```bash
# Development
docker exec oficina-manager-app composer dev     # Starts server + queue + logs + vite concurrently

# Testing (PHP)
docker exec oficina-manager-app php artisan test
docker exec oficina-manager-app php artisan test tests/Unit/Actions/CreateClientActionTest.php
docker exec oficina-manager-app php artisan test --filter=testCreatesClientWhenNotExists
docker exec oficina-manager-app php artisan test --testsuite=Unit

# Testing (JS)
docker exec oficina-manager-app npm test
docker exec oficina-manager-app npx vitest resources/js/tests/SomeTest.spec.js

# Database
docker exec oficina-manager-app php artisan migrate
docker exec oficina-manager-app php artisan migrate:fresh --seed
```

Makefile shortcuts (runs from host):
```bash
make start           # Start Docker containers, install deps, run all migrations
make migrate-all     # Migrate all databases (manager + tenant + test variants)
make test-db-refresh # Reset and recreate test databases
make test            # Run PHP tests
```

## Architecture

### Multi-Tenant: Database-per-Tenant

The app maintains two separate PostgreSQL database groups:

- **Manager DB** (`oficina_manager`): Admin users, tenants, subscriptions
- **Tenant DB** (one per tenant, e.g. `dev_tenant`): All business data (clients, vehicles, services, products, service orders)

The `IdentifyTenant` middleware extracts the subdomain and switches the active database connection at runtime. Four named connections exist in `config/database.php`: `manager`, `tenant`, `manager_test`, `tenant_test`.

### Request Flow

```
FormRequest (validates) → toDto() → Controller → Action(__invoke(DTO)) → Model → setResponse()
```

- **FormRequest**: validates input, has `toDto()` method
- **DTO** (`app/Dto/`): typed value objects with promoted constructor properties
- **Action** (`app/Actions/Tenant/`): invokable class, single responsibility, receives DTOs
- **Domain** (`app/Domain/Tenant/`): rich domain models for complex business rules (e.g., ServiceOrder state machine)
- **Service** (`app/Services/`): orchestrates multiple actions/repositories via constructor DI
- **Controller**: injects Actions as method parameters (not constructor), wraps in try-catch, returns via `setResponse()`

### Key Conventions

- **Primary keys**: ULIDs (`HasUlids` trait), not auto-increment integers
- **Error messages**: defined in `App\Constants\Messages`, used in logs and responses
- **Custom exceptions**: extend `HttpException`, organized under `app/Exceptions/{Domain}/`
- **Enums**: `app/Enum/Tenant/` — use for status, types, event kinds
- **QueryBuilderTrait**: reusable search/sort/filter query logic for simple CRUD list actions
- **No `else` blocks**: use early returns and guard clauses
- **Test method names**: camelCase with `test` prefix — `testCreatesClientWhenNotExists()`

### Frontend

- **Inertia.js** bridges Laravel controllers and Vue 3 components — controllers return `Inertia::render(...)`, not JSON
- **Pages**: `resources/js/Pages/{Tenant|Admin}/{Feature}/Index.vue`
- **Services** (`resources/js/services/`): async functions using axios, return `{ success, data?, error? }`
- **Composables** (`resources/js/Composables/use*.js`): reactive logic shared across components
- **Validation**: Vee-Validate + Yup schemas in forms
- **Unmasking**: always unmask CPF, phone, zip code before sending to backend

### Route Organization

```
routes/
├── web.php                   # Registers admin + tenant route groups
├── admin/{admin,auth}.php    # Admin routes
└── tenant/{feature}.php      # One file per feature (client, vehicle, service_order, etc.)
```
