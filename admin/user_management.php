<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'User Management';
requireRole(['Admin']);

$users = getAllUsers();
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
                <h1 class="h3 mb-4"><i class="bi bi-gear"></i> User Management</h1>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Department</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr id="user-row-<?php echo $user['id']; ?>">
                                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($user['role']); ?></span></td>
                                    <td>
                                        <?php 
                                        $statusColor = $user['status'] === 'APPROVED' ? 'success' : ($user['status'] === 'PENDING' ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?php echo $statusColor; ?>"><?php echo htmlspecialchars($user['status']); ?></span>
                                    </td>
                                    <td><?php echo getDepartmentName($user['department_id']); ?></td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info edit-user-btn" data-user-id="<?php echo $user['id']; ?>" title="Edit User">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($user['id'] !== $currentUserId): ?>
                                                <button type="button" class="btn btn-danger delete-user-btn" data-user-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['fullname']); ?>" title="Delete User">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm">
                    <div class="modal-body">
                        <input type="hidden" id="editUserId" name="user_id">
                        
                        <div class="mb-3">
                            <label for="editFullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editFullname" name="fullname" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editContactNumber" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="editContactNumber" name="contact_number">
                        </div>
                        
                        <div class="mb-3">
                            <label for="editDepartmentId" class="form-label">Department</label>
                            <select class="form-control" id="editDepartmentId" name="department_id">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="editUserMessage" class="alert d-none" role="alert"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveUserBtn">
                            <span class="spinner-border spinner-border-sm d-none me-2" id="editUserSpinner" role="status" aria-hidden="true"></span>
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
        
        // Edit User Button Handler
        document.querySelectorAll('.edit-user-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                loadUserForEdit(userId);
            });
        });
        
        // Delete User Button Handler
        document.querySelectorAll('.delete-user-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                const username = this.dataset.username;
                deleteUser(userId, username);
            });
        });
        
        // Load user data for editing
        function loadUserForEdit(userId) {
            fetch(`${SITE_URL}/api/get_user.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.data;
                        document.getElementById('editUserId').value = user.id;
                        document.getElementById('editFullname').value = user.fullname;
                        document.getElementById('editEmail').value = user.email;
                        document.getElementById('editContactNumber').value = user.contact_number || '';
                        document.getElementById('editDepartmentId').value = user.department_id || '';
                        
                        // Clear previous messages
                        clearMessages('editUserMessage');
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                        modal.show();
                    } else {
                        showAlert('Error loading user details: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error loading user details', 'danger');
                });
        }
        
        // Handle form submission
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const spinner = document.getElementById('editUserSpinner');
            const saveBtn = document.getElementById('saveUserBtn');
            
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
                    showMessage('editUserMessage', 'User updated successfully!', 'success');
                    
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                        location.reload();
                    }, 1500);
                } else {
                    showMessage('editUserMessage', data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                spinner.classList.add('d-none');
                saveBtn.disabled = false;
                showMessage('editUserMessage', 'Error updating user', 'danger');
            });
        });
        
        // Delete user function
        function deleteUser(userId, username) {
            if (confirm(`Are you sure you want to delete the account for ${username}? This action cannot be undone.`)) {
                const formData = new FormData();
                formData.append('user_id', userId);
                
                fetch(`${SITE_URL}/api/delete_user.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('User account deleted successfully!', 'success');
                        document.getElementById(`user-row-${userId}`).style.display = 'none';
                        
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error deleting user account', 'danger');
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
