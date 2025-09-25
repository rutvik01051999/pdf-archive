/**
 * Special Dates JavaScript
 * Handles all special date management functionality
 */

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
            url: window.specialDatesDataUrl || '/admin/archive/special-dates/data',
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
        buttons: [],
        language: {
            processing: "Loading special dates...",
            emptyTable: "No special dates found",
            zeroRecords: "No matching special dates found"
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

    function validateSpecialDateDescription(description) {
        if (!description || description.length < 3) {
            return { valid: false, message: 'Description must be at least 3 characters long.' };
        }
        
        if (description.length > 200) {
            return { valid: false, message: 'Description must not exceed 200 characters.' };
        }
        
        // Check for allowed characters only
        if (!/^[a-zA-Z0-9\s\-_.,()!?@#$%&*]+$/.test(description)) {
            return { valid: false, message: 'Description contains invalid characters. Only letters, numbers, spaces, and common punctuation are allowed.' };
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
            if (pattern.test(description)) {
                return { valid: false, message: 'Description contains potentially harmful content.' };
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
            if (pattern.test(description)) {
                return { valid: false, message: 'Description contains potentially harmful content.' };
            }
        }
        
        return { valid: true };
    }

    function validateSpecialDate(dateString) {
        if (!dateString || !dateString.match(/^\d{2}-\d{2}$/)) {
            return { valid: false, message: 'Please select both day and month.' };
        }
        
        const parts = dateString.split('-');
        const day = parseInt(parts[0]);
        const month = parseInt(parts[1]);
        
        if (day < 1 || day > 31) {
            return { valid: false, message: 'Invalid day. Day must be between 1 and 31.' };
        }
        
        if (month < 1 || month > 12) {
            return { valid: false, message: 'Invalid month. Month must be between 1 and 12.' };
        }
        
        // Check for invalid date combinations
        if (month === 2 && day > 29) {
            return { valid: false, message: 'Invalid date. February has maximum 29 days.' };
        }
        
        if ([4, 6, 9, 11].includes(month) && day > 30) {
            return { valid: false, message: 'Invalid date. This month has maximum 30 days.' };
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

    // Special Date management functions
    SpecialDateJquery = {
        AddSpecialDate: function() {
            // Get and validate form data
            const description = $('#description').val().trim();
            const specialDate = $('#special_date').val().trim();
            
            // Sanitize inputs
            const sanitizedDescription = sanitizeInput(description);
            $('#description').val(sanitizedDescription);
            
            // Validate description
            const descriptionValidation = validateSpecialDateDescription(sanitizedDescription);
            if (!descriptionValidation.valid) {
                showValidationError(descriptionValidation.message);
                return;
            }
            
            // Validate special date
            const dateValidation = validateSpecialDate(specialDate);
            if (!dateValidation.valid) {
                showValidationError(dateValidation.message);
                return;
            }
            
            $.ajax({
                type: 'POST',
                data: $('#defaultForm').serializeArray(),
                url: window.specialDatesStoreUrl || '/admin/archive/special-dates/store',
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
                url: (window.specialDatesDeleteUrl || '/admin/archive/special-dates/delete/:id').replace(':id', id),
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
                url: (window.specialDatesEditUrl || '/admin/archive/special-dates/edit/:id').replace(':id', id),
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

    // Real-time input validation and sanitization
    $('#description').on('input', function() {
        const input = $(this);
        const value = input.val();
        const sanitized = sanitizeInput(value);
        
        // Update the input with sanitized value if it changed
        if (sanitized !== value) {
            input.val(sanitized);
        }
        
        // Real-time validation feedback
        if (sanitized.length > 0) {
            const validation = validateSpecialDateDescription(sanitized);
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

    // Validate special date when dropdowns change
    $('#day_select, #month_select').on('change', function() {
        updateSpecialDate();
        
        // Validate the special date
        const specialDate = $('#special_date').val();
        if (specialDate) {
            const validation = validateSpecialDate(specialDate);
            if (!validation.valid) {
                $('#day_select, #month_select').addClass('is-invalid');
                $('#day_select, #month_select').removeClass('is-valid');
                
                // Show validation message
                let feedback = $('.date-validation-feedback');
                if (feedback.length === 0) {
                    feedback = $('<div class="invalid-feedback date-validation-feedback"></div>');
                    $('#special_date').after(feedback);
                }
                feedback.text(validation.message);
            } else {
                $('#day_select, #month_select').addClass('is-valid');
                $('#day_select, #month_select').removeClass('is-invalid');
                $('.date-validation-feedback').remove();
            }
        }
    });

    // Event handlers
    $('#addNew').on('click', function() {
        $('#form').show();
        $('#id').val("");
        $('#dataTable').hide();
        $('input').css("border-color", "");
        
        // Clear validation states
        $('#description').removeClass('is-valid is-invalid');
        $('#day_select, #month_select').removeClass('is-valid is-invalid');
        $('.invalid-feedback').remove();
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
