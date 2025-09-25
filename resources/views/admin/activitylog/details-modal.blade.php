<div class="activity-details-modal">
    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-semibold text-primary mb-3">Activity Information</h6>
            <table class="table table-sm">
                <tr>
                    <td class="fw-semibold">Description:</td>
                    <td>{{ $activity->description }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">User:</td>
                    <td>{{ $activity->causer ? $activity->causer->name : 'System' }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Date & Time:</td>
                    <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
                @if($activity->subject_type)
                <tr>
                    <td class="fw-semibold">Subject Type:</td>
                    <td>{{ class_basename($activity->subject_type) }}</td>
                </tr>
                @endif
                @if($activity->subject_id)
                <tr>
                    <td class="fw-semibold">Subject ID:</td>
                    <td>{{ $activity->subject_id }}</td>
                </tr>
                @endif
            </table>
        </div>
        
        <div class="col-md-6">
            <h6 class="fw-semibold text-primary mb-3">Technical Details</h6>
            <table class="table table-sm">
                @if(isset($properties['ip']))
                <tr>
                    <td class="fw-semibold">IP Address:</td>
                    <td><code>{{ $properties['ip'] }}</code></td>
                </tr>
                @endif
                
                @if(isset($properties['method']))
                <tr>
                    <td class="fw-semibold">HTTP Method:</td>
                    <td><span class="badge bg-info">{{ $properties['method'] }}</span></td>
                </tr>
                @endif
                
                @if(isset($properties['user_agent']))
                <tr>
                    <td class="fw-semibold">User Agent:</td>
                    <td><small class="text-muted">{{ $properties['user_agent'] }}</small></td>
                </tr>
                @endif
                
                @if(isset($properties['url']))
                <tr>
                    <td class="fw-semibold">URL:</td>
                    <td><small class="text-muted">{{ $properties['url'] }}</small></td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    
    @if(!empty($properties))
        <div class="row mt-4">
            <div class="col-12">
                <h6 class="fw-semibold text-primary mb-3">Additional Properties</h6>
                <div class="card">
                    <div class="card-body">
                        <pre class="mb-0" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($properties, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

