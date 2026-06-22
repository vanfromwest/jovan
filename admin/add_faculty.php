<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Add Faculty';
requireRole(['Admin']);

$addError = '';
$addSuccess = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitizeInput($_POST['fullname'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $position = sanitizeInput($_POST['position'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 0);
    $contact_number = sanitizeInput($_POST['contact_number'] ?? '');

    // Validation
    if (empty($fullname) || empty($email) || empty($password) || empty($position) || $department_id <= 0) {
        $addError = 'All fields are required';
    } elseif (!validateEmail($email)) {
        $addError = 'Please enter a valid email address';
    } elseif (!validatePassword($password)) {
        $addError = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $addError = 'Passwords do not match';
    } else {
        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        if ($checkEmail->get_result()->num_rows > 0) {
            $addError = 'Email already registered';
        }

        if (empty($addError)) {
            // Hash password
            $hashedPassword = hashPassword($password);

            // Handle profile picture upload
            $profileImage = 'default.png';
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Validate file
                $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                    $addError = 'Invalid file type. Only JPG, PNG, GIF allowed';
                } elseif ($_FILES['profile_image']['size'] > MAX_FILE_SIZE) {
                    $addError = 'File size exceeds limit (5MB max)';
                } else {
                    // Create temp filename for upload
                    $tempName = 'profile_' . time() . '.' . $ext;
                    if (!is_dir(UPLOAD_DIR . 'profiles')) {
                        mkdir(UPLOAD_DIR . 'profiles', 0755, true);
                    }
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], UPLOAD_DIR . 'profiles/' . $tempName)) {
                        $profileImage = $tempName;
                    }
                }
            }

            if (empty($addError)) {
                // Insert user into database with APPROVED status
                $stmt = $conn->prepare("
                    INSERT INTO users 
                    (fullname, email, password, role, status, contact_number, department_id, profile_image, username)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $role = 'Faculty';
                $status = 'APPROVED';
                $username = strtolower(str_replace(' ', '.', $fullname)) . '_' . time();

                if (!$stmt) {
                    $addError = 'Database error: ' . $conn->error;
                } else {
                    $stmt->bind_param(
                        "sssssssss",
                        $fullname,
                        $email,
                        $hashedPassword,
                        $role,
                        $status,
                        $contact_number,
                        $department_id,
                        $profileImage,
                        $username
                    );

                    if ($stmt->execute()) {
                        $userId = $stmt->insert_id;

                        // Create faculty record
                        $qrToken = generateQRToken();
                        $qrFilename = 'qr_' . $userId . '_' . time() . '.png';
                        $qrPath = generateQRCode(
                            SITE_URL . '/scan.php?token=' . $qrToken,
                            QR_CODE_SIZE,
                            $qrFilename
                        );

                        $facultyStmt = $conn->prepare("
                            INSERT INTO faculty (user_id, position, qr_token)
                            VALUES (?, ?, ?)
                        ");
                        $facultyStmt->bind_param("iss", $userId, $position, $qrToken);
                        $facultyStmt->execute();

                        $facultyId = $conn->insert_id;

                        $qrStmt = $conn->prepare("
                            INSERT INTO qr_codes (faculty_id, qr_token, qr_path)
                            VALUES (?, ?, ?)
                        ");
                        $qrStmt->bind_param("iss", $facultyId, $qrToken, $qrPath);
                        $qrStmt->execute();

                        // Initialize faculty status
                        $statusInit = $conn->prepare("
                            INSERT INTO faculty_status (faculty_id, status)
                            VALUES (?, ?)
                        ");
                        $statusValue = 'OUT';
                        $statusInit->bind_param("is", $facultyId, $statusValue);
                        $statusInit->execute();

                        logActivity('FACULTY_CREATE', 'New faculty added: ' . $fullname, getCurrentUserId());

                        $addSuccess = 'Faculty member added successfully!';

                        // Reset form
                        $_POST = [];
                    } else {
                        $addError = 'Failed to add faculty member. Please try again.';
                    }
                    $stmt->close();
                }
            }
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
                    <h1 class="h3"><i class="bi bi-person-plus"></i> Add Faculty Member</h1>
                    <a href="faculty_management.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <?php if (!empty($addSuccess)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?php echo $addSuccess; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($addError)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> <?php echo $addError; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="dashboard-card">
                            <div class="card-header">Faculty Information</div>
                            <div class="card-body">
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" 
                                                   value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="position" name="position" 
                                                   placeholder="e.g., Professor, Lecturer, Instructor"
                                                   value="<?php echo htmlspecialchars($_POST['position'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                            <select class="form-select" id="department_id" name="department_id" required>
                                                <option value="">Select Department</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?php echo $dept['id']; ?>"
                                                        <?php echo ($_POST['department_id'] ?? 0) == $dept['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($dept['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="Minimum 6 characters" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                   placeholder="Confirm password" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="contact_number" class="form-label">Contact Number</label>
                                            <input type="text" class="form-control" id="contact_number" name="contact_number" 
                                                   value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="profile_image" class="form-label">Profile Image</label>
                                            <input type="file" class="form-control" id="profile_image" name="profile_image" 
                                                   accept="image/jpeg,image/png,image/gif">
                                            <small class="text-muted">JPG, PNG, GIF only (Max 5MB)</small>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Add Faculty Member
                                        </button>
                                        <a href="faculty_management.php" class="btn btn-secondary">
                                            <i class="bi bi-x"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="dashboard-card">
                            <div class="card-header">Information</div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">New Faculty Account</h6>
                                    <ul class="mb-0">
                                        <li>Account will be immediately APPROVED</li>
                                        <li>QR code will be automatically generated</li>
                                        <li>Faculty can login immediately after creation</li>
                                        <li>Initial status set to OUT</li>
                                    </ul>
                                </div>
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
