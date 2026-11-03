# TrackSpend multi-agent coordination

This folder coordinates work on TrackSpend. It is not application code and must not be deployed.

## Repository baseline
- Repository: https://github.com/em757896-alt/emmanuel-tech-portfolio
- Project path: `projects/applications/mobile apps/trackspend-native/`
- Coordination branch created from `main` at commit `cdacd09886501f2396808f04bfbf1fccbed2cac6`.
- Production/default branch: `main`. Do not push implementation changes directly to it.

## Read first
1. [MASTER_CONTEXT.md](MASTER_CONTEXT.md)
2. The relevant agent work order in this folder.
3. The full approved Backend Development Addendum v1.5 and the relevant v1.0 specifications supplied by the owner.
4. The current source package and Kimi backend package supplied by the owner.

## Team roles
- **Gemini:** independent architecture/specification audit and cross-check. Do not edit the same SQL files as the implementation agent.
- **Kimi:** primary backend implementation agent, assigned one bounded work package at a time.
- **Claude:** independent QA design, API/Android contract review and targeted code review. It is not the owner of database migrations.
- **ChatGPT:** coordinator, integrator and independent auditor. Reviews diffs, test evidence and consistency before any merge.
- **GitHub Copilot Free:** optional local autocomplete only. Its Free plan does not include the hosted Copilot coding agent / cloud PR workflow; do not make the project depend on it.

## Collaboration protocol
- GitHub is the handoff record, not shared model memory. Agents do not automatically see other agents' chats.
- Each agent returns a named artifact: patch/PR, changed-file list, test commands and real outputs, known gaps, and status.
- One owner per file. No two agents edit the same migration or API contract simultaneously.
- All implementation work starts from a named feature branch. Submit a pull request to the coordination/integration branch for review.
- No agent merges its own PR. ChatGPT audits; the owner approves merges.
- Never push to `main`, run production migrations, deploy, publish an APK, or change live services without the owner's explicit approval.
- Never put secrets, keys, database URLs, tokens, keystores, private user data or production exports in prompts, issues, commits or chat.

## First milestone
Do not start parallel feature coding yet. First reconcile the uploaded Kimi backend package and the latest login codebase against the current GitHub tree. The owner-provided Kimi ZIP contains backend work that is not yet confirmed to exist in the GitHub project path. Identify the exact baseline and file ownership before opening implementation work packages.
