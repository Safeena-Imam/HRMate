# 📋 HRMate – Comprehensive Project Documentation
### Enterprise Human Resource Management System (HRMS)
**Complete Technical & Functional Documentation for Project Reports, Viva & Presentations**

---

## 📌 1. Project Overview & Problem Statement

### 1.1 What is HRMate?
**HRMate** is a modern, modular, web-based **Human Resource Management System (HRMS)** built using the **Model-View-Controller (MVC)** architectural pattern in native PHP 8.2 and MySQL (MariaDB). 

The platform centralizes all core company administrative functions—including employee directory, organizational hierarchy, daily roll-call attendance, monthly timesheets, leave request approvals, payroll batch generation, printable payslips, company bulletins, and forensic audit trails—into a unified, role-governed digital workspace.

### 1.2 Problem Statement & Motivation
In traditional and mid-sized organizations, HR workflows suffer from:
1. **Scattered Paper & Spreadsheets:** Attendance sheets, leave applications, and salary registers are maintained in disconnected files, causing data loss and calculation errors.
2. **Attendance Manipulation:** Remote web-punching without physical verification leads to proxy attendance.
3. **Delayed Approvals:** Leave requests submitted on physical forms or informal chat messages lack structured audit trails.
4. **Payroll Calculation Overhead:** Manual computation of basic pay, allowances, deductions, and tax across varying attendance days is time-consuming and error-prone.
5. **Lack of Transparency for Employees:** Staff members lack a self-service portal to review their attendance percentages, leave balances, and official payslips.

### 1.3 Project Objectives
- Build a centralized, secure digital HR platform with distinct portals for **Administrators** and **Employees**.
- Provide high-efficiency **dual-mode attendance management** (fast daily roll-call sheet + individual monthly timesheets).
- Implement a formal **Leave Request & Approval workflow** with live status indicators.
- Automate **Payroll batch generation**, net salary computation, and printable payslip rendering.
- Enforce enterprise-grade security (CSRF protection, Bcrypt hashing, PDO prepared statements, and tamper-evident audit logging).

---

## 💻 2. System Architecture & Tech Stack

### 2.1 Technology Stack

| Layer | Technology | Version | Purpose in HRMate |
|---|---|---|---|
| **Backend Language** | PHP | 8.2+ | Native OOP backend, custom routing, controllers, business logic |
| **Database** | MySQL / MariaDB | 10.4.32 | Relational schema, foreign key constraints, transactional integrity |
| **Architectural Pattern** | MVC | Custom | Clear separation of Model (Data), View (UI), and Controller (Logic) |
| **Frontend Framework** | Bootstrap | 5.3.2 | Responsive layout, modal dialogs, utility grid |
| **Icons & Assets** | Bootstrap Icons | 1.11.0 | Consistent iconography across navigation, actions, and status badges |
| **Client Scripting** | JavaScript (ES6) | Native | Dynamic sidebar toggle, 1-click status presets, live UI updates |
| **Local Runtime** | PHP Built-in Server | Port 8000 | Development and demonstration server |
| **Environment Config** | Dotenv (`.env`) | Custom | Secure credential isolation (DB host, port, credentials, app keys) |
| **Version Control** | Git & GitHub | Latest | Source control (`https://github.com/Safeena-Imam/HRMate.git`) |
| **Secure Tunneling** | Localtunnel / Ngrok | Latest | Public HTTPS presentation link for evaluators & remote clients |

---

### 2.2 MVC Architectural Flow

