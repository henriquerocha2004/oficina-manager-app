# AGENTS.md - Developer Guide for Oficina Manager

This guide is for AI coding agents working in this Laravel + Vue.js (Inertia.js) codebase.

## Project Overview

- **Backend**: Laravel 12 (PHP 8.5+)
- **Frontend**: Vue 3 + Inertia.js + Tailwind CSS 4
- **Database**: PostgreSQL (multi-tenant architecture)
- **Testing**: PHPUnit (backend), Vitest (frontend)
- **Build Tool**: Vite

## Build, Lint & Test Commands

### Backend (PHP)

```bash
# Run all tests
docker exec oficina-manager-app composer test
# or
docker exec oficina-manager-app php artisan test

# Run specific test file
docker exec oficina-manager-app php artisan test tests/Unit/Actions/CreateClientActionTest.php

# Run specific test method
docker exec oficina-manager-app php artisan test --filter=testCreatesClientWhenNotExists

# Run tests by suite
docker exec oficina-manager-app php artisan test --testsuite=Unit
docker exec oficina-manager-app php artisan test --testsuite=Feature

# Start development server
docker exec oficina-manager-app composer dev  # Starts server, queue, logs, and vite concurrently
# or individually:
docker exec oficina-manager-app php artisan serve
docker exec oficina-manager-app php artisan queue:listen --tries=1
docker exec oficina-manager-app php artisan pail --timeout=0
```

### Frontend (JavaScript/Vue)

```bash
# Development
docker exec oficina-manager-app npm run dev

# Build for production
docker exec oficina-manager-app npm run build

# Run tests
docker exec oficina-manager-app npm test
# or
docker exec oficina-manager-app npx vitest

# Run specific test file
docker exec oficina-manager-app npx vitest resources/js/tests/SomeTest.spec.js
```

### Database

```bash
# Run migrations
docker exec oficina-manager-app php artisan migrate

# Rollback migrations
docker exec oficina-manager-app php artisan migrate:rollback

# Fresh database with seeders
docker exec oficina-manager-app php artisan migrate:fresh --seed
```

## Code Style Guidelines

### PHP Backend

#### General Rules (PSR-12)

- Follow PSR-12 coding standard
- All class, method, and variable names in English with clear meaning
- Use explicit typing for methods and properties
- Indentation: 4 spaces (no tabs)
- Braces for classes and methods always on new line
- Write self-explanatory code with clear naming and best practices

#### Docblocks

- **Only add docblocks to document exceptions** (types are already explicit)
- Do not comment every line of code
- Use comments only for truly complex logic that needs extra explanation

Example:
```php
/**
 * Throws ClientAlreadyExistsException if business rule fails.
 */
public function __invoke(ClientDto $clientDto): Client
{
    // ...
}
```

#### Imports & Namespaces

- Use PSR-4 autoloading: `App\`, `Database\Factories\`, `Database\Seeders\`
- Group imports logically: exceptions first, then framework classes, then application classes
- Use fully qualified class names in imports (no aliases unless necessary)
- Controller namespaces: `App\Http\Controllers\tenant\` or `App\Http\Controllers\admin\`
- Use namespaces according to project folder structure (including in tests)

Example:
```php
<?php

namespace App\Actions\Tenant\Client;

