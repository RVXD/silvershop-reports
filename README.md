# SilverShop Reports

Catalog and order hygiene reports for [SilverShop](https://github.com/silvershop/silvershop-core), shown under
**CMS → Reports**. They help shop managers spot data problems before customers do.

Inspired by the "side reports" in `sunnysideup/ecommerce`, rebuilt as a small, standalone, opt-in SilverShop module.

## Reports

- **Products with no price** — products with a zero base price (data-entry oversight; note a product priced only
  through variations will also show).
- **Products with no image** — products missing a main image (render with a placeholder on the storefront).
- **Products not under a category** — products whose page-tree parent is not a `ProductCategory` (orphaned, hard to
  find on the storefront).

Reports are auto-discovered — no configuration needed. Install and they appear under CMS → Reports.

## Status

Prototype — a first slice of the reporting ideas from `sunnysideup/ecommerce`. More to come (duplicate SKUs, long
titles, orders side-reports, …).

## Requirements

- silvershop/core ^6, silverstripe/reports ^6, silverstripe/framework ^6, PHP ^8.3
