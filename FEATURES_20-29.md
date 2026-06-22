# CCSICT Faculty Monitoring System - Features 20-29 Implementation Guide

## Overview
This document outlines all the new mandatory features (20-29) that have been implemented in the CCSICT Faculty Monitoring System.

---

## Feature 20: Account Approval Notification System

### Description
When an Admin approves a Faculty or Student account, the user automatically receives an email notification.

### Implementation Details

#### Email Method: Gmail SMTP
- **PHPMailer Integration**: Uses PHPMailer library for reliable email delivery
- **SMTP Configuration**: Stores configuration in database (`email_config` table)

#### Key Components

1. **Email Configuration Page** (`admin/smtp_config.php`)
   - Configure SMTP Host, Port, Username, Password
   - Set From Email and From Name
   - Test email functionality
   - View email logs
   - Enable/disable email notifications

2. **Email Functions** (`includes/email_functions.php`)
   - `getEmailConfig()` - Retrieve configuration
   - `setEmailConfig()` - Save configuration
   - `sendEmail()` - Send emails via SMTP or PHP mail()
   - `sendApprovalEmail()` - Send approval notification
   - `testEmailConfiguration()` - Test email setup
   - `logEmail()` - Track email delivery
   - `getEmailLogs()` - View email history

3. **Database Changes**
   - New table: `email_logs` - Track email delivery status
   - New table: `email_config` - Store SMTP settings

4. **Modified API**
   - `api/approve_user.php` - Now sends approval email and creates notification

#### Email Template
**Subject**: CCSICT Faculty Monitoring System - Account Approved

**Content**:
```
Hello [Full Name],

Your account has been approved by the administrator.

You may now log in to the CCSICT Faculty Monitoring System.

Account Information:
- Username: [username]
- Email: [email]
- Role: [role]

Thank you!
```

### Setup
1. Go to Admin > SMTP Configuration
2. Enter your Gmail SMTP credentials
3. Test the email configuration
4. Enable email notifications

---

## Feature 21: Pinned Notifications on Dashboard

### Description
Create a Notification Center with pinned notifications displayed on Admin, Faculty, and Student dashboards.

### Notification Types
- Account Approved
- New Announcement
- Faculty Status Changes
- Attendance Notifications
- System Notifications

### Features
- ✓ Pinned Notifications (always appear at top)
- ✓ Mark as Read
- ✓ Unread Counter
- ✓ Real-Time Updates
- ✓ Notification Bell Icon (ready for frontend integration)

### Key Components

1. **Notification Functions** (`includes/email_functions.php`)
   - `createNotification()` - Create new notification
   - `getNotifications()` - Retrieve notifications
   - `getPinnedNotifications()` - Get pinned only
   - `getUnreadNotificationCount()` - Get unread count
   - `markNotificationAsRead()` - Mark as read
   - `markAllNotificationsAsRead()` - Mark all as read
   - `toggleNotificationPin()` - Pin/unpin notification
   - `deleteNotification()` - Delete notification

2. **API Endpoint** (`api/notifications.php`)
   - GET notifications: `/api/notifications.php?action=get`
   - Get pinned: `/api/notifications.php?action=pinned`
   - Mark as read: `/api/notifications.php` (POST with action=mark_read)
   - Get unread count: `/api/notifications.php?action=unread_count`
   - Toggle pin: `/api/notifications.php` (POST with action=toggle_pin)

3. **Database Changes**
   - New table: `notifications`
   - Fields: id, user_id, title, message, type, is_read, is_pinned, action_url, created_at, updated_at

### Usage Example
```php
// Create a notification when account is approved
createNotification(
    $userId,
    'Account Approved',
    'Your account has been approved by the administrator.',
    NOTIFICATION_ACCOUNT_APPROVED,
    SITE_URL . '/login.php',
    true  // Pin the notification
);

// Get unread count for dashboard
$count = getUnreadNotificationCount($userId);
```

---

## Feature 22: Advanced Faculty Search

