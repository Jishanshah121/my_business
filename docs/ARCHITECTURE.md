# Serve, Pack & Celebrate — Technical Architecture
**Status:** Draft v0.1 — awaiting approval
**Date:** 2026-08-31
**Status update (Phase 1 complete):** schema built and verified — 78 tables, 123 foreign keys, 305 indexes, 42 migrations, 17 seeders.
**Scope:** B2B + B2C ecommerce and procurement platform for disposables, food-service supplies, packaging, hygiene and event consumables (India, INR, GST).

---

## 0. Decisions that need your sign-off

Five choices shape everything downstream. Three I've made; two are genuinely yours.

### ADR-001 — Custom PHP MVC vs. Laravel  **← YOUR CALL**
The brief specifies a hand-rolled MVC on PDO. I'll build exactly that if you say so, but I want the trade-off on record before we start, because it is the single most expensive decision to reverse.

A commercial ecommerce platform of this scope needs, on day one: a migration runner, a query builder, a DI container, a validator, a queue, a mailer, a scheduler, CSRF middleware, session hardening, a template engine with auto-escaping, and a test harness. Writing those ourselves is roughly 4–6 weeks of work that adds zero business value and carries our own security bugs. Laravel 11 supplies all of it, is PHP 8.2+, uses PDO underneath, and enforces the exact MVC + service + repository separation the brief asks for.

- **Option A — Laravel 11 (recommended).** Faster to a working product, better security defaults, easier to hire for, Eloquent or query builder as you prefer. The brief's "no giant PHP file / clean separation" rules are Laravel's defaults.
- **Option B — Slim custom MVC (as specified).** Full control, no framework lock-in, smaller dependency surface. We write and maintain the plumbing. Add ~4–6 weeks to the timeline and expect a thinner test/tooling story.

Everything else in this document is framework-neutral: the schema, the pricing engine, the domain model, the Python service and the phase plan are identical under either choice. **Default if you don't specify: Option B**, exactly as briefed.

### ADR-002 — Admin lives inside the main app, not in a top-level `admin/` folder  **(decided)**
The brief proposes `admin/` as a sibling of `app/`. That forces duplicated models, services, auth and validation, and the two copies drift. Instead: `app/Controllers/Admin/*`, `views/admin/*`, routes under an `/admin` prefix behind an auth + RBAC middleware group, separate asset bundle. One codebase, one set of business rules, one place a pricing bug can hide.

### ADR-003 — Inventory is held in base units (pieces) at the variant level  **(decided)**
Packs (sleeve of 50, carton of 1000) are *presentations*, not stock pools. See §4.1. This prevents the classic disposables bug where the warehouse holds 5,000 cups but the site says "Carton of 1000: out of stock".

### ADR-004 — Prices are stored tax-exclusive; display mode is per customer group  **(decided)**
B2C sees GST-inclusive pricing (MRP feel), B2B sees exclusive + GST line. One storage convention, two presentations. See §4.3.

### ADR-005 — Payment gateway  **← YOUR CALL (can be deferred to Phase 4)**
Razorpay is the default assumption (best Indian B2B coverage: UPI, cards, netbanking, NEFT/RTGS mandates, and Payment Links for quote-to-cash). Cashfree and PhonePe PG are equivalent alternatives. The abstraction in §9.4 means this is a one-class change, so we do not need the answer today.

---

## 1. Technology stack

| Layer | Choice | Version | Why |
|---|---|---|---|
| Web server | Nginx | 1.26 | Static assets, TLS, reverse proxy to both apps |
| App runtime | PHP-FPM | 8.2 LTS (min); 8.3 preferred | Brief specifies 8.2+. Your machine has 8.5 — we pin via Docker so dev matches prod |
| App framework | Custom MVC (or Laravel 11 — ADR-001) | — | See ADR-001 |
| Database | MySQL | 8.4 LTS | InnoDB, CTEs, window functions, FULLTEXT with ngram, `JSON` columns for flexible attributes |
| Cache / sessions / locks / rate limits | Redis | 7.2 | Session store (enables horizontal scale later), cart cache, price-rule cache, throttling counters |
| Queue | Redis-backed worker | — | Emails, WhatsApp, invoice PDFs, image processing, co-purchase recompute |
| AI/search service | Python + FastAPI | 3.12 | Brief specifies 3.11+. **Your machine has 3.9.6 — Docker handles this** |
| ASGI server | Uvicorn behind Gunicorn | — | Standard FastAPI production pattern |
| CSS | Tailwind CSS | 3.4 | Token-driven; we ship a custom theme so it does not read as default Tailwind |
| JS | Vanilla ES modules + Alpine.js | 3.x | Server-rendered pages with progressive enhancement. Alpine is 15 KB and handles the cart drawer, filter sheet, variant/pack selector and Party Box wizard without an SPA |
| Build | Vite | 5 | Hashed asset bundles, PostCSS, minification |
| Object storage | S3-compatible (Cloudflare R2) | — | Product images out of the app server; CDN in front |
| Local dev | Docker Compose | — | Removes the PHP 8.5 / Python 3.9 mismatch on your Mac |
| CI | GitHub Actions | — | Lint, static analysis, tests, build assets |

