# Elevate Media University — Official Institution Website

> The official website for Elevate Media University — a modern, full-stack institution website built with Next.js, TypeScript, Tailwind CSS, and Supabase. It presents the university to the public (programs, courses, achievements, applications) and powers secure online portals for students, teaching staff, and administrators.

**🌐 Live Demo:** https://elevate-media-dun.vercel.app

---

## Overview

Elevate Media University's official website serves two audiences. For the public, it is an informational and marketing website — showcasing the institution, its programs and courses, achievements, and application channels. For the university community, it provides **three independent, role-scoped portals** — a student portal, a staff portal (with Unit Lecturer and HOD views), and an admin portal — each showing only the features relevant to its role.

> **Note:** The separate, classic PHP/MySQL **Student Management System (SMS)** lives in its own project — [`projects/websites/student-management-system`](https://github.com/em757896-alt/emmanuel-tech-portfolio/tree/main/projects/websites/student-management-system). This project is the institution's website.

---

## Key Features

### Public Website
- Landing page with university branding and tagline
- Courses and programs listing
- Achievements page
- Student application form (`/student-apply`)
- Staff application form (`/teacher-apply`)

### Student Portal
- Email-verified registration with unique Admission Number (e.g. `EM20261001`)
- Three-field login (Email + Adm No + Password) via Supabase Auth
- Course enrollment and assignment submission
- Results release viewing with "Save as POE" conversion
- Attendance sign-in and status tracking
- Dashboard with personal academic overview

### Staff Portal
- Login with Employee ID + Email + Password, with Unit Lecturer / HOD role selection
- Lecturer dashboard scoped to own courses, students, and pending submissions
- HOD dashboard scoped to department or faculty overview
- Assignment creation and submission grading
- Result release workflow (upload → release → verified results)
- POE and attendance approval queues
- Faculty HOD view: every department and course under the faculty

### Admin Portal
- System-wide oversight of students, teachers, courses, and departments
- Legacy student migration to Supabase Auth
- Storage bucket setup and management
- Institutional workflow configuration

### Authentication & Security
- Auth.js (NextAuth v5) with JWT session strategy
- Role-Based Access Control (RBAC) enforced in middleware
- Email verification via Supabase (Confirm signup)
- bcrypt password hashing for staff accounts
- Generic forgot-password responses (no account enumeration)
- Route protection — each role is isolated from the others

### Academic Workflow
- POE upload (manual) with lecturer + HOD approval chain
- Attendance approval chain (lecturer → HOD)
- Result documents with verified/unverified status
- Enrollment tracking per course and department

### Design
- Fully responsive (mobile drawer navigation, desktop sidebar)
- Modern Tailwind CSS UI with shadcn-style components
- Role-specific sidebar and navigation labels
- Distinct branding for each academic role

---

## Technology Stack

## Frontend

- Next.js 16 (App Router, Turbopack)
- TypeScript
- Tailwind CSS
- Auth.js (NextAuth v5)
- shadcn-style UI components

## Backend

- Next.js API Routes (serverless functions)
- Supabase HTTP REST API

## Database & Storage

- PostgreSQL (Supabase)
- Supabase Auth
- Supabase Storage (avatars, POE documents, research papers, submissions, results)

## Deployment

- Vercel

---

## Core Modules

- Public Landing Page
- Courses & Achievements (public pages)
- Student Application (`/student-apply`)
- Staff Application (`/teacher-apply`)
- Student Portal (`/student-login` → `/dashboard`)
- Staff Portal (`/teacher-login` → `/teacher` or `/teacher/hod`)
- Admin Portal (`/admin-login` → `/admin`)
- Student Portal pages (courses, assignments, exams, attendance, results, profile)
- Staff Portal pages (classes, submissions, results, POE approvals, attendance, HOD overview)
- Admin Portal pages (users, courses, departments, setup)

---

## Screenshots

<img src="Screenshots/Screenshot (108).png" alt="Elevate Media — Landing Page" />

<img src="Screenshots/Screenshot (110).png" alt="Elevate Media — Portal View" />

<img src="Screenshots/Screenshot (111).png" alt="Elevate Media — Dashboard View" />

<img src="Screenshots/Screenshot (112).png" alt="Elevate Media — Dashboard View" />

<img src="Screenshots/Screenshot (113).png" alt="Elevate Media — Dashboard View" />

<img src="Screenshots/Screenshot (114).png" alt="Elevate Media — Dashboard View" />

---

## Demo Accounts

> Test credentials for the live demo. Teacher accounts require the Employee ID and the Unit Lecturer / HOD role toggle.

| Role | Login Page | Email | Password | Additional |
|------|-----------|-------|----------|------------|
| Admin | `/admin-login` | `admin@elevatemedia.edu` | `admin123` | — |
| Student | `/student-login` | `john.doe@student.elevatemedia.edu` | `student123` | Adm No: `EM20261001` |
| Teacher (HOD) | `/teacher-login` | `sarah.jones@elevatemedia.edu` | `teacher123` | Employee ID: `T2026001`, toggle HOD |
| Teacher (Lecturer) | `/teacher-login` | `jane.smith@elevatemedia.edu` | `teacher456` | Employee ID: `T2026002`, toggle Lecturer |
| Teacher (Lecturer) | `/teacher-login` | `patricia.mwangi@elevatemedia.edu` | `lecturer789` | Employee ID: `T2026003`, toggle Lecturer |
| Teacher (Faculty HOD) | `/teacher-login` | `daniel.otieno@elevatemedia.edu` | `hod12345` | Employee ID: `T2026004`, toggle HOD |

---

## Installation

Clone the repository

```bash
git clone https://github.com/em757896-alt/emmanuel-tech-portfolio.git
```

Navigate into the project

```bash
cd projects/websites/elevate-media
```

Install dependencies

```bash
npm install
```

Set up environment variables. Copy `.env.example` (or request the encrypted secrets) and configure:

```
NEXT_PUBLIC_SUPABASE_URL=https://<project>.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=<anon-key>
SUPABASE_SERVICE_ROLE_KEY=<service-role-key>
AUTH_SECRET=<next-auth-secret>
NEXTAUTH_URL=http://localhost:3000
```

Run the development server

```bash
npm run dev
```

Visit:

```
http://localhost:3000
```

---

## Project Status

**Actively Maintained**

The project continues to receive updates focused on new academic workflows, role-scoped features, data seeding, and UI polish.

---

## Live Demo

🌐 https://elevate-media-dun.vercel.app/

---

## Repository Structure

```
elevate-media/
│
├── src/
│   ├── app/
│   │   ├── (auth)/          # Login redirect, registration
│   │   ├── (dashboard)/     # Student, Staff & Admin portals
│   │   ├── admin-login/     # Admin portal
│   │   ├── student-login/   # Student portal
│   │   ├── teacher-login/   # Staff portal
│   │   ├── student-apply/   # Student application form
│   │   ├── teacher-apply/   # Staff application form
│   │   ├── courses/         # Public courses listing
│   │   ├── achievements/    # Public achievements page
│   │   ├── api/             # API route handlers (Supabase HTTP)
│   │   ├── layout.tsx       # Root layout with SessionProvider
│   │   └── page.tsx         # Landing page
│   ├── components/
│   │   ├── ui/              # Reusable UI components
│   │   ├── layout/          # Navbar, Footer, Sidebar, DashboardLayout
│   ├── hooks/
│   │   └── useFetch.ts      # Data fetching hook
│   ├── lib/
│   │   ├── auth.ts          # NextAuth v5 config
│   │   ├── supabase.ts      # Supabase client + file upload
│   │   └── validations.ts   # Zod schemas
│   └── middleware.ts        # Role-based route protection
├── supabase/                # SQL migrations & email templates
├── scripts/                 # Helper scripts
├── Screenshots/             # Project screenshots
└── README.md
```

---

## Author

## Emmanuel Michael

ICT Specialist | Full-Stack Web Developer

📧 Email: em757896@gmail.com

📞 Phone: +254111275630

📍 Mombasa, Kenya

GitHub:
https://github.com/em757896-alt

Portfolio:
https://github.com/em757896-alt/emmanuel-tech-portfolio

---

## License

This project is provided for demonstration and portfolio purposes.

© 2026 Emmanuel Michael. All Rights Reserved.

---

## Acknowledgements

Built by **Elevate Media Productions** — WhatsApp +254775333673 | Call +254111275630 | Email em757896@gmail.com.

Special thanks to the open-source ecosystem — Next.js, Supabase, Tailwind CSS, and the wider developer community whose technologies helped shape this project.

---

### If you found this project useful, consider giving it a ⭐ on GitHub.