### Description
Create an advanced faculty search system with AJAX live search, autocomplete, and instant results.

### Search By
- Faculty Name
- Department
- Position
- Status
- Location
- Activity

### Features
- ✓ AJAX Live Search
- ✓ Search Suggestions
- ✓ Auto Complete
- ✓ Instant Results (no page refresh)

### Search Results Display
- Faculty Picture
- Faculty Name
- Department
- Current Status
- Current Activity
- Current Location

### API Endpoint

**URL**: `/api/search_faculty.php`

**Methods**:
```
GET /api/search_faculty.php?q=john&type=name
GET /api/search_faculty.php?q=CS&type=department
GET /api/search_faculty.php?q=room&type=location
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "faculty_id": 1,
      "fullname": "Dr. John Doe",
      "email": "john@university.edu",
      "position": "Professor",
      "status": "IN",
      "activity": "Teaching",
      "location": "Room 204",
      "profile_image": "profile_1.jpg"
    }
  ],
  "count": 1
}
```

### Frontend Integration Example
```html
<input type="text" id="faculty-search" placeholder="Search faculty...">
<div id="search-results"></div>

<script>
$(document).on('input', '#faculty-search', function() {
    let query = $(this).val();
    if (query.length < 1) return;
    
    $.get('/api/search_faculty.php', { q: query }, function(response) {
        if (response.success) {
            // Display search results
        }
    });
});
</script>
```

---

## Feature 23: Enhanced Status Management - Travel

### Description
Add "ON TRAVEL" status for faculty with automatic duration calculation.

### New Status: ON TRAVEL

**Additional Fields**:
- Destination
- Purpose
- Start Date
- End Date
- Total Days (automatically calculated)

### Features
- ✓ Create travel records
- ✓ Automatic duration calculation
- ✓ Update faculty status to "ON TRAVEL"
- ✓ Display travel information on monitor

### Example Display
```
Prof. Juan Dela Cruz

✈ ON TRAVEL

Destination: Manila
Duration: 5 Days
Purpose: Faculty Seminar
```

### Key Components

1. **Travel Functions** (`includes/email_functions.php`)
   - `createTravelRecord()` - Create travel record
   - `getActiveTravelRecord()` - Get current travel
   - `getTravelRecords()` - Get all travel records
   - `updateTravelRecord()` - Update travel info
   - `endTravelRecord()` - End travel status
   - `updateFacultyStatusToTravel()` - Update status to ON TRAVEL

2. **API Endpoint** (`api/travel_status.php`)
   - GET active travel: `/api/travel_status.php?action=get`
   - Create travel: `/api/travel_status.php` (POST with action=create)
   - Update travel: `/api/travel_status.php` (POST with action=update)
   - End travel: `/api/travel_status.php` (POST with action=end)

3. **Database Changes**
   - New table: `travel_records` (id, faculty_id, destination, purpose, start_date, end_date, total_days, is_active, timestamps)
   - Modified: `faculty_status` table
     - Added: `status` ENUM includes 'ON TRAVEL'
     - Added: `travel_record_id` (FK to travel_records)

### Usage Example
```php
// Create travel record
createTravelRecord($facultyId, 'Manila', 'Faculty Seminar', '2026-07-01', '2026-07-05');

// Get active travel
$travel = getActiveTravelRecord($facultyId);

// End travel
endTravelRecord($travelId);
```

---

## Feature 24: Real-Time Announcement System

### Description
Announcements update automatically without page refresh with priority levels and expiration dates.

### Features
- ✓ AJAX Auto Refresh (5-second intervals)
- ✓ Live Announcement Feed
- ✓ Real-Time Dashboard Updates
- ✓ New Announcement Popup
- ✓ Pin Announcements
- ✓ Priority Levels (High, Medium, Low)
- ✓ Expiration Dates

### Announcement Fields
- Title
- Content
- Priority (HIGH, MEDIUM, LOW)
- is_pinned (Boolean)
- expiration_date (DateTime)