**PHP dependencies (deliberately few):** `vlucas/phpdotenv` (env), `nikic/fast-route` (routing), `php-di/php-di` (container), `monolog/monolog` (logging), `phpmailer/phpmailer` or `symfony/mailer` (SMTP), `respect/validation` (input rules), `ramsey/uuid`, `intervention/image` (upload re-encoding — also a security control), `phpunit/phpunit` + `phpstan/phpstan` (dev). Nothing else without a written reason.

**Python dependencies:** `fastapi`, `uvicorn`, `pydantic` v2, `sqlalchemy` (read-only), `rapidfuzz` (fuzzy match), `httpx`, `redis`. Phase 8+ adds `sentence-transformers` and a vector index; Phase 9+ optionally the Anthropic SDK for intent extraction.

---

## 2. System topology

```
                    Cloudflare (CDN, WAF, TLS)
                              |
                            Nginx
              /                              \
     PHP-FPM (app + admin)            static assets (Vite build)
        |        |        |
        |        |        +---> Redis (session, cache, queue, locks)
        |        +------------> MySQL 8.4  (READ+WRITE — source of truth)
        |
        +--- HTTPS + HMAC service token --->  FastAPI (AI service)
                                                |
                                                +--> MySQL (READ-ONLY user,
                                                |     catalog tables + one
                                                |     anonymized fact view)
                                                +--> Redis (embedding &
                                                      intent cache)
```

**The hard boundary:** the AI service can read the catalog and return *identifiers and scores*. It cannot write anything, anywhere. It never returns a price, a stock number, or an order instruction. PHP hydrates every result from the live database, applies pricing and visibility rules, and is the only thing that ever tells a customer what something costs.

**Degradation:** if FastAPI is unreachable, PHP falls back to MySQL FULLTEXT search. 300 ms timeout, circuit breaker opens for 30 s after 5 consecutive failures. The store never goes down because the AI does.

---

## 3. Folder structure

```
my-business/
├── app/
│   ├── Controllers/
│   │   ├── Web/            HomeController, CatalogController, ProductController,
│   │   │                   CartController, CheckoutController, AccountController,
│   │   │                   OccasionController, BusinessController, EventPlannerController
│   │   ├── Api/V1/         ProductApiController, CartApiController, QuoteApiController,
│   │   │                   EventCalculatorApiController, AiProxyController
│   │   └── Admin/          DashboardController, ProductController, InventoryController,
│   │                       OrderController, QuoteController, CustomerController,
│   │                       CouponController, BundleController, EventRuleController,
│   │                       ReportController, SettingsController, UserController
│   ├── Models/             Thin persistence objects (one per aggregate root)
│   ├── Repositories/       All SQL lives here. Nowhere else.
│   ├── Services/
│   │   ├── Pricing/        PriceResolver, QuantityTierResolver, CouponEngine,
│   │   │                   TaxCalculator, PriceCalculator, MoneyFormatter
│   │   ├── Catalog/        ProductService, VariantService, SearchService,
│   │   │                   MysqlSearchService, AiSearchClient
│   │   ├── Cart/           CartService, CartValidator, CartMerger
│   │   ├── Checkout/       CheckoutService, OrderPlacementService, IdempotencyGuard
│   │   ├── Inventory/      InventoryService, ReservationService, StockLedger
│   │   ├── Payment/        PaymentGateway (interface), RazorpayGateway, CodGateway,
│   │   │                   BankTransferGateway, CreditTermsGateway, GatewayRegistry
│   │   ├── Shipping/       ShippingRateProvider (interface), ZoneWeightRateProvider,
│   │   │                   ShipmentTracker, ServiceabilityService
│   │   ├── Quote/          QuoteService, QuoteToOrderConverter
│   │   ├── Events/         EventPlanService, EventRuleEngine, KitBuilder
│   │   ├── Notification/   Notifier (interface), EmailChannel, SmsChannel,
│   │   │                   WhatsAppChannel, NotificationDispatcher
│   │   ├── Tax/            GstResolver, HsnRegistry, InvoiceNumberSeries
│   │   └── Analytics/      EventTracker, ReportBuilder
│   ├── Middleware/         Authenticate, Authorize, VerifyCsrf, RateLimit,
│   │                       StartSession, ShareViewData, ForceHttps, SecurityHeaders
│   ├── Validators/         One class per form/endpoint
│   ├── DTOs/               PricingContext, PricedLine, CartSnapshot, EventPlanRequest,
│   │                       EventKit, OrderDraft, Money, Quantity
│   ├── Support/            Money (integer paise VO), Result, Paginator, Slugger
│   ├── Helpers/            View helpers: e(), url(), asset(), price(), csrf()
│   └── Exceptions/
├── config/                 app.php, database.php, cache.php, payment.php, shipping.php,
│                           tax.php, search.php, mail.php, security.php
├── routes/                 web.php, admin.php, api.php
├── public/                 index.php (only PHP file in the web root)
│   └── build/              Vite output (hashed)
├── resources/
│   ├── css/                tailwind entry + design tokens
│   ├── js/                 modules: cart-drawer, filters, variant-picker,
│   │                       party-box, event-planner, quote-form, search
│   └── images/
├── views/
│   ├── layouts/            app, admin, checkout, email, error
│   ├── components/         button, card, product-card, badge, modal, drawer,
│   │                       navbar, footer, price-display, quantity-selector,
│   │                       filter-panel, breadcrumb, toast, form/*, table,
│   │                       empty-state, loading-state, pagination, rating
│   ├── home/ products/ categories/ cart/ checkout/ account/
│   ├── b2b/ events/ occasions/ businesses/ quotes/ bundles/
│   └── admin/
├── python-service/
│   ├── app/
│   │   ├── main.py
│   │   ├── config.py
│   │   ├── security.py         HMAC service-token verification
│   │   ├── routers/            search.py, recommend.py, event_plan.py, health.py
│   │   ├── services/           catalog_reader.py, ranker.py, cache.py
│   │   ├── ai/                 intent.py, entities.py, normalizer.py, synonyms.py
│   │   ├── search/             lexical.py, fuzzy.py, vector.py (P8+)
│   │   ├── recommendations/    cooccurrence.py, curated.py, cold_start.py
│   │   ├── models/             Pydantic request/response schemas
│   │   └── data/               synonyms.yml, hinglish.yml, units.yml
│   ├── tests/
│   └── requirements.txt
├── database/
│   ├── migrations/         NNN_description.sql, forward-only, checksummed
│   ├── seeders/            categories, attributes, roles/permissions, tax rates,
│   │                       shipping zones, event rules, demo products
│   └── views/              analytics + AI read-only views
├── storage/
│   ├── logs/  uploads/  cache/  invoices/  exports/
├── tests/                  Unit/  Feature/  Integration/
├── docker/                 nginx/, php/, mysql/, python/
├── docs/                   ARCHITECTURE.md, API.md, DB.md, RUNBOOK.md, DECISIONS/
├── docker-compose.yml
├── .env.example
├── .gitignore
└── README.md
```

