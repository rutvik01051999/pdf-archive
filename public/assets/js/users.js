/**
 * Users JavaScript
 * Handles all user management functionality
 */

// Toggle user status function
function toggleUserStatus(userId, newStatus) {
    $.ajax({
        url: `/admin/archive/users/${userId}/status`,
        type: 'PUT',
        data: { status: newStatus },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', 'User status updated successfully!');
                location.reload();
            } else {
                showAlert('danger', response.message || 'Failed to update user status');
            }
        },
        error: function(xhr) {
            showAlert('danger', 'Failed to update user status');
        }
    });
}

// Show alert function
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container-fluid').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}

// Initialize users functionality
function initUsers() {
    console.log('Users functionality initialized');
}

// Initialize when document is ready
$(document).ready(function() {
    initUsers();
});
