# Elevate Media - Student Management System

## READ THIS FIRST — Session History & Mistakes

### What This AI Did Wrong (Repeatedly)
1. **Said "everything is working" without testing.** Checked HTTP 200 on pages and API responses, but NEVER tested the actual login → redirect → dashboard flow. The user had to discover that logging in as a student redirected to the wrong page.
2. **Ignored clear instructions.** User said 5+ times: "student-login should redirect to student-dashboard, teacher-login to teacher-dashboard, admin-login to admin-dashboard, each portal ONLY shows links related to itself." AI kept adding cross-links between portals.
3. **Named the admin login page just `/login`** instead of `/admin-login`. User explicitly said all 3 portals need their own named pages.
4. **Fixed the wrong things first.** Spent time on non-critical issues while the core redirect flow was broken.
5. **Never ran end-to-end tests.** Would deploy, check pages load (HTTP 200), check API returns data, then declare "verified." Never simulated an actual login session with cookies and tested where the user lands.
6. **The root cause of the redirect failure:** The middleware was using `getToken()` from `next-auth/jwt` which couldn't read the v5 session cookies. Fixed by switching to `auth()` wrapper from `@/lib/auth`. Also, login pages used `router.push()` (client-side) which doesn't trigger middleware — fixed with `window.location.href` (full page reload).

### Current State (as of last deploy)
- **FACULTY HOD SYSTEM + LOGIN/CRASH FIXES: BUILT, DEPLOYED, E2E-VERIFIED LIVE (2026-08-04, session 2):**
  - **Patricia `/teacher` crash FIXED:** root cause was `teacher/page.tsx` reading `sub.assignment`/`sub.student` while `/api/teacher/dashboard` returned `assignments`/`students` (Supabase embed names). `sub.assignment` was `undefined` → TypeError → "couldn't load" on any teacher WITH a pending submission. Fixed by normalizing the API payload (`assignment`/`student` aliases with `Array.isArray` guard). `/api/submissions` and `/api/student/dashboard` already normalized. ✅ Verified: Patricia (T2026003) login → `/api/teacher/dashboard` 200 → `/teacher` page 200.
  - **Teacher login now REQUIRES Employee ID (Email + Employee ID + Password):** `auth.ts` added `employeeId` credential + `findTeacherByEmployeeId`; teacher path matches by Employee ID, verifies the email belongs to that teacher, then bcrypt. The email-only branch is now **ADMIN-only** (`role !== "ADMIN"` returns null), so teachers can no longer log in without Employee ID. Admin login unchanged (email+password). ✅ Verified: Patricia/Jane/Sarah login with Employee ID; Patricia WITHOUT Employee ID → session null; admin login OK.
  - **Daniel is now a FACULTY HOD of Faculty of Social Sciences:** seed `POST /api/seed/social-sciences` re-run (idempotent) created faculty `seed-fac-social`/SOC, linked dept SP, set `faculties.hodTeacherId` = Daniel's teacher id, `teachers.facultyId`, and now overwrites `isHod`/`departmentId`/`facultyId` on EXISTING teacher rows (was insert-only before). ✅ Verified live: Daniel session shows `facultyHod:true, facultyId:seed-fac-social`.
  - **HOD dashboard = Faculty Overview:** `/api/teacher/hod/dashboard` now returns `{ scope: FACULTY|DEPARTMENT, stats (dept/course/student/pending counts), departments: [{id,name,code,courses:[{...,_count.enrollments}]}] }` — faculty HOD sees every department under the faculty, dept HOD sees just their dept. `/teacher/hod` page renders Departments & Courses grouped. ✅ Verified: Daniel → Faculty of Social Sciences, 1 dept, PAD301; Sarah → Computer Science dept, CS101+CS102.
  - **Faculty Students:** `/api/teacher/hod/students` now returns nested `departments → courses → students` (was flat, dept-only). Page renders the nested tree with Verified/Unverified badges. ✅ Verified: Daniel → dept SP → PAD301 → John Doe (EM20261001).
  - **Approvals:** `/api/teacher/hod/approvals`, `/api/poe?scope=approvals`, `/api/attendance?action=approvals` were already faculty-scoped; page now shows Faculty/Department Approvals title + scope badge. ✅
  - **HOD sidebar/courses:** sidebar labels switch Department↔Faculty based on `session.user.facultyHod`; `/teacher/hod/courses` rebuilt for the new API shape.
