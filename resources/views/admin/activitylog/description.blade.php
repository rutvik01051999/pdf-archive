<div class="activity-description">
    <div class="fw-semibold text-primary mb-1">{{ $activity->description }}</div>
    @if($activity->subject_type)
        <small class="text-muted">
            <i class="bx bx-link me-1"></i>
            {{ class_basename($activity->subject_type) }}
            @if($activity->subject_id)
                #{{ $activity->subject_id }}
            @endif
        </small>
    @endif
</div>

