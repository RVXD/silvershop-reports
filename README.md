# SilverShop Reports

Catalog, order and sales reports for [SilverShop](https://github.com/silvershop/silvershop-core), shown under
**CMS → Reports**. They **complement** silvershop-core's built-ins (All/No-image/Featured/Heavy Products, Shop
Sales, Tax, Customers, Abandoned Carts) with checks and analytics core doesn't have.

## Reports

**Catalog hygiene** — spot data problems before customers do:

- **Products with no price** — products with a zero base price (data-entry oversight; note a product priced only
  through variations will also show).
- **Products not under a category** — products whose page-tree parent is not a `ProductCategory` (orphaned, hard to
  find on the storefront).
- **Products with duplicate SKUs** — products sharing an `InternalItemID` with another product (import/copy error;
  breaks anything that keys on SKU).

**Sales & orders** — with a date-range filter:

- **Orders by status** — order count and total value per status (Paid / Processing / Sent / … ), the operational
  pipeline (hidden Cart statuses excluded).
- **Best sellers by revenue** — top products by revenue (and units); complements core's `ProductReport`, which ranks
  by quantity, so a low-volume high-value line surfaces here.
- **Top customers by spend** — registered customers ranked by total spend across placed orders (guests excluded).

Reports are auto-discovered — no configuration needed. Install and they appear under CMS → Reports.

## Scope

This module is the **generic catalog + order/sales** report bucket. Domain-specific reports live in the module they
concern (the suite convention) — e.g. sales-by-brand in `silvershop/brands`, sales-by-category in
`silvershop/category-index`, refunds in `silvershop/invoicing`, a ledger summary in `silvershop/ledger`, low-stock in
`silvershop/stock`, discounts in `silvershop/discounts`. Together with core's built-ins these cover the common
"80/20" shop reports; search-terms / most-viewed need view-tracking infrastructure and are out of scope.

## Status

v1.0 — six reports above, auto-discovered, i18n (en/nl/de/fr/es/it), tests + CI.

## Requirements

- silvershop/core ^6, silverstripe/reports ^6, silverstripe/framework ^6, PHP ^8.3