- **Student auth now uses Supabase email verification** (added earlier, verified end-to-end via `verify-auth-flow.ps1`):
  - `POST /api/register` → 201, assigns unique Adm No (`EM{year}{random4}`), sends activation email. ✅
  - Unverified student login → blocked (session null). ✅
  - 3-field login (Email + Adm No + Password) for student `EM20261001` → `/dashboard` 200. ✅
  - Forgot password returns the SAME generic message whether or not email+admNo match (no account enumeration). ✅
  - Teacher (`T2026001`) and Admin logins unchanged (bcrypt path). ✅
  - `/api/verify-email` → 307 → `/student-login?verified=1`. ✅
  - `/student-apply`, `/student-login`, `/forgot-password`, `/reset-password` all 200. ✅
- **KNOWN BUG FIXED:** register returned 500 because the `students` table has NO `createdAt`/`updatedAt` columns (only `users` does). Removed them from the students insert + fallback. ✅
- **USER-SIDE SETUP STILL PENDING (Supabase dashboard — REQUIRED for full flow):**
  1. Run `supabase/auth-email-verification.sql` (`alter table public.students add column if not exists "emailVerified" boolean not null default false;`)
  2. Paste the two email templates (`supabase/email-template-confirm-signup.html`, `email-template-reset-password.html`) into Authentication → Email Templates → Confirm signup + Reset password.
  3. Authentication → URL Configuration: confirm "Confirm email" is ON and add the redirect URL allow-list entry for `https://elevate-media-dun.vercel.app/**` (needed so the emailed activation/reset links redirect back correctly).
- **Legacy students** must be migrated to Supabase Auth before 3-field login works — via `POST /api/auth/migrate` (ADMIN email+password; creates auth user with `email_confirm: true`). john.doe already migrated. Returns 400 if already migrated (expected, safe).
- Role-based access works: teacher can't access `/dashboard`, etc.
- Old `/login` redirects (307) to `/admin-login`
- **POE / Results / Attendance approval workflow: BUILT, DEPLOYED, E2E-VERIFIED LIVE** (all 13 checks passed 2026-08-04):
  - Ran `supabase/institution-workflow.sql` in Supabase SQL editor (added `teachers.isHod`, `result_documents` table, POE + attendance approval columns). ✅
  - Ran `supabase/seed-e2e-data.sql` (created CS101 course, enrolled John Doe, assigned Sarah as unit lecturer + HOD). ✅
  - **Storage buckets did NOT exist in Supabase** — created all 5 (`avatars`, `poe-documents`, `research-papers`, `assignment-submissions`, `results`) via new admin-only `POST /api/storage/setup` (idempotent). This was the real reason earlier "verified" file uploads never worked.
  - Verified live: teacher uploads result file → releases result → student sees it → "Save as POE" (auto APPROVED, source RESULT, exempt from 5-cap) → student attendance sign-in → lecturer+HOD approve → APPROVED → manual POE upload (PENDING) → lecturer approve (PARTIALLY_APPROVED) → HOD approve (APPROVED). ✅
  - New pages: `/teacher/results` (release verified results), `/teacher/poe-approvals` (approve queue), attendance approvals section on `/teacher/attendance`, Verified Results section on `/dashboard/results`.
  - Test data now in DB: 1 result doc, 2 POE docs, 1 attendance record (all APPROVED) — visible in the live UI.
