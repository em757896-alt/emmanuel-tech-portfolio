<p align="center">
  <strong style="font-size:1.5rem; letter-spacing:.14em; text-transform:uppercase;">Student Management System</strong><br>
  <span style="letter-spacing:.28em; text-transform:uppercase; opacity:.7; font-size:.8rem;">Elevate Media Productions</span>
</p>

<p align="center">
  A production-grade student management platform — interactive <strong>3D campus</strong>, live timetables,
  a digital library, and complete student record management.
</p>

<p align="center">
  <a href="https://studentmanagement.gt.tc"><strong>View Live Demo</strong></a>
  &nbsp;&middot;&nbsp; Admin demo: <code>admin</code> / <code>admin123</code>
</p>

---

## Overview

This **Student Management System** is a standalone web platform that brings academic administration into
one place. Instead of a flat list of records, the system opens with an **interactive 3D campus** — visitors
can rotate and zoom around the grounds and click any building to explore its departments, classrooms, and
timetable.

The project is a production of **Elevate Media Productions**.

## Features

| Area | What it does |
|---|---|
| 🏫 **Interactive 3D Campus** | Full Three.js scene — buildings, lawns and paths. Click a building to open its department, a classroom to see its timetable, or a student avatar to view their profile. Graceful 2D fallback on devices without WebGL. |
| 🎓 **Student Management** | Register, search, edit and delete student records. Enrolment by department with live counts. |
| 🏛️ **Departments & Classrooms** | Department pages with building, colour identity and classroom lists; room-tagged timetable lookups. |
| 📅 **Live Timetables** | Daily schedules grouped by classroom and department, always fetched from the database. |
| 📚 **Library & Loans** | Book catalogue with availability status, plus loan tracking (active vs returned). |
| 🔐 **Admin Dashboard** | Staff-only area to manage students, departments, classrooms, timetables and library stock. |
| 📬 **Contact** | Working enquiry form wired to a processing script. |

## Tech Stack

- **Backend** — PHP 8 (procedural, no framework) + PDO prepared statements
- **Database** — MySQL / MariaDB
- **Frontend** — Semantic HTML5, modern CSS (custom design system), vanilla JavaScript
- **3D** — Three.js (r160 module build on campus, r149 UMD on the hero) with an import-map-driven module graph
- **Hosting** — [InfinityFree](https://infinityfree.net) with a free MySQL database

## Getting Started

### 1. Requirements
- PHP **8.0+** with PDO and the MySQL driver enabled
- MySQL / MariaDB server
- Any web server (Apache, Nginx, or PHP's built-in server)

### 2. Clone & configure

```bash
git clone https://github.com/em757896-alt/emmanuel-tech-portfolio.git
cd emmanuel-tech-portfolio/projects/websites/student-management-system
```

1. Copy `db.example.php` to `db.php` and fill in your database credentials.
2. Create a database and import `database.sql` (self-cleaning import — safe to re-run).
3. Seed the first admin account by visiting `setup_admin.php` once, or create one via SQL.
4. Serve the folder and open `index.php` — it redirects to `home.php`.

### 3. Default admin credentials

| Username | Password |
|----------|----------|
| `admin`  | `admin123` |

> ⚠️ **Change the admin password immediately** after your first login.

## Project Structure

```
student-management-system/
├── assets/
│   ├── css/styles.css      # Full design system
│   └── js/
│       ├── campus3d.js     # 3D campus scene (ES module)
│       └── hero3d.js       # Home hero background scene
├── includes/
│   ├── config.php          # Config, DB connection, helpers
│   ├── header.php          # Shared header / navigation
│   └── footer.php          # Shared footer / contact details
├── admin/                  # Admin dashboard pages
├── api/search.php          # JSON endpoint for live admin student search
├── campus.php              # 3D campus page
├── students.php            # Student registration & search
├── departments.php         # Department directory
├── department.php          # Single department detail
├── timetable.php           # Live timetables
├── library.php             # Library catalogue & loans
├── contact.php             # Contact form
├── database.sql            # Schema + seed data (self-cleaning)
└── db.example.php          # DB credentials template (copy to db.php)
```

## Deployment Notes

- This is a **shared-hosting-friendly** app: no build step, no server-side runtime beyond PHP, no package
  manager required at runtime.
- The 3D campus loads its Three.js modules from a public CDN (jsDelivr) via an **import map** declared in
  `campus.php`. The page falls back to a 2D layout automatically if WebGL is unavailable.
- `db.php` contains live credentials and is **git-ignored** by design — never commit it.

## Security

- PDO prepared statements everywhere — no SQL injection.
- Output escaping via the `e()` helper — no reflected XSS.
- Admin area is behind session-based access checks.
- Credentials and the `db.php` file are excluded from version control.

## Production

- **Live:** https://studentmanagement.gt.tc
- **Host:** InfinityFree (shared hosting, PHP 8.3 + MariaDB)

## License

Provided for demonstration and portfolio purposes.
© 2026 Emmanuel Michael · Elevate Media Productions. All Rights Reserved.
