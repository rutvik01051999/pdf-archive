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

    // XSS Protection functions
    function sanitizeInput(input) {
        if (!input) return '';
        
        // Remove HTML tags
        input = input.replace(/<[^>]*>/g, '');
        
        // Remove script tags and content
        input = input.replace(/<script[^>]*>.*?<\/script>/gi, '');
        input = input.replace(/<script[^>]*>/gi, '');
        
        // Remove event handlers
        input = input.replace(/on\w+\s*=\s*["'][^"']*["']/gi, '');
        
        // Remove javascript: URLs
        input = input.replace(/javascript:/gi, '');
        
        // Remove suspicious characters
        input = input.replace(/[<>"'&\\/;|`$]/g, '');
        
        return input.trim();
    }

    function validateCategoryName(name) {
        if (!name || name.length < 2) {
            return { valid: false, message: 'Category name must be at least 2 characters long.' };
        }
        
        if (name.length > 100) {
            return { valid: false, message: 'Category name must not exceed 100 characters.' };
        }
        
        // Check for allowed characters only
        if (!/^[a-zA-Z0-9\s\-_.,()]+$/.test(name)) {
            return { valid: false, message: 'Category name contains invalid characters. Only letters, numbers, spaces, hyphens, underscores, dots, commas, and parentheses are allowed.' };
        }
        
        // Check for XSS patterns
        const xssPatterns = [
            /<script[^>]*>/i,
            /javascript:/i,
            /on\w+\s*=/i,
            /<iframe[^>]*>/i,
            /<object[^>]*>/i,
            /<embed[^>]*>/i,
            /<form[^>]*>/i,
            /<input[^>]*>/i,
            /<meta[^>]*>/i,
            /<link[^>]*>/i,
            /<style[^>]*>/i,
            /expression\s*\(/i,
            /url\s*\(/i,
            /vbscript:/i,
            /data:/i
        ];
        
        for (const pattern of xssPatterns) {
            if (pattern.test(name)) {
                return { valid: false, message: 'Category name contains potentially harmful content.' };
            }
        }
        
        // Check for SQL injection patterns
        const sqlPatterns = [
            /union\s+select/i,
            /drop\s+table/i,
            /delete\s+from/i,
            /insert\s+into/i,
            /update\s+set/i,
            /select\s+.*\s+from/i,
            /or\s+1\s*=\s*1/i,
            /and\s+1\s*=\s*1/i,
            /'\s*or\s*'/i,
            /'\s*and\s*'/i
        ];
        
        for (const pattern of sqlPatterns) {
            if (pattern.test(name)) {
                return { valid: false, message: 'Category name contains potentially harmful content.' };
            }
        }
        
        return { valid: true };
    }

    function showValidationError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Validation Error',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Validation Error: ' + message);
        }
    }

    // Category management functions
    CategoryJquery = {
        AddCategory: function() {
            // Get and validate category name
            const categoryName = $('#category_name').val().trim();
            
            // Sanitize input
            const sanitizedName = sanitizeInput(categoryName);
            $('#category_name').val(sanitizedName);
            
            // Validate category name
            const validation = validateCategoryName(sanitizedName);
            if (!validation.valid) {
                showValidationError(validation.message);
                return;
            }
            
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

    // Real-time input validation and sanitization
    $('#category_name').on('input', function() {
        const input = $(this);
        const value = input.val();
        const sanitized = sanitizeInput(value);
        
        // Update the input with sanitized value if it changed
        if (sanitized !== value) {
            input.val(sanitized);
        }
        
        // Real-time validation feedback
        if (sanitized.length > 0) {
            const validation = validateCategoryName(sanitized);
            if (!validation.valid) {
                input.addClass('is-invalid');
                input.removeClass('is-valid');
                
                // Show validation message
                let feedback = input.siblings('.invalid-feedback');
                if (feedback.length === 0) {
                    feedback = $('<div class="invalid-feedback"></div>');
                    input.after(feedback);
                }
                feedback.text(validation.message);
            } else {
                input.addClass('is-valid');
                input.removeClass('is-invalid');
            }
        } else {
            input.removeClass('is-valid is-invalid');
        }
    });

    // Event handlers
    $('#addNew').on('click', function() {
        $('#form').show();
        $('#id').val("");
        $('#dataTable').hide();
        $('input').css("border-color", "");
        
        // Clear validation states
        $('#category_name').removeClass('is-valid is-invalid');
        $('.invalid-feedback').remove();
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
