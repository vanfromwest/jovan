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
            margin-bottom: 8px;
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

        .monitor-controls {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .monitor-filters {
            display: flex;
            gap: 6px;
        }

        .filter-btn {
            padding: 6px 18px;
            border-radius: 20px;
            border: 2px solid rgba(255,255,255,0.25);
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .filter-btn:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .filter-btn.active {
            background: white;
            color: #2d5016;
            border-color: white;
        }

        .filter-btn.active.in {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .filter-btn.active.out {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        .filter-btn.active.travel {
            background: #fff3cd;
            color: #856404;
            border-color: #ffc107;
        }

        .monitor-search {
            max-width: 360px;
            width: 100%;
            position: relative;
        }

        .monitor-search input {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 6px 14px 6px 34px;
            border-radius: 20px;
            font-size: 13px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .monitor-search input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .monitor-search input:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
        }

        .monitor-search .bi-search {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
        }

        .search-clear-monitor {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            display: none;
            font-size: 16px;
            padding: 0 4px;
        }

        .search-clear-monitor:hover {
            color: #ff6b6b;
        }

        .monitor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 8px;
            height: calc(100vh - 210px);
            align-content: start;
            padding-right: 4px;
        }

        .monitor-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 14px 18px;
            gap: 16px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
            min-height: 88px;
        }

        .monitor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .monitor-card.filtered-out {
            display: none !important;
        }

        .monitor-card-image {
            width: 60px;
            height: 60px;
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
            gap: 3px;
        }

        .monitor-card-name {
            font-size: 20px;
            font-weight: 700;
            color: #2d5016;
            line-height: 1.2;
        }

        .monitor-card-position {
            font-size: 13px;
            color: #888;
        }

        .monitor-card-bottom {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 2px;
        }

        .monitor-card-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 14px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 14px;
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

        .monitor-card-status.travel {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .monitor-card-activity {
            font-size: 13px;
            color: #555;
            display: flex;
            align-items: center;
            gap: 4px;
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

        .status-pulse.travel {
            background-color: #ffc107;
            box-shadow: 0 0 6px rgba(255, 193, 7, 0.5);
        }

        .monitor-count {
            color: white;
            font-size: 12px;
            opacity: 0.8;
            text-align: center;
            margin-bottom: 6px;
        }

        .monitor-count span {
            font-weight: 700;
        }

        .monitor-count .count-in { color: #5cb85c; }
        .monitor-count .count-out { color: #d9534f; }
        .monitor-count .count-travel { color: #f0ad4e; }

        .monitor-refresh-info {
            display: none;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.3); }
        }

        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            color: rgba(255,255,255,0.6);
            padding: 40px 20px;
        }
        .no-results i { font-size: 48px; margin-bottom: 12px; display: block; }

        .monitor-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 10px 0 0;
        }

        .monitor-pagination .page-btn {
            min-width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            padding: 0 8px;
        }

        .monitor-pagination .page-btn:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .monitor-pagination .page-btn.active {
            background: white;
            color: #2d5016;
            border-color: white;
        }

        .monitor-pagination .page-btn:disabled {
            opacity: 0.3;
            cursor: default;
        }

        .monitor-pagination .page-info {
            color: rgba(255,255,255,0.6);
            font-size: 13px;
            margin: 0 8px;
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

        <!-- Controls: Filters + Search -->
        <div class="monitor-controls">
            <div class="monitor-filters">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn active in" data-filter="in">IN</button>
                <button class="filter-btn active out" data-filter="out">OUT</button>
                <button class="filter-btn active travel" data-filter="travel">ON TRAVEL</button>
            </div>
            <div class="monitor-search">
                <i class="bi bi-search"></i>
                <input type="text" id="monitor-search-input" placeholder="Search faculty..." autocomplete="off">
                <button class="search-clear-monitor" id="monitor-search-clear">&times;</button>
            </div>
        </div>

        <!-- Count strip -->
        <div class="monitor-count" id="count-strip">
            Total: <span id="total-count">0</span> |
            <span class="count-in">IN: <span id="in-count">0</span></span> |
            <span class="count-out">OUT: <span id="out-count">0</span></span> |
            <span class="count-travel">Travel: <span id="travel-count">0</span></span>
        </div>

        <?php
        $inFaculty = [];
        $outFaculty = [];
        $travelFaculty = [];
        foreach ($facultyList as $faculty):
            $status = getFacultyStatus($faculty['faculty_id']);
            if ($status['status'] === 'IN') {
                $inFaculty[] = ['faculty' => $faculty, 'status' => $status];
            } elseif ($status['status'] === 'TRAVEL') {
                $travelFaculty[] = ['faculty' => $faculty, 'status' => $status];
            } else {
                $outFaculty[] = ['faculty' => $faculty, 'status' => $status];
            }
        endforeach;

        $allFaculty = array_merge($inFaculty, $travelFaculty, $outFaculty);
        ?>

        <!-- Unified grid -->
        <div class="monitor-grid" id="faculty-grid">
            <?php foreach ($allFaculty as $entry):
                $faculty = $entry['faculty'];
                $status = $entry['status'];
                $statusClass = strtolower($status['status']);
                $isTravel = $statusClass === 'travel';
                $isIn = $statusClass === 'in';
            ?>
                <div class="monitor-card" data-faculty-id="<?php echo $faculty['faculty_id']; ?>" data-status="<?php echo $statusClass; ?>">
                    <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . 'profiles/' . ($faculty['profile_image'] ?? 'default.png'); ?>" 
                         class="monitor-card-image" alt="<?php echo $faculty['fullname']; ?>">
                    <div class="monitor-card-body">
                        <div class="monitor-card-name"><?php echo htmlspecialchars($faculty['fullname']); ?></div>
                        <div class="monitor-card-position"><?php echo htmlspecialchars($faculty['position'] ?? 'Faculty'); ?></div>
                        <div class="monitor-card-bottom">
                            <div class="monitor-card-status <?php echo $statusClass; ?>">
                                <span class="status-pulse <?php echo $statusClass; ?>"></span>
                                <?php if ($isIn): ?>✓ IN
                                <?php elseif ($isTravel): ?>✈ TRAVEL
                                <?php else: ?>✕ OUT
                                <?php endif; ?>
                            </div>
                            <?php if ($isTravel && $status['travel_from']): ?>
                                <div class="monitor-card-activity">
                                    <i class="bi bi-calendar-range"></i>
                                    <?php echo htmlspecialchars($status['travel_from']) . ' to ' . htmlspecialchars($status['travel_to']) . ' (' . intval($status['travel_days']) . ' day(s))'; ?>
                                </div>
                            <?php elseif (!$isTravel && ($status['activity'] || $status['location'])): ?>
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
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($allFaculty)): ?>
                <div class="no-results"><i class="bi bi-people"></i> No faculty data available</div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="monitor-pagination" id="monitor-pagination"></div>

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

        let activeFilters = new Set(['all']);
        let currentPage = 1;
        const PER_PAGE = 12;

        $(document).ready(function() {
            updateTime();
            updateCounts();
            renderPagination();
            startAutoRefresh(5000);
            setInterval(updateTime, 1000);
        });

        function updateTime() {
            const now = new Date();
            $('#update-time').text('Last updated: ' + now.toLocaleTimeString('en-US'));
        }

        function updateCounts() {
            var total = 0, inC = 0, outC = 0, travelC = 0;
            $('#faculty-grid .monitor-card').each(function() {
                total++;
                var s = $(this).data('status');
                if (s === 'in') inC++;
                else if (s === 'travel') travelC++;
                else outC++;
            });
            $('#total-count').text(total);
            $('#in-count').text(inC);
            $('#out-count').text(outC);
            $('#travel-count').text(travelC);
        }

        function renderPagination() {
            var $visibleCards = $('#faculty-grid .monitor-card').not('.filtered-out');
            var totalItems = $visibleCards.length;
            var totalPages = Math.max(1, Math.ceil(totalItems / PER_PAGE));

            if (currentPage > totalPages) currentPage = totalPages;

            var html = '';

            if (totalItems > PER_PAGE) {
                html += '<button class="page-btn" data-page="prev" ' + (currentPage === 1 ? 'disabled' : '') + '>&#8249;</button>';

                var startPage = Math.max(1, currentPage - 2);
                var endPage = Math.min(totalPages, currentPage + 2);

                if (startPage > 1) {
                    html += '<button class="page-btn" data-page="1">1</button>';
                    if (startPage > 2) html += '<span class="page-info">...</span>';
                }

                for (var i = startPage; i <= endPage; i++) {
                    html += '<button class="page-btn' + (i === currentPage ? ' active' : '') + '" data-page="' + i + '">' + i + '</button>';
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) html += '<span class="page-info">...</span>';
                    html += '<button class="page-btn" data-page="' + totalPages + '">' + totalPages + '</button>';
                }

                html += '<button class="page-btn" data-page="next" ' + (currentPage === totalPages ? 'disabled' : '') + '>&#8250;</button>';
            }

            $('#monitor-pagination').html(html);
            applyPage();
        }

        function applyPage() {
            var $visibleCards = $('#faculty-grid .monitor-card').not('.filtered-out');
            var start = (currentPage - 1) * PER_PAGE;
            var end = start + PER_PAGE;

            $visibleCards.each(function(index) {
                $(this).toggleClass('filtered-out', index < start || index >= end);
            });
        }

        // Filter buttons
        $(document).on('click', '.filter-btn', function() {
            var filter = $(this).data('filter');

            if (filter === 'all') {
                $('.filter-btn').addClass('active');
                activeFilters = new Set(['all']);
            } else {
                activeFilters.delete('all');

                if (activeFilters.has(filter)) {
                    activeFilters.delete(filter);
                    $(this).removeClass('active');
                } else {
                    activeFilters.add(filter);
                    $(this).addClass('active');
                }

                if (activeFilters.size === 0) {
                    activeFilters = new Set(['all']);
                    $('.filter-btn').addClass('active');
                } else {
                    $('.filter-btn[data-filter="all"]').removeClass('active');
                }
            }

            currentPage = 1;
            applyMonitorFilter();
        });

        // Search input
        $(document).ready(function() {
            $('#monitor-search-input').on('input', function() {
                currentPage = 1;
                applyMonitorFilter();
                $(this).siblings('.search-clear-monitor').toggle(this.value.length > 0);
            });

            $('#monitor-search-clear').on('click', function() {
                $('#monitor-search-input').val('').trigger('input').focus();
            });
        });

        // Pagination clicks
        $(document).on('click', '#monitor-pagination .page-btn:not(:disabled)', function() {
            var page = $(this).data('page');
            if (page === 'prev') currentPage--;
            else if (page === 'next') currentPage++;
            else currentPage = parseInt(page);
            renderPagination();
        });

        function applyMonitorFilter() {
            var query = $('#monitor-search-input').val().toLowerCase().trim();

            $('#faculty-grid .monitor-card').each(function() {
                var $card = $(this);
                var status = $card.data('status');
                var showByStatus = activeFilters.has('all') || activeFilters.has(status);

                var showBySearch = true;
                if (query.length > 0) {
                    var name = $card.find('.monitor-card-name').text().toLowerCase();
                    var position = $card.find('.monitor-card-position').text().toLowerCase();
                    showBySearch = name.includes(query) || position.includes(query);
                }

                $card.toggleClass('filtered-out', !(showByStatus && showBySearch));
            });

            var visible = $('#faculty-grid .monitor-card:not(.filtered-out)').length;
            if ($('#faculty-grid .no-results').length === 0) {
                var msg = activeFilters.has('all') ? 'No faculty match your search' : 'No faculty match the selected filter';
                if (visible === 0) {
                    $('#faculty-grid').append(
                        '<div class="no-results"><i class="bi bi-search"></i> ' + msg + '</div>'
                    );
                }
            } else {
                if (visible > 0) {
                    $('#faculty-grid .no-results').remove();
                }
            }

            renderPagination();
        }

        // Override the updateStatusDisplay function for monitor page
        function updateStatusDisplay(facultyData) {
            $.each(facultyData, function(index, faculty) {
                var $card = $('[data-faculty-id="' + faculty.id + '"]');

                if ($card.length) {
                    var statusClass, statusText;

                    if (faculty.status === 'IN') {
                        statusClass = 'in';
                        statusText = '✓ IN';
                    } else if (faculty.status === 'TRAVEL') {
                        statusClass = 'travel';
                        statusText = '✈ TRAVEL';
                    } else {
                        statusClass = 'out';
                        statusText = '✕ OUT';
                    }

                    $card.attr('data-status', statusClass);

                    var $status = $card.find('.monitor-card-status');
                    $status.html('<span class="status-pulse ' + statusClass + '"></span> ' + statusText)
                           .removeClass('in out travel').addClass(statusClass);

                    var $bottom = $card.find('.monitor-card-bottom');
                    var $activity = $bottom.find('.monitor-card-activity');

                    if (faculty.status === 'TRAVEL' && faculty.travel_from) {
                        var travelText = faculty.travel_from + ' to ' + faculty.travel_to + ' (' + faculty.travel_days + ' day(s))';
                        if ($activity.length) {
                            $activity.html('<i class="bi bi-calendar-range"></i> ' + travelText);
                        } else {
                            $bottom.append(
                                '<div class="monitor-card-activity"><i class="bi bi-calendar-range"></i> ' + travelText + '</div>'
                            );
                        }
                    } else if (faculty.activity || faculty.location) {
                        var activityText = (faculty.activity ? faculty.activity : '') +
                                         (faculty.location ? ' at ' + faculty.location : '');
                        if ($activity.length) {
                            $activity.html('<i class="bi bi-geo-alt"></i> ' + activityText);
                        } else {
                            $bottom.append(
                                '<div class="monitor-card-activity"><i class="bi bi-geo-alt"></i> ' + activityText + '</div>'
                            );
                        }
                    } else {
                        $activity.remove();
                    }
                }
            });

            updateCounts();
            applyMonitorFilter();
        }
    </script>
</body>
</html>
