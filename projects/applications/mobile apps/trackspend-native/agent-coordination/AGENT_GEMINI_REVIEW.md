# Agent work order — Gemini (independent architecture and code auditor)

## Role
Independent reviewer. Do not become a second implementation owner and do not edit the same migrations as Kimi.

## Inputs
- MASTER_CONTEXT.md
- Backend Development Addendum v1.5
- MASTER_BACKEND_BUILD_PROMPT_v1_5
- Database Design v1.0
- API Contracts v1.0
- Security, Privacy and Testing Strategy v1.0
- 31 October decision log and 17 launch gates
- Kimi backend source and latest login codebase

## Assignment: baseline reconciliation and risk audit
1. Compare the approved v1.5 target architecture with every architecture/handoff/setup file in the supplied source packages.
2. Identify every Clerk/Neon reference and classify as historical legacy, active dependency, misleading documentation or unresolved.
3. Audit migration ordering and dependencies.
4. Independently inspect RLS, SECURITY DEFINER functions, posting/idempotency logic, balances/views, opening balances, reversals and ownership checks.
5. Trace the Android transaction model to the intended BIGINT minor-unit ledger API. Identify every required integration boundary.
6. Map all approved backend requirements to files and evidence.

## Known issues to verify, not blindly repeat
- Balance view security/invoker behaviour may prevent authenticated balance reads when journal tables have no client SELECT policy.
- Financial overview may reference a column name not exposed by its underlying view.
- Opening-balance RPC may permit repeated initial postings.
- Direct writes may bypass trusted ledger workflows.
- Expired-session test may not actually model a revoked/expired Supabase session.

Verify each issue in source before reporting it. Find additional defects too.

## Restrictions
- Review only. Do not rewrite migrations or modify production.
- Do not treat an SQL file existing as proof the feature works.
- Do not claim tests passed unless you ran them and can show commands/output.
- Do not recommend Clerk/Neon as the target architecture.

## Deliverables
1. `GEMINI_BASELINE_AUDIT.md` with severity, file/line evidence and reproducible reasoning.
2. Requirement coverage matrix.
3. Top 10 release blockers ordered by severity.
4. Suggested test cases for each critical finding.
5. A short list of questions that genuinely require owner decisions.

Do not start implementation. Send findings to the coordinator for reconciliation with the other reviews.
