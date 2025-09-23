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

<script>
$(document).ready(function() {
    // Update hidden field when day or month changes
    function updateSpecialDate() {
        let day = $("#day_select").val();
        let month = $("#month_select").val();
        if (day && month) {
            $("#special_date").val(day + '-' + month);
        } else {
            $("#special_date").val('');
        }
    }
    
    // Bind change events to dropdowns
    $("#day_select, #month_select").on('change', function() {
        updateSpecialDate();
    });

    // DataTable initialization
    var specialDatesTable = $('#dataTableId').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            type: 'POST',
            url: "{{ route('admin.archive.special-dates.data') }}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataSrc: function(json) {
                console.log('Special Dates DataTable response received:', json);
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error('Special Dates DataTable AJAX error:', error, xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load special dates data: ' + error
                });
            }
        },
        columns: [
            { data: 0, title: '#' },
            { data: 1, title: 'Description' },
            { data: 2, title: 'Special Date' },
            { data: 3, title: 'Actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            // No export buttons as requested
        ],
        language: {
            processing: "Loading special dates...",
            emptyTable: "No special dates found",
            zeroRecords: "No matching special dates found"
        }
    });

    // Special Date management functions
    SpecialDateJquery = {
        AddSpecialDate: function() {
            $.ajax({
                type: 'POST',
                data: $('#defaultForm').serializeArray(),
                url: '{{ route("admin.archive.special-dates.store") }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 10000,
                success: function(response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#form').hide();
                        $('#dataTable').show();
                        document.getElementById('defaultForm').reset();
                        specialDatesTable.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    if (status === 'timeout') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Timeout!',
                            text: 'Request timed out. Please try again.'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to add special date'
                        });
                    }
                }
            });
        },

        DeleteSpecialDate: function(id) {
            $.ajax({
                type: 'POST',
                data: {'id': id},
                url: '{{ route("admin.archive.special-dates.delete", ":id") }}'.replace(':id', id),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 10000,
                success: function(response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        specialDatesTable.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    if (status === 'timeout') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Timeout!',
                            text: 'Request timed out. Please try again.'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete special date'
                        });
                    }
                }
            });
        },

        EditSpecialDate: function(id) {
            $.ajax({
                type: 'POST',
                data: {'id': id},
                url: '{{ route("admin.archive.special-dates.edit", ":id") }}'.replace(':id', id),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 10000,
                success: function(response) {
                    $("#dataTable,#form").toggle();
                    $.each(response, function(key, value) {
                        if (key === 'special_date' && value) {
                            // Parse DD-MM format and populate dropdowns
                            let parts = value.split('-');
                            if (parts.length === 2) {
                                $("#day_select").val(parts[0]);
                                $("#month_select").val(parts[1]);
                                $("#special_date").val(value);
                            }
                        } else {
                            $("#" + key).val(value);
                        }
                    });
                },
                error: function(xhr, status, error) {
                    if (status === 'timeout') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Timeout!',
                            text: 'Request timed out. Please try again.'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load special date data'
                        });
                    }
                }
            });
        }
    };

    // Event handlers
    $('#addNew').on('click', function() {
        $('#form').show();
        $('#id').val("");
        $('#dataTable').hide();
        $('input').css("border-color", "");
    });

    $('#cancel').on('click', function() {
        $('#form').hide();
        $('#dataTable').show();
        $('input, select').css("border-color", "");
    });

    $("#submit_Bnews").off().click(function() {
        let day = $("#day_select").val();
        let month = $("#month_select").val();
        let specialDate = $("#special_date").val();
        let description = $("#description").val();
        let isValid = true;

        // Reset border colors
        $('input, select').css("border-color", "");

        if (!day || !month) {
            if (!day) $("#day_select").css("border-color", "red");
            if (!month) $("#month_select").css("border-color", "red");
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'Please select both day and month.'
            });
            isValid = false;
        } else if (description == "") {
            $("#description").css("border-color", "red");
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'Description is required.'
            });
            isValid = false;
        }

        if (isValid) {
            SpecialDateJquery.AddSpecialDate();
        }
    });

    // Delete special date with SweetAlert2
    $(document).on('click', '.btn-delete', function() {
        var data_id = $(this).attr('data-id');
        var description = $(this).closest('tr').find('td:eq(1)').text();
        
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete "${description}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                SpecialDateJquery.DeleteSpecialDate(data_id);
            }
        });
    });

    // Edit special date
    $(document).on('click', '.btn-edit', function() {
        SpecialDateJquery.EditSpecialDate($(this).attr('data-id'));
    });
});
</script>
@endpush