### Display Logic
- Pinned announcements always appear first
- Sorted by priority level
- Expired announcements are hidden
- New announcements trigger notifications to all users

### API Endpoint

**URL**: `/api/announcements.php`

**Methods**:
```
GET /api/announcements.php?action=get&limit=20
POST /api/announcements.php (action=create) - Admin only
POST /api/announcements.php (action=update)
POST /api/announcements.php (action=delete) - Admin only
```

**Create Announcement**:
```json
{
  "action": "create",
  "title": "New Announcement",
  "content": "Announcement content here",
  "is_pinned": 1,
  "priority": "HIGH",
  "expiration_date": "2026-12-31 23:59:59"
}
```

### Database Changes
- Modified: `announcements` table
  - Added: `is_pinned` (Boolean, default 0)
  - Added: `priority` (ENUM: LOW, MEDIUM, HIGH, default MEDIUM)
  - Added: `expiration_date` (DateTime, nullable)

---

## Feature 25: Single Screen Faculty Monitor

### Description
Display ALL faculty members on ONE SCREEN with responsive grid layout, no scrolling required.

### Requirements Met
- ✓ No Vertical Scrolling (with smart grid)
- ✓ No Horizontal Scrolling
- ✓ Responsive Grid Layout
- ✓ TV Display Friendly
- ✓ Auto Resize Faculty Cards

### Display Includes
- Faculty Picture
- Faculty Name
- Department
- Status
- Activity
- Location
- Last Updated

### Technical Implementation
- **CSS Grid**: Dynamic grid with `grid-template-columns: repeat(auto-fill, minmax(180px, 1fr))`
- **Responsive Breakpoints**: Adapts to screen size
- **Flexbox Layout**: Proper spacing and alignment
- **No Fixed Heights**: Cards scale with content

### Responsive Grid
- **Desktop (1200px+)**: ~7-8 faculty per row
- **Tablet (768px-1199px)**: ~5-6 faculty per row
- **Mobile (< 768px)**: ~3-4 faculty per row

---

## Feature 26: Separate IN/OUT/TRAVEL Display

### Description
Faculty divided into three color-coded sections for better usability and organization.

