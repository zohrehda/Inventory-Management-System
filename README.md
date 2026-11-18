## Setup

### 2. Install dependencies
```bash
composer install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update your database credentials in the `.env` file.

### 4. Run migrations and seeders

```bash
php artisan migrate --seed
```

### 5. Start the application

```bash
php artisan serve
```

The application will be available at:

`http://127.0.0.1:8000`



## Architecture

### Multi-tenancy

A lightweight, **single-database, shared-schema** multi-tenancy module for Laravel. Every tenant's data lives in the same tables, and rows are separated by a `tenant_id` column.

#### How it works

1. **Identify:** The `tenant` middleware reads the `X-Tenant` header (configurable via `TENANCY_HEADER`).
2. **Resolve:** `TenantResolver` looks up the tenant by its `subdomain` value. If the header is missing or no tenant matches, it throws `TenantCouldNotBeIdentifiedException`.
3. **Initialize:** The `Tenancy` singleton stores the current tenant for the request.
4. **Isolate:** Models that use the `BelongsToTenant` trait are automatically filtered by `TenantScope` (`WHERE tenant_id = ?`), and `tenant_id` is filled in automatically when records are created.
5. **Clean up:** In `terminate()`, the middleware calls `Tenancy::end()` so the tenant can't leak between requests in long-lived workers such as Octane.

#### Components

| File | Responsibility |
|---|---|
| `Tenancy.php` | Holds the current tenant. Provides `initialize()`, `end()`, `initialized()`, `tenant()`, `id()` and `run()`. |
| `TenantResolver.php` | Finds the tenant from the request. |
| `Middleware/InitializeTenancy.php` | Starts and ends tenancy per request (alias: `tenant`). |
| `Concerns/BelongsToTenant.php` | Trait for tenant-owned models: adds the global scope, auto-assigns `tenant_id` and provides a `tenant()` relation. |
| `Scopes/TenantScope.php` | Global scope that adds the `tenant_id` filter to queries. |
| `Models/Tenant.php` | The tenant model (`name`, `subdomain`). |
| `TenancyServiceProvider.php` | Registers the singletons, config, migrations, translations and the middleware alias. |
| `Exceptions/` and `lang/` | Error type and messages for tenant identification failures. |
| `helpers.php` | `tenancy()` and `tenant()` helper functions. |
| `config/tenancy.php` | Header name and tenant model class. |


### Concurrency Test

A feature test that checks the stock system never oversells under heavy parallel load.

#### Scenario

1. Create a tenant, a product and a warehouse.
2. Record an initial **IN** movement of **50 units**.
3. Run **100 parallel OUT movements of 1 unit each**. Each runs in its own process via `Concurrency::run()`, re-initialises the tenant and calls `StockMovementService::record()`.
4. Count how each task ended: `ok`, `insufficient` (an `InsufficientStockException`) or `error`.

#### Expected outcome

| Check | Expected |
|---|---|
| Successful OUT movements | 50 |
| Rejected with `InsufficientStockException` | 50 |
| Final `StockLevel.quantity` | 0 (never negative) |

#### What it proves

- **No race conditions:** the service must lock or atomically update the stock row, so two processes can't both consume the last unit.
- **Tenant context works across processes:** each worker resolves the tenant via `tenancy()->run()`.
- **Stock never goes below zero.**

#### Requirements

- A DB engine with real row locking (MySQL).
