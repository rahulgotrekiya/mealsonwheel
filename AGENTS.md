# Meals on Wheels

A pet food marketplace built with Laravel. Three kinds of account share one application:
customers shop, merchants supply, and admins run the store.

## Stack

- PHP 8.4, Laravel 13
- MariaDB 11 in development and production; the test suite runs on in-memory SQLite
- Bootstrap 5 for both the storefront and the staff panel; no frontend build step — the CSS and
  JavaScript are served from `public/assets` as static files

## Running it

The development environment is pinned with Nix, so the PHP version, extensions, Composer and Node
all match what CI uses:

```sh
nix develop            # optional; a matching PHP 8.4 on PATH works just as well
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Seeded accounts, all with the password `password`:

| Account | Role |
|---|---|
| `admin@mealsonwheels.test` | admin |
| `customer@mealsonwheels.test` | customer, with an address and order history |
| `pawsome@` `whiskers@` `featherfur@` | approved merchants |
| `newsupplier@` | merchant awaiting approval |

## Checks

```sh
php artisan test        # the full suite
./vendor/bin/pint       # formatting; --test to check without writing
```

Both run in CI on every push, once against SQLite and once against MariaDB. The second job is not
redundant: `SELECT … FOR UPDATE` is a no-op under SQLite, so the row locking that protects checkout
from overselling is only genuinely exercised there.

## How the domain works

**Merchants are suppliers, not shippers.** They list products and send stock to the platform's
warehouse; the platform owns fulfilment. An order therefore carries a single status that only an
admin advances — there are no sub-orders and no per-line status.

**Order lines are a historical record.** `order_items` stores `unit_price`, `seller_id` and
`commission_rate` as they were at the moment of sale, and nothing reads the live product price when
reporting on a past order. Editing a product must never change what an order was worth or what a
merchant earned from it; `OrderTotalsTest` holds that guarantee.

**Products are reviewed before they reach the storefront.** Merchant listings start `pending`; only
`approved` products appear in the catalog, and an unapproved one 404s even if its address is
guessed.

## Where things live

| Concern | Code |
|---|---|
| Basket, for guests and signed-in customers alike | `app/Support/Cart.php` |
| Turning a basket into an order | `app/Actions/PlaceOrder.php` |
| Creating and updating products, including uploads | `app/Actions/SaveProduct.php` |
| Sales figures, whole-store or scoped to one merchant | `app/Support/SalesReport.php` |
| Staff sidebar, per role | `app/Support/PanelMenu.php` |
| Roles, statuses and order states | `app/Enums/` |

Storefront views extend `layouts/shop`; the admin and merchant panels share `layouts/panel`.

## Conventions

- Routes are named and referenced with `route()`, never a hand-written path.
- Authorization lives in policies (`app/Policies`), not in controller conditionals.
- Money is `decimal(10,2)` everywhere. Never a float column.
- Uploads are validated for type and size, and the framework names every stored file — a
  client-supplied filename is never trusted.
- Deliberate simplifications carry a `// NOTE:` comment naming the ceiling and what would lift it.
- One branch per stage of work, one pull request, merged only when CI is green.
