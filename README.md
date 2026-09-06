# SupplyKaro

B2B + B2C ecommerce and procurement platform for disposables, food-service
supplies, packaging, tissues and event consumables. India, INR, GST.

> **Everything you need to serve, pack & celebrate.**

One supplier for restaurants, cafes, cloud kitchens, caterers, hotels,
bakeries, offices, weddings, events and wholesale.

**Status:** Phase 1 complete — foundation, database schema and seed data.
Architecture: [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

---

## Requirements

| | Version | Note |
|---|---|---|
| PHP | 8.2+ | 8.5 works. `pdo_mysql`, `mbstring`, `bcmath` required |
| MySQL | 8.0+ | 8.4 LTS recommended |
| Composer | 2.x | Not needed for Phase 1 — the console runs dependency-free |
| Node | 20+ | Not needed until the Phase 3 frontend |
| Python | 3.11+ | Not needed until Phase 8 (AI service) |

Docker Compose pins all of these — see [Docker](#running-with-docker).

---

## Quick start

```bash
cp .env.example .env
php bin/console key:generate
```

Set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` in
`.env`, then:

```bash
php bin/console db:create
php bin/console migrate
php bin/console db:seed
php bin/console db:verify
```

`db:verify` should report **10 passing checks**. If it does, the schema and
seed data are sound.

### Local MySQL on macOS

If port 3306 is already taken by another MySQL, run a second instance on 3307:

```bash
brew install mysql@8.4
```

Then set a distinct port **and socket** in `/opt/homebrew/etc/my.cnf` — without
the socket change the second instance aborts on `/tmp/mysql.sock.lock`:

```ini
[mysqld]
port          = 3307
mysqlx_port   = 33070
socket        = /opt/homebrew/var/mysql/mysql-3307.sock
mysqlx_socket = /opt/homebrew/var/mysql/mysqlx-3307.sock

[client]
port   = 3307
socket = /opt/homebrew/var/mysql/mysql-3307.sock
```

```bash
brew services start mysql@8.4
mysql --protocol=TCP -h127.0.0.1 -P3307 -uroot -e "
  CREATE DATABASE supplykaro CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
  CREATE USER 'supplykaro'@'localhost' IDENTIFIED BY 'supplykaro_dev';
  GRANT ALL PRIVILEGES ON supplykaro.* TO 'supplykaro'@'localhost';"
```

---

## Running with Docker

Pins PHP 8.2, MySQL 8.4, Redis 7 and Python 3.12 regardless of the host.

```bash
docker compose up -d
docker compose exec php php bin/console migrate
docker compose exec php php bin/console db:seed
```

| Service | URL |
|---|---|
| App | http://localhost:8000 |
| Adminer | http://localhost:8080 |
| Mailhog | http://localhost:8025 |
| MySQL | `127.0.0.1:3307` |

The AI service is behind a profile and is not needed before Phase 8:
`docker compose --profile ai up -d`

---

## Console

```bash
php bin/console                       # list commands
php bin/console key:generate          # write a fresh APP_KEY into .env
php bin/console db:create             # create the database
php bin/console migrate               # run pending migrations
php bin/console migrate:status        # applied vs pending, plus drift warnings
php bin/console migrate:fresh --force --seed
php bin/console db:seed               # idempotent; safe to re-run
php bin/console db:seed --class=CatalogSeeder
php bin/console db:check              # tables, row counts, FK and index totals
php bin/console db:verify             # assert the seeded data is consistent
php bin/console db:wipe-demo          # delete every DEMO catalog row
php bin/console db:grants --host=localhost   # read-only GRANTs for the AI user
```

Migrations are **forward-only** and checksummed. Editing an applied migration
aborts the next `migrate` — write a new migration instead.

---

## Development accounts

Created by `AdminUserSeeder`, which **refuses to run when `APP_ENV=production`**.

| Email | Role | Group |
|---|---|---|
| `admin@supplykaro.test`        | Super Admin       | Retail |
| `ops@supplykaro.test`          | Order Manager     | Retail |
| `sales@supplykaro.test`        | Sales Manager     | Retail |
| `stock@supplykaro.test`        | Inventory Manager | Retail |
| `customer@supplykaro.test`     | Customer          | Retail |
| `cafe@supplykaro.test`         | Customer (approved business) | Business Standard |

Password for all: `SupplyKaro#Dev2026`

**Delete these before any deployment.**

---

## Demo catalog

The seeded catalog is **placeholder data**: 78 products, 125 variants, 279
sellable pack SKUs across every planned category, with realistic Indian-market
prices quoted **exclusive of GST**.

Every catalog row carries `is_demo_data = 1`:

```bash
php bin/console db:wipe-demo
```

Reference data (roles, tax rates, shipping zones, event rules, categories)
survives that, because it is not demo data — it is configuration.

To load your real catalog, replace the files in
`database/Seeders/data/catalog/` keeping the same array shape (documented in
that directory's `README.md`) and re-run `db:seed`. **No application code
changes.**

> ⚠️ **HSN codes and GST rates in `TaxRateSeeder` are placeholders.** GST
> classification is the seller's legal responsibility — have your CA confirm
> every code and rate before you invoice a real customer.

---

## Architecture in one screen

Three levels in the catalog, and inventory lives in the middle one:

```
products            marketing entity — no price, no stock
  └── product_variants    physical spec  — INVENTORY, in base units (pieces)
        └── variant_packs   the SKU      — pieces_per_pack + the price shown
```

Buying 2 sleeves of 50 deducts 100 pieces from one stock pool. Packs are
presentations, not stock pools — which makes "warehouse has 5,000 cups but the
1,000-carton is out of stock" structurally impossible.

**Prices are computed server-side, always.** `cart_items` stores a
`variant_pack_id` and a quantity and nothing else — there is deliberately no
price column. A tampered request can change what is in the cart, never what it
costs.

**The event calculator is data, not code.** `event_rules` rows hold
`qty_per_guest`, `safety_margin_pct`, `rounding_step` and `min_qty` per item
role, so an admin retunes them without a deploy:

```
qty = max( ceil_to_step( guests × qty_per_guest × (1 + margin/100), step ), min_qty )
```

**Python may read the catalog; it may never write anything.** The AI service
gets a MySQL user with `SELECT` on catalog tables and three views — and no
write grant anywhere, so the boundary is enforced by the database rather than
by discipline. It cannot read `quantity_price_tiers`, `orders`, `users`,
`addresses` or `inventory`.

Full detail: [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

---

## Project layout

```
app/          Controllers · Models · Repositories · Services · Middleware · DTOs
  Support/    Env · Config · Database · Money · SqlSplitter · SeedVerifier
bootstrap/    autoload.php (PSR-4, no Composer needed) · app.php
config/       app · database · tax · security · search · payment · shipping · mail · cache
database/
  migrations/ 42 numbered, forward-only, checksummed .sql files
  Seeders/    17 idempotent seeders
  Seeders/data/catalog/   the DEMO catalog — replace this, not the code
docker/       nginx · php · mysql · python
public/       index.php is the only PHP file in the web root
storage/      logs · uploads · cache · invoices  (all outside the web root)
docs/         ARCHITECTURE.md
```

**Rules enforced at review:** SQL only in `Repositories/`. Business rules only
in `Services/`. Controllers translate HTTP to service calls and back.

---

## Security notes

- `.env` is gitignored; `.env.example` carries dummy values only.
- Passwords are hashed with Argon2id. Reset and verification tokens are stored
  as SHA-256 hashes — the raw token is never persisted.
- Uploads live in `storage/uploads`, outside the web root, and are served
  through a controller after MIME validation and re-encoding.
- `APP_DEBUG=true` with `APP_ENV=production` throws at boot rather than
  shipping stack traces to customers.

---

## Testing

```bash
composer install     # once Composer is available
composer test        # PHPUnit
composer analyse     # PHPStan level 6
composer lint        # PSR-12
```

`php bin/console db:verify` runs today with no dependencies and asserts the
seeded data is internally consistent, including that the wedding event rules
reproduce the brief's 500-guest example exactly (600 / 600 / 600 / 650 / 650 /
750 / 2,000 / 50).

---

## Roadmap

Foundation → **Database** → Authentication → Catalog → Search → Cart → Pricing
→ Checkout → Orders → B2B → Quotes → Inventory → Admin → Event Calculator →
Python AI → Recommendations → Analytics → Deployment.

Phases 0–5 are the MVP. AI features come only after the core commerce system is
stable.


#admin@supplykaro.test 
SupplyKaro#Dev2026

php -S localhost:8000 -t public