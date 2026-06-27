/**
 * AJAX Functions
 * CCSICT Faculty Monitoring System
 */

// Auto-refresh faculty status on live monitor page
var autoRefreshInterval = null;

/**
 * Start auto-refresh for live monitor
 */
function startAutoRefresh(interval = 5000) {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    autoRefreshInterval = setInterval(function() {
        refreshFacultyStatus();
    }, interval);
}

/**
 * Stop auto-refresh
 */
function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

/**
 * Refresh faculty status via AJAX
 */
function refreshFacultyStatus() {
    $.ajax({
        url: SITE_URL + '/api/get_faculty_status.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                updateStatusDisplay(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error refreshing status:', error);
        }
    });
}

/**
 * Update status display with new data
 */
function updateStatusDisplay(facultyData) {
    $.each(facultyData, function(index, faculty) {
        var $card = $('[data-faculty-id="' + faculty.id + '"]');
        
        if ($card.length) {
            var statusClass = faculty.status === 'IN' ? 'in' : (faculty.status === 'TRAVEL' ? 'travel' : 'out');
            var statusText = faculty.status === 'IN' ? 'IN' : (faculty.status === 'TRAVEL' ? 'TRAVEL' : 'OUT');
            var activity = faculty.activity ? faculty.activity : '';
            var location = faculty.location ? faculty.location : '';
            
            $card.find('.faculty-status-badge').html(`
                <span class="status-badge-pulse"></span> ${statusText}
            `).removeClass('in out travel').addClass(statusClass);
            
            if (faculty.status === 'TRAVEL' && faculty.travel_from) {
                var travelInfo = 'Travel: ' + faculty.travel_from + ' to ' + faculty.travel_to + ' (' + faculty.travel_days + ' day(s))';
                $card.find('.faculty-activity').text(travelInfo);
            } else if (activity || location) {
                var activityText = activity + (location ? ' at ' + location : '');
                $card.find('.faculty-activity').text(activityText);
            } else {
                $card.find('.faculty-activity').text('');
            }
            
            $card.find('.faculty-time').text('Updated: ' + formatDateTime(new Date()));
        }
    });
}

/**
 * Scan QR Code
 * @param {string} qrToken - The QR token to scan
 * @param {function} [onComplete] - Optional callback(response|null) after AJAX completes
 */