```
                      ┌─────────────────────────────────────────┐
                      │             Web Browser                 │
                      │  (Admin / Manager / Staff Employee)    │
                      └────────────────────┬────────────────────┘
                                           │ HTTP Request (GET/POST)
                                           ▼
                      ┌─────────────────────────────────────────┐
                      │           public/index.php              │
                      │     (Front Controller Entry Point)      │
                      └────────────────────┬────────────────────┘
                                           │
                                           ▼
                      ┌─────────────────────────────────────────┐
                      │           routes/web.php                │
                      │       (Custom Fast Regex Router)        │
                      └────────────────────┬────────────────────┘
                                           │
                                           ▼
                                ┌─────────────────────┐
                                │   Middleware Pipeline│
                                │ • GuestMiddleware   │
                                │ • AuthMiddleware    │
                                │ • RBAC Role Guard   │
                                └──────────┬──────────┘
                                           │ Authenticated & Authorized
                                           ▼
                      ┌─────────────────────────────────────────┐
                      │          app/Controllers/               │
                      │ • AuthController                        │
                      │ • DashboardController                   │
                      │ • BaseController (CSRF, JSON, Render)   │
                      └─────────────┬───────────────────────────┘
                                    │
           ┌────────────────────────┴────────────────────────┐
           ▼                                                 ▼
┌───────────────────────────┐                     ┌───────────────────────────┐
│       app/Models/         │                     │       app/Views/          │
│ • User       • Employee   │                     │ • layouts/main, auth      │
│ • Attendance • Leave      │                     │ • dashboard/index         │
│ • Payroll    • Department │                     │ • dashboard/attendance    │
│ • AuditLog   • Role       │                     │ • dashboard/employee      │
└─────────────┬─────────────┘                     │ • dashboard/payroll, etc. │
              │ PDO Prepared Statements           └─────────────┬─────────────┘
              ▼                                                 │
┌───────────────────────────┐                                   │
│       MySQL Database      │                                   │
│       `hrm_system`        │                                   │
│   (13 Relational Tables)  │                                   │
└───────────────────────────┘                                   │
              │ Data Result                                     │ Compiled HTML
              └─────────────────────────────┬───────────────────┘
                                            ▼
                                  HTTP Response (HTML/JSON)
                                            ▼
                                     User Interface
```

---

### 2.3 Directory Structure & File Map

```
HRM SYSTEM/
├── app/
│   ├── Controllers/
│   │   ├── BaseController.php         # Master controller with CSRF, JSON responses, view rendering
│   │   ├── AuthController.php         # Authentication, login, logout, password recovery lifecycle
│   │   └── DashboardController.php    # Core HR modules (staff, departments, attendance, payroll, leaves)
│   ├── Middleware/
│   │   ├── AuthMiddleware.php         # Redirects unauthenticated sessions to /login
│   │   └── GuestMiddleware.php        # Prevents logged-in users from accessing /login
│   ├── Models/
│   │   ├── User.php                   # Account verification, password hashing, user queries
│   │   ├── Employee.php               # Employee profile, join date, department, salary records
│   │   ├── Attendance.php             # Daily roll-call batching, monthly grid calculation, stats
│   │   ├── LeaveRequest.php           # Leave application submission, balance check, admin approvals
│   │   ├── Payroll.php                # Monthly salary generation, net pay calculation, payslips
│   │   ├── Department.php             # Department headcount, creation, editing
│   │   ├── Designation.php            # Job roles and reporting lines
│   │   ├── Announcement.php           # Company-wide bulletins and notices
│   │   ├── AuditLog.php               # Forensic action recording with IP and timestamps
│   │   └── Role.php                   # RBAC definitions (Admin, Manager, Employee)
│   ├── Services/
│   │   ├── Router.php                 # Lightweight HTTP URI router supporting GET and POST
│   │   ├── CSRF.php                   # Cryptographic token generator & hash_equals validator
│   │   ├── Auth.php                   # Session management, current user resolver, remember tokens
│   │   └── PermissionService.php      # Granular permission and role checking
│   └── helpers.php                    # Global utilities: url(), asset(), e(), csrf_field(), set_flash()
├── bin/
│   ├── create_admin.php               # CLI tool to initialize or reset master administrator
│   └── seed_employee_user.php         # CLI tool to seed test employee accounts
├── config/
│   └── Database.php                   # Singleton PDO database connector with utf8mb4 encoding
├── database/
│   ├── schema.sql                     # Base structural schema
│   └── hrm_system_full.sql            # Complete database dump with sample records
├── public/
│   ├── index.php                      # Application front-controller
│   └── assets/
│       ├── css/app.css                # Custom UI design system, badges, card layouts, animations
│       ├── js/app.js                  # Dynamic JavaScript, responsive sidebar handler
│       └── images/                    # System logos and visual brand assets
├── routes/
│   └── web.php                        # Application route registry mapping URIs to controllers
├── storage/
│   └── logs/                          # Runtime diagnostic error logs
├── .env                               # Environment configurations (DB name, user, password)
├── PROJECT_DOCUMENTATION.md           # Master Project Report & Reference Manual
└── START_HRM.bat                      # 1-Click System Startup launcher for Windows
```

