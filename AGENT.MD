# AI Coding Guidelines (AGENT.md)

This is a multi-vendor e-commerce platform ("Cupo") built with Laravel 11.
Follow these rules strictly when writing or modifying code.

## 1. Tech Stack
- **Backend:** Laravel 11 (PHP 8.3+)
- **Database:** MySQL, Redis (cache & flash sale stock handling)
- **Search:** Laravel Scout + Meilisearch
- **Real-time Chat:** Laravel Echo + Soketi (self-hosted WebSocket server)
- **Payment:** VNPay & Momo Sandbox

## 2. Do Not Edit
- `vendor/`, `node_modules/`, `composer.lock`, `package-lock.json`
- `storage/` — runtime cache, compiled views, logs
- `bootstrap/cache/`
- `.env` — never commit; use `.env.example` as the template
- Do not create documentation files unless explicitly requested

## 3. Database & Architecture Rules
- **No EAV.** Store dynamic product attributes in a JSON column
  `attributes` on the `products` table. Do not create separate
  attribute/attribute_value tables.
- **Order Splitting.** The parent `orders` table represents the overall
  checkout/payment. Each `seller_orders` row belongs to a specific
  `seller_id` and tracks its own fulfillment `status`
  (pending → confirmed → shipping → completed → cancelled), independent
  from other sellers in the same order. `order_items` rows belong to a
  `seller_order_id` and store a snapshot of the purchased product
  (`product_name`, `product_image`, `price` at time of purchase) —
  never read live product data for historical orders.
- **Controller & Service Pattern.** Keep controllers skinny. Business
  logic (payment, wallet, order splitting, flash sale stock handling)
  belongs in `app/Services/`.
- **Checkout must be wrapped in a DB transaction.** A single checkout
  writes to `orders`, one or more `seller_orders`, their `order_items`,
  and deletes the related `cart_items`. Wrap all of this in
  `DB::transaction()` — a partial failure must not leave an `orders`
  row without its `seller_orders`.
- **Wallet & Reconciliation.** Only credit a seller's `seller_profiles.balance`
  when the corresponding `seller_orders.status` becomes `completed`.
  Every balance change must be logged in `seller_balance_logs`
  (amount, type, reference_id pointing to `seller_orders.id` or
  `withdrawals.id`).
- **Withdrawal holds.** When a seller creates a `withdrawals` request,
  validate `amount <= seller_profiles.balance` and treat the amount as
  held immediately (do not allow a second concurrent withdrawal request
  to double-spend the same balance).
## Withdrawal Limit (MVP scope)
- Withdrawals are NOT processed via real banking APIs. The `withdrawals`
  table only tracks the request lifecycle (pending → approved/rejected).
- Actual bank transfer happens manually, outside the system, performed
  by the Admin. Approving a withdrawal in the UI simply deducts
  `seller_profiles.balance` and logs `seller_balance_logs` — it does not
  trigger any real money movement.

## 4. Folder Structure

app/
Http/
Controllers/
Admin/
Seller/
Customer/
Requests/
Admin/
Seller/
Customer/
Services/
OrderSplittingService.php
WalletService.php
FlashSaleStockService.php
Models/


## 5. Flash Sale Stock Handling (critical path — read carefully)
- Use `Redis::decr("flash_sale_stock:{product_id}")` for atomic stock
  deduction. If the result is negative, roll back with `Redis::incr()`
  and return an out-of-stock error — never let stock go below zero.
- Do NOT write to MySQL synchronously inside the request. After a
  successful Redis deduction, dispatch a queued job
  (`SyncFlashSaleStockToDatabase`) to persist the change.
- If Redis is unreachable, fail safe: reject the flash sale purchase
  rather than falling back to an unlocked MySQL write.

## 6. Coding Standards
- Follow **PSR-12**. Format with Laravel Pint before every commit:
  `vendor/bin/pint --dirty`
- **Naming:**
  - Foreign keys: `foreignId('table_name_id')->constrained()`
  - Routes: kebab-case (e.g. `/seller/manage-products`)
  - FormRequests: `{Domain}/{Action}{Entity}Request`
    (e.g. `Admin/ApproveSellerRequest`)
- **Validation:** Always use a dedicated `FormRequest` class. Never call
  `$request->validate()` directly inside a controller.
