# Installation & Setup Guide - Features 20-29

## Quick Start

### Step 1: Database Migration

1. Log in as Admin
2. Navigate to `/setup/migration.php`
3. Click "Run Migration" button
4. Wait for all migrations to complete

### Step 2: Configure Email (SMTP)

1. Go to Admin Panel > SMTP Configuration
2. Fill in the following settings:

**For Gmail Users:**
```
SMTP Host: smtp.gmail.com
SMTP Port: 587
SMTP Username: your-email@gmail.com
SMTP Password: (Gmail App Password - 16 characters)
From Email: your-email@gmail.com
From Name: CCSICT Faculty Monitoring System
```

**Gmail App Password Setup:**
1. Go to myaccount.google.com
2. Click Security in the left menu
3. Enable 2-Step Verification (if not already done)
4. Find "App passwords" section
5. Select "Mail" and "Windows Computer"
6. Google will generate 16-character password
7. Copy and paste into SMTP Password field

3. Click "Enable Email Notifications" checkbox
4. Enter a test email address
5. Click "Send Test Email"
6. Check the test email was received
7. Save configuration

### Step 3: Configure Additional Features (Optional)

The following features require no additional configuration:
- Notifications system
- Faculty search
- Travel status
- Announcements
- Dashboard widgets

---

## Feature Usage

### For Admins

#### Account Approval with Email
1. Go to Admin > Pending Users
2. Click approve button for a user
3. User automatically receives approval email
4. Notification is created on user's dashboard

#### SMTP Configuration
1. Go to Admin > SMTP Configuration
2. Review and update settings
3. Test email functionality
4. View email delivery logs

#### Create Pinned Announcements
1. Go to Admin > Announcements
2. Click "New Announcement"
3. Fill in title and content
4. Check "Pin Announcement"
5. Set priority level (High/Medium/Low)
6. Set expiration date (optional)
7. Click Create
8. All users receive notification

#### Monitor Faculty Status
1. Go to Monitor > Live Display
2. View all faculty organized by status:
   - **AVAILABLE** (Green section) - Faculty marked as IN
   - **UNAVAILABLE** (Red section) - Faculty marked as OUT with activity/location
   - **ON TRAVEL** (Blue section) - Faculty on approved travel with destination

### For Faculty

#### View Dashboard
1. Log in to Faculty Dashboard
2. See pinned notifications at top
3. Click notification bell to see all notifications
4. Click announcement to read full details

#### Search Faculty
1. Use search box on dashboard
2. Search by name, department, position, location
3. Results update instantly
4. Click on faculty member to view details

#### Update Travel Status
1. Go to Faculty > My Status
2. If going on travel:
   - Select "ON TRAVEL" status
   - Enter destination
   - Enter purpose
   - Select start and end dates
   - System auto-calculates duration
   - Save travel record

### For Students

#### View Announcements
1. Go to Dashboard > Announcements
2. Pinned announcements appear first
3. Read full announcement details
4. See publication date and expiration date

#### Search Faculty
1. Use Faculty Search on dashboard
2. Find faculty member information
3. View current status and location

---

## File Locations

### New Files Created
```
includes/email_functions.php         - Email and notification functions
api/notifications.php                - Notification API
api/search_faculty.php               - Faculty search API
api/announcements.php                - Announcement management
api/travel_status.php                - Travel record management
api/get_widgets.php                  - Dashboard widgets
admin/smtp_config.php                - SMTP configuration page
setup/migration.php                  - Database migration script
database/migrations.sql              - Migration SQL
config/mail.php                      - Email configuration
FEATURES_20-29.md                    - This documentation
```

### Modified Files
```
api/approve_user.php                 - Now sends email + creates notification
monitor/live_display.php             - Enhanced with IN/OUT/TRAVEL sections
config/config.php                    - Added new constants
```

---

## Database Changes

### New Tables
- `notifications` - Store notification messages
- `email_logs` - Track email delivery
- `travel_records` - Store faculty travel information
- `email_config` - Store SMTP settings

### Modified Tables
- `faculty_status`: Added travel_record_id and support for ON TRAVEL status
- `announcements`: Added is_pinned, priority, expiration_date

---

## Testing the Features

### Test 1: Email Approval Notification
1. Create a new user account (pending approval)
2. Go to Admin > Pending Users
3. Approve the account
4. Check the user's email for approval message

### Test 2: Pinned Notifications
1. Create announcement and pin it
2. Log in as student/faculty
3. Check dashboard - pinned announcement should appear at top

### Test 3: Faculty Search
1. On any dashboard
2. Use search box
3. Type faculty member name
4. Results should appear instantly

### Test 4: Travel Status
1. Log in as faculty
2. Go to My Status
3. Create a travel record
4. Check monitor display - should show in ON TRAVEL section

### Test 5: Dashboard Widgets
1. View dashboard
2. Widgets should show current counts
3. Update a faculty status
4. Refresh page - widget counts should update

---

## Troubleshooting

### Issue: Emails not sending
**Solution:**
1. Check SMTP credentials in Admin > SMTP Configuration
2. Verify email_config table has correct values
3. Check email logs for error messages
4. Test email button should work if configured correctly

### Issue: Notifications not appearing
**Solution:**
1. Check notifications table exists in database
2. Verify notification type is valid
3. Check user_id is correct
4. Ensure database migration completed successfully

### Issue: Faculty search returns no results
**Solution:**
1. Check faculty members exist in database with status = APPROVED
2. Verify search query matches faculty data
3. Check database connection is working

### Issue: Travel status not updating
**Solution:**
1. Ensure travel_records table exists
2. Verify faculty_status has travel_record_id column
3. Check travel dates are valid (start_date <= end_date)
4. Ensure faculty_id exists and is active

---

## Security Notes

### Email Configuration
- SMTP passwords are stored in database
- Only admins can view/edit SMTP settings
- Use Gmail app passwords, not account passwords
- Enable 2FA on Gmail accounts for security

### Notifications
- Notifications are user-specific
- Only logged-in users can access their notifications
- Admins cannot view other users' private notifications

### Search
- Faculty search only returns APPROVED faculty
- All user roles can search
- Search is read-only operation

---

## Performance Optimization

### Database Indexes
All new tables have proper indexes on:
- Foreign keys
- Frequently queried columns
- Date/time fields used in filtering

### API Caching
- Dashboard widgets can be cached (10 seconds)
- Announcements can be cached (5 seconds)
- Notifications marked as read are efficient

---

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari 14+, Chrome Mobile)

---

## Support & Updates

For support or issues:
1. Check FEATURES_20-29.md for detailed documentation
2. Review error logs in email_logs table
3. Check database migration status
4. Contact system administrator

---

## What's New Summary

✓ **Email Notifications** - Account approval emails via Gmail SMTP
✓ **Notification Center** - Pinned notifications on all dashboards
✓ **Faculty Search** - AJAX search across faculty database
✓ **Travel Tracking** - Enhanced status management with travel records
✓ **Real-Time Announcements** - Pinned and prioritized announcements
✓ **Monitor Enhancement** - Separate IN/OUT/TRAVEL sections
✓ **Dashboard Widgets** - Real-time statistics display
✓ **Mobile Responsive** - Works on all devices
✓ **TV Display Friendly** - Optimized for monitor walls
✓ **Email Management** - Complete email log tracking

---

**Version:** 2.0 (Features 20-29)
**Last Updated:** June 22, 2026
**Status:** Production Ready
