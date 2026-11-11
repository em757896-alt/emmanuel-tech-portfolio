# Cloud-Based Inventory & Retail POS

A PHP 8.1+ and MySQL/MariaDB retail point-of-sale application for small businesses. This project is hosted separately from the legacy student-management system and uses its own database schema.

## Current application modules

- Staff authentication with role-based access (administrator, manager, cashier)
- Product catalogue, SKU/barcode fields, categories, pricing and stock levels
- Point-of-sale cart, discounts, payment method selection, payment/change validation and receipt records
- Stock movement ledger for opening stock, sales, purchases, returns and adjustments
- Supplier directory and purchase receiving
- Sales history, receipt printing, voiding with reason and stock restoration
- Dashboard, low-stock alerts and basic operational reports
- Administrator-managed staff accounts and audit events

## Runtime requirements

- PHP 8.1 or later with PDO MySQL enabled
- MySQL 5.7+/MariaDB with InnoDB and utf8mb4
- HTTPS-enabled hosting
- A private database configuration file at the parent directory: `../db.php`

The private configuration must return an array with `host`, `dbname`, `user`, `pass`, and optional `port` and `charset` keys. Never commit this file or put credentials in this repository.

## First installation

1. Create a dedicated empty MySQL database and database user with access only to that database.
2. Import `schema.sql` into that empty database. **The schema contains DROP TABLE statements and must not be imported into a database containing data you need to keep.**
3. Create the private `../db.php` configuration on the server.
4. Deploy the POS directory to the PHP web root.
5. Open `setup.php` once and create the first administrator using a unique password of at least 12 characters.
6. Sign in at `index.php?page=login`.
7. Remove or rename `setup.php` on the server after administrator creation. The setup endpoint also refuses to run once any user exists.
8. Confirm HTTPS is active and verify that backups are being made and can be restored.

## Roles

| Capability | Administrator | Manager | Cashier |
|---|---:|---:|---:|
| Create/update products and categories | Yes | Yes | No |
| Adjust stock and receive purchases | Yes | Yes | No |
| Create sales and print receipts | Yes | Yes | Yes |
| View sales history and reports | Yes | Yes | Yes |
| Void a completed sale (reason required) | Yes | Yes | No |
| Create/deactivate staff accounts | Yes | No | No |

## Security controls present in source

- PDO prepared statements with native prepares
- Password hashing and verification using PHP password APIs
- CSRF tokens on state-changing forms
- Role checks on privileged operations
- Session strict mode, cookie-only sessions, HttpOnly and SameSite cookies
- Session idle and absolute lifetime limits, session ID rotation at login, logout invalidation
- Security response headers and HTTPS HSTS header
- Login failure throttling by client IP using audit events
- Transactional stock/sale operations and audit logging
- Generic database error shown to visitors; detailed failure goes to server error log

## Important production validation

A source-code review is not a live security certification. Before treating a deployment as production-ready, validate the actual hosted PHP version, HTTPS headers, database permissions, login throttling, CSRF rejection, role enforcement, concurrent checkout/stock behavior, receipt printing, backups/restore and mobile browser interaction.

Cloudflare Turnstile is a required project security standard, but this source snapshot does not include a configured Siteverify integration or live site/secret keys. Do not represent Turnstile as active until the server validates tokens with Cloudflare and the live pages render the widget. Keep all secrets server-side.

## Data and operational cautions

- Do not expose `db.php`, database credentials, backups or production logs publicly.
- Restrict database permissions to the dedicated POS database.
- Back up the database before schema changes.
- The current schema is an initial-install schema; do not re-import it over live business data.
- Configure the store name, currency and tax rate in `store_settings` only after confirming the applicable local tax rules.
- Void is an auditable correction that restores stock; it is not an integrated payment-provider refund.

## Project identity

Product name: **Cloud-Based Inventory & Retail POS**  
Currency default: KES  
Publisher: Elevate Media Productions
