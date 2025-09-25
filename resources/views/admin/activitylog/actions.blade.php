<div class="activity-actions">
    <button class="btn btn-sm btn-outline-primary" onclick="viewActivityDetails({{ $activity->id }})" title="View Details">
        <i class="bx bx-show"></i>
    </button>
    
    @if($activity->subject_type && $activity->subject_id)
        <button class="btn btn-sm btn-outline-info" onclick="viewRelatedItem('{{ $activity->subject_type }}', {{ $activity->subject_id }})" title="View Related Item">
            <i class="bx bx-link"></i>
        </button>
    @endif
</div>

<script>
function viewActivityDetails(activityId) {
    // Create modal for detailed view
    const modal = `
        <div class="modal fade" id="activityModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Activity Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
    
    // Show modal
    $('#activityModal').modal('show');
    
    // Load activity details via AJAX
    $.ajax({
        url: '{{ route("admin.activities.activity-logs.details", ":id") }}'.replace(':id', activityId),
        method: 'GET',
        success: function(response) {
            $('#activityDetails').html(response);
        },
        error: function() {
            $('#activityDetails').html('<div class="alert alert-danger">Failed to load activity details.</div>');
        }
    });
}

function viewRelatedItem(subjectType, subjectId) {
    // Handle viewing related items based on type
    if (subjectType.includes('PdfArchive') || subjectType.includes('pdf')) {
        // Redirect to archive edit page
        window.open('{{ route("admin.archive.edit", ":id") }}'.replace(':id', subjectId), '_blank');
    } else {
        // Generic handling
        alert('Related item: ' + subjectType + ' #' + subjectId);
    }
}
</script>