use Exception;
use Throwable;
use App\Dto\ClientDto;
use App\Exceptions\Client\ClientAlreadyExistsException;
use App\Models\Tenant\Client;
use Illuminate\Support\Facades\Log;
```

#### Naming Conventions

- **Controllers**: Singular + `Controller` suffix → `ClientController`, `VehicleController`
- **Actions**: Verb + Subject + `Action` suffix → `CreateClientAction`, `UpdateVehicleAction`
- **Models**: Singular → `Client`, `Vehicle`, `Service`, `User`
- **DTOs**: Singular + `Dto` suffix → `ClientDto`, `VehicleDto`
- **Requests**: Context + Subject + `Request` → `tenant\ClientRequest`
- **Exceptions**: Descriptive + `Exception` → `ClientAlreadyExistsException`, `ClientNotFoundException`
- **Methods**: camelCase → `findOne()`, `toDto()`, `setResponse()`
- **Variables**: camelCase → `$clientDto`, `$searchDto`
- **Test methods**: camelCase (REQUIRED) → `testCreatesClientWhenNotExists()`, `testReturnsValidationError()`, `testDeletesSupplierWhenFound()`

#### Type Hints & Documentation

- Always use strict types for properties and return types
- Use PHPDoc blocks for models with `@property` annotations
- Use named parameters in constructors and method calls where it improves readability
- DTOs use promoted properties with typed constructor parameters

Example:
```php
public function __construct(
    public string $name,
    public string $email,
    public ?string $city = null,
) {}
```

#### Separation of Responsibilities

- Use Actions for simple CRUD operations
- Use Domain Models for complex business tasks
- Use Services for orchestration with constructor dependency injection
- Use Dtos for data transfer between layers
- Use Traits where appropriate

**Action Example (simple CRUD):**
```php
class CreateUserAction
{
    public function __invoke(UserDto $dto): User
    {
        // ...
    }
}
```

**Domain Model Example (complex task):**
```php
class SubscriptionDomain
{
    public function activate(Subscription $subscription): void
    {
        // ...
    }
}
```

**Service Example:**
```php
class UserService
{
    public function __construct(private UserRepository $repo) {}
}
```

#### Dependency Injection

- **Services**: Use constructor dependency injection
- **Actions**: Inject in the method that calls them (NOT in constructor)

Example:
```php
// Controller method
public function store(StoreUserRequest $request, CreateUserAction $action)
{
    $user = $action($request->toDto());
}
```

#### Global Functions

- **Ask before creating global functions** - avoid when possible

#### Error Handling

- Wrap controller actions in try-catch blocks
- Log errors with context: message, line, file
- Use custom exceptions extending `HttpException` for domain errors
- Return JSON responses with consistent structure: `{ message, data }`
- Use HTTP status codes from `Symfony\Component\HttpFoundation\Response`

Example:
```php
try {
    $client = $createClientAction(clientDto: $request->toDto());
    return $this->setResponse(
        message: Messages::CLIENT_CREATED_SUCCESS,
        code: Response::HTTP_CREATED,
        data: ['client' => $client],
    );
} catch (Exception $exception) {
    Log::error(Messages::ERROR_CREATING_CLIENT, [
        'error' => $exception->getMessage(),
        'line' => $exception->getLine(),
        'file' => $exception->getFile(),
    ]);
    return $this->setResponse(
        message: Messages::ERROR_CREATING_CLIENT,
        code: Response::HTTP_INTERNAL_SERVER_ERROR,
    );
}
```

#### Action Pattern

- Each business logic operation is an Action class
- Actions are invokable with `__invoke()` method
- Actions receive DTOs as parameters, not raw arrays
- Use `throw_if()` for validation within actions

Example:
```php
public function __invoke(ClientDto $clientDto): Client
{
    $client = Client::query()
        ->whereDocumentNumber($clientDto->document_number)
        ->first();

    throw_if(!is_null($client), ClientAlreadyExistsException::class);

    return Client::query()->create($clientDto->toArray());
}
```

#### Database Queries

- **Simple queries (single table, Action class)**: Use `QueryBuilderTrait`
- **Complex queries (joins, etc.)**: Use Models + QueryBuilder

**Simple query example:**
```php
class ListUsersAction
{
    use QueryBuilderTrait;

    public function __invoke(): Collection
    {
        return $this->query(User::query())->get();
    }
}
```

**Complex query example:**
```php
$users = User::query()
    ->join('profiles', 'users.id', '=', 'profiles.user_id')
    ->where('profiles.active', true)
    ->get();
```

#### Controllers and Validation

- **Always use FormRequest for validation** - never validate in controller
- Follow existing folder organization pattern (Actions, Dto, Services, etc.)

Example:
```php
public function store(StoreUserRequest $request)
{
    // $request already validated
}
```

#### Models

- Use Eloquent models with ULID primary keys (`HasUlids` trait)
- Enable soft deletes where appropriate (`SoftDeletes` trait)
- Set `$incrementing = false` and `$keyType = 'string'` for ULIDs
- Define relationships with proper type hints
- Use comprehensive PHPDoc blocks (IDE Helper generates these)

#### Best Practices (Backend)

- Use custom exceptions for business rules
- Always use migrations for database changes
- Prefer Requests for controller data validation
- Use Dtos for data transfer between layers
- Adopt unit and feature tests for new functionality
- Follow **SOLID principles** in code design
- Follow **FIRST principles** in automated tests
- Follow **Object Calisthenics** principles
- Use try/catch in controller methods to handle exceptions and return appropriate HTTP responses
- **Test method names MUST be in camelCase** (NOT snake_case) → `testCreatesClientWhenNotExists()`, `testReturnsValidationError()`
- Use AAA pattern (Arrange, Act, Assert) when writing tests

### Frontend (Vue.js)

#### General Rules

- Follow Vue.js 3 Composition API with `<script setup>`
- All component, variable, and function names in English with clear meaning
- Use TypeScript-like typing in JSDoc comments when necessary
- Indentation: 4 spaces (no tabs)
- Use arrow functions, destructuring, and template literals
- Braces always on new line for functions and blocks

#### Docblocks

- Add docblocks for exported functions and complex methods
- Include JSDoc comments for function signatures with parameters and return types

Example:
```javascript
/**
 * Fetches clients from the API.
 * @param {Object} params - Query parameters.
 * @returns {Promise<Object>} Paginated clients data.
 */
