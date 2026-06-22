<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Edit Faculty';
requireRole(['Admin']);

$facultyId = intval($_GET['id'] ?? 0);

if ($facultyId <= 0) {
    header('Location: faculty_management.php?error=Invalid faculty ID');
    exit();
}

// Get faculty details
$faculty = getFacultyWithUser($facultyId);

if (!$faculty) {
    header('Location: faculty_management.php?error=Faculty not found');
    exit();
}

$updateMessage = '';
$updateError = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitizeInput($_POST['fullname'] ?? '');
    $position = sanitizeInput($_POST['position'] ?? '');
    $departmentId = intval($_POST['department_id'] ?? 0);
    $email = sanitizeInput($_POST['email'] ?? '');
    
    if (empty($fullname) || empty($position) || empty($email) || $departmentId <= 0) {
        $updateError = 'All fields are required.';
    } elseif (!validateEmail($email)) {
        $updateError = 'Invalid email format.';
    } else {
        if (updateFacultyInfo($facultyId, $fullname, $position, $departmentId, $email)) {
            $updateMessage = 'Faculty information updated successfully!';
            logActivity('FACULTY_UPDATE', 'Updated faculty: ' . $fullname, getCurrentUserId());
            // Refresh faculty data
            $faculty = getFacultyWithUser($facultyId);
        } else {
            $updateError = 'Failed to update faculty information. Please try again.';
        }
    }
}

$departments = getAllDepartments();
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3"><i class="bi bi-pencil"></i> Edit Faculty</h1>
                    <a href="faculty_management.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <?php if (!empty($updateMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?php echo $updateMessage; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($updateError)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> <?php echo $updateError; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <div class="card-header">Faculty Information</div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="fullname" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" 
                                               value="<?php echo htmlspecialchars($faculty['fullname']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($faculty['email']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="position" class="form-label">Position</label>
                                        <input type="text" class="form-control" id="position" name="position" 
                                               value="<?php echo htmlspecialchars($faculty['position'] ?? ''); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="department_id" class="form-label">Department</label>
                                        <select class="form-select" id="department_id" name="department_id" required>
                                            <option value="">Select Department</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo $dept['id']; ?>" 
                                                    <?php echo $faculty['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dept['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Save Changes
                                    </button>
                                    <a href="faculty_management.php" class="btn btn-secondary">
                                        <i class="bi bi-x"></i> Cancel
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <div class="card-header">Faculty Profile</div>
                            <div class="card-body text-center">
                                <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . 'profiles/' . ($faculty['profile_image'] ?? 'default.png'); ?>" 
                                     class="rounded-circle mb-3" width="120" height="120" alt="<?php echo $faculty['fullname']; ?>">
                                <h5><?php echo htmlspecialchars($faculty['fullname']); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($faculty['position'] ?? 'Faculty'); ?></p>
                                <p class="text-muted small">QR Token: <?php echo htmlspecialchars(substr($faculty['qr_token'], 0, 20) . '...'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
    </script>
</body>
</html>
