<div class="activity-details">
    @if(!empty($properties))
        <div class="row">
            @if(isset($properties['ip']))
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">IP Address</small>
                    <span class="fw-semibold">{{ $properties['ip'] }}</span>
                </div>
            @endif
            
            @if(isset($properties['user_agent']))
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">User Agent</small>
                    <span class="fw-semibold text-truncate d-block" style="max-width: 200px;" title="{{ $properties['user_agent'] }}">
                        {{ Str::limit($properties['user_agent'], 30) }}
                    </span>
                </div>
            @endif
            
            @if(isset($properties['url']))
                <div class="col-12 mb-2">
                    <small class="text-muted d-block">URL</small>
                    <span class="fw-semibold text-truncate d-block" style="max-width: 300px;" title="{{ $properties['url'] }}">
                        {{ Str::limit($properties['url'], 50) }}
                    </span>
                </div>
            @endif
            
            @if(isset($properties['method']))
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Method</small>
                    <span class="badge bg-info">{{ $properties['method'] }}</span>
                </div>
            @endif
            
            @if(isset($properties['archive_id']))
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Archive ID</small>
                    <span class="fw-semibold">{{ $properties['archive_id'] }}</span>
                </div>
            @endif
            
            @if(isset($properties['search_params']) && !empty($properties['search_params']))
                <div class="col-12 mb-2">
                    <small class="text-muted d-block">Search Parameters</small>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($properties['search_params'] as $key => $value)
                            @if($value)
                                <span class="badge bg-light text-dark">{{ $key }}: {{ $value }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            
            @if(isset($properties['changes']) && !empty($properties['changes']))
                <div class="col-12 mb-2">
                    <small class="text-muted d-block">Changes</small>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($properties['changes'] as $key => $value)
                            <span class="badge bg-warning">{{ $key }}: {{ $value }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        <span class="text-muted">No additional details</span>
    @endif
</div>

