# TrackSpend master context for AI agents

Last updated: 2026-11-03

## Product and working constraints
TrackSpend is a Kenya-first native Android personal-finance application. The owner works primarily from an Android phone, prefers free tools and low-cost infrastructure, and wants to finish the first production-capable release without repeatedly restarting the project.

Preserve the existing login work and visible in-app error log. Work phase by phase against the approved roadmap. Keep instructions mobile-friendly and avoid requiring desktop-only workflows where a browser/mobile alternative exists.

## Source hierarchy
The approved product specifications are authoritative, not whichever code or README is newest:
1. TrackSpend PRD v1.0
2. App Flow and Screen Structure v1.0
3. Technical Architecture and Technology Stack v1.0
4. Database Design v1.0
5. API Contracts and Backend Workflows v1.0
6. Security, Privacy and Testing Strategy v1.0
7. Implementation Roadmap v1.0
8. 31 October 2026 decision/update addendum, including the 17 security and launch gates
9. Backend Development Addendum v1.5 and MASTER_BACKEND_BUILD_PROMPT_v1_5 (latest backend authority)

The v1.5 Addendum explicitly says code, legacy migrations, README claims and deployed behaviour do not override approved requirements.

## Approved target architecture (v1.5)
- Android: Kotlin + Jetpack Compose.
- Identity: Supabase Auth as the single authoritative identity provider.
- Primary financial database: Supabase PostgreSQL.
- Data access: ordered SQL migrations, constraints, indexes, triggers, RLS and controlled RPC functions.
- Money: BIGINT integer minor units end-to-end; Android uses Long. Never Double/float for financial values.
- Ledger: double-entry, balanced postings, immutable posted history, reversals rather than editing posted entries.
- Private files: Supabase private Storage initially. Evaluate Cloudflare R2 later only if justified and after equivalent access controls are designed.
- Android offline support: Room cache/drafts/sync outbox and WorkManager retryable synchronization.
- Supporting public website: Cloudflare Pages. This does not host the Android application and does not replace Supabase.
- Gmail support Worker is support-only. Supabase Auth handles verification, recovery and authentication emails with production SMTP configured later.
- No new production dependency on Clerk or Neon. Preserve and label legacy code safely; do not delete history blindly.

## Conflicting legacy material
Some handoff files, setup notes and older repository instructions mention Clerk/Neon or an earlier architecture. These are historical and are superseded by Backend Development Addendum v1.5. Do not implement Clerk/Neon as the target. If there is a genuine contradiction between approved owner documents, stop and report it; do not silently choose.

The Kimi package includes a GAP_REGISTER and a HANDOFF with conflicting architecture statements. The GAP_REGISTER aligns with v1.5; the older HANDOFF text mentioning Clerk/Neon does not. Follow v1.5 and record the conflict.

## Current supplied baselines
- `TrackSpend_Codebase_2026-11-02_login-1.0.2.zip`: latest login-phase Android/backend/site source package.
- `Kimi_Agent_Trackspend.zip`: Kimi's prior backend implementation and its handoff/gap register.
- `TrackSpend_Backend_Development_Pack_v1_5.zip`: approved product documents, addenda and backend v1.5 instructions.

The owner supplied these archives in the ChatGPT project conversation. Do not assume another AI platform can see those uploads. Give each agent only the relevant source/docs through its own upload or the shared GitHub branch.

## Known baseline facts from the package review
- The Android client is still on a simple transaction model that uses floating-point Double; it is not yet integrated with the new ledger API.
- The login phase is the protected baseline; preserve login/sign-up/sign-out and the in-app error log unless a specific, tested correction is necessary.
- The Kimi package contains Supabase migrations, RLS, posting functions and SQL tests, but they require independent review and remediation before staging.
- The Kimi archive contains legacy Neon/Clerk material in some handoff/setup files. Those references must be isolated/documented, not followed as target architecture.
- The current GitHub repository is `em757896-alt/emmanuel-tech-portfolio`, project folder `projects/applications/mobile apps/trackspend-native/`. The current GitHub main tree has not yet been verified to contain the full Kimi backend package. Reconcile before coding.
- The repository's existing README/AGENTS and ZIP handoff files may be stale. Treat approved v1.5 docs as higher authority.

## Non-negotiable safety rules
1. Never connect to or modify the production Supabase project during development.
2. Never run migrations against production.
3. No deployment, domain/DNS changes, live provider setup or APK release as part of backend implementation.
4. Never commit secrets or ask the owner to paste secrets into chat.
5. Never include the Supabase service-role key in Android/public code.
6. All user-owned rows and private objects must have tested ownership isolation.
7. Never trust a client-supplied user ID; derive ownership from auth.uid().
8. Financial writes must go through trusted, validated, idempotent functions.
9. Do not alter posted journal history; correct with reversals.
10. No production-ready claim without test evidence and all applicable launch gates.
11. Preserve the in-app error log in every build.
12. Do not silently omit approved PRD features or change product requirements.

## Required agent output
Every work package must return:
- exact baseline commit/branch and files inspected;
- files changed and why;
- requirement-to-implementation mapping;
- commands actually run and complete relevant outputs;
- tests passed, failed, skipped or blocked (with reasons);
- security implications and known limitations;
- remaining work;
- a patch or pull request that can be reviewed independently.

Status vocabulary: **implemented and verified**, **implemented but not verified**, **partially implemented**, **blocked by external configuration**, **not implemented**.