Rule enforced by review: **SQL only in `Repositories/`. Business rules only in `Services/`. Controllers translate HTTP to service calls and back — nothing else.**

---

## 4. The four hard problems

Everything in this platform is ordinary ecommerce except four things. These are where the design effort belongs.

### 4.1 Pack sizes and units

The catalog has three levels, not two:

| Level | Table | Example | Owns |
|---|---|---|---|
| Product | `products` | Ripple Coffee Cup | Marketing copy, images, category, SEO |
| Variant | `product_variants` | 150 ml, Kraft Brown | Physical spec, weight, dimensions, **inventory** |
| Pack | `variant_packs` | Sleeve of 50 · Carton of 1000 | **SKU**, `pieces_per_pack`, MOQ, the thing a customer actually buys |

Cart lines, order lines, quote lines and kit lines all reference a `variant_pack_id` and a **pack quantity**. Inventory is held once, at the variant, in **base units (pieces)**. Buying 2 sleeves of 50 deducts 100 pieces.

Why this matters: if you stock packs independently you get split-stock — the warehouse has 5,000 cups but the 1,000-carton SKU shows out of stock because nobody updated that row. Base-unit inventory makes that structurally impossible.

Where you genuinely cannot break a sealed carton, `variant_packs.is_breakable = 0` and `inventory` carries a nullable `pack_id`, giving that pack its own pool. `pack_id IS NULL` is the loose base-unit pool. The same nullable-key trick carries `warehouse_id` for multi-location later.

**Display rule.** The `PriceDisplay` component always leads with the pack price and states the pack, then shows the per-piece equivalent quietly:

> **₹149** / 50 pcs  ·  ₹2.98 per piece

Never per-piece as the headline. The brief is right that it confuses buyers.

### 4.2 The pricing engine

One class, `PriceResolver`, is the only thing in the system permitted to decide what something costs. Cart, product page, quote, event kit, admin preview and order placement all call it. There is no second implementation, and none in JavaScript.

**Input:** `PricingContext { customer_id?, customer_group_id, is_b2b, ship_to_state, channel, currency, evaluated_at }` and a `LineRequest { variant_pack_id, pack_qty }`.

**Waterfall — evaluated in this order, every time:**

1. **Base price** — `variant_packs.base_price`, or derived as `variant.base_unit_price × pieces_per_pack` when the pack has no explicit price.
2. **Contract price** — `customer_price_overrides` matched on customer, then customer group. A negotiated B2B rate.
3. **Quantity tier** — `quantity_price_tiers`, matched on scope (pack → variant → product → category, most specific wins) and customer group, selecting the row with the greatest `min_base_qty ≤ requested base qty`. Yields a unit price.
4. **Campaign** — `price_rules`, date-bounded sale prices and percentage discounts.
5. **Resolution policy** — the engine takes the **lowest** resulting unit price among candidates, unless a candidate is flagged `is_exclusive`, in which case that one wins outright. This rule is written down because unstated stacking policy is where every pricing bug in every ecommerce system comes from.
6. **Cart-level discounts** — coupons never touch the unit price. They are computed on the cart and **allocated back across lines pro-rata**, so GST is charged on the actually-discounted value per HSN code. This is a legal requirement, not a nicety.
7. **Tax** — §4.3.

**Output:** an immutable `PricedLine` DTO carrying unit price, pack price, line subtotal, discount, taxable value, tax breakdown, and an `applied_rules[]` trace. The trace powers the admin "why is this price what it is?" view and the customer-facing "you save ₹X" copy.

**Money.** Never a float. A `Money` value object holds **integer paise** and does all arithmetic. The database stores `DECIMAL(12,4)` for unit prices (₹0.4750 per piece is a real wholesale number) and `DECIMAL(12,2)` for line and order totals. Rounding is half-up, applied at line level, then summed — one rule, one helper, used by cart, quote, order and invoice alike, so the four never disagree by a rupee.