- **KNOWN BUG (not fixed, needs approval):** `teacher/grades/page.tsx:117` uses `ca.courses.id` but `/api/teacher/dashboard` returns `courses` as an ARRAY (and now also a single `course` object) — "Enter Grade" course dropdown renders empty options.
- **Teacher portal split: BUILT, DEPLOYED, E2E-VERIFIED LIVE (2026-08-04):**
  - Teacher login now asks **Unit Lecturer vs HOD**; a HOD claim that doesn't match `teachers.isHod` is DENIED (session null). `hodClaim` passed via Credentials. ✅
  - Redirects: unit lecturer → `/teacher`, HOD → `/teacher/hod`. Middleware blocks non-HODs from `/teacher/hod*` (307 → `/teacher`). ✅
  - `/api/teacher/dashboard` is now SCOPED to the teacher's OWN courses/students/pending submissions (was showing all dept courses + all enrollments). ✅
  - New HOD pages: `/teacher/hod` (overview), `/teacher/hod/students`, `/teacher/hod/courses`, `/teacher/hod/approvals` (POE + attendance queues). New HOD APIs: `/api/teacher/hod/dashboard`, `/api/teacher/hod/students`. ✅
  - New unit-lecturer test account + data seeded via admin-only idempotent `POST /api/teacher/seed`: CS102 (Data Structures), Jane Smith (T2026002), enrollment + assignment `seed-hw1` + 1 pending submission from John. ✅
  - Verified: Jane → 1 course/1 student/1 pending, all scoped to CS102; Sarah → dept CS sees CS101+CS102; false HOD claim → login denied. ✅
  - Mobile sidebar (drawer) + HOD/lecturer sidebar label shipped — desktop unchanged.

### What Still Needs To Be Done
1. **Run the pending Supabase setup steps above**, then do a real (non-test) Apply Now → email activation → login round-trip with a real inbox.
2. **Fix the teacher Grades "Enter Grade" course dropdown bug** (`ca.course` → `ca.courses` in `teacher/grades/page.tsx`).
3. **Test every dashboard sub-page** — each role has ~10 sub-pages (courses, assignments, exams, attendance, profile, etc.). They may have broken API calls or missing data handling.
4. **Test the full Apply Now flow** — student-apply → email → activation → login → dashboard. Same for teacher-apply.
5. **Test registration flow** — `/register` page
6. **Seed more realistic data** — more courses, enrollments, assignments, grades so dashboards show meaningful content.
7. **Fix any remaining UI issues** — university name font size, branding consistency.

### The #1 Standing Rule (The User Has Said This Over and Over)
**DO NOT TRY TO FIX ANYTHING — TELL ME FIRST.** Analyze, investigate, report what's wrong and your plan, and WAIT for approval before making any changes. Never assume permission to fix.

### How To Continue From Here
In a new chat, say: **"Read AGENTS.md. Pick up from 'What Still Needs To Be Done'. Test each dashboard page end-to-end before making any changes."**

### How To Verify Anything (USE THIS EXACT PROCEDURE — Never Skip It)
Never say "works" or "verified" without running this. It simulates the REAL login flow with cookies (not just HTTP 200):
```powershell
$base = "https://elevate-media-dun.vercel.app"
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# 1) Get CSRF token
$csrf = Invoke-WebRequest -Uri "$base/api/auth/csrf" -WebSession $session -UseBasicParsing
$csrfToken = ($csrf.Content | ConvertFrom-Json).csrfToken

# 2) Sign in as the role being tested. Teachers MUST include employeeId:
#    Student:  email=<EMAIL>&admNo=<ADM>&password=<PASSWORD>
#    Teacher:  email=<EMAIL>&employeeId=<EMP_ID>&password=<PASSWORD>&hodClaim=yes|no
#    Admin:    email=<EMAIL>&password=<PASSWORD>
$body = "csrfToken=$csrfToken&email=<EMAIL>&password=<PASSWORD>&json=true"
Invoke-WebRequest -Uri "$base/api/auth/callback/credentials" -Method POST -ContentType "application/x-www-form-urlencoded" -Body $body -WebSession $session -UseBasicParsing -MaximumRedirection 5 | Out-Null

# 3) Confirm the session has the right role
$me = Invoke-WebRequest -Uri "$base/api/auth/session" -WebSession $session -UseBasicParsing
Write-Host "Session: $($me.Content)"   # must show role: STUDENT / TEACHER / ADMIN

# 4) Hit the destination page with -MaximumRedirection 0; 200 = OK, 307 + wrong location = BUG
$page = Invoke-WebRequest -Uri "$base/dashboard" -WebSession $session -UseBasicParsing -MaximumRedirection 0
Write-Host "Status: $($page.StatusCode) | Location: $($page.Headers['Location'])"
```
Test accounts: Student `john.doe@student.elevatemedia.edu`/`student123` → `/dashboard`, Teacher `sarah.jones@elevatemedia.edu`/`teacher123`/T2026001 → `/teacher/hod`, Teacher `patricia.mwangi@elevatemedia.edu`/`lecturer789`/T2026003 → `/teacher`, Faculty HOD `daniel.otieno@elevatemedia.edu`/`hod12345`/T2026004 → `/teacher/hod`, Admin `admin@elevatemedia.edu`/`admin123` → `/admin`.

