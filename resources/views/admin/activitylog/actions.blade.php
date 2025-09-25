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

