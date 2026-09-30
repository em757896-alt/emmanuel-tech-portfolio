<h1 align="center">Emmanuel Tech Portfolio</h1>

<p align="center">
  <strong>Websites, applications &amp; systems engineered by Emmanuel Michael.</strong>
  <br />
  Delivered under the <strong>Elevate Media Productions</strong> brand — production-grade
  web apps, civic tech and management systems, all built, deployed and maintained for real use.
</p>

<p align="center">
  <a href="https://elevate-media-productions.vercel.app"><img src="https://img.shields.io/badge/Live%20Demo-elevate--media--productions.vercel.app-111?style=for-the-badge&logo=vercel" alt="Live demo" /></a>
  <a href="https://github.com/em757896-alt/emmanuel-tech-portfolio/stargazers"><img src="https://img.shields.io/github/stars/em757896-alt/emmanuel-tech-portfolio?style=for-the-badge&logo=github&color=ffd166" alt="Stars" /></a>
  <a href="https://github.com/em757896-alt/emmanuel-tech-portfolio/releases"><img src="https://img.shields.io/github/v/release/em757896-alt/emmanuel-tech-portfolio?style=for-the-badge&color=06d6a0" alt="Release" /></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue?style=for-the-badge&color=118ab2" alt="License" /></a>
</p>

<p align="center">
  <a href="#project-catalogue"><img src="https://img.shields.io/badge/4%20projects-civic%20%C2%B7%20education%20%C2%B7%20fintech-06d6a0?style=for-the-badge" alt="Projects" /></a>
  <a href="#technology-arsenal"><img src="https://img.shields.io/badge/stack-Next.js%20%C2%B7%20PHP%20%C2%B7%20Kotlin-118ab2?style=for-the-badge" alt="Stack" /></a>
  <img src="https://img.shields.io/badge/security-no%20committed%20secrets-22c55e?style=for-the-badge" alt="Security" />
</p>

---

## Why this exists

Most portfolios are screenshots. This repository is the opposite — it ships **real, runnable
software** with the source for every project:

- 🏛️ **Study how a multi-portal institution system works** — read the source of our own university platform.
- 🏛️ **Building for civic tech?** — a full PBO compliance, legal-awareness and monitoring platform in vanilla PHP.
- 📱 **Interested in mobile fintech?** — a native Android expense tracker that parses SMS payments.
- 🔐 **Everything is security-reviewed** — no credentials are committed; secrets load from git-ignored files.

**The problem:** showcasing real engineering requires shipping real code.
**The solution:** production systems, documented and reusable, with zero secrets in version control.

## Project Catalogue

