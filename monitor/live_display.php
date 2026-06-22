<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$facultyList = getAllFaculty();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Faculty Monitor - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #2d5016 0%, #1a3a0a 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .monitor-header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .monitor-header h1 {
            font-size: 40px;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .monitor-header p {
            font-size: 18px;
            opacity: 0.9;
        }

        .monitor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .monitor-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .monitor-card:hover {
            transform: scale(1.05);
        }

        .monitor-card-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: linear-gradient(135deg, #2d5016, #1a3a0a);
        }

        .monitor-card-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .monitor-card-name {
            font-size: 24px;
            font-weight: bold;
            color: #2d5016;
            margin-bottom: 10px;
        }

        .monitor-card-position {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .monitor-card-status {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .monitor-card-status.in {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }

        .monitor-card-status.out {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }

        .monitor-card-activity {
            font-size: 16px;
            color: #333;
            font-weight: 500;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .monitor-card-time {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }

        .status-pulse {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }

        .status-pulse.in {
            background-color: #28a745;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }

        .status-pulse.out {
            background-color: #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
        }

        @media (max-width: 768px) {
            .monitor-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .monitor-header h1 {
                font-size: 28px;
            }
        }

        .monitor-refresh-info {
            text-align: center;
            color: white;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="monitor-header">
            <h1><i class="bi bi-building"></i> CCSICT Faculty Status</h1>
            <p>Real-Time Faculty Availability Monitor</p>
            <small id="update-time" style="opacity: 0.7;"></small>
        </div>

        <!-- Faculty Grid -->
        <div class="monitor-grid" id="faculty-grid">
            <?php foreach ($facultyList as $faculty): 
                $status = getFacultyStatus($faculty['faculty_id']);
                $statusClass = $status['status'] === 'IN' ? 'in' : 'out';
                $statusText = $status['status'] === 'IN' ? '✓ IN' : '✕ OUT';
            ?>
                <div class="monitor-card" data-faculty-id="<?php echo $faculty['faculty_id']; ?>">
                    <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . 'profiles/' . ($faculty['profile_image'] ?? 'default.png'); ?>" 
                         class="monitor-card-image" alt="<?php echo $faculty['fullname']; ?>">
                    
                    <div class="monitor-card-content">
                        <div>
                            <div class="monitor-card-name"><?php echo htmlspecialchars($faculty['fullname']); ?></div>
                            <div class="monitor-card-position"><?php echo htmlspecialchars($faculty['position'] ?? 'Faculty'); ?></div>
                            
                            <div class="monitor-card-status <?php echo $statusClass; ?>">
                                <span class="status-pulse <?php echo $statusClass; ?>"></span>
                                <?php echo $statusText; ?>
                            </div>
                            
                            <?php if ($status['activity'] || $status['location']): ?>
                                <div class="monitor-card-activity">
                                    <i class="bi bi-geo-alt"></i>
                                    <?php 
                                    $activity = !empty($status['activity']) ? htmlspecialchars($status['activity']) : '';
                                    $location = !empty($status['location']) ? htmlspecialchars($status['location']) : '';
                                    echo $activity . ($location ? ' at ' . $location : '');
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="monitor-card-time">
                            Updated: <span class="faculty-time"><?php echo formatDateTime($status['updated_at']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="monitor-refresh-info">
            <i class="bi bi-arrow-repeat"></i> Auto-refreshing every 5 seconds
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/ajax.js"></script>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const UPLOAD_DIR = '<?php echo UPLOAD_DIR; ?>';

        // Auto-refresh faculty status
        $(document).ready(function() {
            updateTime();
            startAutoRefresh(5000);
            setInterval(updateTime, 1000);
        });

        function updateTime() {
            const now = new Date();
            $('#update-time').text('Last updated: ' + now.toLocaleTimeString('en-US'));
        }

        // Override the updateStatusDisplay function for monitor page
        function updateStatusDisplay(facultyData) {
            $.each(facultyData, function(index, faculty) {
                var $card = $('[data-faculty-id="' + faculty.id + '"]');
                
                if ($card.length) {
                    var statusClass = faculty.status === 'IN' ? 'in' : 'out';
                    var statusText = faculty.status === 'IN' ? '✓ IN' : '✕ OUT';
                    
                    $card.find('.monitor-card-status').html(
                        '<span class="status-pulse ' + statusClass + '"></span> ' + statusText
                    ).removeClass('in out').addClass(statusClass);
                    
                    if (faculty.activity || faculty.location) {
                        var activityText = (faculty.activity ? faculty.activity : '') + 
                                         (faculty.location ? ' at ' + faculty.location : '');
                        $card.find('.monitor-card-activity').html(
                            '<i class="bi bi-geo-alt"></i> ' + activityText
                        );
                    }
                    
                    $card.find('.faculty-time').text(formatDateTime(new Date()));
                }
            });
        }
    </script>
</body>
</html>
