@extends('admin.layouts.app')

@section('content')
@include('admin.layouts.partials.page-header', [
    'title' => 'Login Logs',
    'breadcrumb' => [
        'Home' => route('admin.dashboard.index'),
        'Archive' => route('admin.archive.dashboard'),
        'Login Logs' => route('admin.archive.login-logs'),
    ],
])

<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="mb-0">Filter Logs</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.archive.login-logs') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="username" value="{{ request('username') }}" placeholder="Username">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="center">
                                <option value="">All Centers</option>
                                @foreach($centers as $center)
                                    <option value="{{ $center->center_code }}" {{ request('center') == $center->center_code ? 'selected' : '' }}>
                                        {{ $center->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h6 class="mb-0">Login Logs ({{ $logs->total() }} found)</h6>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Center</th>
                                <th>IP Address</th>
                                <th>Login Time</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td><strong>{{ $log->username }}</strong></td>
                                    <td>{{ $log->center }}</td>
                                    <td>{{ $log->ip_address ?: 'N/A' }}</td>
                                    <td>{{ $log->login_time->format('d M Y H:i:s') }}</td>
                                    <td>{{ Str::limit($log->user_agent ?: 'N/A', 50) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No login logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