---

## Project Overview
A comprehensive student management platform built with Next.js 16, TypeScript, Tailwind CSS, Prisma ORM, Auth.js (NextAuth v5), and Supabase. Deployed on Vercel with PostgreSQL via Supabase.

## Tech Stack
- **Framework:** Next.js 16+ (App Router, Turbopack)
- **Language:** TypeScript
- **UI:** Tailwind CSS + custom components (shadcn-style)
- **ORM:** Prisma (build-time only — NOT used at runtime)
- **Database:** PostgreSQL (Supabase) — accessed via Supabase HTTP REST API
- **Auth:** Auth.js (NextAuth v5 beta) with JWT strategy
- **Storage:** Supabase Storage
- **Deployment:** Vercel

## CRITICAL: Database Access
**DO NOT USE PRISMA AT RUNTIME.** The TCP connection to `db.afcdfiqpomabpuucfdbw.supabase.co:5432` is broken. ALL API routes use the Supabase HTTP REST API:
```typescript
import { createClient } from "@supabase/supabase-js";
const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL!,
  process.env.SUPABASE_SERVICE_ROLE_KEY!
);
const { data, error } = await supabase.from("tableName").select("*");
```

### Environment Variables
**ENCRYPTED.** Sensitive values are AES-256 (PBKDF2, 100k iters) encrypted. Ask the user for the passphrase, then decrypt with the helper script in `scripts/decrypt-secret.ps1` (or PowerShell: `[System.Convert]::FromBase64String(...)` + Rfc2898DeriveBytes).
```
DATABASE_URL=AES256:KnGeSAivbCYT3LzvYCRy1TVlKI08DWLqnFv6DQcFaS/YhZseMLZQgR7u5GSXE1Cn42NtYj3IuQCMUmiiuaSW7ElF0q71b1yQy3ZHp3kpzG2Y33hok38YMImuH/kNux0MCW1nWDAGzVI4Ep9RpKSR9n78nkp8+Mipj/daAB3gglHCCqrycgDNj92P6dZ2IjDb1jgi8xKTiU5KImIK4ppSNg==
NEXT_PUBLIC_SUPABASE_URL=https://afcdfiqpomabpuucfdbw.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=<set in Vercel>
SUPABASE_SERVICE_ROLE_KEY=<set in Vercel>
AUTH_SECRET=AES256:jVGBNM8Sk/+uhKfxTwuRvv6w20eyqqDZdMFyNsTKFLD123SnT7bOh47818KFQ+Fp3zoA7TVRzAEbBPLY2IHhfd4d50ucyA487Vgj7LIoLCY=
NEXTAUTH_URL=https://elevate-media-dun.vercel.app
```

### Supabase Credentials
- Password: `AES256:GUdSCTL7S/nQmAaeJSFznKRq9aKjl74MCd44nU6OK2tXsVjgTU1DYN/uxF1LfyntFDfjAKOtqAg4TqmFJQ8ZbA==`