function scanQRCode(qrToken, onComplete, skipDefaultUI) {
    $.ajax({
        url: SITE_URL + '/api/scan_qr.php',
        type: 'POST',
        dataType: 'json',
        timeout: 10000,
        data: {
            qr_token: qrToken
        },
        success: function(response) {
            if (response.success && !skipDefaultUI) {
                showSuccess(response.message);
                
                if (response.scan_type === 'OUT') {
                    showActivitySelection(response.faculty_id);
                } else {
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            } else if (!response.success) {
                showError(response.message);
            }
            if (typeof onComplete === 'function') {
                onComplete(response);
            }
        },
        error: function(xhr, status, error) {
            if (!skipDefaultUI) {
                showError('Error during QR scan: ' + error);
            }
            if (typeof onComplete === 'function') {
                onComplete(null);
            }
        }
    });
}

/**
 * Show activity selection modal for time-out
 */
function showActivitySelection(facultyId) {
    $.ajax({
        url: SITE_URL + '/api/get_activities.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var activities = response.data;
                var optionsHtml = '';
                
                $.each(activities, function(index, activity) {
                    optionsHtml += `<option value="${activity.id}">${activity.name}</option>`;
                });
                
                var modalHtml = `
                    <div class="modal fade" id="activityModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Select Your Activity</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="activityForm">
                                        <div class="mb-3">
                                            <label class="form-label">Activity</label>
                                            <select class="form-select" name="activity_id" required>
                                                <option value="">-- Select Activity --</option>
                                                ${optionsHtml}
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Location</label>
                                            <input type="text" class="form-control" name="location" placeholder="e.g., Room 204">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" onclick="submitActivitySelection(${facultyId})">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('body').append(modalHtml);
                new bootstrap.Modal(document.getElementById('activityModal')).show();
            }
        }
    });
}

/**
 * Submit activity selection
 */
function submitActivitySelection(facultyId) {
    var activityId = $('select[name="activity_id"]').val();
    var location = $('input[name="location"]').val();
    
    if (!activityId) {
        showError('Please select an activity');
        return;
    }
    
    $.ajax({
        url: SITE_URL + '/api/record_timeout.php',
        type: 'POST',
        dataType: 'json',
        data: {
            faculty_id: facultyId,
            activity_id: activityId,
            location: location
        },
        success: function(response) {
            if (response.success) {
                $('#activityModal').modal('hide');
                showSuccess(response.message);
                
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                showError(response.message);
            }
        },
        error: function(xhr, status, error) {
            showError('Error recording time-out: ' + error);
        }
    });
}

/**
 * Update faculty status
 */
function updateFacultyStatus(facultyId, status, activity, location, travelFrom, travelTo, travelDays) {
    $.ajax({
        url: SITE_URL + '/api/update_status.php',
        type: 'POST',
        dataType: 'json',
        data: {
            faculty_id: facultyId,
            status: status,
            activity: activity,
            location: location,
            travel_from: travelFrom,
            travel_to: travelTo,
            travel_days: travelDays
        },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                refreshFacultyStatus();
            } else {
                showError(response.message);
            }
        },
        error: function(xhr, status, error) {
            showError('Error updating status: ' + error);
        }
    });
}

/**
 * Approve user account
 */
function approveUser(userId) {
    if (!confirmAction('Are you sure you want to approve this account?')) {
        return;
    }
    
    $.ajax({
        url: SITE_URL + '/api/approve_user.php',
        type: 'POST',
        dataType: 'json',
        data: {
            user_id: userId
        },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                showError(response.message);
            }
        },
        error: function(xhr, status, error) {
            showError('Error approving user: ' + error);
        }
    });
}

/**
 * Reject user account
 */
function rejectUser(userId) {
    if (!confirmAction('Are you sure you want to reject this account?')) {
        return;
    }
    
    $.ajax({
        url: SITE_URL + '/api/reject_user.php',
        type: 'POST',
        dataType: 'json',
        data: {
            user_id: userId
        },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                showError(response.message);
            }
        },
        error: function(xhr, status, error) {
            showError('Error rejecting user: ' + error);
        }
    });
}

/**
 * Delete announcement
 */
function deleteAnnouncement(announcementId) {
    if (!confirmAction('Are you sure you want to delete this announcement?')) {
        return;
    }
    
    $.ajax({
        url: SITE_URL + '/api/delete_announcement.php',
        type: 'POST',
        dataType: 'json',
        data: {
            announcement_id: announcementId
        },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message);
                $('[data-announcement-id="' + announcementId + '"]').fadeOut('slow', function() {
                    $(this).remove();
                });
            } else {
                showError(response.message);
            }
        },
        error: function(xhr, status, error) {
            showError('Error deleting announcement: ' + error);
        }
    });
}

/**
 * Search faculty
 */
function searchFaculty(query) {
    if (query.length < 2) {
        $('.search-results').empty();
        return;
    }
    
    $.ajax({
        url: SITE_URL + '/api/search_faculty.php',
        type: 'GET',
        dataType: 'json',
        data: {
            q: query
        },
        success: function(response) {
            if (response.success) {
                displaySearchResults(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error searching faculty:', error);
        }
    });
}

/**
 * Display search results
 */
function displaySearchResults(results) {
    var resultsHtml = '';
    
    if (results.length === 0) {
        resultsHtml = '<div class="alert alert-info">No results found</div>';
    } else {
        $.each(results, function(index, faculty) {
            resultsHtml += `
                <div class="faculty-search-result" data-faculty-id="${faculty.id}">
                    <img src="${SITE_URL}/${UPLOAD_DIR}/profiles/${faculty.profile_image}" class="faculty-pic" alt="${faculty.fullname}">
                    <div class="faculty-info">
                        <h6>${faculty.fullname}</h6>
                        <p>${faculty.position}</p>
                    </div>
                </div>
            `;
        });
    }
    
    $('.search-results').html(resultsHtml);
}
