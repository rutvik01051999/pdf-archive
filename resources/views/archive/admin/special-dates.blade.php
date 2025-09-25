@extends('admin.layouts.app')

@section('content')
    @include('admin.layouts.partials.page-header', [
        'title' => 'Special Dates',
        'breadcrumb' => [
            'Home' => route('admin.dashboard.index'),
            'Archive' => route('admin.archive.dashboard'),
            'Special Dates' => route('admin.archive.special-dates'),
        ]
    ])
    @include('admin.layouts.partials.alert')

    <!-- Special Date Form -->
    <div class="row" id="form" style="display: none;">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bx bx-calendar me-2"></i>
                        Special Date Form
                    </div>
                </div>
                <div class="card-body">
                    <form id="defaultForm" method="post">
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="mb-3">
                                    <label for="special_date" class="form-label">Special Date <span class="text-danger">*</span></label>
                                    <input type="hidden" name="id" id="id">
                                    <div class="row">
                                        <div class="col-6">
                                            <select class="form-control" id="day_select" required>
                                                <option value="">Day</option>
                                                <?php for($i = 1; $i <= 31; $i++): ?>
                                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <select class="form-control" id="month_select" required>
                                                <option value="">Month</option>
                                                <?php 
                                                $months = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun',
                                                         '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'];
                                                foreach($months as $num => $name): ?>
                                                    <option value="<?php echo $num; ?>"><?php echo $name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" class="form-control" id="special_date" name="special_date" required>
                                    <div class="form-text">Select day and month (e.g., 15th March)</div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="description" name="description" placeholder="Enter special date description" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <button type="button" class="btn btn-primary" id="submit_Bnews">
                                    <i class="bx bx-check me-1"></i> Submit
                                </button>
                                <button type="reset" class="btn btn-light" id="cancel">
                                    <i class="bx bx-x me-1"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Special Dates Table -->
    <div class="row" id="dataTable">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title">
                        <i class="bx bx-list me-2"></i>
                        Special Date List
                    </div>
                    <button type="button" class="btn btn-primary" id="addNew">
                        <i class="bx bx-plus me-1"></i> Add New Special Date
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap w-100" id="dataTableId">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Description</th>
                                    <th>Special Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Special Dates JavaScript -->
<script src="{{ asset('assets/js/special-dates.js') }}"></script>
<script>
// Set up URLs for the special dates functionality
window.specialDatesDataUrl = '{{ route("admin.archive.special-dates.data") }}';
window.specialDatesStoreUrl = '{{ route("admin.archive.special-dates.store") }}';
window.specialDatesDeleteUrl = '{{ route("admin.archive.special-dates.delete", ":id") }}';
window.specialDatesEditUrl = '{{ route("admin.archive.special-dates.edit", ":id") }}';
</script>
@endpush