### Section 1: AVAILABLE FACULTY (IN)
- **Header Color**: Green (#28a745)
- **Status Badge**: ✓ IN
- **Display Info**: Faculty picture, name, position
- **Count**: Shows total available faculty

### Section 2: UNAVAILABLE FACULTY (OUT)
- **Header Color**: Red (#dc3545)
- **Status Badge**: ✕ OUT
- **Display Info**: 
  - Faculty picture
  - Name
  - Position
  - Current Activity
  - Current Location
- **Example**: "Teaching at Room 204"
- **Count**: Shows total unavailable faculty

### Section 3: ON TRAVEL FACULTY
- **Header Color**: Blue (#0066cc)
- **Status Badge**: ✈ ON TRAVEL
- **Display Info**:
  - Faculty picture
  - Name
  - Position
  - ✈ Destination
  - Duration (days)
  - Purpose (optional)
- **Example**: "Prof. Juan Dela Cruz | ✈ Manila | 5 Days"
- **Count**: Shows total traveling faculty

### Implementation
- **Location**: `/monitor/live_display.php` (Enhanced version)
- **Layout**: Three separate sections, each with faculty grid
- **Styling**: Color-coded headers and status badges
- **Dashboard Widgets**: Quick count view above sections

---

## Feature 27: Real-Time Dashboard Widgets

### Description
Dashboard showing key statistics that update in real-time via AJAX.

### Widgets Display
1. **Faculty IN** - Count of faculty with status IN
2. **Faculty OUT** - Count of faculty with status OUT
3. **Faculty ON TRAVEL** - Count of faculty on travel
4. **Total Faculty** - Total number of faculty
5. **Total Students** - Count of approved students
6. **Pending Accounts** - Count of pending approvals
7. **Total Announcements** - Count of active announcements

### API Endpoint

**URL**: `/api/get_widgets.php`

**Response**:
```json
{
  "success": true,
  "data": {
    "total_faculty": 45,
    "faculty_in": 28,
    "faculty_out": 15,
    "faculty_travel": 2,
    "total_students": 850,
    "pending_accounts": 3,
    "total_announcements": 12
  },
  "timestamp": "2026-06-22 14:30:00"
}
```

### Key Functions

```php
// Get all statistics at once
$stats = getDashboardStats();

// Returns array with:
// - total_faculty
// - faculty_in
// - faculty_out
// - faculty_travel
// - total_students
// - pending_accounts
// - total_announcements
```

### Frontend Integration

```html
<div class="widgets-row">
    <div class="widget">
        <h6>Faculty IN</h6>
        <div class="number" id="faculty-in">0</div>
    </div>
    <div class="widget">
        <h6>Faculty OUT</h6>
        <div class="number" id="faculty-out">0</div>
    </div>
    <!-- More widgets -->
</div>

<script>
// Auto-refresh widgets every 10 seconds
setInterval(function() {
    $.get('/api/get_widgets.php', function(response) {
        if (response.success) {
            $('#faculty-in').text(response.data.faculty_in);
            $('#faculty-out').text(response.data.faculty_out);
            // Update other widgets
        }
    });
}, 10000);
</script>
```

---

## Feature 28: Database Tables

### Tables Created

#### 1. notifications
```sql
CREATE TABLE `notifications` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('ACCOUNT_APPROVED', 'ANNOUNCEMENT', 'STATUS_CHANGE', 'ATTENDANCE', 'SYSTEM'),
  `is_read` BOOLEAN DEFAULT 0,
  `is_pinned` BOOLEAN DEFAULT 0,
  `action_url` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
)
```

#### 2. email_logs
```sql
CREATE TABLE `email_logs` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` LONGTEXT,
  `status` ENUM('PENDING', 'SENT', 'FAILED') DEFAULT 'PENDING',
  `error_message` TEXT,
  `sent_at` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
)
```

#### 3. travel_records
```sql
CREATE TABLE `travel_records` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `faculty_id` INT NOT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `purpose` TEXT,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_days` INT,
  `is_active` BOOLEAN DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`faculty_id`) REFERENCES `faculty`(`id`) ON DELETE CASCADE
)
```

#### 4. email_config
```sql
CREATE TABLE `email_config` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `config_key` VARCHAR(100) NOT NULL UNIQUE,
  `config_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
```

### Tables Modified

#### faculty_status
- Added: `status` ENUM now includes 'ON TRAVEL'
- Added: `travel_record_id` INT (FK to travel_records)

#### announcements
- Added: `is_pinned` BOOLEAN DEFAULT 0
- Added: `priority` ENUM('LOW', 'MEDIUM', 'HIGH') DEFAULT 'MEDIUM'
- Added: `expiration_date` DATETIME

---

## Feature 29: Installation & Migration

### Database Setup

1. **Run Migration Script**
   - URL: `http://localhost/ccsict_faculty_monitoring/setup/migration.php`
   - Creates all new tables
   - Modifies existing tables
   - Initializes email configuration

2. **SMTP Configuration**
   - URL: `http://localhost/ccsict_faculty_monitoring/admin/smtp_config.php`
   - Configure SMTP settings
   - Test email delivery
   - View email logs

### Files Created

#### Backend
- `includes/email_functions.php` - All email and notification functions
- `api/notifications.php` - Notification API endpoints
- `api/search_faculty.php` - Faculty search endpoint
- `api/announcements.php` - Announcement management
- `api/travel_status.php` - Travel record management
- `api/get_widgets.php` - Dashboard widgets data
- `admin/smtp_config.php` - SMTP configuration page
- `setup/migration.php` - Database migration script

#### Database
- `database/migrations.sql` - Migration SQL script
- `config/mail.php` - Email configuration

#### Modified
- `api/approve_user.php` - Now sends approval email
- `monitor/live_display.php` - Enhanced with IN/OUT/TRAVEL sections
- `config/config.php` - Added new constants

### Dependencies

#### Composer (Optional but Recommended)
```json
{
    "require": {
        "phpmailer/phpmailer": "^6.9"
    }
}
```

**Installation**:
```bash
cd /path/to/ccsict_faculty_monitoring
composer require phpmailer/phpmailer
```

If Composer is not available, the system will fall back to PHP's built-in mail() function.

---

## Configuration Files

### config/mail.php
Contains email template and configuration constants.

### config/config.php
Added constants:
- `FACULTY_STATUS_TRAVEL` = 'ON TRAVEL'
- `NOTIFICATION_*` - Notification type constants
- `ANNOUNCEMENT_*` - Priority level constants
- `*_REFRESH_INTERVAL` - Update intervals

---

## API Reference

### Notifications
- GET `/api/notifications.php?action=get` - Get all notifications
- GET `/api/notifications.php?action=pinned` - Get pinned notifications
- GET `/api/notifications.php?action=unread_count` - Get unread count
- POST `/api/notifications.php` (action=mark_read) - Mark as read
- POST `/api/notifications.php` (action=toggle_pin) - Toggle pin
- POST `/api/notifications.php` (action=delete) - Delete notification

### Faculty Search
- GET `/api/search_faculty.php?q=query&type=search_type`

### Announcements
- GET `/api/announcements.php?action=get`
- POST `/api/announcements.php` (action=create)
- POST `/api/announcements.php` (action=update)
- POST `/api/announcements.php` (action=delete)

### Travel Status
- GET `/api/travel_status.php?action=get`
- POST `/api/travel_status.php` (action=create)
- POST `/api/travel_status.php` (action=update)
- POST `/api/travel_status.php` (action=end)

### Dashboard Widgets
- GET `/api/get_widgets.php`

---

## User Experience Improvements Implemented

✓ Real-Time Notifications on approval
✓ Gmail Approval Email Notifications
✓ AJAX Faculty Search
✓ Travel Monitoring with auto-calculated duration
✓ Real-Time Announcements with priorities
✓ One-Screen Faculty View (no scrolling)
✓ Separate IN/OUT/TRAVEL Sections
✓ Pinned Dashboard Notifications
✓ Mobile Responsive Design
✓ TV Monitor Optimized Display
✓ Dashboard Widgets with real-time updates
✓ Email logs and tracking
✓ SMTP configuration management

---

## Testing Checklist

- [ ] Database migration completes successfully
- [ ] SMTP configuration page loads and works
- [ ] Test email sends successfully
- [ ] Approve user account and receive email
- [ ] Notification appears on dashboard
- [ ] Search faculty by name returns results
- [ ] Create travel record for faculty
- [ ] ON TRAVEL status displays correctly on monitor
- [ ] Create new announcement
- [ ] Announcement appears on all dashboards
- [ ] Pin announcement and verify it appears first
- [ ] Dashboard widgets load and display correct counts
- [ ] Monitor display shows IN/OUT/TRAVEL sections

---

## Support & Troubleshooting

### Email Not Sending?
1. Check SMTP configuration in Admin > SMTP Configuration
2. Verify credentials are correct
3. Check email logs for errors
4. Ensure Gmail has 2FA enabled and app password is used

### Travel Records Not Showing?
1. Verify travel_records table exists
2. Check faculty_status has travel_record_id column
3. Ensure start_date <= today <= end_date for active records

### Notifications Not Appearing?
1. Verify notifications table exists
2. Check user_id is correct
3. Ensure notification type is valid
4. Verify is_read and is_pinned flags

---

## Version Information
- **Features**: 20-29 (Mandatory Features)
- **Database Tables**: 4 new, 2 modified
- **API Endpoints**: 7 new
- **Admin Pages**: 1 new (SMTP Configuration)
- **Display Pages**: 1 enhanced (Live Monitor)

---

For more information or support, contact the system administrator.
