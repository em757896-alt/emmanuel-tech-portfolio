# Elevate Retail POS & Inventory Management System

**A cloud-hosted retail operations portfolio application by Elevate Media Productions.**

The former Student Management System has been superseded as the active application by a retail inventory and point-of-sale system. Its historical source files and screenshots are retained in this repository for traceability; the new application lives in `pos/`.

## Project goals

Provide a clear, professional demonstration of the workflows used by a small retail business:

- Role-based staff access: administrator, manager and cashier
- Product catalogue with SKU, barcode, category, cost, selling price and reorder level
- Inventory quantities and stock movement ledger
- Point-of-sale cart and checkout
- Cash, card and mobile-money payment method recording
- Receipt references, discounts, tax calculation and change due
- Supplier directory
- Sales summaries and low-stock reporting
- Audit log for important actions

## Technology

| Layer | Implementation |
|---|---|
| Server | PHP 8.x |
| Database | MySQL / MariaDB |
| Data access | PDO with native prepared statements |
| UI | HTML5, CSS3, vanilla JavaScript |
| Responsive styling | Custom CSS |
| Hosting target | InfinityFree PHP + MySQL shared hosting |

The frontend uses no Node.js server or build step. This is intentional for shared PHP hosting. External font loading is optional; the interface has local system-font fallbacks.

## Current implementation status

Implemented in the initial portfolio release:
- Secure first-administrator setup with password hashing
- Login/logout with session ID regeneration
- Role checks for administrator, manager and cashier
- CSRF validation on state-changing forms
- Product and category creation
- Supplier creation
- Stock adjustment with movement history
- POS cart and server-side checkout validation
- Database transaction around sale creation and stock deduction
- Sales receipt references and transaction listing
- Dashboard metrics and low-stock indicators
- Audit records for key operations

Not yet implemented in the interface and must not be represented as complete:
- Purchase receiving workflow (schema foundation exists)
- Returns/refunds and void approvals
- Barcode scanner hardware integration
- Receipt printer integration
- Multi-branch inventory
- Tax-compliance configuration for a real jurisdiction
- Automated backups, monitoring and production service-level guarantees

## Installation on InfinityFree

1. Create a MySQL database in the InfinityFree control panel.
2. Open phpMyAdmin for that database and import `pos/schema.sql`.
3. Copy `db.example.php` to `db.php` in this project folder.
4. Fill `db.php` with the exact host, database name, username and password shown in the hosting control panel. **Never commit `db.php` or publish database credentials.**
5. Upload the contents of this project folder so that `index.php` is the folder entry point and the `pos/` directory is alongside it.
6. Visit `/pos/setup.php` once and create a unique administrator password of at least 12 characters.
7. Sign in at `/pos/index.php?page=login`.
8. Delete `pos/setup.php` from the live server immediately after creating the administrator.
9. Add categories and products, then test with dummy transactions only.

The project expects the database configuration file at `db.php` in this folder. It is intentionally not included in source control.

## Security and operational notes

- Use HTTPS for every login and transaction.
- Keep database credentials outside Git.
- Use a unique password for each staff account; do not share administrator credentials.
- Do not load real customer, payment-card or commercially sensitive records into the free-hosting demo.
- The application records payment method and amount received; it does **not** process card or mobile-money payments.
- A successful test on GitHub does not prove the InfinityFree deployment is configured. Verify the live database connection, setup, login, sale and stock deduction after upload.
- Shared free hosting is suitable for a learning/demo deployment, not a dependable production POS for a live shop.

## Database model

Core tables: `users`, `categories`, `suppliers`, `products`, `stock_movements`, `sales`, `sale_items`, `purchases`, `purchase_items`, `store_settings`, and `audit_logs`.

The checkout operation locks product rows, validates available stock, writes the sale and line items, deducts inventory and records stock movements inside one database transaction. If an operation fails, the transaction is rolled back.

## Project structure

```
student-management-system/
├── pos/
│   ├── assets/app.css
│   ├── bootstrap.php
│   ├── index.php
│   ├── schema.sql
│   └── setup.php
├── db.example.php
├── db.php                 # private local/live configuration; never commit
├── index.php              # redirects to the POS application
└── [legacy student-system source and screenshots]
```

## Interview explanation

This project demonstrates a practical PHP/MySQL business application. The most important engineering decision is that stock is not decremented by browser JavaScript: the server re-reads and locks product rows, checks stock, and commits the sale and inventory movements together. This protects against stale browser quantities and concurrent checkout races at the database level.

The current version is a portfolio MVP. Explain clearly which workflows are implemented and which are planned; do not claim real payment processing, production reliability or full accounting functionality.
