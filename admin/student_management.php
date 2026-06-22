<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Student Management';
requireRole(['Admin']);

$students = getUsersByRole('Student');
$departments = getAllDepartments();
$currentUserId = getCurrentUserId();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <h1 class="h3 mb-4"><i class="bi bi-people"></i> Student Management</h1>

                <?php if (empty($students)): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i> No students found in the system.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact Number</th>
                                    <th>Status</th>
                                    <th>Department</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr id="student-row-<?php echo $student['id']; ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($student['fullname']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['contact_number'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php 
                                            $statusColor = $student['status'] === 'APPROVED' ? 'success' : ($student['status'] === 'PENDING' ? 'warning' : 'danger');
                                            ?>
                                            <span class="badge bg-<?php echo $statusColor; ?>"><?php echo htmlspecialchars($student['status']); ?></span>
                                        </td>
                                        <td><?php echo getDepartmentName($student['department_id']); ?></td>
                                        <td><?php echo formatDate($student['created_at']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-primary edit-student-btn" data-student-id="<?php echo $student['id']; ?>" title="Edit Student">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-danger delete-student-btn" data-student-id="<?php echo $student['id']; ?>" data-student-name="<?php echo htmlspecialchars($student['fullname']); ?>" title="Delete Student">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editStudentModalLabel">Edit Student Information</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editStudentForm">
                    <div class="modal-body">
                        <input type="hidden" id="editStudentId" name="user_id">
                        
                        <div class="mb-3">
                            <label for="editStudentFullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editStudentFullname" name="fullname" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editStudentEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="editStudentEmail" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editStudentContact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="editStudentContact" name="contact_number" placeholder="+63 9XX XXX XXXX">
                        </div>
                        
                        <div class="mb-3">
                            <label for="editStudentDept" class="form-label">Department</label>
                            <select class="form-select" id="editStudentDept" name="department_id">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="editStudentMessage" class="alert d-none" role="alert"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveStudentBtn">
                            <span class="spinner-border spinner-border-sm d-none me-2" id="editStudentSpinner" role="status" aria-hidden="true"></span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        
        // Edit Student Button Handler
        document.querySelectorAll('.edit-student-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const studentId = this.dataset.studentId;
                loadStudentForEdit(studentId);
            });
        });
        
        // Delete Student Button Handler
        document.querySelectorAll('.delete-student-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const studentId = this.dataset.studentId;
                const studentName = this.dataset.studentName;
                deleteStudent(studentId, studentName);
            });
        });
        
        // Load student data for editing
        function loadStudentForEdit(studentId) {
            fetch(`${SITE_URL}/api/get_user.php?user_id=${studentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const student = data.data;
                        document.getElementById('editStudentId').value = student.id;
                        document.getElementById('editStudentFullname').value = student.fullname;
                        document.getElementById('editStudentEmail').value = student.email;
                        document.getElementById('editStudentContact').value = student.contact_number || '';
                        document.getElementById('editStudentDept').value = student.department_id || '';
                        
                        // Clear previous messages
                        clearMessages('editStudentMessage');
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
                        modal.show();
                    } else {
                        showAlert('Error loading student details: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error loading student details', 'danger');
                });
        }
        
        // Handle form submission
        document.getElementById('editStudentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const spinner = document.getElementById('editStudentSpinner');
            const saveBtn = document.getElementById('saveStudentBtn');
            
            spinner.classList.remove('d-none');
            saveBtn.disabled = true;
            
            fetch(`${SITE_URL}/api/update_user.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                spinner.classList.add('d-none');
                saveBtn.disabled = false;
                
                if (data.success) {
                    showMessage('editStudentMessage', 'Student information updated successfully!', 'success');
                    
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('editStudentModal')).hide();
                        location.reload();
                    }, 1500);
                } else {
                    showMessage('editStudentMessage', data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                spinner.classList.add('d-none');
                saveBtn.disabled = false;
                showMessage('editStudentMessage', 'Error updating student information', 'danger');
            });
        });
        
        // Delete student function
        function deleteStudent(studentId, studentName) {
            const confirmText = `Are you sure you want to delete the student account for "${studentName}"?\n\nThis action cannot be undone and all associated data will be permanently removed.`;
            
            if (confirm(confirmText)) {
                const formData = new FormData();
                formData.append('user_id', studentId);
                
                fetch(`${SITE_URL}/api/delete_user.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Student account deleted successfully!', 'success');
                        document.getElementById(`student-row-${studentId}`).style.opacity = '0.5';
                        
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error deleting student account', 'danger');
                });
            }
        }
        
        // Helper functions
        function showMessage(elementId, message, type) {
            const element = document.getElementById(elementId);
            element.className = `alert alert-${type}`;
            element.textContent = message;
            element.classList.remove('d-none');
        }
        
        function clearMessages(elementId) {
            const element = document.getElementById(elementId);
            element.className = 'alert d-none';
            element.textContent = '';
        }
        
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Insert at top of content
            const contentWrapper = document.querySelector('.content-wrapper');
            contentWrapper.insertBefore(alertDiv, contentWrapper.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>
