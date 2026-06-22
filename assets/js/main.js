/**
 * Main JavaScript
 * CCSICT Faculty Monitoring System
 */

$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').not('.alert-persistent').slideUp('slow', function() {
            $(this).remove();
        });
    }, 5000);

    // Toggle sidebar on mobile
    $(document).on('click', '.toggle-sidebar', function() {
        $('.sidebar').toggleClass('show');
    });

    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('.toggle-sidebar').length) {
                $('.sidebar').removeClass('show');
            }
        }
    });

    // Set active sidebar link based on current page
    setActiveSidebarLink();

    // Format time inputs
    formatTimeInputs();

    // Initialize tooltips (Bootstrap)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

/**
 * Set active sidebar link based on current page
 */
function setActiveSidebarLink() {
    var currentUrl = window.location.pathname;
    var currentFile = currentUrl.substring(currentUrl.lastIndexOf('/') + 1);

    $('.sidebar-link').each(function() {
        var href = $(this).attr('href');
        var hrefFile = href.substring(href.lastIndexOf('/') + 1);

        if (hrefFile === currentFile) {
            $(this).addClass('active');
        }
    });
}

/**
 * Format time display
 */
function formatTime(time) {
    if (!time) return '';
    
    var date = new Date(time);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

/**
 * Format date display
 */
function formatDate(date) {
    if (!date) return '';
    
    var d = new Date(date);
    return d.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Format DateTime display
 */
function formatDateTime(datetime) {
    if (!datetime) return '';
    
    var date = new Date(datetime);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Format time inputs
 */
function formatTimeInputs() {
    $(document).on('change', 'input[type="time"]', function() {
        var time = $(this).val();
        if (time) {
            console.log('Time selected: ' + time);
        }
    });
}

/**
 * Show success message
 */
function showSuccess(message, duration = 5000) {
    var alertHtml = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    var $alert = $(alertHtml);
    
    if ($('.content-wrapper').length) {
        $('.content-wrapper').prepend($alert);
    } else {
        $('body').prepend($alert);
    }
    
    if (duration > 0) {
        setTimeout(function() {
            $alert.slideUp('slow', function() {
                $(this).remove();
            });
        }, duration);
    }
}

/**
 * Show error message
 */
function showError(message, duration = 5000) {
    var alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle-fill"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    var $alert = $(alertHtml);
    
    if ($('.content-wrapper').length) {
        $('.content-wrapper').prepend($alert);
    } else {
        $('body').prepend($alert);
    }
    
    if (duration > 0) {
        setTimeout(function() {
            $alert.slideUp('slow', function() {
                $(this).remove();
            });
        }, duration);
    }
}

/**
 * Show warning message
 */
function showWarning(message, duration = 5000) {
    var alertHtml = `
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    var $alert = $(alertHtml);
    
    if ($('.content-wrapper').length) {
        $('.content-wrapper').prepend($alert);
    } else {
        $('body').prepend($alert);
    }
    
    if (duration > 0) {
        setTimeout(function() {
            $alert.slideUp('slow', function() {
                $(this).remove();
            });
        }, duration);
    }
}

/**
 * Show loading spinner
 */
function showLoader() {
    var loaderHtml = `
        <div class="loader-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    $('body').append(loaderHtml);
}

/**
 * Hide loading spinner
 */
function hideLoader() {
    $('.loader-overlay').remove();
}

/**
 * Confirm action before proceeding
 */
function confirmAction(message) {
    return confirm(message);
}

/**
 * Logout user
 */
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = SITE_URL + '/logout.php';
    }
}

/**
 * Redirect to page
 */
function redirectTo(url) {
    setTimeout(function() {
        window.location.href = url;
    }, 500);
}

/**
 * Validate email
 */
function validateEmail(email) {
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate password strength
 */
function validatePasswordStrength(password) {
    if (password.length < 6) {
        return { valid: false, message: 'Password must be at least 6 characters long' };
    }
    
    if (!/[A-Z]/.test(password)) {
        return { valid: false, message: 'Password must contain at least one uppercase letter' };
    }
    
    if (!/[a-z]/.test(password)) {
        return { valid: false, message: 'Password must contain at least one lowercase letter' };
    }
    
    if (!/[0-9]/.test(password)) {
        return { valid: false, message: 'Password must contain at least one number' };
    }
    
    return { valid: true, message: 'Password is strong' };
}

/**
 * Truncate text
 */
function truncateText(text, length) {
    if (text.length > length) {
        return text.substring(0, length) + '...';
    }
    return text;
}
