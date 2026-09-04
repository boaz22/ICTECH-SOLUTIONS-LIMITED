# ICTECH Solutions Limited - Website & Training Management Platform

**Professional Technology Training Platform Built with PHP, MySQL, Bootstrap 5, and M-Pesa Integration**

---

## 📋 Project Overview

ICTECH Solutions is a complete web-based training management platform featuring:

✅ **Public Website** - Professional corporate site with course catalog
✅ **Student Portal** - Dashboard, course enrollment, payment tracking
✅ **Admin Dashboard** - Course and user management (Phase 2)
✅ **M-Pesa Integration** - Secure payment processing
✅ **Responsive Design** - Mobile-friendly interface
✅ **Secure Authentication** - Password hashing, session management, CSRF protection
✅ **Database Driven** - MySQL with prepared statements for security

---

## 🚀 Quick Start Guide

### Phase 1 Setup (Current)

#### Prerequisites
- **Apache Web Server** (included in XAMPP)
- **PHP 7.4 or higher**
- **MySQL 5.7 or higher**
- **XAMPP** (recommended for local development)

---

## 📦 Installation Steps

### 1. Download and Extract

The project is located at: `c:\xampp\htdocs\ICTECH\`

### 2. Create Database

**Method A: Using phpMyAdmin**

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database: `ictech_solutions_limited`
3. Select the database
4. Go to **Import** tab
5. Upload file: `database/schema.sql`
6. Click **Import**

For an existing database, import `database/migrations/001_training_workflow.sql` once after pulling the latest code.

**Method B: Using Command Line**

```bash
mysql -u root -p < c:\xampp\htdocs\ICTECH\database\schema.sql
```

(Press Enter when prompted for password - default XAMPP has empty password)

### 3. Configure Database Connection

1. Copy configuration template:
   ```bash
   copy includes\config.sample.php includes\config.php
   ```

2. Edit `includes/config.php` if needed (default XAMPP settings are already configured):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Empty for XAMPP
   define('DB_NAME', 'ictech_solutions_limited');
   ```

### 4. Create Uploads Directory

Create the `uploads/` folder:
```bash
mkdir c:\xampp\htdocs\ICTECH\uploads
```

Or manually create it in Windows Explorer.

### 5. Start XAMPP

1. Start Apache and MySQL from XAMPP Control Panel
2. Access the site: `http://localhost/ICTECH/`

---

## 🔑 Test Credentials (Development Only)

### Admin Account
- **Email:** `admin@ictech.local`
- **Password:** `Admin@123`
- **Role:** Administrator

### Student Accounts
- **Email:** `john.kamau@email.com`
- **Email:** `mary.kipchoge@email.com`
- **Email:** `peter.ochieng@email.com`
- **Password:** All use `password` (hashed in database)

⚠️ **IMPORTANT:** Change these credentials before deploying to production!

---

## 📁 Project Structure

```
ICTECH/
├── admin/                      # Admin dashboard (Phase 2)
├── student/                    # Student portal
│   ├── dashboard.php          # Student dashboard
│   ├── my-courses.php         # Course management
│   ├── payments.php           # Payment history
│   └── profile.php            # Account settings
├── includes/                  # Core components
│   ├── config.php             # Configuration (not in git)
│   ├── config.sample.php      # Configuration template
│   ├── db.php                 # Database connection
│   ├── auth.php               # Authentication functions
│   ├── helpers.php            # Utility functions
│   ├── header.php             # Public header
│   ├── footer.php             # Public footer
│   ├── student-header.php     # Student portal header
│   └── student-footer.php     # Student portal footer
├── assets/                    # Static assets
│   ├── css/
│   │   ├── style.css         # Main stylesheet
│   │   └── student.css       # Student portal styles
│   ├── js/
│   │   └── main.js           # JavaScript utilities
│   └── images/               # Logo, hero, course, and partner images
├── payment/                   # Payment processing (Phase 2)
├── uploads/                   # User uploads
├── database/
│   └── schema.sql            # Database schema
├── index.php                 # Homepage
├── courses.php               # Course listing
├── course-details.php        # Course details
├── about.php                 # About page
├── services.php              # Services page
├── students.php              # For students page
├── resources.php             # Resources page
├── contact.php               # Contact form
├── register.php              # Student registration
├── login.php                 # Student login
├── logout.php                # Logout handler
└── .gitignore               # Git ignore file
```

---

## 🔐 Security Features

✅ **Password Security**
- Passwords hashed with bcrypt (`password_hash()`)
- Verified with `password_verify()`
- Never stored in plaintext

