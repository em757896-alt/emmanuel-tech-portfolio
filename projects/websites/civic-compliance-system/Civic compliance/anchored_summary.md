# PBO Kenya Platform — Anchored Summary

## Goal
- Complete the PBO Kenya Platform by fixing all remaining SQL/column mismatches between code and schema, creating all missing pages referenced from navigation/links, and making the project fully functional for its live InfinityFree deployment.

## Constraints & Preferences
- Keep the existing format/style of every file — do not change the current look and feel
- Use the existing database password (`AES256:4m0deNaMM0HA+yKw/HIgbYzFLvAjq8o1cD7cfheTaOSB8M/MqTc/Edx85mfbuzOL`) from `config/database.php` in all new files that need database access (via the centralized config includes)
- The live database on InfinityFree already matches `schema.sql` — fix code queries to match schema column names, not vice versa
- Create all pages referenced in navigation and links so the website becomes functional end-to-end
- Follow existing patterns: `includes/navbar.php`, `includes/footer.php`, Bootstrap 5, Font Awesome, AOS animations, same CSS classes

## Progress
### Done
- Fixed `SECRET_KEY` in `config/config.php`: replaced `bin2hex(random_bytes(32))` (regenerated on every load) with a static 64-char hex string
- Added 6 helper functions to `config/config.php`: `sanitizeInput()`, `checkRateLimit()`, `generateCSRFTokenValue()`, `validateCSRFToken()`, `generateCSRFToken()`, `generateCSRFField()`
- Added `requireAdmin()` method to Auth class in `config/auth.php` and a global `requireAdmin()` function
- Updated `database/schema.sql`: added `monitoring_attachments` table and `report_data` column to `monitoring_reports`
- Fixed SQL column name mismatches across 8 existing admin/API files to match `schema.sql`:
  - `admin/dashboard.php` — 7 queries fixed
  - `admin/knowledge/index.php` — fixed publish/unpublish/feature actions, filters, table display, editor form, removed type filter
  - `admin/monitoring/index.php` — SELECT, filters, ordering fixed
  - `admin/monitoring/view.php` — moderation actions, related reports fixed
  - `admin/reports/export.php` — SELECT aliases, county summaries fixed
  - `admin/chatbot/index.php` — table renames, column fixes, KB INSERT fixed
  - `admin/analytics/index.php` — 6 column fixes, removed `knowledge_categories` join
  - `api/monitoring.php` — INSERT columns, email config fixed
- Fixed `modules/knowledge-hub/article.php`: removed `knowledge_categories` JOIN, replaced all old column names with schema columns, removed dead `key_takeaways` block
- Fixed `modules/knowledge-hub/index.php` SQL: removed `knowledge_categories` join, replaced column names, category filter uses ENUM labels
- Fixed `modules/monitoring/index.php`: `submitter_county`→`county`, `status='approved'`→`status='verified'`, `privacy-policy.php`→`/privacy.php`
- Fixed `api/admin-dashboard.php`, `api/health-check.php`, `admin/reports/export.php`: `status='published'`→`is_published=1`
- Fixed `api/health-check.php`: removed `knowledge_categories` from required tables list
- Fixed `api/admin-moderation.php`: table renames, column fixes for chatbot moderation
- Fixed `api/admin-dashboard.php` chatbot queries: table renames, column fixes
- Created all missing directories: `uploads/`, `uploads/documents/`, `uploads/images/`, `uploads/monitoring/`, `logs/`, `auth/`, `errors/`, `modules/dashboard/`, `assets/js/`, `admin/includes/`, `admin/incidents/`, `admin/resources/`, `admin/faqs/`, `admin/settings/`, `admin/analytics/`
- Created auth pages: `auth/login.php`, `auth/register.php`, `auth/logout.php`
- Created error pages: `errors/403.php`, `errors/404.php`
- Created root pages: `about.php`, `dashboard.php`, `profile.php`, `search.php`, `privacy.php`, `terms.php`, `contact.php`, `accessibility.php`
- Created module pages: `modules/knowledge-hub/resources.php`, `faqs.php`, `multimedia.php`, `modules/compliance-tools/registration.php`, `self-assessment.php`, `templates.php`, `modules/monitoring/report.php`, `modules/monitoring/incident.php`, `modules/dashboard/index.php`
- Created admin pages: `admin/includes/admin-sidebar.php`, `admin/incidents/index.php`, `admin/resources/index.php`, `admin/faqs/index.php`, `admin/settings/index.php`, `admin/analytics/county.php`, `admin/index.php`
- Created API endpoint: `api/subscribe.php`
- Created asset files: `assets/js/main.js`, `assets/js/chatbot.js`
- Fixed auth CSS/HTML: login and register pages now use the proper `auth.css` classes (split-panel layout), removed forgot-password link
- Fixed auto-login after registration in `auth.php`
- Fixed session cookie settings in `config.php` (conditional `cookie_secure`)
- Added welcome messages on dashboard for new users and returning users
- Added `session_write_close()` before redirects in login/register