- **Security & Performance:**
  - Always verify ownership (e.g. Seller A must not be able to edit
    Seller B's product).
  - Use eager loading (`with(['product', 'seller'])`) to avoid N+1
    queries.
  - Never hardcode payment gateway credentials — read them via
    `config('services.vnpay.key')`.
- **Testing:** Write tests for any service touching money or stock:
  `OrderSplittingService`, `WalletService`, `FlashSaleStockService`.
- **Migrations:** Every migration must implement a working `down()`
  method. Never edit a migration that has already run on a shared
  environment — create a new one instead.

## 7. Common Commands
| Task | Command |
|---|---|
| Run migrations | `php artisan migrate` |
| Seed database | `php artisan db:seed` |
| Start queue worker | `php artisan queue:work` |
| Check code style | `./vendor/bin/pint` |
| Run tests | `php artisan test` |

## 8. CI Workflow (`.github/workflows/ci.yml`)
Runs automatically on every push/PR to `main` and `develop`:
Pint style check → run migrations against a test MySQL service → `php artisan test`.
A PR should not be merged if this workflow is failing.

## 9. Validation Checklist (before marking any task complete)
1. `vendor/bin/pint --dirty` passes with no style violations
2. `php artisan test` — all related tests pass
3. No hardcoded credentials or secrets anywhere in the diff
4. Ownership checks are in place for any seller-scoped or admin-scoped action
5. New service classes touching stock or payment have at least one test
6. New migrations have a working `down()` method
7. No stray `dd()`, `dump()`, or `console.log` left in the code
## 10. Middleware & Access Control Rules

- **Separate "not authenticated" from "wrong role".** Never collapse
  both into a single `abort(403)`. An unauthenticated user must be
  redirected to `route('login')`; only an authenticated user with the
  wrong role/permission gets a 403.
- **Always check account status before role.** A user with
  `status = 'blocked'` must be denied regardless of their `role`, even
  if their session is still valid. Check status immediately after
  confirming authentication, before checking role/permission.
- **Middleware should accept multiple roles via variadic parameters**
  (`string ...$roles`), not a single `string $role` — routes shared
  across roles (e.g. a report page visible to both admin and seller)
  are common in this system.
- **Use strict comparison** (`in_array($x, $y, true)`, `===`) for role/
  status checks. Never rely on loose comparison for anything
  security-related.
- **Prefer `$request->user()` over `auth()->user()`** inside middleware
  — it resolves against the guard actually handling the request,
  which matters once multiple guards exist (e.g. separate admin guard).

## 11. General Code Rigor Checklist

When writing any Controller, Middleware, or Service method, verify:
1. Every foreign key's `on delete` behavior is deliberate — see
   "Foreign Key Delete Rules" below, don't default to cascade.
2. Every list of allowed values (roles, statuses, enums) is checked
   with strict comparison, not loose `==`.
3. Every "not found" / "not authorized" / "invalid input" case is
   handled as a *distinct* branch with the correct HTTP status
   (401 = not authenticated, 403 = authenticated but forbidden,
   404 = not found, 422 = validation failed) — never collapse
   different failure reasons into one generic error.
4. Every monetary or stock-related write is wrapped in a transaction
   or an atomic Redis operation — never a bare `->save()` with no
   consistency guarantee when multiple rows are involved.

## 12. Foreign Key Delete Rules

Ask before writing any `->constrained()`: *"If the parent row is
deleted, does this child row still have meaning on its own?"*

| Answer | Use |
|---|---|
| No — child is meaningless without the parent | `cascadeOnDelete()` |
| Yes — child is financial/historical data that must not silently disappear | `restrictOnDelete()` |
| Yes — child has its own value, the link is just optional/reference | `nullOnDelete()` (column must be `nullable()`) |

Examples already decided in this codebase (do not re-litigate unless
the business rule changes):
- `orders.user_id`, `seller_orders.seller_id` → `restrictOnDelete()`
  (financial history)
- `products.category_id` → `restrictOnDelete()` (forces re-categorization
  before deleting a category)
- `addresses.user_id`, `cart_items.cart_id`, `product_images.product_id`
  → `cascadeOnDelete()` (meaningless without parent)
- `order_items.product_id`, `order_items.product_variant_id`,
  `cart_items.product_variant_id` → `nullOnDelete()` (order_items keep
  a snapshot; cart_items should degrade gracefully, not vanish silently)
