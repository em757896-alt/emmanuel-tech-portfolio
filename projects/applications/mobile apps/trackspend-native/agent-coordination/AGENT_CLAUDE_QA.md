# Agent work order — Claude (independent QA and integration-contract reviewer)

## Role
Independent test-design and Android/API contract specialist. Do not own database migrations and do not make overlapping edits to Kimi's SQL.

## Inputs
- MASTER_CONTEXT.md
- Backend Development Addendum v1.5
- Database Design v1.0
- API Contracts v1.0
- Security, Privacy and Testing Strategy v1.0
- Latest login codebase, Kimi backend package and Gemini findings when available

Claude Free does not need the ZIP archive. Supply the relevant extracted Markdown/PDF specifications and individual SQL/Kotlin files in manageable batches. Do not assume a ZIP upload or persistent cross-chat memory.

## Assignment
1. Create a test matrix for two-user RLS isolation, ledger invariants, idempotency, opening balances, transfers, loans, receivables, reversals, drafts/imports, storage ownership and account lifecycle.
2. For each test, specify setup, actor, action, expected database state and failure condition.
3. Review the Android model/repository layer and define the migration from Double amounts to Long integer minor units without changing the login flow.
4. Define typed API/RPC contracts for Android repositories, including validation and error mapping.
5. Identify offline-first requirements using Room, sync outbox and WorkManager; do not invent server-side replacements.
6. Review the privacy implications of SMS parsing, notification permissions and local financial-data storage.
7. Review the backend test suite for false positives and missing negative tests.

## Restrictions
- Review/design only unless a separate patch task is authorised.
- Do not edit Kimi-owned SQL migrations.
- Do not claim device testing or database execution that did not occur.
- No secrets, production changes or new paid dependencies.
- Follow v1.5; no Clerk/Neon target.

## Deliverables
1. `CLAUDE_TEST_MATRIX.md`
2. `ANDROID_API_INTEGRATION_CONTRACT.md`
3. `CLAUDE_REVIEW_FINDINGS.md` with exact file/line evidence.
4. A list of tests that must block staging/release.
