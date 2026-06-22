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
            padding: 10px 15px;
            min-height: 100vh;
            overflow: hidden;
        }

        .monitor-header {
            text-align: center;
            color: white;
            margin-bottom: 12px;
        }

        .monitor-header h1 {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .monitor-header p {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 2px;
        }

        .monitor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 8px;
            height: calc(100vh - 110px);
            align-content: start;
        }

        .monitor-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 10px 14px;
            gap: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-height: 72px;
        }

        .monitor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .monitor-card-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-color);
            flex-shrink: 0;
            background: linear-gradient(135deg, #2d5016, #1a3a0a);
        }

        .monitor-card-body {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .monitor-card-top {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
        }

        .monitor-card-name {
            font-size: 14px;
            font-weight: 700;
            color: #2d5016;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .monitor-card-position {
            font-size: 11px;
            color: #888;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .monitor-card-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 10px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 11px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .monitor-card-status.in {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #28a745;
        }

        .monitor-card-status.out {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
        }

        .monitor-card-activity {
            font-size: 11px;
            color: #555;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .monitor-card-time {
            font-size: 10px;
            color: #aaa;
            flex-shrink: 0;
        }

        .status-pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .status-pulse.in {
            background-color: #28a745;
            box-shadow: 0 0 6px rgba(40, 167, 69, 0.5);
        }

        .status-pulse.out {
            background-color: #dc3545;
            box-shadow: 0 0 6px rgba(220, 53, 69, 0.5);
        }

        @media (max-width: 768px) {
            .monitor-grid {
                grid-template-columns: 1fr;
                height: auto;
                overflow-y: auto;
            }
            body { overflow-y: auto; }
        }

        .monitor-refresh-info {
            display: none;
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

        <!-- Search -->
        <div class="monitor-search">
            <i class="bi bi-search"></i>
            <input type="text" id="monitor-search-input" placeholder="Search faculty by name or position..." autocomplete="off">
            <button class="search-clear-monitor" id="monitor-search-clear">&times;</button>
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
                    
                    <div class="monitor-card-body">
                        <div class="monitor-card-top">
                            <div class="monitor-card-name"><?php echo htmlspecialchars($faculty['fullname']); ?></div>
                            <div class="monitor-card-status <?php echo $statusClass; ?>">
                                <span class="status-pulse <?php echo $statusClass; ?>"></span>
                                <?php echo $statusText; ?>
                            </div>
                        </div>
                        <div class="monitor-card-position"><?php echo htmlspecialchars($faculty['position'] ?? 'Faculty'); ?></div>
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
                        <span class="faculty-time"><?php echo formatDateTime($status['updated_at']); ?></span>
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
                    
                    var $status = $card.find('.monitor-card-status');
                    $status.html('<span class="status-pulse ' + statusClass + '"></span> ' + statusText)
                           .removeClass('in out').addClass(statusClass);
                    
                    var $activity = $card.find('.monitor-card-activity');
                    if (faculty.activity || faculty.location) {
                        var activityText = (faculty.activity ? faculty.activity : '') + 
                                         (faculty.location ? ' at ' + faculty.location : '');
                        if ($activity.length) {
                            $activity.html('<i class="bi bi-geo-alt"></i> ' + activityText);
                        } else {
                            $card.find('.monitor-card-position').after(
                                '<div class="monitor-card-activity"><i class="bi bi-geo-alt"></i> ' + activityText + '</div>'
                            );
                        }
                    } else {
                        $activity.remove();
                    }
                    
                    $card.find('.faculty-time').text(formatDateTime(new Date()));
                }
            });
            
            // Re-apply search filter after update
            applyMonitorFilter();
        }

        // Monitor search functionality
        $(document).ready(function() {
            $('#monitor-search-input').on('input', function() {
                applyMonitorFilter();
                $(this).siblings('.search-clear-monitor').toggle(this.value.length > 0);
            });

            $('#monitor-search-clear').on('click', function() {
                $('#monitor-search-input').val('').trigger('input').focus();
            });
        });

        function applyMonitorFilter() {
            var query = $('#monitor-search-input').val().toLowerCase().trim();
            
            if (query.length === 0) {
                $('.monitor-card').removeClass('filtered-out');
                return;
            }
            
            $('.monitor-card').each(function() {
                var $card = $(this);
                var name = $card.find('.monitor-card-name').text().toLowerCase();
                var position = $card.find('.monitor-card-position').text().toLowerCase();
                
                if (name.includes(query) || position.includes(query)) {
                    $card.removeClass('filtered-out');
                } else {
                    $card.addClass('filtered-out');
                }
            });
        }
    </script>
</body>
</html>
