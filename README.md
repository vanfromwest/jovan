# CCSICT Faculty Monitoring System
## Real-Time Faculty Presence & Attendance Tracking with QR Code

Complete, professional, and fully functional faculty monitoring system with QR code scanning and real-time status updates.

---

## 📋 TABLE OF CONTENTS

1. [Features](#features)
2. [System Requirements](#system-requirements)
3. [Installation Guide](#installation-guide)
4. [Configuration](#configuration)
5. [Usage Guide](#usage-guide)
6. [Default Credentials](#default-credentials)
7. [Technical Architecture](#technical-architecture)
8. [Database Schema](#database-schema)
9. [Troubleshooting](#troubleshooting)
10. [Support](#support)

---

## ✨ FEATURES

### Core Features
- **QR Code Attendance Tracking**: Automatic attendance recording via unique QR codes
- **Real-Time Status Monitoring**: Live faculty presence status (IN/OUT)
- **Activity & Location Tracking**: Track professor activities and locations
- **Live Public Display**: Display faculty status on monitors and screens
- **Mobile Responsive**: Works on desktop, tablets, and mobile devices
- **AJAX Real-Time Updates**: Updates without page refresh

### User Roles
- **Admin**: Full system control, faculty management, user approval
- **Faculty**: QR scanning, status updates, attendance viewing
- **Student**: Faculty availability search, announcements viewing

### Account Management
- **Registration System**: Self-registration for students and faculty
- **Account Approval**: Admin approval before account activation
- **Profile Management**: Upload profile pictures, manage details
- **Role-Based Access Control**: Different features for different roles

### Faculty Management
- **Faculty Profiles**: Detailed faculty information storage
- **QR Code Generation**: Automatic QR code creation for each faculty
- **Department Assignment**: Organize faculty by departments
- **Status Tracking**: Real-time status updates and history

### Announcements
- **Create/Edit/Delete**: Manage announcements
- **Role-Based Creation**: Admin and faculty can create
- **Student Viewing**: Students can view all announcements
- **Timestamped**: Track announcement creation dates

### Reports & Analytics
- **Attendance Reports**: Monthly and custom period reports
- **Activity Logs**: Track all system activities
- **Attendance Statistics**: Calculate attendance rates

---

## 💻 SYSTEM REQUIREMENTS

### Minimum Requirements
- **Server**: Apache 2.4+
- **PHP**: 7.4 or higher
- **MySQL**: 5.7+ or MariaDB 10.3+
- **RAM**: 2GB minimum
- **Disk Space**: 500MB minimum

### Recommended Requirements
- **Server**: Apache 2.4+
- **PHP**: 8.0 or higher
- **MySQL**: 8.0+
- **RAM**: 4GB
- **Disk Space**: 1GB

### Required PHP Extensions
- MySQLi
- GD Library
- cURL
- Session
- JSON
- Filter

### Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

---

## 🚀 INSTALLATION GUIDE

### Step 1: Download & Extract Project

1. Download the project files
2. Extract the ZIP file
3. Copy the `ccsict_faculty_monitoring` folder to `C:\xampp\htdocs\`
   - The path should be: `C:\xampp\htdocs\ccsict_faculty_monitoring`

### Step 2: Start XAMPP Services

1. Open XAMPP Control Panel
2. Click **Start** next to:
   - **Apache** (wait for it to turn green)
   - **MySQL** (wait for it to turn green)
3. Wait 10-15 seconds for services to fully initialize

### Step 3: Run Setup Script

1. Open your browser
2. Navigate to: `http://localhost/ccsict_faculty_monitoring/setup.php`
3. The setup script will:
   - Create the database
   - Create all necessary tables
   - Insert sample data
   - Initialize default admin account
4. You should see a success message
5. If you see any errors, note them and proceed to the Troubleshooting section

### Step 4: Access the System

1. Go to: `http://localhost/ccsict_faculty_monitoring/`
2. Click "Login" button
3. Use default admin credentials (see below)

---

## ⚙️ CONFIGURATION

### Database Configuration

The database configuration is in: `config/database.php`

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ccsict_faculty_monitoring');
```

**If your MySQL password is different:**

1. Open `config/database.php`
2. Change `define('DB_PASS', '');` to `define('DB_PASS', 'your_password');`
3. Save the file

### System Configuration

Main settings are in: `config/config.php`

- **Site Name**: Change `SITE_NAME` constant
- **Theme Colors**: Modify `SITE_THEME_COLOR` and `SITE_ACCENT_COLOR`
- **Upload Settings**: Adjust file size limits
- **Auto-Refresh Rate**: Change `STATUS_AUTO_REFRESH` (in milliseconds)

### Email Configuration (Optional)

For email notifications, add SMTP settings to `config/config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your@email.com');
define('SMTP_PASS', 'your_password');
```

---

## 👥 DEFAULT CREDENTIALS

### Admin Account
- **Email**: `adminsonic@ccsict.com`
- **Password**: `sonic123`
- **Role**: Admin (Full Access)

⚠️ **IMPORTANT**: Change the admin password immediately after first login!

### Test Accounts

After setup, the following test accounts are created:

#### Test Student
- **Email**: `student@example.com`
- **Password**: `student123`
- **Status**: Pending (needs admin approval)

#### Test Faculty
- **Email**: `faculty@example.com`
- **Password**: `faculty123`
- **Status**: Pending (needs admin approval)

---

## 📖 USAGE GUIDE

### For Admin Users

#### Approve New Accounts
1. Log in with admin credentials
2. Go to "Pending Users" in the sidebar
3. Review user information
4. Click "Approve" or "Reject"
5. Approved users can now login

#### Manage Faculty
1. Go to "Faculty Management"
2. View all faculty members
3. Click "Edit" to modify faculty information
4. Click "Delete" to remove faculty (use cautiously)
5. Each faculty member has a unique QR code

#### Create Announcements
1. Go to "Announcements"
2. Fill in Title and Content
3. Click "Post Announcement"
4. All users can see the announcement

#### View Reports
1. Go to "Reports"
2. Select date range
3. Click "Generate Report"
4. View or print the attendance report

### For Faculty Users

#### Log In
1. Go to the login page
2. Enter your email and password
3. Click "Login"

#### Scan QR Code for Attendance
1. Go to "Scan QR Code" in the sidebar
2. Allow camera access when prompted
3. Point camera at your faculty QR code
4. Attendance is recorded automatically

#### Update Status
1. Go to "My Status"
2. Select your current status (IN or OUT)
3. If OUT, select activity and location
4. Click "Update Status"
5. Status updates in real-time

#### View Attendance Records
1. Go to "Attendance" in the sidebar
2. View your attendance history
3. See time in, time out, and activities

#### View Announcements
1. Go to "Announcements"
2. Read all system announcements

### For Student Users

#### Log In
1. Go to registration page
2. Fill in all required fields
3. Select your role as "Student"
4. Wait for admin approval
5. Once approved, you can login

#### Search Faculty Availability
1. Log in with your student account
2. Go to "Faculty Status"
3. Search by faculty name
4. See real-time availability status

#### View Live Monitor
1. Click "Live Monitor" in navigation
2. See all faculty status in real-time
3. Status updates automatically every 5 seconds
4. Perfect for displaying on office monitors

#### Read Announcements
1. Go to "Announcements"
2. Read all announcements from admin and faculty

---

## 🗄️ DATABASE SCHEMA

### Main Tables

#### users
- Stores all user accounts (Admin, Faculty, Student)
- Fields: id, fullname, username, email, password, role, status, etc.

#### faculty
- Faculty-specific information
- Linked to users table
- Contains QR token information

#### faculty_status
- Real-time faculty status
- Fields: status (IN/OUT), activity, location, updated_at

#### attendance
- Daily attendance records
- Records: time_in, time_out, activity_out, location_out

#### announcements
- System announcements
- Fields: title, content, created_by, created_at

#### activity_logs
- System activity tracking
- Tracks all user actions

#### qr_codes
- QR code metadata and image paths
- Links to faculty records

#### departments
- Department information
- Used for organizing faculty

#### activities
- Predefined activity list (Teaching, Meeting, Break, etc.)

---

## 🔍 TROUBLESHOOTING

### Problem: "Connection refused" error

**Solution:**
1. Make sure MySQL is running in XAMPP
2. Check if Apache is running
3. Verify database credentials in `config/database.php`
4. Restart XAMPP services

### Problem: "Database does not exist" error

**Solution:**
1. Run `setup.php` again at `http://localhost/ccsict_faculty_monitoring/setup.php`
2. Check XAMPP MySQL is running
3. Make sure you have write permissions on the folder

### Problem: White blank page

**Solution:**
1. Check PHP error logs in `php_error.log`
2. Enable error reporting in `config/config.php`
3. Verify all files are uploaded correctly
4. Check file permissions (should be 755 for folders, 644 for files)

### Problem: QR code not generating

**Solution:**
1. Check internet connection (uses external QR service)
2. Verify `qrcodes` folder exists and is writable
3. Enable cURL extension in PHP
4. Check file permissions

### Problem: Can't upload profile pictures

**Solution:**
1. Verify `uploads/profiles` folder exists
2. Set folder permissions to 755
3. Check file size is under 5MB
4. Use JPG, PNG, or GIF format only

### Problem: Login not working

**Solution:**
1. Verify MySQL is running
2. Check if account is approved (status = APPROVED)
3. Verify password is correct
4. Check browser cookies are enabled
5. Clear browser cache and try again

### Problem: Real-time updates not working

**Solution:**
1. Check browser console for AJAX errors (F12)
2. Verify `config/config.php` `SITE_URL` is correct
3. Enable JavaScript in browser
4. Check firewall settings
5. Try refreshing the page

---

## 📊 TECHNICAL ARCHITECTURE

### File Structure

```
ccsict_faculty_monitoring/
├── config/                 # Configuration files
│   ├── config.php         # System configuration
│   └── database.php       # Database connection
├── database/              # Database files
│   └── schema.sql         # Database schema
├── includes/              # Reusable components
│   ├── header.php         # Page header
│   ├── footer.php         # Page footer
│   ├── sidebar.php        # Navigation sidebar
│   ├── session_check.php  # Authentication functions
│   └── functions.php      # Helper functions
├── assets/                # Static files
│   ├── css/style.css      # Styling
│   ├── js/main.js         # Main JavaScript
│   └── js/ajax.js         # AJAX functions
├── admin/                 # Admin pages
│   ├── dashboard.php      # Admin dashboard
│   ├── faculty_management.php
│   ├── pending_users.php  # Account approval
│   ├── announcements.php
│   ├── user_management.php
│   └── attendance_reports.php
├── faculty/               # Faculty pages
│   ├── dashboard.php      # Faculty dashboard
│   ├── scan.php           # QR scanner
│   ├── my_status.php      # Status update
│   ├── attendance.php     # Attendance records
│   ├── announcements.php
│   └── profile.php
├── student/               # Student pages
│   ├── dashboard.php      # Student dashboard
│   ├── faculty_status.php # Faculty search
│   └── announcements.php
├── monitor/               # Public display
│   └── live_display.php   # Live faculty monitor
├── api/                   # AJAX API endpoints
│   ├── scan_qr.php        # QR scanning
│   ├── update_status.php  # Status updates
│   ├── get_faculty_status.php  # Get current status
│   ├── get_activities.php
│   ├── approve_user.php
│   ├── reject_user.php
│   └── delete_announcement.php
├── uploads/               # Uploaded files
│   └── profiles/          # Profile pictures
├── qrcodes/               # Generated QR codes
├── index.php              # Home page
├── login.php              # Login page
├── register.php           # Registration page
├── logout.php             # Logout handler
├── scan.php               # QR scan handler
├── dashboard.php          # Dashboard redirect
├── setup.php              # Installation script
└── README.md              # This file
```

### Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript, jQuery, AJAX
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **QR Generation**: Online QR Code API
- **Server**: Apache 2.4+
- **Browser**: Modern browsers with ES6 support

### Security Features

- **Password Hashing**: bcrypt password hashing
- **SQL Injection Protection**: Prepared statements with parameterized queries
- **Session Management**: Secure session handling
- **Input Validation**: Sanitization and validation of all inputs
- **CSRF Protection**: Token-based protection (can be added)
- **File Upload Validation**: Type and size validation
- **Authorization Checks**: Role-based access control

---

## 📱 RESPONSIVE DESIGN

The system is fully responsive and works on:
- **Desktop**: 1920x1080 and above
- **Laptop**: 1366x768
- **Tablet**: iPad, Android tablets (768px and above)
- **Mobile**: iPhoneX, Android phones (375px and above)

All pages automatically adjust layout for different screen sizes.

---

## 🎨 CUSTOMIZATION

### Change Theme Colors

Edit `config/config.php`:

```php
define('SITE_THEME_COLOR', '#2d5016');     // Dark Green
define('SITE_ACCENT_COLOR', '#ffd700');    // Yellow
```

Edit `assets/css/style.css`:

```css
:root {
    --primary-color: #2d5016;      /* Dark Green */
    --accent-color: #ffd700;       /* Yellow */
}
```

### Change Site Name

Edit `config/config.php`:

```php
define('SITE_NAME', 'Your System Name');
```

### Customize Email Templates

Modify email sections in relevant PHP files.

---

## 📞 SUPPORT

### Common Issues & Solutions

1. **MySQL Connection Issues**
   - Ensure XAMPP MySQL service is running
   - Check database credentials
   - Verify MySQL is listening on port 3306

2. **File Permission Issues**
   - Set folder permissions to 755
   - Set file permissions to 644
   - Run as administrator if needed

3. **QR Code Issues**
   - Check internet connection
   - Verify cURL is enabled in PHP
   - Check `qrcodes` folder permissions

4. **Session Issues**
   - Clear browser cookies
   - Enable sessions in PHP
   - Check session timeout settings

### Getting Help

- Review logs in `php_error.log`
- Check browser developer console (F12)
- Verify all files are in correct locations
- Re-run setup.php if needed
- Check this README for solutions

---

## 📝 LICENSE

This system is provided for CCSICT Faculty use only.

---

## ✅ VERIFICATION CHECKLIST

After installation, verify:

- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running
- [ ] Setup.php ran successfully
- [ ] Can login with admin credentials
- [ ] Admin dashboard loads without errors
- [ ] Can approve pending users
- [ ] Can create faculty accounts
- [ ] Can generate QR codes
- [ ] Live monitor displays faculty correctly
- [ ] AJAX updates work (no console errors)

---

## 🎯 NEXT STEPS

1. **Change Admin Password**: 
   - Login with default credentials
   - Go to Profile
   - Change password

2. **Add Faculty Members**:
   - Go to Faculty Management
   - Add faculty with all required information
   - QR codes are auto-generated

3. **Approve New Accounts**:
   - Go to Pending Users
   - Review and approve accounts

4. **Create Announcements**:
   - Go to Announcements
   - Post important messages

5. **Set Up Live Monitor**:
   - Open Live Monitor page in a browser
   - Display on faculty office monitors
   - Auto-refreshes every 5 seconds

---

**Version**: 1.0  
**Last Updated**: May 28, 2026  
**Status**: Production Ready

For questions or issues, please contact your system administrator.

---

**Made with ❤️ for CCSICT Faculty Monitoring**
