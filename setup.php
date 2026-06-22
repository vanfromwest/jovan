<?php
/**
 * Setup / Installation Script
 * CCSICT Faculty Monitoring System
 * 
 * This script automatically sets up the database and initial data
 */

require_once 'config/config.php';

// Create connection to MySQL server (without selecting database yet)
$conn = new mysqli('localhost', 'root', '');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$setupStatus = [];
$hasError = false;

// Step 1: Create Database
$dbName = 'ccsict_faculty_monitoring';
$createDbSQL = "CREATE DATABASE IF NOT EXISTS `$dbName`";

if ($conn->query($createDbSQL) === TRUE) {
    $setupStatus[] = ['status' => 'success', 'message' => 'Database created successfully'];
} else {
    $setupStatus[] = ['status' => 'error', 'message' => 'Failed to create database: ' . $conn->error];
    $hasError = true;
}

// Select the database
$conn->select_db($dbName);

// Step 2: Read and execute schema.sql
$schemaFile = 'database/schema.sql';

if (file_exists($schemaFile)) {
    $sqlContent = file_get_contents($schemaFile);
    
    // Remove comments and split by semicolon
    $sqlLines = array_filter(array_map('trim', explode("\n", $sqlContent)));
    $statement = '';
    $tablesCreated = 0;
    
    foreach ($sqlLines as $line) {
        // Skip comments and empty lines
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        // Add line to statement
        $statement .= ' ' . $line;
        
        // Check if statement ends with semicolon
        if (substr(trim($statement), -1) === ';') {
            $statement = trim($statement);
            $statement = substr($statement, 0, -1); // Remove trailing semicolon
            
            if (!empty($statement)) {
                if ($conn->query($statement) === TRUE) {
                    if (stripos($statement, 'CREATE TABLE') !== false) {
                        $tablesCreated++;
                    }
                } else {
                    // Some statements might fail if tables already exist, which is OK
                    if ($conn->errno !== 1050 && $conn->errno !== 0) { // 1050 = table already exists
                        $setupStatus[] = ['status' => 'error', 'message' => 'SQL Error: ' . $conn->error];
                    }
                }
            }
            $statement = '';
        }
    }
    
    $setupStatus[] = ['status' => 'success', 'message' => 'Database schema imported successfully (' . $tablesCreated . ' tables)'];
} else {
    $setupStatus[] = ['status' => 'error', 'message' => 'Schema file not found'];
    $hasError = true;
}

// Step 3: Create directories
$dirs = [
    'uploads/profiles',
    'qrcodes'
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            $setupStatus[] = ['status' => 'success', 'message' => "Directory '$dir' created"];
        } else {
            $setupStatus[] = ['status' => 'warning', 'message' => "Could not create directory '$dir'"];
        }
    }
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCSICT System Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #2d5016 0%, #1a3a0a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
            color: #2d5016;
        }
        
        .setup-header h1 {
            font-weight: bold;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .status-item {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .credentials-box {
            background-color: #f0f8f0;
            border: 2px solid #2d5016;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
            margin-bottom: 20px;
        }
        
        .credentials-box h4 {
            color: #2d5016;
            margin-bottom: 15px;
        }
        
        .credential-item {
            margin-bottom: 10px;
            font-family: monospace;
        }
        
        .credential-item label {
            font-weight: bold;
            color: #2d5016;
        }
        
        .setup-actions {
            margin-top: 30px;
            text-align: center;
        }
        
        .btn-custom {
            background-color: #2d5016;
            color: white;
            border: none;
            padding: 10px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-custom:hover {
            background-color: #1a3a0a;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1><i class="bi bi-gear-fill"></i> System Setup</h1>
            <p class="text-muted">CCSICT Faculty Monitoring System</p>
        </div>

        <!-- Setup Status -->
        <div class="setup-status">
            <h5 class="mb-3">Installation Status:</h5>
            <?php foreach ($setupStatus as $item): ?>
                <div class="status-item status-<?php echo $item['status']; ?>">
                    <?php if ($item['status'] === 'success'): ?>
                        <i class="bi bi-check-circle-fill" style="font-size: 20px;"></i>
                    <?php elseif ($item['status'] === 'error'): ?>
                        <i class="bi bi-x-circle-fill" style="font-size: 20px;"></i>
                    <?php else: ?>
                        <i class="bi bi-exclamation-circle-fill" style="font-size: 20px;"></i>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($item['message']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Default Credentials -->
        <div class="credentials-box">
            <h4><i class="bi bi-key-fill"></i> Default Admin Account</h4>
            <div class="credential-item">
                <label>Email:</label>
                <div>adminsonic@ccsict.com</div>
            </div>
            <div class="credential-item">
                <label>Password:</label>
                <div>sonic123</div>
            </div>
            <p class="text-muted small mt-3">
                <strong>Important:</strong> Please change the admin password after first login for security.
            </p>
        </div>

        <!-- Setup Complete Message -->
        <?php if (!$hasError): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <strong>Setup Complete!</strong> The system has been successfully installed.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php else: ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle-fill"></i>
                <strong>Setup Warning!</strong> Some errors occurred during setup. Please review the messages above.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="setup-actions">
            <a href="index.php" class="btn btn-custom me-2">
                <i class="bi bi-house"></i> Go to Home
            </a>
            <a href="login.php" class="btn btn-custom">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
