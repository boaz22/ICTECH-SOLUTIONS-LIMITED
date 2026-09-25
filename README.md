# ICTECH Solutions Limited - Training Management Platform

ICTECH Solutions Limited is a PHP and MySQL training platform with a public course catalogue, administrator-managed enrollment, student progress tracking, trainer workflows, and certificate issuance.

## Features

- Public website with course information and enquiries
- Administrator console for students, enrollments, courses, trainers, certificates, reports, and site content
- Student portal for enrolled courses, progress, profile, and certificates
- Trainer portal for assigned learner progress and completion approval
- Secure authentication with bcrypt password hashing, CSRF protection, and session management
- First-login password change for accounts provisioned by an administrator

## Enrollment model

ICTECH does **not** process online payments or store payment gateway data. Course fees and arrangements are handled directly with ICTECH outside the website.

1. A prospective student browses courses and submits an enquiry, or contacts ICTECH directly.
2. ICTECH and the student agree on the course, schedule, and fees.
3. An administrator creates the student account in the Admin Console and may enroll the student in the agreed course immediately.
4. The student receives an email containing a temporary password and a direct student-login URL.
5. The student must replace the temporary password at first login before accessing the portal.

Public registration does not create student accounts. The direct student-login URL is intentionally not displayed in the public navigation.

## Requirements

- Apache (included with XAMPP)
- PHP 7.4 or later
- MySQL 5.7 or later
- Composer dependencies installed from `composer.json`

## Local setup

1. Create a database named `ictech_solutions_limited`.
2. Import `database/schema.sql`.
3. For an existing installation, run every file in `database/migrations/` in numeric order (001 through 011) if they have not already been applied.
4. Copy `includes/config.sample.php` to `includes/config.php`, then configure the database, site URL, and mail credentials.
5. Start Apache and MySQL and open the configured `SITE_URL`.

## Development credentials

The schema includes a development administrator account:

- Email: `admin@ictech.local`
- Password: `Admin@123`

Change or remove sample credentials and data before deployment.

## Project structure

```text
ICTECH-SOLUTIONS-LIMITED/
├── admin/                 Administrator console
├── assets/                Stylesheets, scripts, and images
├── database/
│   ├── migrations/        Incremental schema migrations
│   └── schema.sql         Fresh-install database schema
├── includes/              Authentication, database, mail, and shared helpers
├── student/               Student portal
├── trainer/               Trainer portal
├── uploads/               Runtime user uploads
├── course-enquiry.php     Public course enquiry endpoint
├── force-password-change.php
├── login.php              Staff login and direct student-login endpoint
└── register.php           Account-provisioning information page
```

## Email configuration

Set the following values in `includes/config.php`:

```php
define('MAIL_FROM', 'noreply@your-domain.example');
define('MAIL_FROM_NAME', 'ICTECH Solutions Limited');
define('MAIL_REPLY_TO', 'info@your-domain.example');
define('MAIL_HOST', 'mail.your-domain.example');
define('MAIL_PORT', 465);
define('MAIL_USERNAME', 'noreply@your-domain.example');
define('MAIL_PASSWORD', 'replace-with-a-secure-password');
define('MAIL_ENCRYPTION', 'ssl');
```

The administrator account-creation process uses these settings to deliver the temporary password and direct login URL.

## Before production

- Set `DEBUG_MODE` to `false`.
- Configure a production database user and strong credentials.
- Configure a real HTTPS `SITE_URL`.
- Configure valid SMTP credentials and test account-provisioning email delivery.
- Change or remove all development accounts and sample data.
- Ensure `uploads/` is writable by the web server and is not executable.
- Apply all outstanding database migrations.

## Technology

- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5
- PDO prepared statements
- PHPMailer when installed through Composer, with PHP `mail()` fallback
