@extends('archive.layouts.app')

@section('title', 'Login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="archive-card card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-archive fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">PDF Archive Login</h3>
                        <p class="text-muted">Please login to access the archive system</p>
                    </div>

                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="center" class="form-label">Select Center <span class="text-danger">*</span></label>
                            <select class="form-select" id="center" name="center" required>
                                <option value="">-- Select Center --</option>
                                @foreach($centers as $center)
                                    <option value="{{ $center->center_code }}">{{ $center->description }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-archive" id="loginBtn">
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Having trouble logging in? Contact your system administrator.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous validation
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Get form data
        const formData = {
            username: $('#username').val(),
            password: $('#password').val(),
            center: $('#center').val()
        };
        
        // Validate form
        let isValid = true;
        if (!formData.username.trim()) {
            $('#username').addClass('is-invalid');
            $('#username').siblings('.invalid-feedback').text('Username is required');
            isValid = false;
        }
        
        if (!formData.password.trim()) {
            $('#password').addClass('is-invalid');
            $('#password').siblings('.invalid-feedback').text('Password is required');
            isValid = false;
        }
        
        if (!formData.center) {
            $('#center').addClass('is-invalid');
            $('#center').siblings('.invalid-feedback').text('Please select a center');
            isValid = false;
        }
        
        if (!isValid) return;
        
        // Show loading state
        const $btn = $('#loginBtn');
        const $spinner = $btn.find('.spinner-border');
        const $icon = $btn.find('i');
        
        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $icon.addClass('d-none');
        $btn.find('span:not(.spinner-border)').text('Authenticating...');
        
        // Make AJAX request
        $.ajax({
            url: '{{ route("archive.authenticate") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showAlert('success', 'Login successful! Redirecting...');
                    
                    // Redirect after short delay
                    setTimeout(function() {
                        window.location.href = response.redirect || '{{ route("archive.display") }}';
                    }, 1000);
                } else {
                    showAlert('danger', response.message || 'Login failed');
                    resetLoginButton();
                }
            },
            error: function(xhr) {
                let message = 'Login failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
                resetLoginButton();
            }
        });
        
        function resetLoginButton() {
            $btn.prop('disabled', false);
            $spinner.addClass('d-none');
            $icon.removeClass('d-none');
            $btn.find('span:not(.spinner-border)').text('Login');
        }
    });
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.card-body').prepend(alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }
});
</script>
@endpush

