@extends('admin.layouts.app')

@section('content')
@include('admin.layouts.partials.page-header', [
'title' => 'Edit Archive',
'breadcrumb' => [
'Home' => route('admin.dashboard.index'),
'Archive' => route('admin.archive.display'),
'Edit' => '#',
]
])

@include('admin.layouts.partials.alert')

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bx bx-edit me-2"></i>
                    Edit Archive
                </div>
                <div class="card-tools">
                    <a href="{{ route('admin.archive.display') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>
                        Back to Archives
                    </a>
                </div>
            </div>
                
            <div class="card-body">
                <form id="edit-form" action="{{ route('admin.archive.update', $archive->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Left side - PDF Thumbnail -->
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label"><strong>PDF Thumb:</strong></label>
                                <div class="border p-2 text-center" style="min-height: 250px; display: flex; align-items: center; justify-content: center;">
                                    @php
                                        $thumbnailPath = (new App\Http\Controllers\ArchiveAdminController())->generateThumbnailPath($archive);
                                    @endphp
                                    <img src="{{ $thumbnailPath }}" class="img-fluid" alt="PDF Thumbnail" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMjUgMTEyLjVIMTg3LjVWNzVIMjI1VjExMi41WiIgZmlsbD0iI0Q5RDlEOSIvPgo8cGF0aCBkPSJNMjI1IDExMi41SDE4Ny41Vjc1IiBzdHJva2U9IiNDQ0NDQ0MiIHN0cm9rZS13aWR0aD0iMyIvPgo8dGV4dCB4PSIxNTAiIHk9IjE5NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIxIj5GaWxlPC90ZXh0Pgo8dGV4dCB4PSIxNTAiIHk9IjIyNSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE4Ij5Ob3QgYXZhaWxhYmxlPC90ZXh0Pgo8L3N2Zz4K';" style="max-height: 250px; object-fit: contain;">
                                </div>
                                <button type="button" id="generate_thumb" class="btn btn-sm btn-outline-primary" style="margin-top: 10px; width: 100%;">
                                    <i class="bx bx-image me-1"></i>
                                    Generate Thumb
                                </button>
                            </div>
                        </div>
                        
                        <!-- Right side - Form Fields -->
                        <div class="col-md-8 text-center">
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Center:</strong></td>
                                    <td>
                                        <input type="hidden" id="id" name="Id" value="{{ $archive->id }}">
                                        <select class="form-control" name="published_center" id="center" required>
                                            <option value="">--Select Center--</option>
                                            @foreach($centers as $center)
                                                <option value="{{ $center->centercode }}" {{ old('published_center', $archive->published_center) == $center->centercode ? 'selected' : '' }}>
                                                    {{ $center->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>
                                        <select class="form-control" name="category" id="category" required>
                                            <option value="">--Select Category--</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->category }}" {{ old('category', $archive->category) == $cat->category ? 'selected' : '' }}>
                                                    {{ $cat->category }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Edition Name:</strong></td>
                                    <td>
                                        @if($archive->category != 'Matrix Auto')
                                            <input class="form-control" value="{{ old('edition_name', $archive->edition_name) }}" name="edition_name" id="txt_ename" type="text" style="font-size: 14px;">
                                        @else
                                            <select class="form-control" name="edition_name" id="ename">
                                                <option value="">--Select Edition--</option>
                                                @foreach($editions as $edition)
                                                    <option value="{{ $edition->EDITIONCODE }}~{{ $edition->DESCRIPTION }}" 
                                                            {{ old('edition_name', $archive->edition_code.'~'.$archive->edition_name) == $edition->EDITIONCODE.'~'.$edition->DESCRIPTION ? 'selected' : '' }}>
                                                        {{ $edition->DESCRIPTION }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Edition Page No:</strong></td>
                                    <td>
                                        <select class="form-control" id="pno" name="edition_pageno">
                                            @for($i = 1; $i <= 100; $i++)
                                                <option value="{{ $i }}" {{ old('edition_pageno', $archive->edition_pageno) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Event:</strong></td>
                                    <td>
                                        <input class="form-control" value="{{ old('title', $archive->title) }}" required type="text" name="title" id="title">
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Event Description:</strong></td>
                                    <td>
                                        <textarea class="form-control" name="event" rows="5" id="event" cols="70">{{ old('event', $archive->event) }}</textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Published Date:</strong></td>
                                    <td>
                                        <input class="form-control" value="{{ old('published_date', \Carbon\Carbon::parse($archive->published_date)->format('m/d/Y')) }}" required type="text" name="published_date" id="pdate">
                                    </td>
                                </tr>
                            </table>
                            <input type="Submit" name="Submit" value="Update" class="submitbutton btn btn-warning text-center">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Flatpickr CSS (already included in layout) -->
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Edit JavaScript -->
<script src="{{ asset('assets/js/edit.js') }}"></script>
<script>
// Set up URLs for the edit functionality
window.generateThumbUrl = '{{ route("admin.archive.generate-thumb") }}';
window.getEditionsUrl = '{{ route("admin.archive.get-editions") }}';
</script>
<script>
// Additional edit page functionality
$(document).ready(function() {
    // Date picker using native Flatpickr API
    flatpickr("#pdate", {
        dateFormat: "m/d/Y",
        allowInput: true,
        clickOpens: true,
        clearBtn: true
    });

    // Center change handler
    $("#center").change(function(){
        var center_id = $(this).val();
        $.ajax({
            type:'POST',
            data:{ 'center_id': center_id },
            url: window.getEditionsUrl || '/admin/archive/get-editions',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            async:false,
            success:function(response){
                $('#ename').empty();
                $('#ename').append('<option value="">--Select Edition--</option>');
                if(response.editions && response.editions.length > 0){
                    for (var i = 0; i < response.editions.length; i++) {
                        $('#ename').append('<option value="'+response.editions[i]['EDITIONCODE']+'~'+response.editions[i]['DESCRIPTION']+'">'+response.editions[i]['DESCRIPTION']+'</option>');
                    }
                }
            }
        });
    });

    // Edition name change handler
    $('#ename').on('change', function (e) {
        var str_val = '';
        var val_arr = this.value.split("~");
        str_val = val_arr[1];
        var str_pno = $("#pno").val();
        str_val += ' Page ' + str_pno;
        $("#title").val(str_val);
    });
    
    // Page number change handler
    $("#pno").on('change', function (e) {
        var val = $('#category').val();
        if (val == 'Matrix Auto') {
            var str_ename = $("#ename").val();
            var str_val = '';
            var pno = this.value;
            if(str_ename != ''){
                var val_arr = str_ename.split("~");
                str_val = val_arr[1]+' Page '+pno;
            }else{
                str_val = 'Page '+pno;
            }
            $("#title").val(str_val);
        }
    });
});
</script>
@endpush