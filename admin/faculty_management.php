<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/session_check.php';
require_once '../includes/functions.php';

$pageTitle = 'Faculty Management';
requireRole(['Admin']);

$deleted = isset($_GET['deleted']) ? true : false;
$error = sanitizeInput($_GET['error'] ?? '');

$faculty = getAllFaculty();
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
                    <h1 class="h3"><i class="bi bi-people"></i> Faculty Management</h1>
                    <a href="add_faculty.php" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Add Faculty
                    </a>
                </div>

                <?php if ($deleted): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> Faculty member deleted successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Search -->
                <div class="dashboard-card mb-4">
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="bi bi-search"></i> Search Faculty</label>
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
                                </select>
                            </div>
                        </div>
                        <div class="search-stats" id="search-stats"></div>
                    </div>
                </div>

                <!-- Faculty Grid -->
                <div class="row" id="faculty-grid">
                    <?php foreach ($faculty as $f): 
                        $status = getFacultyStatus($f['faculty_id']);
                        $statusClass = ($status['status'] === 'IN') ? 'in' : 'out';
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4 faculty-card" data-faculty-id="<?php echo $f['faculty_id']; ?>">
                            <div class="dashboard-card">
                                <div class="card-body text-center">
                                    <img src="<?php echo SITE_URL . '/' . UPLOAD_DIR . 'profiles/' . ($f['profile_image'] ?? 'default.png'); ?>" 
                                         class="rounded-circle mb-3" width="80" height="80" alt="<?php echo $f['fullname']; ?>">
                                    
                                    <h5><?php echo htmlspecialchars($f['fullname']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($f['position'] ?? 'Faculty'); ?></p>
                                    
                                    <span class="status-badge <?php echo $statusClass; ?> mb-3">
                                        <span class="status-badge-pulse"></span>
                                        <?php echo $status['status'] === 'IN' ? 'IN' : 'OUT'; ?>
                                    </span>
                                    
                                    <div class="mt-3">
                                        <a href="edit_faculty.php?id=<?php echo $f['faculty_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="delete_faculty.php?id=<?php echo $f['faculty_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </div>
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

        $(document).ready(function() {
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
                    resetToAllFaculty();
                }
            });
        });

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
                const statusClass = f.status === 'IN' ? 'in' : 'out';
                const statusText = f.status === 'IN' ? 'IN' : 'OUT';

                const card = `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="dashboard-card">
                            <div class="card-body text-center">
                                <img src="${SITE_URL}/${UPLOAD_DIR}profiles/${f.profile_image || 'default.png'}"
                                     class="rounded-circle mb-3" width="80" height="80" alt="${escapeHtml(f.fullname)}">
                                <h5>${highlightText(escapeHtml(f.fullname), query)}</h5>
                                <p class="text-muted">${escapeHtml(f.position || 'Faculty')}</p>
                                <span class="status-badge ${statusClass} mb-3">
                                    <span class="status-badge-pulse"></span>
                                    ${statusText}
                                </span>
                                <div class="mt-3">
                                    <a href="edit_faculty.php?id=${f.faculty_id}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="delete_faculty.php?id=${f.faculty_id}"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $grid.append(card);
            });
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
