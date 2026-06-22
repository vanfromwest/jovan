<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session_check.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$registerError = '';
$registerSuccess = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitizeInput($_POST['fullname'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $contact_number = sanitizeInput($_POST['contact_number'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 0);
    $role = sanitizeInput($_POST['role'] ?? 'Student');

    // Validation
    if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        $registerError = 'All fields are required';
    } elseif (strlen($fullname) < 3) {
        $registerError = 'Full name must be at least 3 characters';
    } elseif (strlen($username) < 3) {
        $registerError = 'Username must be at least 3 characters';
    } elseif (!validateEmail($email)) {
        $registerError = 'Please enter a valid email address';
    } elseif (!validatePassword($password)) {
        $registerError = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $registerError = 'Passwords do not match';
    } elseif ($role !== 'Student' && $role !== 'Faculty') {
        $registerError = 'Invalid role selected';
    } else {
        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        if ($checkEmail->get_result()->num_rows > 0) {
            $registerError = 'Email already registered';
        }
        
        // Check if username already exists
        $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkUsername->bind_param("s", $username);
        $checkUsername->execute();
        if ($checkUsername->get_result()->num_rows > 0) {
            $registerError = 'Username already taken';
        }

        if (empty($registerError)) {
            // Hash password
            $hashedPassword = hashPassword($password);

            // Handle profile picture upload
            $profileImage = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Validate file
                $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                    $registerError = 'Invalid file type. Only JPG, PNG, GIF allowed';
                } elseif ($_FILES['profile_image']['size'] > MAX_FILE_SIZE) {
                    $registerError = 'File size exceeds limit (5MB max)';
                } else {
                    // Create temp filename for upload
                    $tempName = 'profile_temp_' . time() . '.' . $ext;
                    if (!is_dir(UPLOAD_DIR . 'profiles')) {
                        mkdir(UPLOAD_DIR . 'profiles', 0755, true);
                    }
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], UPLOAD_DIR . 'profiles/' . $tempName)) {
                        $profileImage = $tempName;
                    }
                }
            }

            if (empty($registerError)) {
                // Insert user into database
                $stmt = $conn->prepare("
                    INSERT INTO users 
                    (fullname, username, email, password, role, status, contact_number, department_id, profile_image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $status = 'PENDING';
                
                if (!$stmt) {
                    $registerError = 'Database error: ' . $conn->error;
                } else {
                    $stmt->bind_param(
                        "sssssssss",
                        $fullname,
                        $username,
                        $email,
                        $hashedPassword,
                        $role,
                        $status,
                        $contact_number,
                        $department_id,
                        $profileImage
                    );

                    if ($stmt->execute()) {
                        $userId = $stmt->insert_id;

                        // If Faculty, create faculty record
                        if ($role === 'Faculty') {
                            $qrToken = generateQRToken();
                            $qrFilename = 'qr_' . $userId . '_' . time() . '.png';
                            $qrPath = generateQRCode(
                                SITE_URL . '/scan.php?token=' . $qrToken,
                                QR_CODE_SIZE,
                                $qrFilename
                            );

                            $facultyStmt = $conn->prepare("
                                INSERT INTO faculty (user_id, qr_token)
                                VALUES (?, ?)
                            ");
                            $facultyStmt->bind_param("is", $userId, $qrToken);
                            $facultyStmt->execute();

                            $qrStmt = $conn->prepare("
                                INSERT INTO qr_codes (faculty_id, qr_token, qr_path)
                                VALUES (?, ?, ?)
                            ");
                            $facultyId = $conn->insert_id;
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
                        }

                        $registerSuccess = 'Registration successful! Your account is pending admin approval. You will receive an email once it\'s approved.';
                    } else {
                        $registerError = 'Registration failed. Please try again.';
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// Define departments
$departments = [
    ['id' => 1, 'name' => 'BS In Information Technology'],
    ['id' => 2, 'name' => 'BS In Computer Science'],
    ['id' => 3, 'name' => 'BS In Entertainment and Multimedia Computing']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="register-container">
    <div class="register-box">
        <div class="register-header">
            <h1><i class="bi bi-building"></i> CCSICT</h1>
            <p>Faculty Monitoring System - Registration</p>
        </div>

        <?php if (!empty($registerError)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i> <?php echo htmlspecialchars($registerError); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($registerSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($registerSuccess); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <div class="text-center mt-3">
                <p>Redirecting to login...</p>
                <a href="login.php" class="btn btn-primary">Go to Login</a>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 3000);
            </script>
        <?php else: ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="fullname" class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">At least 6 characters</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" id="contact_number" name="contact_number">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="department_id" class="form-label">Department *</label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">User Type *</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">-- Select Type --</option>
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty Member</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="profile_image" class="form-label">Profile Picture</label>
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                    <small class="text-muted">JPG, PNG, or GIF (Max 5MB)</small>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-person-plus"></i> Create Account
                </button>
            </form>

            <hr>

            <div class="text-center">
                <p class="mb-2">Already have an account?</p>
                <a href="login.php" class="btn btn-outline-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Login Here
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