**The invariant:** the browser never sends a price. Cart items store `variant_pack_id` + `pack_qty` and nothing else. Every page render recomputes from scratch. A tampered request can only ask for a different quantity of a different product — never a different price. Tested explicitly (§17).

Example tier table for a paper coffee cup, exactly as briefed:

| Min qty (pcs) | Unit price | Effective for 1,000 pcs |
|---|---|---|
| 100 | ₹2.5000 | — |
| 500 | ₹2.2000 | — |
| 1,000 | ₹2.0000 | ₹2,000 |
| 5,000 | ₹1.7000 | — |
| 10,000 | ₹1.5000 | — |

### 4.3 GST and place of supply

- Every product carries an `hsn_code`; every HSN maps to a `gst_rate` in `tax_rates` (dated, so a rate change does not rewrite history).
- The seller has a place of supply (per warehouse). If ship-to state == seller state → **CGST + SGST** at half the rate each. Otherwise → **IGST** at the full rate. Stored per line, not recomputed later.
- `customer_groups.prices_include_tax` drives display only. Storage is always exclusive.
- `order_items` persists `hsn_code`, `gst_rate`, `taxable_value`, `cgst_amount`, `sgst_amount`, `igst_amount`. `orders` carries a `round_off` field for the ±₹0.50 invoice rounding.
- `order_invoices` exists from day one with nullable `irn`, `ack_no`, `signed_qr_payload`, `eway_bill_no`. We will not implement e-invoicing now, but adding it later must not require a schema migration on live order data.
- Invoice numbering runs through `invoice_series` with a financial-year reset (`INV/2026-27/00001`) and a row-level lock so two concurrent orders cannot take the same number.

### 4.4 The event calculator as data, not code

The brief's numbers (600 plates for 500 guests, 2,000 napkins) must be editable by an admin without a deploy. So the rules are rows.

```
item_roles            DINNER_PLATE, SIDE_PLATE, BOWL, SPOON, FORK, GLASS,
                      NAPKIN, TISSUE, GARBAGE_BAG, SERVING_TRAY, CUP, STRAW...

event_rule_sets       (event_type, meal_type, serving_style, version, is_active)

event_rules           (rule_set_id, item_role_id, qty_per_guest DECIMAL(8,3),
                       safety_margin_pct, min_qty, rounding_step, is_required,
                       priority, conditions JSON)

item_role_products    (item_role_id, variant_pack_id, tier ENUM(standard,
                       premium, eco), rank)
```

**Algorithm:**

```
base   = guests × qty_per_guest
padded = base × (1 + safety_margin_pct/100)
qty    = max( ceil_to_step(padded, rounding_step), min_qty )
```

Then for each role: pick the highest-ranked product in `item_role_products` matching the requested tier that is in stock and published; convert base quantity to pack quantity with `ceil(qty / pieces_per_pack)`; price the whole basket through `PriceResolver` (§4.2).

Rule sets are **versioned**, so a kit generated in March is reproducible in December after the admin has retuned the margins.

The result is an `EventKit` DTO — rendered as "Your Event Kit", one-click into the cart, optionally saved to `event_kits` as a named, reusable basket. Budget-aware planning (Phase 3 of the AI work) walks the tier down by rank until the total fits, and never drops a role marked `is_required`.

**Build Your Party Box** is the same engine driven interactively: the wizard collects guest count, then lets the customer choose the actual product for each role while the engine keeps the quantities honest.

---

## 5. Database plan

**78 tables (as built) in 12 bounded contexts** — the plan estimated ~52 because several groups below were counted as single entries. Every table: `id BIGINT UNSIGNED AUTO_INCREMENT`, `created_at`, `updated_at`; `deleted_at` where the brief calls for soft deletion; InnoDB, `utf8mb4_0900_ai_ci`; foreign keys on every relationship with deliberate `ON DELETE` behaviour (`RESTRICT` for anything referenced by an order).

### Identity & access
| Table | Key columns | Notes |
|---|---|---|
| `users` | email UQ, phone UQ, password_hash, status, email_verified_at, customer_group_id | Argon2id hashes |
| `roles` | code UQ, name | Super Admin, Admin, Inventory Manager, Order Manager, Sales Manager, B2B Manager, Customer |
| `permissions` | code UQ (`products.edit`, `orders.refund`) | |
| `role_permissions`, `user_roles` | composite PK | |
| `customer_groups` | code, is_b2b, prices_include_tax, default_discount_pct | B2C Retail, B2B Standard, B2B Gold, Distributor |
| `business_profiles` | user_id, company_name, gstin, pan, business_type, expected_monthly_spend, status, approved_by, credit_limit, credit_days | B2B approval workflow |
| `addresses` | user_id, type, line1/2, city, state_code, pincode, is_default | state_code drives GST |
| `password_resets`, `login_attempts`, `sessions` | | Throttling + Redis-backed sessions |

