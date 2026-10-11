# gemcart

A PHP e-commerce storefront — product catalogue, cart, checkout and order tracking,
written without a framework.

## Features

- Product catalogue with listing and detail pages (`products.php`)
- Shopping cart
- Checkout flow (`checkout.php`)
- Order tracking (`order_tracking.php`)

## Run it

Needs PHP and MySQL/MariaDB.

```bash
git clone https://github.com/Zephyr4772/gemcart.git
cd gemcart
php -S localhost:8000
```

Import the schema and point the DB connection settings at your database before
loading the site. Built against a classic LAMP setup — XAMPP/WAMP works.

## Why no framework

Written to understand what a framework actually does for you: routing, templating,
query building and session handling are all hand-rolled here. Instructive to write
once; not how you'd build this for real.

## Limitations

- **Not production-safe.** No CSRF protection, no prepared-statement audit, no input
  validation layer. **Do not deploy this and take real payments.**
- No migrations — schema setup is manual.
- No tests.
