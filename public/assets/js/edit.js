/**
 * Edit JavaScript
 * Handles all edit page functionality
 */

$(document).ready(function() {
    // Page number change handler
    $("#edition_pageno").on('change', function () {
        var pno = $(this).val();
        var str_val = '';
        
        if (pno) {
            str_val = 'Page '+pno;
        }
        $("#title").val(str_val);
    });

    // Generate thumb handler
    $("#generate_thumb").on('click', function (e) {
        var id = $("#id").val();
        $.ajax({
            type: 'post',
            url: window.generateThumbUrl || '/admin/archive/generate-thumb',
            data:{ 'id': id },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            async:false,
            beforeSend: function(){
                $("#generate_thumb").prop('disabled', true);
            },
            success: function (response) {
                console.log(response);
                if(response.success){
                    Swal.fire({
                        title: 'Success!',
                        text: 'Thumbnail generated successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    });
                }else{
                    Swal.fire({
                        title: 'Warning!',
                        text: response.message || 'Something went wrong.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                }
                $("#generate_thumb").prop('disabled', false);
            },
            error: function (response) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Something went wrong while generating thumbnail.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
                $("#generate_thumb").prop('disabled', false);
            }
        });
    });

    // Form validation
    $('#edit-form').on('submit', function(e) {
        var isValid = true;
        
        // Check required fields
        if ($('#category').val() === '') {
            $('#category').addClass('is-invalid');
            isValid = false;
        } else {
            $('#category').removeClass('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                title: 'Validation Error!',
                text: 'Please fill in all required fields.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
        }
    });
    
    // Remove validation classes on change
    $('.form-control, .form-select').on('change', function() {
        $(this).removeClass('is-invalid');
    });
});
