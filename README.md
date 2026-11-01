# Apex HRMS &bull; Advanced Human Resource Management System (Part 1)

Apex HRMS is an enterprise-grade, modular Human Resource Management System built with **PHP 8+**, **MySQL / MariaDB**, **PDO**, **Bootstrap 5**, and **Vanilla JavaScript**, designed for high security, role-based governance, and seamless local deployment with **XAMPP**.

---

## 🌟 Architecture & Part 1 Completed Modules

- **Modern Clean MVC Architecture**: Front controller pattern with lightweight PSR-4 autoloader, environment variable loader, and subfolder-tolerant router.
- **Relational Database Design**: 8 tables with primary keys, foreign key constraints, indexes, cascade policies, and audit logging (`database/schema.sql`).
- **Strict Security Layer**:
  - Secure password hashing with PHP native `password_hash()` and `password_verify()` (BCrypt, cost factor 12).
  - Session Fixation mitigation with `session_regenerate_id()`, `HttpOnly`, `SameSite=Lax`, and inactivity timeouts.
  - Cryptographically secure CSRF protection on state-modifying requests (`hash_equals()`).
  - Parameterized prepared statements across all PDO queries to eliminate SQL injection.
  - Server-side input validation and error collection (`Validator.php`).
- **Role-Based Access Control (RBAC)**: Support for Administrator, HR Officer, Department Manager, and Employee tiers with fine-grained permission matrices.
- **CLI-Only Administrator Provisioning**: Safe provisioning tool (`bin/create_admin.php`) preventing public registration exposure or hardcoded credentials.
- **Executive Dashboard**: Dark teal / emerald design system with real-time employee counts (Total, Active, Inactive, On Leave), department distribution, recent employee roster, Chart.js attendance preview, and security audit logs.
- **Automated Test Suite**: 28 automated checks covering database connectivity, schema integrity, credentials verification, RBAC, CSRF, and session termination (`bin/test_suite.php`).

---

## 📂 Project Structure

```text
HRM SYSTEM/
├── .env                  # Active environment configuration (excluded from public web)
├── .env.example          # Safe configuration template without credentials
├── .gitignore            # Excludes sensitive environment files and logs
├── README.md             # Complete installation & XAMPP guide
├── index.php             # Root forwarder (gracefully routes XAMPP htdocs access to public/)
├── .htaccess             # Root Apache rewrite configuration
├── app/
│   ├── bootstrap.php     # Core initialization, PSR-4 autoloader, centralized error handling
│   ├── helpers.php       # Global helpers (url, asset, csrf_token, csrf_field, has_role, etc.)
│   ├── Controllers/
│   │   ├── BaseController.php      # Base controller with view and CSRF validation
│   │   ├── AuthController.php      # Login, session generation, and secure logout
│   │   ├── DashboardController.php # Live HR metrics, workforce stats, audit records
│   │   └── ErrorController.php     # 403, 404, 500 error views
│   ├── Middleware/
│   │   ├── AuthMiddleware.php      # Enforces active session and inactivity timeout
│   │   ├── GuestMiddleware.php     # Redirects authenticated users away from login
│   │   └── RoleMiddleware.php      # Enforces role and permission requirements
│   ├── Models/
│   │   ├── User.php                # Authentication lookups, last login tracking
│   │   ├── Role.php                # Roles and role_permissions matrices
│   │   ├── Department.php          # Department lookups and employee aggregates
│   │   ├── Designation.php         # Position titles and department relations
│   │   ├── Employee.php            # Relational workforce data, status breakdowns
│   │   └── AuditLog.php            # Security and administrative event trail
│   ├── Services/
│   │   ├── AuthService.php         # Password verification, session payload, audit logging
│   │   ├── PermissionService.php   # RBAC permission and role evaluation
│   │   ├── CSRF.php                # CSRF token generation and timing-safe comparison
│   │   ├── Validator.php           # Server-side input validation engine
│   │   ├── Logger.php              # Centralized logging to storage/logs/app.log
│   │   └── Env.php                 # Safe environment variables parser
│   └── Views/
│       ├── layouts/
│       │   ├── main.php            # Responsive dashboard shell (sidebar, topbar, footer)
│       │   └── auth.php            # Focused card layout for login and error pages
│       ├── partials/
│       │   ├── topbar.php          # Top navigation, user profile dropdown, secure sign out
│       │   ├── sidebar.php         # Dark teal navigation with active pill indicators
│       │   ├── alerts.php          # Flash notification component
│       │   └── footer.php          # System copyright and metadata footer
│       ├── auth/
│       │   └── login.php           # Sign-in form with password toggle & setup guidance
│       ├── dashboard/
│       │   └── index.php           # Summary cards, workforce roster, Chart.js analytics
│       └── errors/
│           ├── 403.php             # Access forbidden view
│           ├── 404.php             # Page not found view
│           └── 500.php             # Internal server error view
├── bin/
│   ├── create_admin.php            # Secure CLI administrator provisioning tool
│   └── test_suite.php              # Automated test runner (28 checks)
├── config/
│   ├── app.php                     # Application settings, session cookies, timezone
│   └── database.php                # PDO singleton with utf8mb4 and error masking
├── database/
│   └── schema.sql                  # MySQL DDL schema + roles, permissions, depts, designations
├── public/
│   ├── .htaccess                   # Apache URL rewrite rules to front controller
│   ├── index.php                   # Public Front Controller entry point
│   └── assets/
│       ├── css/
│       │   └── app.css             # Enterprise dark teal styling, responsive tables, cards
│       └── js/
│           └── app.js              # Vanilla JS: sidebar toggle, alert auto-dismiss, Chart.js
├── routes/
│   └── web.php                     # Route definitions and middleware mappings
└── storage/
    ├── logs/
    │   └── app.log                 # Application runtime and security log
    └── uploads/                    # Non-public storage for future attachments
```