### In Progress
- (none)

### Blocked
- `uploads/` and `logs/` directories need to be created on InfinityFree server via file manager (they are empty in local repo but must exist and be writable on live server)
  - `logs/` — stores rate-limiting temp files, PHP error logs
  - `uploads/`, `uploads/documents/`, `uploads/images/`, `uploads/monitoring/` — stores submitted attachments (the `api/monitoring.php` auto-creates YEAR/MONTH subdirs via `mkdir(0755,true)` but parent must exist)

## Key Decisions
- Static `SECRET_KEY` value used instead of regenerating per request (was breaking all token-based functionality)
- Centralized `DB_PASS` from `config/database.php` used in all new files via `require_once` — no hardcoded passwords in new code
- Fixed code SQL to match `schema.sql` column names (not vice versa) because the live InfinityFree database matches `schema.sql`
- Used `monitoring_attachments` table and `report_data` column added to schema since multiple existing code paths reference them
- New pages follow the exact template pattern of existing files: same includes, same Bootstrap/FontAwesome/AOS stack, same CSS classes
- Admin sidebar created as shared include (`admin/includes/admin-sidebar.php`) computing its own stats when `$stats` not set by the parent page
- `modules/knowledge-hub/index.php` category system rewritten from DB-driven (`knowledge_categories` table) to static ENUM labels (`$categoryLabels` array) since the schema uses an ENUM column on `knowledge_articles` with no separate categories table

## Next Steps
1. Create the `uploads/` and `logs/` directories on InfinityFree via cPanel file manager and set permissions to 755 (or 777 if PHP cannot write)
2. Populate the database with initial data: seed `knowledge_articles`, `resources`, `faqs`, `monitoring_reports`, `users`, and `settings` tables
3. Stage and deploy all changed/new files to InfinityFree

## Critical Context
- **Database host**: `sql303.infinityfree.com`, **DB name**: `if0_42280606_if0_42280606_`, **DB user**: `if0_42280606`, **DB pass**: `AES256:4m0deNaMM0HA+yKw/HIgbYzFLvAjq8o1cD7cfheTaOSB8M/MqTc/Edx85mfbuzOL`
- **Application URL**: `https://pbokenya.infinityfreeapp.com`
- The Auth class constructor calls `session_start()` conditionally — already handled
- `generateCSRFToken()`, `generateCSRFField()`, `generateCSRFTokenValue()`, `validateCSRFToken()` are now global functions in `config/config.php`; `Auth::generateCSRF()` and `Auth::verifyCSRF()` are instance methods on Auth class
- `requireAdmin()` is both an Auth class method and a global function (creates Auth instance internally)
- The navbar links to: `about.php`, `dashboard.php`, `profile.php`, `search.php`, `modules/*`, `auth/*` — all now exist
- Modules monitoring page (`modules/monitoring/index.php`) already existed and handles 4 report types in one form (compliance, barrier, incident, enabling) via AJAX to `api/monitoring.php`; the new `report.php` and `incident.php` are standalone traditional-POST alternatives
- The `config/database.php` `Database` class has both a generic `query()` method and `getConnection()` returning the raw PDO instance
- `knowledge_articles` schema: `title_en`/`title_sw`, `summary_en`/`summary_sw`, `content_en`/`content_sw`, `category` ENUM (not FK), `is_published`, `is_featured`, `pbo_act_section`, `view_count`, no `content_type`, `read_time_minutes`, `has_kiswahili`, `status`, `featured` columns
- `chatbot_conversations` (not `chatbot_logs`): columns `user_message`, `bot_response`, `flagged_for_review`, `feedback`, etc.

## Relevant Files
- `config/config.php`: central config, SECRET_KEY, all 7 helper functions, session settings
- `config/database.php`: Database singleton, PDO connection with live credentials
- `config/auth.php`: Auth class with login/register/logout, session management, auto-login after registration
- `database/schema.sql`: 15 tables updated with monitoring_attachments + report_data
- `includes/navbar.php`: main navigation — links must stay in sync with created pages
- `includes/footer.php`: 4-column footer with module links + disclaimer
- `assets/css/style.css`: main site styles
- `assets/css/auth.css`: auth page split-panel layout (now properly used by login/register)
- `assets/css/admin.css`: admin panel styles (located at `admin/assets/css/admin.css`)
- `modules/knowledge-hub/index.php`: front-end knowledge hub — rewritten to use schema columns
- `modules/knowledge-hub/article.php`: single article view — rewritten to use schema columns
- `admin/knowledge/index.php`: admin knowledge management — rewritten queries + form
- `admin/includes/admin-sidebar.php`: shared admin sidebar used by all admin sub-pages
- `assets/js/main.js`: shared frontend JavaScript utilities
- `assets/js/chatbot.js`: chatbot widget JavaScript