### Catalog
| Table | Key columns |
|---|---|
| `categories` | parent_id, slug UQ, name, banner, sort_order, seo_* — self-referencing tree, unlimited depth |
| `brands` | slug UQ, name, logo |
| `products` | sku_root, slug UQ, name, category_id, brand_id, short/long description, material, is_eco, is_compostable, is_recyclable, is_featured, is_bestseller, is_new, hsn_code, status, seo_* |
| `product_variants` | product_id, name, size, capacity_ml, color, weight_g, length/width/height_mm, base_unit_price, is_default, status |
| `variant_packs` | variant_id, **sku UQ**, pack_label, pieces_per_pack, base_price, mrp, min_order_qty, max_order_qty, is_breakable, is_default, status |
| `product_images` | product_id, variant_id NULL, path, alt_text, is_primary, sort_order |
| `attributes`, `attribute_values`, `product_attributes` | EAV for specs, so new attributes need no code change |
| `tags`, `product_tags` | search + occasion targeting |
| `product_associations` | product_id, related_id, type (upsell, cross_sell, fbt), rank, is_curated | Curated always outranks learned |

### Pricing
`quantity_price_tiers` (scope_type, scope_id, customer_group_id NULL, min_base_qty, unit_price, is_exclusive) · `customer_price_overrides` (customer_id NULL, customer_group_id NULL, variant_pack_id, unit_price, valid_from/to) · `price_rules` (campaign discounts, dated) · `tax_rates` (hsn_code, gst_rate, valid_from) · `coupons` · `coupon_conditions` · `coupon_usages`.

### Inventory
`warehouses` · `inventory` (variant_id, warehouse_id, pack_id NULL, quantity_on_hand, quantity_reserved, low_stock_threshold, reorder_point) · `inventory_transactions` (append-only ledger: purchase, sale, reservation, release, adjustment, damage, return, transfer — with actor, reference and reason). Available = on_hand − reserved. **Every stock change writes a ledger row.**

### Cart, orders, payments
`carts` · `cart_items` (variant_pack_id, pack_qty, added_via, source_ref — **no prices**) · `orders` · `order_items` (full immutable snapshot: name, sku, pack label, pieces_per_pack, unit price, discount, hsn, gst rate and amounts) · `order_addresses` (copied, not referenced) · `order_payments` · `payment_webhook_events` (idempotent replay) · `order_status_history` (append-only, with actor) · `order_invoices` · `shipments` · `checkout_attempts` (idempotency keys) · `refunds`.

### B2B & quotes
`quotes` (number, customer, status, valid_until, terms, totals) · `quote_items` (with admin-editable price + reason) · `quote_status_history` · `credit_notes` (Phase 2+).

### Bundles, events, occasions
`bundles` · `bundle_items` · `occasions` · `occasion_products` · `business_types` · `business_type_products` · `item_roles` · `item_role_products` · `event_rule_sets` · `event_rules` · `event_kits` · `event_kit_items` · `event_plans` (saved customer plans).

### Shipping
`shipping_zones` · `zone_pincodes` · `shipping_rates` (zone, weight slab, base + per-kg, free-shipping threshold). **Chargeable weight = max(actual, volumetric)** where volumetric = L×W×H ÷ 5000 — this matters enormously for disposables, which are light and bulky, and getting it wrong turns every large order into a loss.

### Engagement & ops
`reviews` (+ `review_images`, `review_votes`, unique on order_item to stop duplicates) · `wishlists` · `wishlist_items` · `notifications` · `notification_templates` · `settings` (typed key/value, the home of GST defaults, safety margins, MOQs, thresholds) · `admin_logs` · `analytics_events` · `search_queries` · `product_views`.

**Indexing highlights:** `products(status, category_id)`, `products(is_featured, status)`, `variant_packs(sku)` UQ, FULLTEXT on `products(name, short_description)` with an ngram parser for partial Indian-language terms, `quantity_price_tiers(scope_type, scope_id, customer_group_id, min_base_qty)`, `orders(user_id, created_at)`, `inventory(variant_id, warehouse_id)` UQ, `analytics_events(event_type, created_at)`.

Migrations are forward-only numbered SQL files (`001_create_users.sql` …) with a `migrations` table recording name, checksum and run time. A checksum mismatch aborts the run — nobody quietly edits an applied migration.

---

## 6. Authentication & authorisation

- **Web:** PHP sessions in Redis. `SameSite=Lax`, `Secure`, `HttpOnly`, session ID regenerated on login and privilege change, absolute + idle timeouts, server-side invalidation on password change.
- **Passwords:** `password_hash($pw, PASSWORD_ARGON2ID)`. Verify with `password_verify`, rehash on algorithm upgrade. Never logged, never emailed.
- **Throttling:** per-IP and per-account counters in Redis. Progressive backoff, generic failure messages (no account enumeration), password-reset tokens single-use and 60-minute-lived.
- **API:** same session for same-origin fetch; bearer tokens for future mobile apps.
- **Service-to-service (PHP ↔ Python):** short-lived HMAC-signed tokens, shared secret from env, replay window, network-level restriction to the internal interface.
- **RBAC:** permission-based, not role-name checks. `Authorize:products.edit` middleware on routes; `Gate::allows()` in views to hide controls the user cannot use. Roles are bags of permissions and are editable in the admin.
- **B2B approval:** registering as a business creates a `business_profiles` row with `status = pending`. The account works as B2C immediately; **B2B pricing, quotes and credit unlock only on admin approval**. GSTIN is format-validated (checksum) on entry, with an API verification hook for later.

---

## 7. B2C experience

Browse → search/filter → product → cart → checkout → pay → track → reorder → review.

