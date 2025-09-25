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
                    alert('Thumbnail generated successfully.');
                }else{
                    alert(response.message || 'Something went wrong.');
                }
                $("#generate_thumb").prop('disabled', false);
            },
            error: function (response) {
                alert('Something went wrong.');
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
            alert('Please fill in all required fields.');
        }
    });
    
    // Remove validation classes on change
    $('.form-control, .form-select').on('change', function() {
        $(this).removeClass('is-invalid');
    });
});
