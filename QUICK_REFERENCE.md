# CCSICT Faculty Monitoring System
## Quick Reference Guide

### 🌐 System URLs

| Page | URL |
|------|-----|
| Home Page | http://localhost/ccsict_faculty_monitoring/ |
| Login | http://localhost/ccsict_faculty_monitoring/login.php |
| Register | http://localhost/ccsict_faculty_monitoring/register.php |
| Setup | http://localhost/ccsict_faculty_monitoring/setup.php |
| Admin Dashboard | http://localhost/ccsict_faculty_monitoring/admin/dashboard.php |
| Faculty Dashboard | http://localhost/ccsict_faculty_monitoring/faculty/dashboard.php |
| Student Dashboard | http://localhost/ccsict_faculty_monitoring/student/dashboard.php |
| Live Monitor | http://localhost/ccsict_faculty_monitoring/monitor/live_display.php |

### 👤 Default Credentials

**Admin Account**
- Email: adminsonic@ccsict.com
- Password: sonic123

### 🚀 Quick Start

1. Start XAMPP (Apache + MySQL)
2. Open http://localhost/ccsict_faculty_monitoring/setup.php
3. Wait for setup to complete
4. Go to http://localhost/ccsict_faculty_monitoring/
5. Click Login
6. Use admin credentials above

### 📋 Admin Tasks

- **Approve Accounts**: Pending Users → Approve/Reject
- **Manage Faculty**: Faculty Management → Add/Edit/Delete
- **Create Announcements**: Announcements → Create New
- **View Reports**: Reports → Select Date Range
- **Manage Users**: User Management → View All

### 👨‍🏫 Faculty Tasks

- **Scan QR**: Scan QR Code → Point camera at QR
- **Update Status**: My Status → Select Status/Activity
- **View Attendance**: Attendance → View History
- **Check Announcements**: Announcements → Read

### 👤 Student Tasks

- **Search Faculty**: Faculty Status → Search by name
- **View Live Monitor**: Live Monitor → Auto-updates
- **Read Announcements**: Announcements → View All

### 🛠️ Folder Locations

| Folder | Purpose |
|--------|---------|
| /config | Configuration files |
| /database | Database schema |
| /includes | Reusable components |
| /assets | CSS, JS files |
| /admin | Admin pages |
| /faculty | Faculty pages |
| /student | Student pages |
| /monitor | Public monitor page |
| /api | AJAX API endpoints |
| /uploads | User uploads |
| /qrcodes | Generated QR codes |

### 📊 Key Features

✓ QR Code Scanning  
✓ Real-Time Status  
✓ Live Monitor Display  
✓ Account Approval System  
✓ Attendance Tracking  
✓ Announcements  
✓ Role-Based Access  
✓ Mobile Responsive  

### 🔐 Security

- Change admin password immediately
- Use strong passwords for all accounts
- Keep system updated
- Backup database regularly
- Monitor activity logs

### 📞 Help

- Check README.md for detailed documentation
- Review TROUBLESHOOTING section in README.md
- Check browser console (F12) for errors
- Review PHP error logs

### ⚡ Performance Tips

1. Clear browser cache regularly
2. Monitor database size
3. Archive old attendance records
4. Optimize server resources
5. Enable compression in Apache

---

**System Version**: 1.0  
**Last Updated**: May 28, 2026

For complete documentation, see README.md
