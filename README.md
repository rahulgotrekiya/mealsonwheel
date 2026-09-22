# Meals on Wheels

A pet food marketplace built with Laravel. Customers shop, merchants supply, and the platform holds
the stock and handles delivery.

---

## What it does

**Customers** browse a catalog, search and filter it, build a basket as a guest or signed in, check
out, follow their orders and download invoices.

**Merchants** are suppliers, not shippers. They apply to join, list products for review, keep their
own stock levels current, and see what they have sold and earned — never who bought it.

**Admins** run the store: the catalog, the review queue for merchant listings, the order lifecycle,
merchant applications, accounts, and reporting.

## Stack

- PHP 8.4, Laravel 13
- MariaDB 11 in development and production; the test suite runs on in-memory SQLite
- Bootstrap 5, served as static assets — no frontend build step
- Nix flake for a pinned development environment, Docker for a release image

## Running it

Either works. The Nix flake pins PHP, Composer and Node to the versions CI uses.

**On your machine**

```sh
nix develop                  # optional; a PHP 8.4 on PATH works too
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve            # http://localhost:8000
```

**In containers**

```sh
docker compose up -d         # http://localhost:8000, mail at http://localhost:8025
docker compose exec app php artisan migrate --seed
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
php artisan test             # 278 tests
./vendor/bin/pint            # formatting; --test to check without writing
```

CI runs both on every push, once against SQLite and once against MariaDB, then builds the Docker
image and smoke-tests the running container.

The MariaDB job is not redundant. `SELECT … FOR UPDATE` is a no-op under SQLite, so the row locking
that protects checkout from overselling is only genuinely exercised there.

---

## Decisions worth explaining

### Merchants supply; the platform fulfils

A merchant sends stock to the warehouse and is paid for what sells. They never handle delivery, so
an order carries a **single status that only an admin advances** — no sub-orders, no per-line status,
and nothing identifying a customer ever reaches a supplier's screen.

This keeps a multi-supplier basket simple: one order, one status, one delivery, with each merchant
seeing only their own lines and their own share of the value.

### Order lines are a historical record

`order_items` stores the `unit_price`, `seller_id` and `commission_rate` that applied **at the moment
of sale**, and nothing recomputes them afterwards. Editing a product's price, changing its supplier,
or altering the commission rate cannot rewrite what an order was worth or what a merchant earned.

Without this, every historical total would drift whenever the catalog was edited. It is the single
most important decision in the schema, and `OrderTotalsTest` exists to hold it.

### Checkout is atomic and takes a lock

Placing an order creates the order, its lines, decrements stock and empties the basket inside one
transaction. Products are re-read under `lockForUpdate` inside that transaction rather than trusted
from the basket, so two customers racing for the last unit cannot both win.

### Rich text is cleaned on the way in

Product descriptions are written in an editor and rendered as HTML, which makes them the one place
where somebody else's markup reaches a customer's browser — and merchants sign themselves up. The
text is sanitised by a model mutator, so every write is covered: forms, seeders, console commands,
anything added later.

### Two panels, one layout

Admin and merchant share `layouts/panel`. Only `App\Support\PanelMenu` differs between them, so
adding a screen means adding a menu entry, not duplicating markup.

### Figures come from one place

`App\Support\SalesReport` computes every money figure, optionally narrowed to one supplier. The
admin dashboard, the merchant dashboard and the earnings report are the same class asked different
questions, so the store and its suppliers cannot end up reading different books. A test reconciles
the admin's grouped query against each merchant's own report.

## Where things live

| Concern | Code |
|---|---|
| Basket, for guests and signed-in customers alike | `app/Support/Cart.php` |
| Turning a basket into an order | `app/Actions/PlaceOrder.php` |
| Creating and updating products, including uploads | `app/Actions/SaveProduct.php` |
| Sales figures, whole-store or per supplier | `app/Support/SalesReport.php` |
| Sanitising editor output | `app/Support/RichText.php` |
| Staff sidebar, per role | `app/Support/PanelMenu.php` |
| Roles, statuses and order states | `app/Enums/` |
| Invoices and reports | `resources/views/pdf/` |

## Conventions

- Routes are named and referenced with `route()`, never a hand-written path.
- Authorization lives in policies, not in controller conditionals.
- Money is `decimal(10,2)` everywhere. Never a float column.
- Uploads are validated for type and size, and the framework names every stored file — a
  client-supplied filename is never trusted.
- Deliberate simplifications carry a `// NOTE:` comment naming the ceiling and what would lift it.
- One branch per stage of work, one pull request, merged only when CI is green.
