@extends('admin.layouts.app')

@section('content')
<!-- Page Header -->
@include('admin.layouts.partials.page-header', [
    'title' => 'Copy Page To Another Category',
    'breadcrumb' => [
        'Home' => route('admin.dashboard.index'),
        'Archive' => route('admin.archive.display'),
        'Copy' => '#',
    ],
])

<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bx bx-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bx bx-error-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.archive.copy-to-category', $archive->id) }}">
                    @csrf
                    
                    <div class="row">
                        <!-- PDF Thumbnail -->
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="mb-3"><strong>PDF Thumbnail:</strong></h6>
                                @php
                                    $path = explode("/", $archive->filepath);
                                    if($archive->auto == '0'){
                                        $thumbName = 'epaper-pdfarchive-live-bucket/PDFArchive/thumb/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", end($path)));
                                    } else {
                                        $thumbName = 'epaper-pdfarchive-live-bucket/PDFArchive/thumb/' . $path[3] . '/' . str_replace(".PDF", ".JPG", str_replace(".pdf", ".jpg", end($path)));
                                    }
                                    $thumb = "https://storage.googleapis.com/" . $thumbName;
                                @endphp
                                <img src="{{ $thumb }}" alt="PDF Thumbnail" class="img-fluid border rounded" style="max-height: 300px; max-width: 100%;" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDMwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0yMjUgMTEyLjVIMTg3LjVWNzVIMjI1VjExMi41WiIgZmlsbD0iI0Q5RDlEOSIvPgo8cGF0aCBkPSJNMjI1IDExMi41SDE4Ny41Vjc1IiBzdHJva2U9IiNDQ0NDQ0MiIHN0cm9rZS13aWR0aD0iMyIvPgo8dGV4dCB4PSIxNTAiIHk9IjE5NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIxIj5GaWxlPC90ZXh0Pgo8dGV4dCB4PSIxNTAiIHk9IjIyNSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE4Ij5Ob3QgYXZhaWxhYmxlPC90ZXh0Pgo8L3N2Zz4K'">
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td class="text-center fw-semibold" style="width: 30%;">Center:</td>
                                            <td>
                                                <span class="text-muted">{{ $center->description ?? 'N/A' }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center fw-semibold">Current Category:</td>
                                            <td>
                                                <span class="text-muted">{{ $archive->category ?? 'N/A' }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center fw-semibold">Select Category To Copy:</td>
                                            <td>
                                                <select class="form-select @error('category') is-invalid @enderror" name="category" id="category" required>
                                                    <option value="">--Select Category--</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->category }}" {{ old('category') == $category->category ? 'selected' : '' }}>
                                                            {{ $category->category }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('category')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center fw-semibold">Event:</td>
                                            <td>
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                                       name="title" id="title" value="{{ old('title', $archive->title) }}" required>
                                                @error('title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center fw-semibold">Event Description:</td>
                                            <td>
                                                <textarea class="form-control @error('event') is-invalid @enderror" 
                                                          name="event" id="event" rows="4" required>{{ old('event', $archive->event) }}</textarea>
                                                @error('event')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center fw-semibold">Published Date:</td>
                                            <td>
                                                <input type="date" class="form-control @error('pdate') is-invalid @enderror" 
                                                       name="pdate" id="pdate" value="{{ old('pdate', $archive->published_date) }}" required>
                                                @error('pdate')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-warning btn-lg px-5">
                                    <i class="bx bx-copy me-2"></i>Copy Page
                                </button>
                                <a href="{{ route('admin.archive.display') }}" class="btn btn-secondary btn-lg px-4 ms-2">
                                    <i class="bx bx-arrow-back me-2"></i>Back to Archives
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .custom-card {
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
    }
    
    .table td {
        vertical-align: middle;
        padding: 12px;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
    }
    
    .btn-secondary {
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
    }
</style>
@endpush

@push('scripts')
<!-- Copy JavaScript -->
<script src="{{ asset('assets/js/copy.js') }}"></script>
@endpush
