/**
 * Categories JavaScript
 * Handles all category management functionality
 */

$(document).ready(function() {
    // DataTable initialization
    var categoriesTable = $('#dataTableId').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            type: 'POST',
            url: window.categoriesDataUrl || '/admin/archive/categories/data',
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
        dom: 'Bfrtip',
        buttons: [],
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
                url: window.categoriesStoreUrl || '/admin/archive/categories/store',
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
                url: (window.categoriesDeleteUrl || '/admin/archive/categories/delete/:id').replace(':id', id),
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
                url: (window.categoriesEditUrl || '/admin/archive/categories/edit/:id').replace(':id', id),
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