## Test Accounts
- **Admin:** admin@elevatemedia.edu / admin123
- **Teacher (Dept HOD, CS):** sarah.jones@elevatemedia.edu / teacher123 (Employee ID: T2026001, `isHod=true`)
- **Teacher (Unit Lecturer, CS102):** jane.smith@elevatemedia.edu / teacher456 (Employee ID: T2026002, `isHod=false`)
- **Teacher (Unit Lecturer, PAD301 Social Sciences):** patricia.mwangi@elevatemedia.edu / lecturer789 (Employee ID: T2026003, `isHod=false`)
- **Teacher (FACULTY HOD, Social Sciences):** daniel.otieno@elevatemedia.edu / hod12345 (Employee ID: T2026004, `isHod=true`, `faculties.hodTeacherId` → faculty `seed-fac-social`)
- **Student:** john.doe@student.elevatemedia.edu / student123 (Adm No: EM20261001)

## Login Portals (3 SEPARATE pages, 3 SEPARATE redirects)
| Portal | URL | Credentials | Redirects To |
|--------|-----|-------------|-------------|
| Student | `/student-login` | Email + Adm No + Password (Supabase auth, email-verified) | `/dashboard` |
| Teacher | `/teacher-login` | Employee ID + Email + Password (bcrypt) + Unit Lecturer/HOD toggle | `/teacher` (lecturer) or `/teacher/hod` (HOD) |
| Admin | `/admin-login` | Email + Password (bcrypt) | `/admin` |

**IMPORTANT:** Each login page ONLY contains links related to that portal. Student login has NO teacher/admin links. Teacher login has NO student/admin links. Admin login has NO student/teacher links.

