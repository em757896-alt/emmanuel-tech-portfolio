# Agent work order — Kimi (primary backend implementer)

## Role
Primary implementation agent for one bounded backend work package at a time. Start with WP0, then wait for the coordinator to approve the WP1 implementation scope. Do not attempt the whole product in one session.

## Inputs
- MASTER_CONTEXT.md
- Backend Development Addendum v1.5
- MASTER_BACKEND_BUILD_PROMPT_v1_5
- Kimi backend package and latest login codebase
- Gemini's baseline audit when available
- Claude's test matrix when available

## WP0 — baseline inventory (first task; no feature coding)
1. State the exact archive/file tree and source commit you are using.
2. Compare the Kimi package to the latest login codebase and the current GitHub project path.
3. Identify files present only in Kimi's package, only in the latest codebase and in both.
4. Resolve architecture documentation conflicts using v1.5 as the authority.
5. Produce a file ownership plan and migration dependency map.
6. Do not run or connect to production.

Return WP0 before starting WP1.

## WP1 — database/RLS/ledger remediation (only after coordinator approval)
- Correct balance retrieval without exposing journal rows across users.
- Fix all broken financial overview/view references.
- Make opening balance a one-time, concurrency-safe operation.
- Restrict direct writes that could bypass journal posting.
- Audit all SECURITY DEFINER functions, fixed search_path, grants and auth.uid() ownership.
- Ensure every posting balances, uses integer minor units and is idempotent.
- Ensure reversals preserve immutable posted history.
- Add regression tests for each fix.

## Later work packages (not authorised in WP1)
- WP2: accounts/categories/transactions/transfers/loans/receivables/drafts.
- WP3: imports, reconciliation, recurring transactions, bills, goals.
- WP4: reports/exports, audit, devices/session design, notifications, entitlements and provider adapters.
- WP5: docs, CI, migration/recovery guide and integration handoff.

Do not begin later packages until the coordinator accepts the preceding package.

## Required delivery
- Work on a dedicated feature branch, never main.
- Submit a PR or a complete patch with exact changed-file list.
- Include actual commands and test output; mark unavailable infrastructure as blocked.
- Do not touch Android login files unless the task explicitly authorises an additive integration patch.
- Do not change live Supabase, Cloudflare, payment, SMTP or Google provider settings.
- Never include secrets.
- Update SESSION.md and IMPLEMENTATION_STATUS.md at each milestone.