export async function fetchClients(params) {
    // ...
}
```

#### File Structure

- **Pages**: `resources/js/Pages/{Tenant|Admin}/{Feature}/Index.vue`
- **Components**: `resources/js/Components/` (global) or `resources/js/Shared/Components/`
- **Composables**: `resources/js/Composables/use*.js`
- **Services**: `resources/js/services/*Service.js`
- **Layouts**: `resources/js/Layouts/`
- **Tests**: `resources/js/tests/unit/` or `resources/js/tests/`

#### Imports

- Use path aliases: `@/` for `resources/js/`, `@assets/` for `resources/assets/`
- Import composables: `import { useToast } from '@/Shared/composables/useToast.js'`
- Import services with explicit `.js` extensions

#### Component Style

- Use `<script setup>` composition API
- Destructure reactive refs: `const { unmask } = useMasks()`
- Define reactive state with `ref()`, not `reactive()`
- Use `onMounted()` for initialization
- Keep template expressions simple
- Template clean, script with logic, style scoped
- Use props for input, emits for output

Example:
```vue
<template>
    <div>{{ message }}</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { fetchClients, createClient } from '@/services/clientService';
import { useToast } from '@/Shared/composables/useToast.js';

const props = defineProps({ message: String });
const toast = useToast();
const items = ref([]);
const page = ref(1);

const load = async () => {
  const res = await fetchClients({ page: page.value });
  items.value = res.items;
};

onMounted(load);
</script>
```

#### Separation of Responsibilities

- Use Composables for reusable reactive logic
- Use Services for API integration
- Use Components for UI elements

**Composable Example:**
```javascript
export function useToast() {
    // ...
}
```

**Service Example:**
```javascript
export async function createClient(data) {
    // ...
}
```

#### State and Reactivity

- Use `ref()` for reactive state
- Avoid direct DOM manipulation; use Vue reactivity

Example:
```javascript
const items = ref([]);
```

#### Services

- Export async functions for API calls
- Use axios for HTTP requests
- Return consistent response format: `{ success, data?, error? }`
- Always use try-catch, returning `{ success, data/error }`
- Include JSDoc comments for function signatures

Example:
```javascript
/**
 * @param {number|string} id
 * @param {Object} clientData
 * @returns {Promise<{success: boolean, data?: Object, error?: any}>}
 */
export async function updateClient(id, clientData) {
    try {
        const { data } = await axios.put(`/clients/${id}`, clientData);
        return { success: true, data };
    } catch (error) {
        return { success: false, error };
    }
}
```

#### Error Handling and API

- Always use try-catch in services
- Show toasts for user feedback (success/error)
- Centralize API calls in services

#### Validation

- Use Vee-Validate + Yup in forms

#### Best Practices (Frontend)

- Avoid unnecessary if-else: use early returns
- Centralize API in services
- Write tests for critical logic
- Use Tailwind + KTUI for styles
- Add ARIA attributes for accessibility
- Focus on component behavior, not implementation details in tests
- Mock services/composables in tests
- Use AAA pattern (Arrange, Act, Assert) in tests
- Avoid global functions; encapsulate in composables
- Use composables for local cache if needed
- Follow examples already present in the project

## Testing Guidelines

### PHP Tests

- Unit tests: Test individual classes (Actions, Models, DTOs) in isolation
- Feature tests: Test HTTP endpoints and integration
- **Test method names MUST be in camelCase** (NOT snake_case): `testCreatesClientWhenNotExists()`, `testReturnsValidationError()`
- Assert database state with `assertDatabaseHas()`
- Test exception cases with `expectException()`
- Follow AAA pattern (Arrange, Act, Assert)

### Vue/JavaScript Tests

- Configure Vitest in `vite.config.js` with jsdom environment
- Test component behavior, not implementation details
- Mock API calls and external dependencies

## Common Patterns

### Request → DTO → Action → Response Flow

1. Request validates input: `ClientRequest::rules()`
2. Request converts to DTO: `$request->toDto()`
3. Controller calls Action with DTO: `$createClientAction(clientDto: $request->toDto())`
4. Action performs business logic, returns model
5. Controller returns JSON response via `setResponse()`

### Inertia.js Pages

- Controllers return Inertia pages: `Inertia::render("Tenant/Clients/Index")`
- Data passed as props to Vue components
- Forms submit to API endpoints, not Inertia routes

## Important Notes

- This is a **multi-tenant application** with separate `tenant_*` and `manager_*` database connections
- ULIDs are used instead of auto-increment IDs
- Brazilian validation rules are available (e.g., `cpf_ou_cnpj`)
- Always unmask form data (CPF, phone, zip code) before sending to backend
- Use consistent error messages from `App\Constants\Messages`
