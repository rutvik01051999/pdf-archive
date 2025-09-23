@extends('admin.layouts.app')

@section('content')
    @include('admin.layouts.partials.page-header', [
        'title' => 'Add New Employee',
        'breadcrumb' => [
            'Home' => route('admin.dashboard.index'),
            'Employee Management' => route('admin.employees.index'),
            'Add New Employee' => '#'
        ]
    ])

    @include('admin.layouts.partials.alert')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title">Employee Information</h5>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.employees.store') }}" method="POST" id="employeeForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee ID/Alias <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control @error('employee_id') is-invalid @enderror" 
                                               id="employee_id" 
                                               name="employee_id" 
                                               value="{{ old('employee_id') }}" 
                                               placeholder="Enter employee ID or name"
                                               required>
                                        <button type="button" 
                                                class="btn btn-outline-primary" 
                                                id="fetchEmployeeBtn">
                                            <i class="bx bx-search"></i> Fetch Data
                                        </button>
                                    </div>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Enter employee ID or name to fetch data from system</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           placeholder="Enter email address"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" 
                                           class="form-control @error('full_name') is-invalid @enderror" 
                                           id="full_name" 
                                           name="full_name" 
                                           value="{{ old('full_name') }}" 
                                           placeholder="Enter full name"
                                           required>
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input type="text" 
                                           class="form-control @error('phone_number') is-invalid @enderror" 
                                           id="phone_number" 
                                           name="phone_number" 
                                           value="{{ old('phone_number') }}" 
                                           placeholder="Enter phone number"
                                           required>
                                    @error('phone_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" 
                                           class="form-control @error('department') is-invalid @enderror" 
                                           id="department" 
                                           name="department" 
                                           value="{{ old('department') }}" 
                                           placeholder="Enter department"
                                           required>
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-x"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save"></i> Save Employee
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Fetching employee data...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fetchBtn = document.getElementById('fetchEmployeeBtn');
    const employeeIdInput = document.getElementById('employee_id');
    const loadingModal = $('#loadingModal');
    
    fetchBtn.addEventListener('click', function() {
        const alias = employeeIdInput.value.trim();
        
        if (!alias) {
            alert('Please enter employee ID or name');
            return;
        }
        
        // Show loading modal
      //  loadingModal.show();
        
        // Fetch employee data
        fetch('{{ route("admin.employees.fetch-data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                alias: alias
            })
        })
        .then(response => response.json())
        .then(data => {
            //loadingModal.hide();            
            if (data.success) {
                // Populate form fields with fetched data
                document.getElementById('employee_id').value = data.data.employee_id || alias;
                document.getElementById('email').value = data.data.email || '';
                document.getElementById('full_name').value = data.data.full_name || '';
                document.getElementById('phone_number').value = data.data.phone_number || '';
                document.getElementById('department').value = data.data.department || '';
                // Show success message
                showAlert('Employee data fetched successfully!', 'success');
            } else {
                showAlert(data.message || 'Failed to fetch employee data', 'error');
            }
        })
        .catch(error => {
           // loadingModal.hide();
            console.error('Error:', error);
            showAlert('An error occurred while fetching employee data', 'error');
        });
    });
    
    function showAlert(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Insert alert at the top of the form
        const form = document.getElementById('employeeForm');
        form.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            const alert = form.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>
@endpush