---

## 🗄️ 3. Database Schema Design (13 Tables)

The system runs on the relational database **`hrm_system`** with strictly defined primary keys, foreign key constraints, and performance indexes.

```
┌─────────────┐       ┌────────────────┐       ┌─────────────────┐
│    users    │◄──────┤   employees    │──────►│   departments   │
└──────┬──────┘       └───────┬────────┘       └─────────────────┘
       │                      │
       ▼                      ├───────────────►┌─────────────────┐
┌─────────────┐               │                │  designations   │
│    roles    │               │                └─────────────────┘
└─────────────┘               ├───────────────►┌─────────────────┐
                              │                │   attendance    │
                              │                └─────────────────┘
                              ├───────────────►┌─────────────────┐
                              │                │ leave_requests  │
                              │                └─────────────────┘
                              └───────────────►┌─────────────────┐
                                               │    payrolls     │
                                               └─────────────────┘
```

### Table Definitions & Roles

| # | Table Name | Purpose & Contents | Key Columns |
|---|---|---|---|
| **1** | `users` | Secure authentication accounts | `id`, `username`, `email`, `password_hash`, `role_id`, `status` |
| **2** | `roles` | Access levels (Admin, Manager, Employee) | `id`, `name`, `slug`, `description` |
| **3** | `permissions` | Granular permission definitions | `id`, `name`, `slug`, `module` |
| **4** | `role_permissions` | Pivot table linking roles to permissions | `role_id`, `permission_id` |
| **5** | `departments` | Organizational divisions (IT, HR, Sales, etc.) | `id`, `name`, `code`, `description`, `head_id` |
| **6** | `designations` | Job titles and organizational hierarchy | `id`, `title`, `department_id`, `grade` |
| **7** | `employees` | Full personal, employment, and banking profiles | `id`, `user_id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`, `department_id`, `designation_id`, `salary`, `date_of_joining`, `status` |
| **8** | `attendance` | Daily physical attendance records | `id`, `employee_id`, `date`, `status` (present, absent, late, half_day, leave, holiday, remote), `notes`, `marked_by`, `created_at` |
| **9** | `leave_requests` | Time-off requests and approval tracking | `id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `days_count`, `reason`, `status` (pending, approved, rejected), `actioned_by`, `admin_remarks` |
| **10** | `payrolls` | Monthly compensation and salary statements | `id`, `employee_id`, `month` (YYYY-MM), `basic_salary`, `allowances`, `deductions`, `net_salary`, `status` (draft, pending, paid), `payment_date` |
| **11** | `announcements` | Company-wide notices and bulletin posts | `id`, `title`, `content`, `priority` (low, normal, high), `published_by`, `created_at` |
| **12** | `audit_logs` | Forensic tamper-evident activity tracking | `id`, `user_id`, `action`, `module`, `target_id`, `ip_address`, `user_agent`, `details`, `created_at` |
| **13** | `password_resets` | Time-limited cryptographic password recovery tokens | `id`, `email`, `token`, `expires_at`, `created_at` |

---

## 👥 4. Role-Based Access Control (RBAC) Matrix

HRMate enforces a strict dual-perspective security model:

```
                            ┌────────────────────────┐
                            │    User Logs In        │
                            └───────────┬────────────┘
                                        │
                         Is Role 'admin' or 'manager'?
                                  /         \
                             YES /           \ NO (Role: 'employee')
                                ▼             ▼
                 ┌───────────────────┐    ┌───────────────────────────┐
                 │ Executive Admin   │    │ Employee Self-Service     │
                 │ Portal Full Access│    │ Portal (Personal Records) │
                 └───────────────────┘    └───────────────────────────┘
