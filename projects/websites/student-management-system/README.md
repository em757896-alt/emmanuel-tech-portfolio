# Student Management System

> A web-based application for registering, viewing, searching, and managing student records — a simple, efficient tool for academic administration.

**🌐 Live Demo:** https://studentmanagement.gt.tc

---

## Overview

The Student Management System is a lightweight web application built with PHP and MySQL that enables institutions to manage student records digitally. It provides core academic administration features including student registration, record viewing, and search — replacing paper-based record keeping with a clean, functional web interface.

The project served as the foundational system that shaped the modern **Elevate Media University Student Management System** (a full multi-portal platform built with Next.js and Supabase).

---

## Key Features

- Student registration and record creation
- Student record viewing and management
- Student search functionality
- Secure login and session management
- Simple, responsive interface
- MySQL-based data storage

---

## Technology Stack

### Frontend
- HTML5
- CSS3
- JavaScript

### Backend
- PHP

### Database
- MySQL

---

## Live Demo

🌐 https://studentmanagement.gt.tc

---

## Installation

1. Clone the repository

```bash
git clone https://github.com/em757896-alt/emmanuel-tech-portfolio.git
```

2. Navigate into the project

```bash
cd projects/websites/student-management-system
```

3. Move the project into your web server directory (XAMPP/WAMP/Laragon), for example:

```
C:\xampp\htdocs\student-management-system\
```

4. Create the required MySQL database and import `database.sql`.

5. Copy `db.example.php` to `db.php` (or your active config file) and set your real database credentials:

```php
$host   = "localhost";
$user   = "YOUR_DB_USERNAME";
$pass   = "YOUR_DB_PASSWORD";
$dbname = "YOUR_DB_NAME";
```

6. Start Apache and MySQL, then visit:

```
http://localhost/student-management-system/
```

---

## Repository Structure

```
student-management-system/
│
├── database.sql            # MySQL database dump
├── db.example.php          # Sample database config (no real credentials)
├── home.php                # Homepage
├── index.php               # Root redirect to home.php
├── login.php               # User login
├── logout.php              # User logout
├── register.php            # Student registration
├── view.php                # View student records
├── search.php              # Search student records
├── process_login.php       # Login handler
├── process_register.php    # Registration handler
├── delete_student.php      # Delete student record
└── README.md
```

---

## Project Status

**Maintained**

A foundational portfolio project demonstrating core PHP and MySQL web development skills. Its successor, the Elevate Media University Student Management System, extends this concept into a modern multi-portal platform with role-based dashboards and cloud infrastructure.

---

## Author

### Emmanuel Michael
**Founder & Owner — Elevate Media Productions**
Full-Stack System Developer | ICT Technology Specialist

- 📧 Email: em757896@gmail.com
- 📞 Phone / WhatsApp: +254111275630 · +254775333673
- 📍 Mombasa, Kenya
- GitHub: https://github.com/em757896-alt
- Portfolio: https://github.com/em757896-alt/emmanuel-tech-portfolio

---

## License

This project is provided for demonstration and portfolio purposes.

© 2026 Emmanuel Michael · Elevate Media Productions. All Rights Reserved.