✅ **Database Security**
- PDO prepared statements prevent SQL injection
- Parameterized queries for all database operations

✅ **Session Security**
- Sessions regenerated after login
- CSRF tokens on all forms
- Session timeout handling

✅ **Input Validation**
- Email validation
- Phone number validation
- File upload validation
- HTML escaping output

✅ **Access Control**
- Admin authentication check
- Student role verification
- Redirect on unauthorized access

---

## 📝 Database Schema Overview

### Users Table
Stores student and admin accounts

### Categories Table
Course categories for organization

### Courses Table
Course information (title, price, duration, status, etc.)

### Enrollments Table
Student course enrollments with status tracking

### Payments Table
Payment transactions and M-Pesa integration data

### Testimonials Table
Student/trainer feedback for homepage

### Partners Table
Training partner organizations and logos

### Contact Messages Table
Contact form submissions

---

## 🎯 Page Structure & Features

### Public Pages

**Homepage (`index.php`)**
- Hero section with call-to-action
- Featured courses carousel
- Statistics display
- Testimonials section
- Training partners logos
- Professional design with animations

**Courses (`courses.php`)**
- Course listing with filtering
- Category and search filters
- Pagination
- Course cards with details
- Enrollment buttons

**Course Details (`course-details.php`)**
- Full course information
- Learning objectives
- Prerequisites/requirements
- Related courses
- Enrollment section

**About (`about.php`)**
- Company mission and vision
- Core values
- Why choose ICTECH
- Professional sections

**Services (`services.php`)**
- Service offerings
- Service cards with descriptions
- Call-to-action sections

**Contact (`contact.php`)**
- Contact form
- Contact information
- Business hours
- Social media links
- Form submission handling

**Resources (`resources.php`)**
- Learning materials
- Video tutorials
- Study guides
- Code examples
- Community forum links

**For Students (`students.php`)**
- Getting started guide
- FAQ section
- Quick links
- Student benefits

### Authentication Pages

**Register (`register.php`)**
- Student registration form
- Email and password validation
- Duplicate email checking
- CSRF protection

**Login (`login.php`)**
- Email/password authentication
- Secure session creation
- Redirect after login
- Forgot password link

**Logout (`logout.php`)**
- Session cleanup
- Cookie removal
- Redirect to homepage

### Student Portal

**Dashboard (`student/dashboard.php`)**
- Welcome message with statistics
- Active courses count
- Completed courses count
- Total enrollment count
- Total spent amount
- Recent enrollments list
- Profile card

**My Courses (`student/my-courses.php`)**
- Course listing by status
- Active courses tab
- Completed courses tab
- Progress indicators
- Enrollment management

**Payments (`student/payments.php`)**
- Payment history table
- Transaction statistics
- Payment status tracking
- Receipt view option

**Profile (`student/profile.php`)**
- Profile information edit
- Password change
- Account settings
- Security settings

---

## 🎨 Design Features

