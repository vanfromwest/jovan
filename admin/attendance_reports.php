<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Attendance Reports';
requireRole(['Admin']);

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

$facultyList = getAllFaculty();
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
                <h1 class="h3 mb-4"><i class="bi bi-file-text"></i> Attendance Reports</h1>

                <!-- Filter -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">Filter Report</div>
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-3 mb-2">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Generate Report
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-secondary w-100" onclick="window.print()">
                                    <i class="bi bi-printer"></i> Print
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Report -->
                <div class="dashboard-card">
                    <div class="card-header">Attendance Summary</div>
                    <div class="card-body">
                        <p class="text-muted">Report Period: <?php echo formatDate($startDate); ?> to <?php echo formatDate($endDate); ?></p>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Faculty Name</th>
                                        <th>Days Present</th>
                                        <th>Days Absent</th>
                                        <th>Attendance Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($facultyList as $faculty):
                                        $attendance = getFacultyAttendance($faculty['faculty_id'], $startDate, $endDate);
                                        $daysPresent = count(array_filter($attendance, fn($a) => !empty($a['time_in'])));
                                        $totalDays = count($attendance);
                                        $rate = $totalDays > 0 ? round(($daysPresent / $totalDays) * 100, 2) : 0;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($faculty['fullname']); ?></td>
                                            <td><?php echo $daysPresent; ?></td>
                                            <td><?php echo ($totalDays - $daysPresent); ?></td>
                                            <td><?php echo $rate; ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const UPLOAD_DIR = '<?php echo UPLOAD_DIR; ?>';
    </script>
</body>
</html>