## Auth Architecture
- `src/lib/auth.ts` — NextAuth v5 config with Credentials provider (fields: email, admNo, password, hodClaim). Exports `handlers`, `signIn`, `signOut`, `auth`. Student path (`admNo` present): find student by `studentId`, verify user email matches, `supabase.auth.signInWithPassword` (unverified emails blocked by Supabase). Admin/teacher path (no admNo): bcrypt compare; a `hodClaim` of yes/true is rejected unless `teacher.isHod === true`.
- `src/middleware.ts` — Uses `auth()` wrapper from `@/lib/auth` (NOT `getToken` from `next-auth/jwt`). Handles role-based routing. Public routes include `/forgot-password` and `/reset-password`.
- Login pages use `window.location.href` for redirects (NOT `router.push` — client-side nav doesn't trigger middleware).
- JWT callback adds `role`, `avatar`, `studentId`, `employeeId`, `isHod`, `departmentId`, `hod` to token.
- Session callback maps token fields to session.user. `session.user.hod` is true only for a real HOD who confirmed the claim on login.
- **Supabase embed gotcha:** `.select("..., courses(id,name,code)")` returns `courses` as an ARRAY, not a single object. Normalize with `Array.isArray(x.courses) ? x.courses[0] : x.courses` (see `/api/teacher/dashboard`).
- Student email flow: `/api/register` (Supabase `auth.signUp` + users/students insert, unique `EM{year}{random4}` Adm No, `emailRedirectTo: SITE/api/verify-email?admNo=...`) → `/api/verify-email` (marks students.emailVerified, 307 → login) → `/api/forgot-password` (only emails when email+admNo match a student, generic response always) → `/reset-password` (parses hash tokens, `updateUser({password})`).
- Legacy students → `POST /api/auth/migrate` (ADMIN-only) creates Supabase auth user with `email_confirm: true`.
- **IMPORTANT:** `students` table has NO `createdAt`/`updatedAt` columns (only `users` does). Never include them in students inserts/updates.

## Project Structure
```
src/
├── app/
│   ├── (auth)/login/page.tsx        # Redirects to /admin-login
│   ├── (auth)/register/page.tsx     # Student registration
│   ├── admin-login/page.tsx         # Admin portal (email + password → /admin)
│   ├── student-login/page.tsx       # Student portal (email + adm no + password → /dashboard)
│   ├── teacher-login/page.tsx       # Teacher portal (employee id → /teacher)
│   ├── student-apply/page.tsx       # Student application form
│   ├── teacher-apply/page.tsx       # Teacher application form
│   ├── (dashboard)/dashboard/       # Student dashboard pages
│   ├── (dashboard)/teacher/         # Teacher dashboard pages
│   ├── (dashboard)/admin/           # Admin dashboard pages
│   ├── courses/page.tsx             # Public courses listing
│   ├── achievements/page.tsx        # Public achievements page
│   ├── api/                         # API route handlers (ALL use Supabase HTTP)
│   ├── layout.tsx                   # Root layout with SessionProvider
│   └── page.tsx                     # Landing page
├── components/
│   ├── ui/                          # Reusable UI components (button, input, card, select, dialog, table, badge, alert, label, avatar)
│   ├── layout/Navbar.tsx            # Top nav with role-based links
│   ├── layout/SiteFooter.tsx        # Footer with contact info
│   ├── layout/Sidebar.tsx           # Dashboard sidebar
│   └── layout/DashboardLayout.tsx   # Providers, layout wrappers
├── hooks/
│   └── useFetch.ts                  # Data fetching hook used by dashboard pages
├── lib/
│   ├── auth.ts                      # NextAuth v5 config
│   ├── prisma.ts                    # Prisma client (BUILD TIME ONLY)
│   ├── supabase.ts                  # Supabase client + file upload
│   ├── cn.ts                        # className merge helper
│   ├── utils.ts                     # Utility functions
│   └── validations.ts               # Zod schemas
└── middleware.ts                     # Uses auth() wrapper for role routing
```

## API Routes (ALL use Supabase HTTP, NOT Prisma)
- `/api/setup` — POST: seeds admin/teacher/student accounts
- `/api/auth/lookup` — POST: looks up email by studentId or employeeId
- `/api/auth/[...nextauth]` — NextAuth handlers
- `/api/register` — POST: student registration
- `/api/teachers` — POST: teacher registration
- `/api/departments` — GET: list departments
- `/api/courses` — GET: list courses
- `/api/student/dashboard` — GET: student dashboard data
- `/api/teacher/dashboard` — GET: teacher dashboard data (scoped to own courses/students/pending submissions)
- `/api/teacher/hod/dashboard` — GET: HOD dept overview (HOD-only)
- `/api/teacher/hod/students` — GET: HOD dept students with their courses (HOD-only)
- `/api/teacher/seed` — POST: admin-only idempotent seed of unit-lecturer test data (Jane/CS102)
- `/api/admin/dashboard` — GET: admin dashboard data

## ID Generation
Prisma schema uses `@default(cuid())` but since we bypass Prisma at runtime, all API routes generate IDs with:
```typescript
function genId() {
  return Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
}
```

## Required Fields for Inserts
Most table inserts must include these timestamp fields (database has no defaults):
```typescript
{ createdAt: new Date().toISOString(), updatedAt: new Date().toISOString() }
```
**EXCEPTION: the `students` table has NO `createdAt`/`updatedAt` columns** (only `users` does). Never include them in students inserts/updates.

## Build & Deploy
```bash
vercel --prod --yes    # Deploy to production
```
Build uses dummy DATABASE_URL for `prisma generate` (not real DB). Vercel installs fine (npm), local npm install is broken.

## Landing Page
- School name: "Elevate Media University"
- Tagline: "Empowering the next generation of professionals through innovative education, cutting-edge technology, and a commitment to academic excellence."
- Roles section with 3 cards: Students (Apply Now + Student Login), Teachers (Join Staff + Teacher Login), Admin (Admin Login)
- Footer on every page: WhatsApp (+254775333673), Call (+254111275630), Email (em757896@gmail.com), "Created by Elevate Media Productions"
- Footer links: Student Portal, Teacher Portal, Admin Portal

## Middleware Rules
- Public routes: `/`, `/admin-login`, `/register`, `/courses`, `/achievements`, `/student-login`, `/student-apply`, `/teacher-login`, `/teacher-apply`, `/forgot-password`, `/reset-password`
- Unauthenticated users accessing protected routes → redirect to `/admin-login`
- Logged-in users on login pages → redirect to their role's dashboard
- Role-based access: `/admin*` only ADMIN, `/teacher*` only TEACHER+ADMIN, `/dashboard*` only STUDENT+ADMIN
