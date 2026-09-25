# TalentTrack - Full-Stack Recruitment Process Tracking Website

A modern, full-stack recruitment and talent acquisition tracking platform built with **PHP 8**, **SQL (PDO SQLite & MySQL)**, and **HTML5 / CSS3 / Vanilla JavaScript**.

TalentTrack delivers an end-to-end recruitment lifecycle system tailored for three distinct user roles: **Candidates (Job Seekers)**, **Recruiters (Hiring Teams)**, and **Administrators (Platform Governance)**.

---

## 🌟 Key Features by Module

### 1. Candidate Module (Job Seeker)
- **Job Discovery & Search**: Keyword search, filters by category, job type (full-time, remote, contract), experience level, and location.
- **1-Click Application**: Apply using saved profile resume or upload tailored resumes & cover letters.
- **Visual Recruitment Stage Tracker**: Interactive 6-step progress stepper:
  $$\text{Applied} \longrightarrow \text{Screening} \longrightarrow \text{Interviewing} \longrightarrow \text{Assessment} \longrightarrow \text{Offer Extended} \longrightarrow \text{Hired}$$
- **Interview Coordination**: View scheduled interview rounds, duration, interviewer details, and direct video conference links.
- **Profile & Resume Manager**: Update headline, bio, skill tags, years of experience, and default resume file.

### 2. Recruiter Module (Hiring Team)
- **Job Management**: Create, edit, publish, close, and archive vacancies with detailed requirements and compensation ranges.
- **Visual Kanban Pipeline Board**: Drag-and-view candidate cards across 7 customizable recruitment stages.
- **Candidate Review & Evaluation**: In-depth applicant profile review, resume download/preview, 5-star evaluation ratings, and feedback notes.
- **Interview Scheduling Engine**: Schedule interview rounds with candidate, duration, format (virtual/onsite), and video conference links with automated pipeline advancement.

### 3. Administrator Module (Platform Governance)
- **Executive Analytics Dashboard**: High-level KPIs including total candidates, active recruiters, job vacancies, conversion rates, and hires.
- **Recruitment Funnel Breakdown**: Candidate distribution counts across all pipeline stages.
- **User Governance**: Search, filter, activate, suspend, or reset passwords for any registered user.
- **Job Posting Moderation**: Global job moderation across all recruiters, feature/unfeature flags, and closure control.
- **Department / Category Management**: Create and manage job departments.
- **System Audit Trail**: Immutable chronological log of recruitment actions and authentication events.

---

## 🚀 Quick Start Guide

### Prerequisites
- PHP 8.1+ with PDO extension enabled.

### 1. Start the Server
Double-click `run_server.bat` or run in terminal:
```bash
php -S localhost:8000 index.php
```

Then navigate to: **[http://localhost:8000](http://localhost:8000)**

---

## 👥 Demo Accounts (Pre-Seeded)

For testing and demonstration, a **1-Click Role Switcher** toolbar is available at the top of every page. You can also log in directly using the following credentials:

| Role | Email | Password | Description |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@talenttrack.com` | `Admin@123` | System Administrator with full analytics and user governance |
| **Recruiter** | `recruiter@techcorp.com` | `Recruiter@123` | Senior Talent Lead at TechCorp Innovations |
| **Candidate** | `candidate@example.com` | `Candidate@123` | David Kim (Senior Full-Stack Engineer with active applications) |

---

## 🗄️ Database Architecture

The platform supports dual database drivers via standard PHP PDO:

- **SQLite (Default Out-of-the-Box)**: Automatically created and seeded upon first launch in `database/talenttrack.sqlite`.
- **MySQL / MariaDB**: Production-ready schema available in `database/schema.sql`. Switch database driver by setting:
  ```env
  DB_DRIVER=mysql
  DB_HOST=127.0.0.1
  DB_DATABASE=talenttrack
  DB_USERNAME=root
  DB_PASSWORD=your_password
  ```

### Relational Schema
- `users`: Core authentication, roles (`candidate`, `recruiter`, `admin`), profile data, skills, status.
- `categories`: Job categories and functional departments.
- `jobs`: Vacancies, requirements, compensation ranges, deadlines, status.
- `applications`: Candidate job submissions, current pipeline stage, recruiter ratings, notes.
- `application_history`: Audit trail tracking every stage transition and status change.
- `interviews`: Scheduled rounds, meeting URLs, duration, feedback ratings.
- `audit_logs`: Platform-wide security and operational activity logs.

---

## 🛡️ Security & Engineering Standards

- **Password Security**: Native `password_hash()` and `password_verify()` with `PASSWORD_DEFAULT` (bcrypt).
- **Session Protection**: Session fixation prevention with `session_regenerate_id(true)` upon authentication.
- **CSRF Defense**: Cryptographically secure anti-CSRF tokens verified on all mutating HTTP POST requests.
- **SQL Injection Prevention**: 100% prepared statements with parameterized PDO queries.
- **XSS Mitigation**: Strict sanitization helper `e()` (`htmlspecialchars` with `ENT_QUOTES`) applied across all views.
- **Role-Based Access Control (RBAC)**: Route middleware strictly enforcing permissions (`require_role()`).
- **File Upload Verification**: Safe file extensions, size limits (5MB max), and cryptographic unique filename storage.
