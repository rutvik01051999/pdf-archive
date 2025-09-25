/**
 * Activity Logs JavaScript
 * Handles all activity log functionality including modals
 */

// Wait for DOM to be ready
$(document).ready(function() {
    console.log('Activity Logs functionality initialized');
});

// View activity details function
function viewActivityDetails(activityId) {
    // Create modal for detailed view
    const modal = `
        <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="activityModalLabel">Activity Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="activityDetails">
                            <div class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#activityModal').remove();
    
    // Add modal to body
    $('body').append(modal);
    
    // Wait for modal to be added to DOM, then initialize
    setTimeout(function() {
        const modalElement = document.getElementById('activityModal');
        
        if (modalElement) {
            // Try Bootstrap 5 first
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                modalInstance.show();
            } 
            // Fallback to jQuery modal
            else if ($.fn.modal) {
                $('#activityModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
            }
            // Last resort - show modal manually
            else {
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                document.body.classList.add('modal-open');
                
                // Add backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'modalBackdrop';
                document.body.appendChild(backdrop);
            }
        }
    }, 100);
    
    // Load activity details via AJAX
    $.ajax({
        url: window.activityLogDetailsUrl.replace(':id', activityId),
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#activityDetails').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Failed to load activity details:', error);
            $('#activityDetails').html('<div class="alert alert-danger">Failed to load activity details.</div>');
        }
    });
}

// View related item function
function viewRelatedItem(subjectType, subjectId) {
    // Handle viewing related items based on type
    if (subjectType.includes('PdfArchive') || subjectType.includes('pdf')) {
        // Redirect to archive edit page
        const editUrl = window.archiveEditUrl.replace(':id', subjectId);
        window.open(editUrl, '_blank');
    } else {
        // Generic handling
        alert('Related item: ' + subjectType + ' #' + subjectId);
    }
}

// Refresh table function
function refreshTable() {
    if ($.fn.DataTable) {
        $('#activitylog-table').DataTable().ajax.reload();
    }
}

// Close modal and cleanup
function closeActivityModal() {
    const modalElement = document.getElementById('activityModal');
    
    if (modalElement) {
        // Try Bootstrap 5 first
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        // Fallback to jQuery modal
        else if ($.fn.modal) {
            $('#activityModal').modal('hide');
        }
        // Manual cleanup
        else {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            document.body.classList.remove('modal-open');
            
            // Remove backdrop
            const backdrop = document.getElementById('modalBackdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
        
        // Remove modal from DOM
        setTimeout(function() {
            $('#activityModal').remove();
        }, 300);
    }
}

// Initialize activity logs functionality
function initActivityLogs() {
    // Set up URLs if they're not already defined
    if (!window.activityLogDetailsUrl) {
        window.activityLogDetailsUrl = '/admin/activities/activity-logs/details/:id';
    }
    if (!window.archiveEditUrl) {
        window.archiveEditUrl = '/admin/archive/edit/:id';
    }
    
    // Add event listener for modal close buttons
    $(document).on('click', '[data-bs-dismiss="modal"]', function() {
        closeActivityModal();
    });
    
    // Add event listener for backdrop clicks
    $(document).on('click', '.modal-backdrop', function() {
        closeActivityModal();
    });
    
    console.log('Activity Logs functionality initialized');
}

// Initialize when document is ready
$(document).ready(function() {
    initActivityLogs();
});