**Color Scheme**
- Primary: Navy Blue (#001a4d)
- Secondary: Orange (#ff9800)
- Accent: Light Gray (#f5f5f5)

**Typography**
- Professional sans-serif fonts
- Clear hierarchy
- Readable font sizes

**Components**
- Bootstrap 5 for responsive design
- Custom CSS for branding
- Font Awesome icons
- Smooth animations and transitions

**Responsive Design**
- Mobile-first approach
- Tablet optimized
- Desktop enhanced
- Media queries for all devices

---

## 💳 M-Pesa Integration (Phase 2)

The payment structure is set up and ready for M-Pesa integration:

**Configuration Location:** `includes/config.php`

**Required Credentials:**
- Consumer Key
- Consumer Secret
- Business Shortcode
- Passkey

**Payment Flow:**
1. Student enrolls in course
2. Enrollment record created (pending status)
3. Student proceeds to payment
4. M-Pesa STK push sent to phone
5. Student enters PIN
6. Callback updates payment status
7. Enrollment activated upon successful payment

**Implementation files ready:**
- `payment/initiate.php` - Initiate M-Pesa transaction
- `payment/callback.php` - Handle M-Pesa callbacks
- `payment/mpesa.php` - M-Pesa API wrapper

---

## 🛠️ Configuration Guide

### Email Settings
Edit `includes/config.php`:
```php
define('MAIL_FROM', 'noreply@ictechsolutions.co.ke');
define('MAIL_FROM_NAME', 'ICTECH Solutions Limited');
```

### M-Pesa Settings (Phase 2)
```php
define('MPESA_CONSUMER_KEY', 'your-key-here');
define('MPESA_CONSUMER_SECRET', 'your-secret-here');
define('MPESA_BUSINESS_SHORTCODE', 'your-shortcode');
define('MPESA_PASSKEY', 'your-passkey');
```

### Upload Settings
```php
define('MAX_UPLOAD_SIZE', 5242880);  // 5MB
define('ALLOWED_UPLOAD_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
```

---

## 📱 Mobile Optimization

The platform is fully responsive:
- **Desktop:** Full sidebar navigation, multiple columns
- **Tablet:** Adjusted layouts, touch-friendly buttons
- **Mobile:** Hamburger menu, single column, large touch targets

---

## 🔄 Workflow Examples

### Student Registration & Enrollment Flow
1. New student visits `register.php`
2. Fills registration form with validation
3. Password hashed and stored
4. Redirected to login
5. Logs in with credentials
6. Accesses student dashboard
7. Browses courses at `courses.php`
8. Views course details at `course-details.php`
9. Clicks "Enroll" button
10. Enrollment record created (pending status)
11. Redirected to payment (Phase 2)
12. After payment confirmed, enrollment activated

### Admin Course Management (Phase 2)
1. Admin logs in to admin panel
2. Navigates to course management
3. Creates new course with details
4. Uploads course image
5. Sets as featured or published
6. Course appears on public site

---

## 🐛 Troubleshooting

### Database Connection Error
- Check XAMPP MySQL is running
- Verify database name in `config.php`
- Ensure `ictech_training` database exists

### Uploads Directory Error
- Create `uploads/` folder in project root
- Set folder permissions to 755 (Linux/Mac) or writable (Windows)

### Sessions Not Working
- Clear browser cookies
- Check that `includes/config.php` exists
- Verify PHP session settings

### Login Issues
- Ensure database has sample data (run schema.sql)
- Check email and password match sample data
- Clear browser cache and cookies

### CSS/JS Not Loading
- Clear browser cache (Ctrl+Shift+Delete)
- Verify SITE_URL in `config.php` is correct
- Check file paths are correct

---

## 📋 Checklist: Before Production Deployment

- [ ] Change all default passwords
- [ ] Update M-Pesa credentials
- [ ] Set DEBUG_MODE to false
- [ ] Configure real email settings
- [ ] Remove sample data from database
- [ ] Set proper file permissions
- [ ] Enable HTTPS
- [ ] Configure backup system
- [ ] Set up error logging
- [ ] Test payment flow completely
- [ ] Perform security audit
- [ ] Load test the system

---

## 🚀 Next Steps (Phase 2)

1. **Admin Dashboard**
   - Admin authentication
   - Course management CRUD
   - User management
   - Payment management
   - Testimonial management
   - Partner management

2. **Payment System**
   - M-Pesa API integration
   - Payment processing
   - Callback handling
   - Transaction logging
   - Invoice generation

3. **Advanced Features**
   - Email notifications
   - SMS alerts
   - Certificate generation
   - Progress tracking
   - Instructor portal
   - Chat/messaging system

---

## 📞 Support & Documentation

For issues or questions:
1. Check the troubleshooting section
2. Review the code comments
3. Check database schema for structure
4. Test with sample data provided

---

## 📄 License & Terms

This platform is built for ICTECH Solutions Limited.

---

## 🎯 Development Workflow

### Making Changes
1. Never edit `includes/config.php` directly - use `config.sample.php` as template
2. Always use prepared statements for database queries
3. Sanitize and validate all user input
4. Use helper functions from `includes/helpers.php`
5. Test on multiple devices

### Testing Locally
1. Start XAMPP (Apache + MySQL)
2. Access `http://localhost/ICTECH/`
3. Test all public pages
4. Test registration and login
5. Test student portal
6. Test contact form

### Git Workflow
- `.gitignore` prevents committing sensitive files
- `config.php` is never committed
- `uploads/` is in `.gitignore`
- Always commit `config.sample.php` after updating structure

---

## 🎓 Key Technologies

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** Bootstrap 5, HTML5, CSS3, Vanilla JavaScript
- **Security:** PDO, password_hash, CSRF tokens, input validation
- **Payment:** M-Pesa Daraja API (Phase 2)

---

## 📊 Sample Data

The database includes:
- 1 admin account
- 3 sample student accounts
- 6 sample courses
- 6 course categories
- 3 sample testimonials
- 5 training partners

Use these for testing all features.

---

**Version:** 1.0 - Phase 1 (Foundation)
**Last Updated:** 2024
**Status:** Ready for Phase 2 Implementation

---

For detailed API documentation and advanced configuration, see individual file comments in the source code.
