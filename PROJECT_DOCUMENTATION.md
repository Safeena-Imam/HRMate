# 📋 HRMate – Project Documentation
### HR Management System | Complete Guide

---

## 📌 What is HRMate?

**HRMate** is a web-based **HR Management System** (Human Resource Management System).
It is used by a company to manage its employees, attendance, payroll, departments, and more — all in one place.

Think of it like a **digital office** where:
- Admin can manage all employees
- Employees can check their own info, attendance, leaves
- Everything is stored safely in a database

---

## 💻 Technologies We Used

| Technology | What it does in our project |
|---|---|
| **PHP 8.2** | Main backend language — handles all logic |
| **MySQL (MariaDB)** | Database — stores all data (employees, users, etc.) |
| **HTML5** | Structure of all web pages |
| **CSS3** | Styling — colors, layout, fonts |
| **JavaScript** | Makes pages interactive (sidebar toggle, etc.) |
| **Bootstrap 5** | Ready-made CSS framework for responsive design |
| **Bootstrap Icons** | All icons used in the UI |
| **XAMPP** | Local server tool — runs MySQL on our PC |
| **PHP Built-in Server** | Runs PHP on `localhost:8000` without Apache |

---

## 🏗️ How We Built It — Architecture

We used the **MVC Pattern** (Model-View-Controller).
This is a way to organize code properly so it is clean and easy to manage.

```
MVC = Model + View + Controller

Model      → Deals with Database (fetch, save, update data)
View       → What the user SEES (HTML pages)
Controller → The brain — connects Model and View
```

### Folder Structure Explained:

```
HRM SYSTEM/
│
├── app/                    ← Main application code
│   ├── Controllers/        ← Brain — handles requests
│   ├── Models/             ← Database logic
│   ├── Views/              ← HTML pages (what user sees)
│   ├── Middleware/         ← Security checks (login required, roles)
│   └── Services/           ← Helper services (auth, validation, etc.)
│
├── config/                 ← Settings (database, app name, etc.)
├── database/               ← SQL file to create all tables
├── public/                 ← Public folder (CSS, JS, Images)
│   └── assets/
│       ├── css/app.css     ← All custom styles
│       ├── js/app.js       ← All custom JavaScript
│       └── images/         ← Logo and other images
│
├── routes/web.php          ← URL routing (which URL goes where)
├── bin/                    ← Admin tools (create admin account, etc.)
├── storage/                ← Logs and uploaded files
├── .env                    ← Secret settings (DB password, app name)
├── START_HRM.bat           ← One-click system starter
└── PROJECT_DOCUMENTATION.md ← This file!
```

---

## ⚙️ How the System Works — Step by Step

### When a user opens the browser:

```
1. User types: http://localhost:8000
        ↓
2. PHP server receives the request
        ↓
3. routes/web.php checks: which page is requested?
        ↓
4. Router calls the correct Controller
        ↓
5. Controller talks to the Model (gets data from database)
        ↓
6. Controller sends data to the View (HTML page)
        ↓
7. User sees the final webpage
```

---

## 🗄️ Database — What We Store

**Database Name:** `hrm_system`

| Table | What it stores |
|---|---|
| `users` | Login accounts (username, password, role) |
| `employees` | Employee personal info (name, email, phone, etc.) |
| `departments` | Company departments (HR, IT, Finance, etc.) |
| `designations` | Job titles (Manager, Developer, Accountant, etc.) |
| `roles` | User roles (Admin, Manager, Employee) |
| `audit_logs` | Record of every action done in the system |
| `password_resets` | Secure time-limited tokens for password recovery |

---

## 👥 User Roles

The system has **3 types of users:**

| Role | What they can do |
|---|---|
| **Admin** | Full access — manage everything |
| **Manager** | Can see team, approve leaves, view reports |
| **Employee** | Can only see their own profile, attendance, payslip |

---

## 🖥️ Pages We Built

### 1. Login Page
- User enters username and password
- Has a "Forgot Password?" recovery link
- If correct → goes to Dashboard

### 2. Password Recovery & Reset Pages
- **Forgot Password:** User enters their registered email to receive a recovery token.
- **Reset Password:** Validates time-sensitive secure link and allows user to set a new password.
- Fully protected with CSRF and expiration timers (60 minutes).

### 3. Executive Dashboard (Admin)
- Shows overview: total employees, departments, recent activity
- Has charts and quick stats

### 3. Attendance Page
- Track who came to work and who didn't
- Mark attendance manually

### 4. Payroll Page
- Manage employee salaries
- View payslips

### 5. Departments Page
- View and manage company departments
- Add/edit department names

### 6. Audit Log Page
- Shows history of every action done in the system
- Who did what and when

---

## 🎨 UI Design — How it Looks

### Color Theme:
- **Dark Blue** `#0a252c` → Sidebar background
- **Teal/Green** `#0a9396` → Buttons, active links, highlights
- **White** → Main content area

### Layout:
```
┌─────────────────────────────────────────┐
│         TOP BAR (Logo + User Info)      │
├──────────┬──────────────────────────────┤
│          │                              │
│ SIDEBAR  │    MAIN CONTENT AREA         │
│ (Menu)   │    (Dashboard, Tables, etc.) │
│          │                              │
└──────────┴──────────────────────────────┘
```

### Sidebar Features:
- Can **collapse** (become narrow) and **expand**
- Arrow button `‹` / `›` to toggle
- Shows icons only when collapsed
- Shows icons + text when expanded

---

## 🔒 Security Features We Added

| Feature | How it works |
|---|---|
| **Password Hashing** | Passwords are never stored as plain text (uses bcrypt) |
| **CSRF Protection** | Prevents fake form submissions from other websites |
| **Role-Based Access** | Each user only sees what they are allowed to see |
| **Auth Middleware** | If not logged in, redirects to login page automatically |
| **Audit Logging** | Every important action is recorded with timestamp |

---

## 🚀 How to Run the System

### Quick Start (Easiest way):
1. Go to `HRM SYSTEM` folder
2. **Double-click** `START_HRM.bat`
3. Open browser → go to `http://localhost:8000`
4. Login with:
   - **Username:** `admin`
   - **Password:** `AdminPassword2026!`

### Manual Start:
1. Open XAMPP → Start **MySQL**
2. Open CMD/PowerShell
3. Type: `cd "c:\Users\safee\Desktop\HRM SYSTEM"`
4. Type: `php -S localhost:8000 -t public`
5. Open browser → `http://localhost:8000`

---

## 📦 What We Used — Quick Summary

```
Backend Language   →  PHP 8.2
Database           →  MySQL (via XAMPP/MariaDB)
Frontend           →  HTML + CSS + JavaScript
CSS Framework      →  Bootstrap 5
Icons              →  Bootstrap Icons
Architecture       →  MVC (Model-View-Controller)
Local Server       →  PHP Built-in Server (port 8000)
Database Tool      →  XAMPP (MariaDB 10.4)
```

---

## 👨‍💻 Developer Notes

- App Name: **HRMate**
- Version: 1.0
- PHP Version Required: 8.0+
- Database: MySQL / MariaDB
- Default Port: 8000
- Environment File: `.env` (contains secret settings)

---

*Documentation created for HRMate HR Management System*
*Last Updated: October 2026*