---

## 🚀 Exact XAMPP Setup Instructions for Windows

Follow these step-by-step instructions to run Apex HRMS on your Windows PC with XAMPP.

### Step 1: Start Apache and MySQL Services

1. Open the **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`).
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
4. Ensure both services show green status indicators.

### Step 2: Database Creation & Schema Import

#### Method A: Using phpMyAdmin (Web Browser)
1. In your browser, open [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Click on the **Databases** tab.
3. Enter database name: `hrm_system` and Collation: `utf8mb4_unicode_ci`, then click **Create**.
4. Select the newly created `hrm_system` database on the left panel.
5. Click on the **Import** tab at the top.
6. Click **Choose File** and select `database/schema.sql` from your project folder:
   `c:\Users\safee\Desktop\HRM SYSTEM\database\schema.sql`
7. Click **Import** at the bottom of the page.

#### Method B: Using Windows Command Prompt / PowerShell
Run the following single command in PowerShell or Command Prompt:
```powershell
Get-Content "c:\Users\safee\Desktop\HRM SYSTEM\database\schema.sql" | C:\xampp\mysql\bin\mysql.exe -u root
```

### Step 3: Configure Environment Variables

1. Ensure the file `.env` exists in the project root directory (it was created automatically from `.env.example`).
2. Open `.env` and verify your local database settings (XAMPP default has user `root` with no password):
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hrm_system
   DB_USERNAME=root
   DB_PASSWORD=
   DB_CHARSET=utf8mb4
   ```

### Step 4: Provision Your First Administrator Account (CLI)

For security, Apex HRMS **does not have a public registration page or hardcoded default passwords**.
Run the provisioning tool from the project root in Command Prompt or PowerShell:

```bash
# Interactive mode (prompts for username, email, and password):
php bin/create_admin.php

# Or non-interactive mode:
php bin/create_admin.php admin admin@company.org AdminPassword2026! "Eleanor" "Vance"
```

Output confirmation:
```text
=======================================================
  [SUCCESS] Administrator Account Successfully Created!
=======================================================
  Username: admin
  Email:    admin@company.org
  Role:     Administrator (Full Privileges)
  Status:   Active
-------------------------------------------------------
  You can now log in at: http://localhost/HRM SYSTEM/public/login
```

### Step 5: Access the Application in Your Browser

You can access the system in either of two ways:

#### Option 1: Via XAMPP Apache
If your project folder is placed in `C:\xampp\htdocs\HRM SYSTEM` or linked via virtual host:
- **Direct URL**: [http://localhost/HRM%20SYSTEM/public/login](http://localhost/HRM%20SYSTEM/public/login)
- Or simply: [http://localhost/HRM%20SYSTEM/](http://localhost/HRM%20SYSTEM/) (the root redirector will automatically route you to `/public/`)

#### Option 2: Via PHP Built-in Server (Recommended for instant testing)
In your project directory, run:
```bash
php -S localhost:8000 -t public
```
Then open your web browser and navigate to:
[http://localhost:8000/login](http://localhost:8000/login)

---

## 🧪 Testing and Verification Suite

To run the automated verification test suite from the terminal:
```bash
php bin/test_suite.php
```

All 28 automated tests will run and report status:
- Database connectivity & PDO configuration
- Relational schema seed data (4 roles, 15 permissions, 5 departments, 8 initial employees)
- Password hashing & secure verification
- Rejection of invalid credentials & nonexistent identities
- Successful login & session state initialization
- Role-based access control (RBAC) & permission evaluation
- CSRF token generation and validation
- Input validation service
- Security & audit log trail
- Safe session logout & destruction

You can also check the health endpoint in your browser or curl:
```bash
curl http://localhost:8000/health
```

---

## 📋 Part 1 Deliverables Summary

| Item | Requirement | Status |
| :--- | :--- | :--- |
| **A. Project Structure** | Modular PHP MVC layout with non-public config and uploads | ✅ Completed |
| **B. MySQL Database** | Schema with 8 tables, PKs, FKs, indexes, constraints, seed data | ✅ Completed |
| **C. Database Connection** | PDO singleton with utf8mb4, exception handling, and `.env` loader | ✅ Completed |
| **D. Authentication** | Login, logout, session security, BCrypt hashing, RBAC, CLI admin provisioning | ✅ Completed |
| **E. Executive Dashboard** | Dark teal design, metric cards, live employee & department counts, Chart.js preview | ✅ Completed |
| **F. Core Functionality** | Front controller, clean routing, layouts, AuthMiddleware, CSRF, Validator, Logger | ✅ Completed |
| **G. Testing** | 28 automated CLI tests + real browser flow validation with recording & screenshots | ✅ Completed |

---

## 🚀 Upcoming in Part 2 (When Requested)
- **Employee Management (CRUD)**: Create, view, edit, filter, and archive employee records with profile image uploads and document attachments.
- **Department & Designation Manager**: Interface to create and assign departments, reporting lines, and salary grades.
- **User & Access Control Panel**: Web-based user account creation, password resets, and dynamic role/permission assignment.
- **Attendance & Timesheet Module**: Daily clock-in/out, biometric log sync, and overtime calculation.
- **Payroll Calculation Engine**: Salary component breakdown, tax formulas, and PDF pay slip generation.
