# Elevate Media Productions — Security Baseline

This baseline applies to every public website and application maintained in this repository. Controls must be implemented and verified per project; this document is not a claim that every control is already deployed.

## Mandatory baseline
- HTTPS everywhere; HSTS only after HTTPS is confirmed across the hostname.
- Authentication: password hashing with Argon2id/bcrypt/PASSWORD_DEFAULT, safe account recovery, MFA for privileged accounts, no public default admin credentials.
- Authorization: server-side role/ownership checks on every protected route and mutation.
- Sessions: strict mode, cookie-only sessions, Secure + HttpOnly + SameSite, rotate IDs after authentication/privilege changes, idle and absolute expiry, full cookie invalidation on logout.
- Abuse protection: rate limits on login, registration, password reset, contact forms and other expensive endpoints; Cloudflare Turnstile (never Google reCAPTCHA) on public abuse-prone forms, with server-side Siteverify and fail-closed behavior in production.
- Data access: parameterized queries; allowlists for dynamic SQL identifiers/order clauses; least-privilege database credentials.
- Request integrity: CSRF protection for state-changing browser requests; origin checks where appropriate.
- Input/output: validate server-side, enforce size/type limits on uploads, escape output by context, prevent stored/reflected XSS.
- Browser hardening: CSP tailored to each app, frame restrictions, nosniff, strict referrer policy, restrictive Permissions-Policy, no-store for authenticated pages.
- Secrets: no keys/passwords in source, commits, logs, screenshots or client bundles; rotate any credential disclosed in chat or committed.
- Privacy: data minimization, retention/deletion rules, access controls and a clear incident/breach process.
- Operations: tested backups and restore procedure, dependency updates, error monitoring, security audit logs with secrets/financial details excluded.
- Verification: test unauthenticated access, role bypass, SQL injection, XSS, CSRF, brute force, session expiry/fixation, upload abuse, backup restoration and mobile accessibility.

## Project-specific rollout
- Cloud-Based Inventory & Retail POS: financial/inventory integrity, cashier/manager/admin separation, stock transaction locking, immutable audit trail, login throttling, Turnstile, session hardening and reliable backups. Shared free hosting is for evaluation only, not production retail operations.
- TrackSpend: financial-data minimization, strict tenant isolation, Keycloak MFA/passkeys, recovery security, device/session controls, auditability, encrypted backups and mobile API authorization.
- Civic Compliance Hub: protect compliance assessments, incident reports and uploaded evidence; strict tenant boundaries, retention controls, secure uploads and qualified legal review of legal guidance.
- Elevate Media University: protect learner accounts/progress, educator/admin roles, course/content ownership, safe uploads, privacy for learner records and abuse controls on community/course interactions.
- Elevate Media Productions public website: secure contact/newsletter/community endpoints, moderator/admin access, spam prevention, consent and unsubscribe handling, privacy-safe analytics.

## POS implementation notes
The POS uses PDO native prepared statements, password_hash/password_verify, CSRF tokens, role checks and transactional stock updates. The current hardening change adds strict cookie-only sessions, idle/absolute session expiry, security response headers, login throttling based on audit events, CSRF rotation after login and cookie invalidation on logout. Cloudflare Turnstile keys still need to be provisioned in Cloudflare and wired into the private server configuration before production use. Test the deployed PHP version and checkout flow on the actual hosting provider; repository changes alone do not deploy or prove runtime behavior.