```

### Feature Permission Breakdown

| Feature / Action | Administrator (`admin`) | Department Manager (`manager`) | Staff Employee (`employee`) |
|---|:---:|:---:|:---:|
| **Executive Overview Dashboard** | Full Company Metrics | Department Metrics | Hidden (Redirects to Self-Service) |
| **Employee Self-Service Portal** | Read-Only Preview | Read-Only Preview | Full Access (Personal data only) |
| **View Staff Directory** | View & Search All Staff | View Department Staff | Personal Profile Only |
| **Add / Edit Employee Profiles** | Allowed | Restricted | Forbidden (403) |
| **Daily Attendance Sheet (Roll Call)** | Mark & Batch Save All | Mark Department | Hidden |
| **Monthly Attendance Timesheets** | View & Edit All Roster | View Department | View Personal Attendance Only |
| **Web Clock-in Buttons** | Removed (Anti-fraud) | Removed (Anti-fraud) | Removed (Admin verified) |
| **Submit Leave Request** | Allowed | Allowed | Allowed (Personal Request) |
| **Approve / Reject Leave Requests** | Full Approval Power | Department Approval | View Own Request Status Only |
| **Generate Monthly Payroll** | Batch Generate All | Hidden | Hidden |
| **View / Print Payslips** | View & Print All Staff | Hidden | View & Print Personal Payslip |
| **Publish Announcements** | Create & Delete | Hidden | Read Bulletin Feed |
| **Audit Logs Inspection** | Full Forensic Trail | Hidden | Hidden |

---

## 🖥️ 5. Detailed Module Walkthroughs

### 5.1 Authentication & Password Lifecycle Module
* **Secure Login:** Validates credentials against `users` table via `password_verify()` with Bcrypt. Regenerates session ID to prevent session fixation attacks.
* **Forgot Password Recovery:** Users enter registered email; system creates a 60-minute cryptographic token stored in `password_resets`.
* **Password Reset Page:** Validates expiration and token integrity before permitting password changes.
* **CSRF Shield:** Every form submission includes `_csrf_token` checked using timing-safe comparison (`hash_equals`).

### 5.2 Executive Dashboard (Admin View)
* **Real-time Analytics Cards:**
  * Active headcount and departmental distribution.
  * Today's physical attendance percentage.
  * Pending leave approval queue with instant action notification pill.
  * Monthly payroll liability tally.
* **Announcements Board:** Admin can compose instant company notices with priority tagging (Normal, High, Urgent).

### 5.3 Employee Self-Service Portal (`/dashboard` for Employees)
* **Identity Banner:** Displays Employee Name, Code (`EMP-XXXX`), Department, Role, and Date of Joining.
* **Monthly Attendance Performance:** Real-time visual progress bar showing attendance percentage, present days, approved leaves, and weekend totals.
* **Personal Attendance Log:** Chronological table of daily records with color-coded status badges and distinction for today's status.
* **Leave Applications Panel:** Shows submitted leaves with status flags (`Pending Review`, `Approved`, `Rejected`) and remaining quota.
* **My Payslips Tab:** Instant access to generated monthly compensation slips with printable modal view.

### 5.4 Dual-Mode Attendance System (Admin Managed)
To eliminate remote clock-in fraud and simplify administrative overhead, HRMate features an optimized **Dual-Mode Attendance Hub**:

1. **Daily Attendance Sheet (Roll Call Mode):**
   * Accessible via toggle button `[ Daily Attendance Sheet ]`.
   * Date Picker with quick jump to "Today".
   * **1-Click Bulk Actions:** "Mark All Present", "Mark Holiday", "Reset Selection".
   * Live counter summary showing counts of Present, Absent, Half Day, Leave, and Unmarked.
   * Single-click **"Save Daily Attendance Sheet"** submits all employees simultaneously in a single atomic database operation.
2. **Monthly Individual Roster (Detailed Timesheet):**
   * Accessible via toggle button `[ Monthly Individual Roster ]`.
   * Employee selector and Month selector (YYYY-MM).
   * Displays all days of the selected month in a calendar grid.
   * Allows fine-grained status adjustments and remark annotations per day.
   * **1-Click Preset:** "Mark Weekdays as Present" (automatically skips Saturdays and Sundays).
3. **Attendance CSV Export:** Generates clean, formatted CSV reports for management audits.

### 5.5 Leave Management & Approvals Workflow
1. **Application:** Employees submit leave requests specifying Leave Type (*Casual, Sick, Annual, Maternity, Unpaid*), date range, and explanation.
2. **Validation:** System automatically calculates total calendar days excluding past dates.
3. **Admin Review Queue:** Admin reviews pending requests on `/leaves` with one-click **"Approve"** or **"Reject"** buttons and optional admin remarks.
4. **Audit Synchrony:** Approved leaves automatically sync with the attendance grid as `Approved Leave`.

### 5.6 Payroll & Compensation Processing
* **Batch Salary Generation:** Admin selects processing month (YYYY-MM); system generates payroll records based on employees' basic salary, designated allowances, and deductions.
* **Net Salary Formula:**
  $$\text{Net Salary} = \text{Basic Salary} + \text{Allowances} - \text{Deductions}$$
* **Status Workflow:** Transition payroll states from `Draft` $\rightarrow$ `Pending` $\rightarrow$ `Paid`.
* **Printable Payslip Modal:** Generates clean, printer-friendly corporate salary slips with employee code, department, itemized breakdown, and authorized signature line.
* **CSV Payroll Export:** One-click financial download for accounting records.

### 5.7 Forensic Audit Log System
* Every sensitive action across the system triggers an immutable audit log entry in `audit_logs`:
  * Action tags: `LOGIN`, `LOGOUT`, `EMPLOYEE_CREATE`, `ATTENDANCE_DAILY_UPDATE`, `LEAVE_ACTION`, `PAYROLL_GENERATE`, etc.
  * Records Actor User ID, Target Entity ID, Client IP Address, Browser User-Agent, and human-readable action description.

---

## 🔒 6. Security Architecture & Implementation

| Vulnerability Vector | Defense Mechanism in HRMate | Source File Reference |
|---|---|---|
| **SQL Injection (SQLi)** | 100% Prepared Statements via PDO with parameterized inputs; no raw string concatenation | `app/Models/*.php` |
| **Cross-Site Request Forgery (CSRF)** | Timing-safe token verification (`CSRF::verify` using `hash_equals`) on all POST routes | `app/Services/CSRF.php`, `app/Controllers/BaseController.php` |
| **Cross-Site Scripting (XSS)** | Global escaping helper `e()` wrapping `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` | `app/helpers.php` |
| **Credential Theft** | Password hashing via `password_hash($pass, PASSWORD_BCRYPT)` | `app/Models/User.php` |
| **Session Hijacking / Fixation** | Session ID regeneration (`session_regenerate_id(true)`) upon authentication | `app/Controllers/AuthController.php` |
| **Privilege Escalation** | Dual-tier role verification via `AuthMiddleware` and `has_role()` guards | `app/Middleware/AuthMiddleware.php` |
| **Proxy Clock-in Fraud** | Removal of client-side web clock-in buttons; attendance restricted to verified admin entry | `app/Views/dashboard/attendance.php` |

---

## 🚀 7. Step-by-Step Presentation & Viva Script

Jab aap project evaluate karne walon (examiner / client) ke samne present karein, to is 5-step flow ko follow karein:

### Step 1: Login & Role-Based Redirection (2 Minutes)
1. Open `http://localhost:8000/login`.
2. Login as **Admin** (`admin` / `password123`):
   * Point out the **Executive Dashboard**, metrics cards, announcements, and navigation sidebar.
3. Open an Incognito window and login as **Employee** (`bareera.imam` / `password123`):
   * Show that the system automatically detects the role and redirects the employee to their **Employee Self-Service Portal**.
   * Highlight that the employee **cannot** access admin controls, salaries of others, or raw databases.

### Step 2: Demonstrating Staff Management & Departments (2 Minutes)
1. In Admin portal, click **"Employees"** in sidebar.
2. Show the clean staff directory, search bar, and badge states.
3. Click **"Departments & Roles"** to show organizational hierarchy (IT, HR, Finance) and how designations are linked.

### Step 3: Demonstrating the Dual-Mode Attendance System (3 Minutes)
1. Click **"Attendance Roster"** in the sidebar.
2. Show the **"Daily Attendance Sheet"**:
   * Change the date or keep today's date.
   * Click the **"Mark All Present"** button (notice all dropdowns change instantly).
   * Change one employee to "Absent" and another to "Half Day" with an admin note (e.g., "Doctor appointment").
   * Click **"Save Daily Attendance Sheet"** — show the green success notification.
3. Switch to **"Monthly Individual Roster"**:
   * Select an employee and month (e.g. November 2026).
   * Show how the monthly grid computes attendance totals and percentage automatically.
4. Switch back to the **Employee's Incognito Window**:
   * Refresh the page — show that the attendance marked by the admin immediately reflects in the employee's live attendance log!

### Step 4: Leave Application & Approval Flow (2 Minutes)
1. In the **Employee window**, go to **"Leaves & Time-off"**:
   * Submit a new Leave Request (e.g. "Sick Leave" for 2 days).
   * Show status is immediately marked as `Pending Review`.
2. In the **Admin window**, navigate to **"Leave Approvals"**:
   * Point out the incoming pending request.
   * Click **"Approve"** with remark "Approved by HR".
3. Return to the Employee window and refresh — show the status changed to `Approved`!

### Step 5: Payroll & Payslip Generation (2 Minutes)
1. In Admin window, go to **"Payroll & Finance"**.
2. Click **"Generate Payroll"** for the current month.
3. Show the calculated table (Basic + Allowances - Deductions = Net Pay).
4. Click **"View Payslip"** on an employee record:
   * Show the professional printable payslip with company branding and signature line.
5. In Employee window, show that the employee can view and print their own payslip without seeing anyone else's.

### Step 6: Security & Audit Trail Verification (1 Minute)
1. Go to **"Security & Audit"** in the Admin sidebar.
2. Show the live audit table: every login, daily attendance save, leave approval, and payroll action is recorded with timestamp and IP address!

---

## 🔑 8. Quick Reference & System Credentials

### System Credentials

| Role | Username / Identifier | Password | Access Scope |
|---|---|---|---|
| **System Administrator** | `admin` | `password123` | Complete Unrestricted Access |
| **Sample Staff Employee** | `bareera.imam` | `password123` | Employee Self-Service Portal |
| **Sample Staff Employee 2** | `john.doe` | `password123` | Employee Self-Service Portal |

### Quick Start Instructions
1. **One-Click Startup:** Double click `START_HRM.bat` in the project root.
2. **Local URL:** `http://localhost:8000`
3. **Database URL:** `localhost:3306` (Database: `hrm_system`, User: `root`, Password: ``)
4. **Health Check API:** `http://localhost:8000/health`
5. **Git Repository:** `https://github.com/Safeena-Imam/HRMate.git`

---

*Documentation compiled for HRMate Enterprise HRMS.*  
*Ready for academic reports, system manuals, and project defense presentations.*