| # | Project | Stack | Live | Source |
|---|---------|-------|------|--------|
| 1 | **Elevate Media University** | Next.js 16, TypeScript, Supabase, Auth.js, Tailwind | [elevate-media-dun.vercel.app](https://elevate-media-dun.vercel.app) | [code](projects/websites/elevate-media-university) |
| 2 | **Civic Compliance System** | PHP, MySQL, Bootstrap, jQuery, Chart.js | [civiccompliancehub.gt.tc](https://civiccompliancehub.gt.tc) | [code](projects/websites/civic-compliance-system) |
| 3 | **Student Management System** | PHP, MySQL, Three.js | [studentmanagement.gt.tc](https://studentmanagement.gt.tc) | [code](projects/websites/student-management-system) |
| 4 | **TrackSpend Native** | Kotlin, Jetpack Compose, Supabase | [trackspend.vercel.app](https://trackspend.vercel.app) | [code](projects/applications/mobile%20apps/trackspend-native) |

---

## 1. Elevate Media University — Official Institution Website

> **Next.js 16 · TypeScript · Supabase · Auth.js · Tailwind CSS**

A comprehensive, role-based university management platform powering students, faculty, and
administrators through a single secure ecosystem.

- **Live:** https://elevate-media-dun.vercel.app
- **Source:** [`projects/websites/elevate-media-university`](projects/websites/elevate-media-university)

| Capability | Details |
|------------|---------|
| **Student Portal** | Apply, email verification, secure login, courses, assignments, results, attendance, POE workflow |
| **Teacher Portal** | Course & student management, grades, submissions, unit-lecturer vs. HOD dashboards |
| **Faculty HOD System** | Faculty-wide overview, department & course trees, nested student lists, approvals |
| **Admin Console** | Full institutional oversight, user management, approval workflows, data analytics |
| **Legal Consent** | Mandatory Privacy Policy + Terms of Use tickbox on every auth form, enforced server-side |
| **Bot Protection** | Cloudflare Turnstile on sign-up and sign-in, server-side token verification |
| **Auth & Security** | Auth.js (NextAuth v5), Supabase email verification, bcrypt, role-based access control (RBAC) |
| **Infrastructure** | Supabase PostgreSQL + Storage, serverless API routes, deployed on Vercel |

## 2. Civic Compliance System

> **PHP · MySQL · Bootstrap · jQuery · Chart.js**

A secure, responsive platform strengthening civic participation in Kenya — supporting Public
Benefit Organization (PBO) compliance, legal awareness, civic space monitoring, incident
reporting, and structured analytics for administrators.

- **Live:** https://civiccompliancehub.gt.tc
- **Source:** [`projects/websites/civic-compliance-system`](projects/websites/civic-compliance-system)

| Capability | Details |
|------------|---------|
| **Compliance Tools** | PBO registration guidance, self-assessment, downloadable templates |
| **Knowledge Hub** | Articles, multimedia resources and FAQs on the PBO Act |
| **Monitoring** | Civic-space incident reporting with county-level breakdowns and exports |
| **Legal Pages** | DPA 2019-aligned Privacy Policy and Terms of Use, independent of the database |
| **Bot Protection** | Cloudflare Turnstile on register and sign-in, server-side verification |
| **Security** | No committed credentials — secrets load from a git-ignored `config/local.php` |

## 3. Student Management System

> **PHP · MySQL · Three.js**

A standalone student management platform featuring an interactive 3D campus, live timetables,
a digital library, and a full admin dashboard — produced under the Elevate Media Productions banner.

- **Live:** https://studentmanagement.gt.tc
- **Source:** [`projects/websites/student-management-system`](projects/websites/student-management-system)

## 4. TrackSpend — Native Expense Tracker

> **Kotlin · Jetpack Compose · Supabase**

A native Android expense tracker that reads SMS payment notifications directly via
`BroadcastReceiver`, auto-categorizes transactions, and syncs to the same Supabase backend as
its PWA counterpart — with fingerprint/PIN security, biometrics, and offline-ready performance.

- **Source:** [`projects/applications/mobile apps/trackspend-native`](projects/applications/mobile%20apps/trackspend-native)

---

## Security posture

This repository is reviewed so that **no credential is ever committed**:

| Control | How it works |
|---------|--------------|
| **No secrets in git** | `.env`, `config/local.php`, `*.pem`, `*.key`, `*.p12` are git-ignored repo-wide |
| **Templates committed** | `.env.example` and `config/local.example.php` document every variable with no values |
| **Server-only secrets** | `SUPABASE_SERVICE_ROLE_KEY`, `AUTH_SECRET`, `TURNSTILE_SECRET_KEY` are never `NEXT_PUBLIC_` |
| **Browser-visible keys** | Only genuinely public values are exposed to the client (`NEXT_PUBLIC_SUPABASE_URL`, anon key, Turnstile site key) — access is controlled by **RLS**, not by hiding the key |
| **Consent enforcement** | Privacy/Terms acceptance is validated on the server, not just in the UI |

> ⚠️ Because earlier history contained values that have since been removed, **rotate anything
> that was ever committed**: database passwords, `AUTH_SECRET`, service-role keys and
> Turnstile secrets. See each project's README for the rotation list.

---

## Technology Arsenal

| Category | Technologies |
|----------|--------------|
| **Frontend** | Next.js 16, React, TypeScript, Tailwind CSS, HTML5, CSS3, Bootstrap, JavaScript, jQuery |
| **Backend** | Node.js, Next.js API Routes, PHP |
| **Databases** | PostgreSQL (Supabase), MySQL |
| **Auth & Security** | Auth.js (NextAuth v5), Supabase Auth, bcrypt, RBAC, Cloudflare Turnstile |
| **Mobile** | Kotlin, Jetpack Compose, Android BroadcastReceiver |
| **Cloud & Ops** | Vercel, Supabase Storage, InfinityFree, Git |
| **Data & Visualization** | Chart.js, Three.js, analytics dashboards, PDF/Excel/CSV export |

## Repository Structure

```
emmanuel-tech-portfolio/
│
├── projects/
│   ├── websites/
│   │   ├── elevate-media-university/       # Next.js multi-portal institution platform (Vercel)
│   │   ├── civic-compliance-system/        # PHP civic tech platform (PBO compliance)
│   │   └── student-management-system/      # PHP + MySQL + Three.js student platform
│   └── applications/
│       ├── mobile apps/
│       │   └── trackspend-native/          # Kotlin/Jetpack Compose expense tracker
│       └── desktop apps/                   # Desktop applications
```

## Getting started

Every project is self-contained. Clone and follow the project README:

```bash
git clone https://github.com/em757896-alt/emmanuel-tech-portfolio.git
cd emmanuel-tech-portfolio
```

```bash
# Next.js project (Elevate Media University)
cd projects/websites/elevate-media-university
npm install
cp .env.example .env.local   # then fill in your own keys
npm run dev
```

```bash
# PHP projects (Civic Compliance / Student Management)
# Serve the folder with any PHP + MySQL stack, then:
cp "Civic compliance/config/local.example.php" "Civic compliance/config/local.php"
# fill in your database credentials
```

## Author

**Emmanuel Michael** — ICT Specialist · Full-Stack Web Developer
*Engineering under Elevate Media Productions*

- 📧 **Email:** elevatemediaproductions1@gmail.com
- 📞 **Phone / WhatsApp:** +254 111 275 630 · +254 775 333 673
- 📍 **Location:** Mombasa, Kenya
- 🌐 **GitHub:** https://github.com/em757896-alt

## License

Released under the [MIT License](LICENSE). Use it, learn from it, ship with it — just keep the
license file and give credit where it's due.

---

<p align="center">
  <sub>Like what you see? Give the repository a star — it fuels the next build.</sub>
</p>