Catalog listing supports facets on category, price band, material, capacity, pack size, eco flags, brand, in-stock; sorting by relevance, price, newest, bestseller. Server-side pagination (24/page), never a 500-product page. Filters render as a bottom-sheet drawer on mobile.

The product page carries everything in the brief: gallery, variant and pack pickers that re-price live via a server call (never client-side maths), stock state, quantity selector honouring MOQ/max, Add to Cart / Buy Now / Request Bulk Quote, the B2B tier table, specs, GST and shipping info, reviews, FAQs, related and frequently-bought-together.

Account area: orders, reorder, invoices, addresses, wishlist, reviews, profile, business details.

---

## 8. B2B experience

A distinct surface, not a discount flag:

- Business registration capturing company, contact, GSTIN, PAN, business type, both addresses, expected monthly purchase.
- Dashboard: monthly spend, order count, savings vs list price, open quotes, frequently ordered products, and reorder prompts driven by observed consumption cadence ("your 90 ml cups usually last 7 days").
- Tier pricing visible on every product; a bulk-quote request on every product.
- GST invoices, downloadable quotations, repeat-order in one click.
- Optional credit terms (`credit_limit`, `credit_days`) gated on admin approval, enforced at checkout by `CreditTermsGateway`.

**Quote lifecycle:** `draft → sent → negotiating → accepted → converted → expired/rejected`. Admin can adjust line prices (with a reason recorded), add shipping and discount, set validity. Accepting a quote creates an order that carries the **quoted** prices as its snapshot — the pricing engine is bypassed by design, and the quote id is recorded on the order for audit.

---

## 9. Cart, checkout, orders

### 9.1 Cart
Server-authoritative, one row per line, IDs and quantities only. Guests get a signed cart token in an HttpOnly cookie; on login the guest cart merges into the user cart by summing quantities and clamping to `max_order_qty`. Every read runs `CartValidator`, which returns non-blocking `CartIssue[]` — out of stock, MOQ not met, price changed since you added it, coupon no longer valid — shown as notices rather than silently mutating the cart. Add-on suggestions come from `product_associations` (cups → lids → carriers → stirrers → napkins).

### 9.2 Checkout
Login/guest → address → delivery → payment → review → confirmation. Totals are recomputed server-side at every step and again at placement; the client's numbers are display only.

### 9.3 Order placement (the transactional core)
```
verify idempotency key (checkout_attempts)
BEGIN
  SELECT ... FROM inventory WHERE variant_id IN (...) FOR UPDATE
  assert available >= required for every line
  re-price every line through PriceResolver          ← last word on price
  INSERT order, order_items (snapshots), order_addresses
  INSERT inventory reservations + ledger rows
  INSERT order_status_history (pending)
COMMIT
→ hand off to payment gateway
```
Payment success is confirmed by **webhook**, never by the browser redirect; the redirect only decides which page the customer sees. Webhooks are signature-verified and deduplicated through `payment_webhook_events`. Reservations convert to consumption at shipment and are released on cancellation or expiry (a scheduled job reaps abandoned unpaid orders).

Statuses: pending → confirmed → processing → packed → shipped → out for delivery → delivered, plus cancelled / returned / refunded. Allowed transitions live in an explicit state-machine map; illegal transitions throw.

### 9.4 Payment abstraction
```php
interface PaymentGateway {
    public function createIntent(Order $o, PaymentContext $c): PaymentIntent;
    public function verifyCallback(array $payload, string $signature): PaymentResult;
    public function refund(Payment $p, Money $amount): RefundResult;
    public function supports(PaymentMethod $m): bool;
}
```
Implementations: `RazorpayGateway`, `CodGateway`, `BankTransferGateway` (B2B NEFT/RTGS, admin marks received), `CreditTermsGateway` (approved B2B, net-30). A registry resolves by method code. Swapping providers touches one class.

### 9.5 Shipping abstraction
`ShippingRateProvider::quote(Shipment): RateQuote[]` and `ShipmentTracker::track(awb)`. Phase 1 ships `ZoneWeightRateProvider` reading `shipping_zones` + `shipping_rates`, with pincode serviceability, free-shipping thresholds and volumetric weight. A courier aggregator (Shiprocket/Delhivery) plugs in later behind the same interface.

---

## 10. The Python AI service

### Contract
```
POST /ai/search       {query, customer_context?} → {intent, entities, results[]}
POST /ai/recommend    {product_id | cart[], context} → {results[]}
POST /ai/event-plan   {query | structured}       → {event_type, guests, roles[]}
GET  /health
```
Every `results[]` entry is `{product_id, variant_pack_id, score, reason}`. **No prices. No stock. No totals.** PHP hydrates from MySQL, applies pricing, visibility and stock rules, and renders.

### Database access
A dedicated MySQL user with `SELECT` on catalog tables plus one anonymised `v_order_item_facts` view for co-purchase learning. No `INSERT`, `UPDATE`, `DELETE` grants exist for it — the boundary is enforced by the database, not by discipline.

