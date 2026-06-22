<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Faculty Status';
requireRole(['Student']);

$facultyList = getAllFaculty();
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
                <h1 class="h3 mb-4"><i class="bi bi-search"></i> Faculty Availability</h1>

                <div class="dashboard-card mb-4">
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="bi bi-person"></i> Search Faculty</label>
                                <div class="search-input-wrapper">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" id="faculty-search" placeholder="Search by name, department, position, or activity...">
                                    <button class="search-clear" id="search-clear" title="Clear">&times;</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><i class="bi bi-funnel"></i> Department</label>
                                <select class="form-select" id="department-filter">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><i class="bi bi-funnel"></i> Status</label>
                                    <select class="form-select" id="status-filter">
                                        <option value="">All Status</option>
                                        <option value="IN">IN</option>
                                        <option value="OUT">OUT</option>
                                        <option value="TRAVEL">ON TRAVEL</option>
                                    </select>
                            </div>
                        </div>
                        <div class="search-stats" id="search-stats"></div>
                    </div>
                </div>

                <div class="row" id="faculty-grid">
                    <?php foreach ($facultyList as $faculty):
                        $status = getFacultyStatus($faculty['faculty_id']);
                        $statusClass = $status['status'] === 'IN' ? 'in' : ($status['status'] === 'TRAVEL' ? 'travel' : 'out');
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4 faculty-card"
                             data-faculty-id="<?php echo $faculty['faculty_id']; ?>"
                             data-faculty-name="<?php echo strtolower($faculty['fullname']); ?>">
                            <div class="dashboard-card h-100">
                                <div class="card-body text-center">
                                    <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . 'profiles/' . ($faculty['profile_image'] ?? 'default.png'); ?>"
                                         class="rounded-circle mb-3" width="80" height="80" alt="<?php echo $faculty['fullname']; ?>">
                                    <h5><?php echo htmlspecialchars($faculty['fullname']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($faculty['position'] ?? 'Faculty'); ?></p>
                                    <span class="status-badge <?php echo $statusClass; ?> mb-3">
                                        <span class="status-badge-pulse"></span>
                                        <?php echo $status['status'] === 'IN' ? 'IN - In Office' : ($status['status'] === 'TRAVEL' ? 'ON TRAVEL' : 'OUT - Away'); ?>
                                    </span>
                                    <?php if ($status['status'] === 'TRAVEL' && $status['travel_from']): ?>
                                        <p class="small mt-2">
                                            <i class="bi bi-calendar-range"></i>
                                            <?php echo htmlspecialchars($status['travel_from']); ?> to <?php echo htmlspecialchars($status['travel_to']); ?>
                                            (<?php echo intval($status['travel_days']); ?> day(s))
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($status['activity']): ?>
                                        <p class="small mt-2">
                                            <i class="bi bi-info-circle"></i>
                                            <?php echo htmlspecialchars($status['activity']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const UPLOAD_DIR = '<?php echo UPLOAD_DIR; ?>';

        let searchTimeout = null;
        let fullFacultyList = [];

        $(document).ready(function() {
            storeInitialFaculty();

            $('#faculty-search').on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim();
                if (query.length === 0) {
                    resetToAllFaculty();
                    return;
                }
                if (query.length < 2) return;
                searchTimeout = setTimeout(doSearch, 300);
            });

            $('#search-clear').on('click', function() {
                $('#faculty-search').val('').trigger('input').focus();
            });

            $('#department-filter, #status-filter').on('change', function() {
                const query = $('#faculty-search').val().trim();
                if (query.length >= 2) {
                    doSearch();
                } else {
                    filterCurrentList();
                }
            });
        });

        function storeInitialFaculty() {
            fullFacultyList = [];
            $('#faculty-grid .faculty-card').each(function() {
                const $card = $(this);
                fullFacultyList.push({
                    faculty_id: $card.data('faculty-id'),
                    fullname: $card.data('faculty-name')
                });
            });
        }

        function doSearch() {
            const query = $('#faculty-search').val().trim();
            const departmentId = $('#department-filter').val();
            const statusFilter = $('#status-filter').val();

            const data = { q: query };
            if (departmentId) data.department_id = departmentId;

            $.ajax({
                url: SITE_URL + '/api/search_faculty.php',
                type: 'GET',
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response.success) {
                        renderResults(response.data.results, query, statusFilter);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Search error:', error);
                }
            });
        }

        function renderResults(results, query, statusFilter) {
            const $grid = $('#faculty-grid');
            $grid.empty();

            let filtered = results;
            if (statusFilter) {
                filtered = filtered.filter(function(f) {
                    return f.status === statusFilter;
                });
            }

            if (filtered.length === 0) {
                $grid.html('<div class="col-12"><div class="alert alert-info text-center">No faculty members match your search criteria.</div></div>');
                $('#search-stats').text('No results found for "' + query + '"');
                return;
            }

            $('#search-stats').text(filtered.length + ' result(s) found for "' + query + '"');

            $.each(filtered, function(_, f) {
                const statusClass = f.status === 'IN' ? 'in' : (f.status === 'TRAVEL' ? 'travel' : 'out');
                const statusText = f.status === 'IN' ? 'IN - In Office' : (f.status === 'TRAVEL' ? 'ON TRAVEL' : 'OUT - Away');
                let activityHtml = '';
                if (f.status === 'TRAVEL' && f.travel_from) {
                    activityHtml = '<p class="small mt-2"><i class="bi bi-calendar-range"></i> ' + escapeHtml(f.travel_from) + ' to ' + escapeHtml(f.travel_to) + ' (' + f.travel_days + ' day(s))</p>';
                } else if (f.activity) {
                    activityHtml = '<p class="small mt-2"><i class="bi bi-info-circle"></i> ' + escapeHtml(f.activity) + '</p>';
                }

                $grid.append(`
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="dashboard-card h-100">
                            <div class="card-body text-center">
                                <img src="${SITE_URL}/${UPLOAD_DIR}profiles/${f.profile_image || 'default.png'}"
                                     class="rounded-circle mb-3" width="80" height="80" alt="${escapeHtml(f.fullname)}">
                                <h5>${highlightText(escapeHtml(f.fullname), query)}</h5>
                                <p class="text-muted">${escapeHtml(f.position || 'Faculty')}</p>
                                <span class="status-badge ${statusClass} mb-3">
                                    <span class="status-badge-pulse"></span>
                                    ${statusText}
                                </span>
                                ${activityHtml}
                            </div>
                        </div>
                    </div>
                `);
            });
        }

        function filterCurrentList() {
            const query = $('#faculty-search').val().trim().toLowerCase();
            const departmentId = $('#department-filter').val();
            const statusFilter = $('#status-filter').val();

            if (query.length > 0 || departmentId || statusFilter) {
                doSearch();
            } else {
                resetToAllFaculty();
            }
        }

        function resetToAllFaculty() {
            $('#search-stats').empty();
            location.reload();
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function highlightText(text, query) {
            if (!query) return text;
            try {
                const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                return text.replace(regex, '<span class="search-highlight">$1</span>');
            } catch(e) {
                return text;
            }
        }
    </script>
</body>
</html>
