@extends('admin.layouts.app')

@section('content')
    @include('admin.layouts.partials.page-header', [
        'title' => 'Archive Categories',
        'breadcrumb' => [
            'Home' => route('admin.dashboard.index'),
            'Archive' => route('admin.archive.dashboard'),
            'Categories' => route('admin.archive.categories'),
        ]
    ])

    @include('admin.layouts.partials.alert')

    <!-- Category Form -->
    <div class="row" id="form" style="display: none;">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bx bx-file me-2"></i>
                        Category Form
                    </div>
                </div>
                <div class="card-body">
                    <form id="defaultForm" method="post">
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input type="hidden" name="id" id="id">
                                    <input type="text" class="form-control" id="category_name" name="category_name" placeholder="Enter category name" required>
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

    <!-- Categories Table -->
    <div class="row" id="dataTable">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title">
                        <i class="bx bx-list me-2"></i>
                        Category List
                    </div>
                    <button type="button" class="btn btn-primary" id="addNew">
                        <i class="bx bx-plus me-1"></i> Add New Category
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap w-100" id="dataTableId">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category Name</th>
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
    // DataTable initialization - Try without server-side processing first
    var categoriesTable = $('#dataTableId').DataTable({
        processing: true,
        serverSide: false, // Disable server-side processing temporarily
        ajax: {
            type: 'POST',
            url: "{{ route('admin.archive.categories.data') }}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataSrc: function(json) {
                console.log('DataTable response received:', json);
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX error:', error, xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load categories data: ' + error
                });
            }
        },
        columns: [
            { data: 0, title: '#' },
            { data: 1, title: 'Category Name' },
            { data: 2, title: 'Actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip', // This controls the layout of DataTable elements
        buttons: [],   // Empty array to ensure no export buttons are shown
        language: {
            processing: "Loading categories...",
            emptyTable: "No categories found",
            zeroRecords: "No matching categories found"
        }
    });

    // Category management functions
    CategoryJquery = {
        AddCategory: function() {
            $.ajax({
                type: 'POST',
                data: $('#defaultForm').serializeArray(),
                url: '{{ route("admin.archive.categories.store") }}',
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
                        categoriesTable.ajax.reload(null, false);
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
                            text: 'Failed to add category'
                        });
                    }
                }
            });
        },

        DeleteCategory: function(id) {
            $.ajax({
                type: 'POST',
                data: {'id': id},
                url: '{{ route("admin.archive.categories.delete", ":id") }}'.replace(':id', id),
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
                        categoriesTable.ajax.reload(null, false);
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
                            text: 'Failed to delete category'
                        });
                    }
                }
            });
        },

        EditCategory: function(id) {
            $.ajax({
                type: 'POST',
                data: {'id': id},
                url: '{{ route("admin.archive.categories.edit", ":id") }}'.replace(':id', id),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 10000,
                success: function(response) {
                    $("#dataTable,#form").toggle();
                    $.each(response, function(key, value) {
                        $("#" + key).val(value);
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
                            text: 'Failed to load category data'
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
        $('input').css("border-color", "");
    });

    $("#submit_Bnews").off().click(function() {
        if ($("#category_name").val() == "") {
            $("#category_name").css("border-color", "red");
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'Category name is required.'
            });
        } else {
            $('input').css("border-color", "");
            CategoryJquery.AddCategory();
        }
    });

    // Delete category with SweetAlert2
    $(document).on('click', '.btn-delete', function() {
        var data_id = $(this).attr('data-id');
        var category_name = $(this).closest('tr').find('td:eq(1)').text();
        
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete "${category_name}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                CategoryJquery.DeleteCategory(data_id);
            }
        });
    });

    // Edit category
    $(document).on('click', '.btn-edit', function() {
        CategoryJquery.EditCategory($(this).attr('data-id'));
    });
});
</script>
@endpush