### Search, in stages
1. **Phase 1 (PHP, no Python):** MySQL FULLTEXT boolean mode + a synonym dictionary + SKU exact match. Good enough to launch.
2. **Phase 8:** FastAPI takes over. Query normalisation, a synonym and **Hinglish** dictionary (`kagaz cup` → paper cup, `dona`, `pattal`, `chamach`), `rapidfuzz` fuzzy matching so "cofee cup" and "butter peper" resolve, and a rule-based entity extractor pulling quantity, guest count, budget, occasion, business type and material out of the query.
3. **Phase 9:** embeddings (multilingual sentence-transformers) in a vector index, hybrid lexical + semantic ranking.
4. **Phase 10+:** an LLM intent parser with a strict JSON schema for the genuinely conversational queries, cached aggressively, always falling back to stage 2 on timeout or malformed output.

### Recommendations
Curated `product_associations` rows always outrank learned signals. Learned signals come from a nightly item-item co-occurrence matrix over order facts. Cold start falls back to category and item-role rules. Occasion and business-type context re-ranks.

### The rule that does not bend
The AI can suggest what to buy. It can never decide what it costs, whether it is in stock, or whether an order is valid. If the model hallucinates a product, hydration against the real catalog drops it before a customer ever sees it.

---

## 11. API design

Versioned under `/api/v1`. Consistent envelope:
```json
{ "data": {}, "meta": { "page": 1, "per_page": 24, "total": 812 }, "errors": [] }
```
Cursor pagination on catalog endpoints. Proper verbs and status codes; `422` with per-field errors for validation; `409` for conflicts (stock, idempotency); `429` with `Retry-After` for throttling.

```
GET    /api/v1/products                 filters, facets, sort, cursor
GET    /api/v1/products/{slug}
POST   /api/v1/products/{id}/price      re-price on variant/pack/qty change
GET    /api/v1/categories
GET    /api/v1/cart
POST   /api/v1/cart/items
PATCH  /api/v1/cart/items/{id}
DELETE /api/v1/cart/items/{id}
POST   /api/v1/cart/coupon
POST   /api/v1/checkout/quote           shipping + tax preview
POST   /api/v1/checkout                 Idempotency-Key required
GET    /api/v1/orders  ·  /orders/{id}
POST   /api/v1/quotes  ·  GET /quotes/{id}  ·  POST /quotes/{id}/accept
POST   /api/v1/event-calculator
POST   /api/v1/search                   proxies to AI, falls back to MySQL
POST   /api/v1/bundles/{id}/add-to-cart
```
Documented in `docs/API.md` with request/response examples per endpoint.

---

## 12. Admin

Sections exactly as briefed: Dashboard, Products, Categories, Variants, Inventory, Orders, Customers, B2B Accounts, Quotes, Coupons, Bundles, Event Kits, Reviews, Reports, Settings, Admins — each gated by permission, each action written to `admin_logs`.

Two things deserve special attention:

- **The price inspector.** Given a product, a customer group and a quantity, show the full `applied_rules[]` trace from `PriceResolver`. Without this, nobody can answer "why did this customer pay that?" and pricing disputes become archaeology.
- **The rules editor.** GST rates, event safety margins, MOQs, shipping slabs, free-shipping thresholds, homepage sections, featured products and item-role mappings are all editable rows. If a business rule is going to change more than once a year, it does not belong in code.

---

## 13. Security

**Input/output:** PDO prepared statements everywhere, no string-built SQL, ever. Output escaped by default in the view layer (`e()` on every echo; raw output requires an explicit, reviewed call). Per-session CSRF token on every state-changing form and fetch. Strict Content-Security-Policy, `X-Content-Type-Options`, `Referrer-Policy`, HSTS.

**Uploads:** stored outside the web root and served through a controller. Extension allowlist + `finfo` MIME check + re-encoding through Intervention Image, which strips any embedded payload. Randomised filenames, size caps, per-user rate limits. The `uploads/` directory is never executable.

**Secrets:** everything in `.env`, nothing in Git, `.env.example` committed with dummy values. Separate credentials per environment. The Python service gets its own read-only DB user.

**Rate limiting:** login, registration, password reset, coupon apply, search, quote submission, and all AI endpoints.

**Logging:** Monolog with rotating files, structured context, correlation IDs across the PHP→Python hop. We log auth events, admin actions, payment and order errors and inventory changes. We never log passwords, card data, tokens or full webhook payloads containing secrets.

**Errors:** friendly, specific customer messages ("This pack is out of stock — 200 pieces are available"), stack traces only to the log, never to the browser in production.

---

## 14. Frontend & design system

**Positioning:** premium, warm, credible, commercial — a modern Indian consumer brand that a restaurant owner also trusts for a ₹80,000 monthly order. Original identity throughout; no other brand's assets, layout or copy.

**Proposed direction (to refine in Phase 9):** a cool paper-white ground with an ink near-black; a deep pine green as primary, earned by the eco range rather than sprayed everywhere; a clay accent reserved for offers and urgency; a sage surface tint for cards and sections. Typography pairs a characterful grotesk for display with a workhorse sans for body and a mono for SKUs, quantities and price tables — tabular numerals wherever prices line up in a column, which on this site is constantly.

**Components** (view partials + matching JS behaviours): Button, Card, ProductCard, Badge, Modal, Drawer, Navbar, Footer, PriceDisplay, QuantitySelector, FilterPanel, Breadcrumb, Toast, Form controls, Table, EmptyState, LoadingState, Pagination, Rating, TierTable, PackPicker.

**Mobile** is the primary target: sticky bottom CTA on product pages, filters in a bottom sheet, one-thumb quantity control, persistent search, WhatsApp and bulk-quote always reachable.

