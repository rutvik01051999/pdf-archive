@extends('admin.layouts.app')

@section('content')
@include('admin.layouts.partials.page-header', [
    'title' => 'Archive Users',
    'breadcrumb' => [
        'Home' => route('admin.dashboard.index'),
        'Archive' => route('admin.archive.dashboard'),
        'Users' => route('admin.archive.users'),
    ],
])

<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h6 class="mb-0">Archive Users</h6>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Center</th>
                                <th>Email</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td><strong>{{ $user->uname }}</strong></td>
                                    <td>{{ $user->full_name ?: 'N/A' }}</td>
                                    <td>{{ $user->center }}</td>
                                    <td>{{ $user->email ?: 'N/A' }}</td>
                                    <td>{{ $user->formatted_last_login }}</td>
                                    <td>
                                        <span class="badge bg-{{ $user->status ? 'success' : 'danger' }}">
                                            {{ $user->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-{{ $user->status ? 'warning' : 'success' }}" 
                                                onclick="toggleUserStatus({{ $user->id }}, {{ $user->status ? 0 : 1 }})">
                                            {{ $user->status ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Users JavaScript -->
<script src="{{ asset('assets/js/users.js') }}"></script>
@endpush

