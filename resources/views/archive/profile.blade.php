@extends('archive.layouts.app')

@section('title', 'User Profile')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="archive-card card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user text-primary me-2"></i>
                        User Profile
                    </h5>
                </div>
                <div class="card-body">
                    <form id="profileForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="uname" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="uname" value="{{ $login->uname }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="center" class="form-label">Center</label>
                                    <input type="text" class="form-control" id="center" value="{{ $login->center }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="{{ $login->full_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ $login->email }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="{{ $login->phone }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_login" class="form-label">Last Login</label>
                                    <input type="text" class="form-control" id="last_login" 
                                           value="{{ $login->formatted_last_login }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('archive.display') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <button type="submit" class="btn btn-archive" id="updateBtn">
                                <span class="spinner-border spinner-border-sm d-none me-2"></span>
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="archive-card card">
                <div class="card-header">
                    <h6 class="mb-0">Recent Login Activity</h6>
                </div>
                <div class="card-body">
                    @if($recentLogins->count() > 0)
                        @foreach($recentLogins as $log)
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <i class="fas fa-sign-in-alt text-success"></i>
                                </div>
                                <div>
                                    <small class="text-muted">{{ $log->login_time->format('d M Y H:i') }}</small><br>
                                    <small>IP: {{ $log->ip_address ?: 'N/A' }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No recent login activity found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            full_name: $('#full_name').val(),
            email: $('#email').val(),
            phone: $('#phone').val()
        };
        
        // Show loading state
        const $btn = $('#updateBtn');
        const $spinner = $btn.find('.spinner-border');
        const $icon = $btn.find('i');
        
        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $icon.addClass('d-none');
        
        $.ajax({
            url: '{{ route("archive.update-profile") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Profile updated successfully!');
                } else {
                    showAlert('danger', response.message || 'Update failed');
                }
            },
            error: function(xhr) {
                let message = 'Update failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
            },
            complete: function() {
                // Reset button state
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                $icon.removeClass('d-none');
            }
        });
    });
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.container').prepend(alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }
});
</script>
@endpush