**Performance:** lazy-loaded responsive images in WebP/AVIF with explicit dimensions, hashed and minified bundles, critical CSS inlined, Redis-cached category trees and price rules, pagination everywhere, and a repository layer written to eager-load and avoid N+1 queries. Target: LCP under 2.5 s on 4G.

**SEO:** per-entity title, meta description, canonical, Open Graph; `Product` and `BreadcrumbList` structured data with SKU, price, currency, availability and brand; `Organization` on the home page; generated `sitemap.xml` and `robots.txt`.

---

## 15. Deployment

**Local:** `docker compose up` gives nginx, php-fpm 8.2, MySQL 8.4, Redis 7, the FastAPI service on 3.12, Mailhog and Adminer. This is why the PHP 8.5 / Python 3.9.6 versions currently on your Mac are not a problem — and also why we should not build against them directly.

**Production (Phase 1 scale):** a single well-provisioned VPS running nginx → php-fpm, MySQL with automated daily backups and binlog retention, Redis, and uvicorn under systemd. Cloudflare in front for CDN, TLS and WAF; product images in R2. Deploys run through GitHub Actions to atomic symlinked release directories with a one-command rollback; migrations run as a gated step before the symlink flips.

**Scale path when needed:** app servers behind a load balancer (already possible — sessions are in Redis), read replicas for catalog queries, MySQL and Redis split onto their own hosts, the AI service scaled independently. Nothing in this design has to change to get there.

**Operational must-haves before launch:** automated backups with a *tested* restore, uptime and error alerting, a runbook for payment-webhook failures and stock discrepancies, and a staging environment that mirrors production.

---

## 16. Roadmap

| Phase | Deliverable | Est. |
|---|---|---|
| **0** | Repo, git init, Docker Compose, `.env`, config, migration runner, CI, base layout + design tokens | 3–4 d |
| **1** | Full schema (~52 tables) as numbered migrations + seed data (categories, roles, tax rates, shipping zones, event rules, 40–50 realistic products with variants, packs and tiers) | 5–7 d |
| **2** | Auth: register, login, verification, reset, sessions, RBAC, B2C + B2B registration, B2B approval workflow, account shell | 5–7 d |
| **3** | Catalog: categories, listing with facets, product page, variant/pack pickers, **PriceResolver + tax engine**, MySQL search, wishlist | 10–14 d |
| **4** | Cart, checkout, order placement, inventory + reservations, payment gateway, shipping rates, order tracking, emails | 12–16 d |
| **5** | B2B: tier display, business dashboard, quote request → admin quote → accept → order, GST invoice PDF, reorder | 8–10 d |
| **6** | Event calculator, Build Your Party Box, bundles/kits, occasion pages, business landing pages | 8–10 d |
| **7** | Admin panel: dashboard, product/variant/pack CRUD, inventory, orders, quotes, customers, coupons, bundles, event rules, reviews, settings, price inspector | 12–16 d |
| **8** | Python service: FastAPI scaffold, read-only access, intent + entity extraction, fuzzy search, `/ai/search`, `/ai/recommend`, PHP client with circuit breaker | 8–10 d |
| **9** | AI event planning, recommendation learning, analytics dashboards, SEO, performance, WhatsApp, notifications | 8–12 d |
| **10** | Hardening, load testing, security review, backups, staging, launch runbook | 5–7 d |

**MVP = Phases 0–5.** That is a store that genuinely sells: real catalog with pack-aware tier pricing and correct GST, working cart and checkout, real payments, inventory that does not oversell, B2B accounts with quotes and invoices, and enough admin to run it. Phases 6–7 make it distinctive and operable. Phases 8–9 make it intelligent.

**Phase 2 features** (post-MVP, ~6–7 above): event calculator, Party Box, bundles, occasion and business landing pages, full admin, reviews, coupons, analytics.

**Phase 3 features** (~8–10 and beyond): AI search and recommendations, budget-aware event planning, WhatsApp ordering, subscriptions and recurring B2B procurement, loyalty and referrals, multi-warehouse, e-invoicing and e-way bills, vendor portal, ERP/accounting integration, vector search, chatbot.

---

## 17. Testing

Written alongside each phase, not after. Non-negotiable coverage:

- **Price manipulation** — a forged cart payload with an altered price must not change the order total. This test exists before checkout ships.
- Quantity-tier boundary selection (99/100/101/999/1000 pieces).
- Coupon stacking, minimum-cart, per-user and total usage limits, and B2B-only exclusivity.
- GST: intra-state CGST+SGST split vs inter-state IGST, per-line rounding, pro-rata discount allocation.
- Inventory: concurrent checkout of the last unit must oversell zero times.
- Order placement idempotency under duplicate submission.
- Event calculator arithmetic against known expected baskets.
- Authorisation: every admin route rejects every insufficient role.
- Pack-to-base-unit conversion in both directions.

---

## 18. What I need from you to start Phase 1

1. **ADR-001** — Laravel 11, or the custom MVC as briefed? (Default: custom MVC.)
2. Business name, legal entity name and seller state — the last one determines GST behaviour and is baked into seed data.
3. Whether the initial catalog should be seeded with your real SKUs and prices, or realistic placeholders we replace later.
4. Anything in §0 or §16 you want reordered.

Once approved, Phase 1 delivers the folder structure, Docker environment, the complete numbered migration set and seed data — with instructions to run and verify it.
