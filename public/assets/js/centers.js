/**
 * Centers JavaScript
 * Handles all center management functionality
 */

$(document).ready(function() {
    // Add Center
    $('#addCenterForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            center_code: $('#add_center_code').val(),
            description: $('#add_description').val(),
            region: $('#add_region').val(),
            state: $('#add_state').val(),
            city: $('#add_city').val(),
            status: $('#add_status').is(':checked') ? 1 : 0
        };
        
        $.ajax({
            url: window.centersStoreUrl || '/admin/archive/centers/store',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Center added successfully!');
                    $('#addCenterModal').modal('hide');
                    location.reload();
                } else {
                    showAlert('danger', response.message || 'Failed to add center');
                }
            },
            error: function(xhr) {
                let message = 'Failed to add center';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
            }
        });
    });
});

// Edit Center
function editCenter(id) {
    // Implementation for editing center
    console.log('Edit center:', id);
}

// Delete Center
function deleteCenter(id) {
    if (confirm('Are you sure you want to delete this center?')) {
        $.ajax({
            url: (window.centersDeleteUrl || '/admin/archive/centers/:id').replace(':id', id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Center deleted successfully!');
                    location.reload();
                } else {
                    showAlert('danger', response.message || 'Failed to delete center');
                }
            },
            error: function(xhr) {
                let message = 'Failed to delete center';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
            }
        });
    }
}

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
