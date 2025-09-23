@extends('archive.layouts.app')

@section('title', 'View Archive')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="archive-card card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-pdf text-primary me-2"></i>
                        {{ $archive->title }}
                    </h5>
                    <div>
                        <a href="{{ route('archive.download', $archive->id) }}" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Download
                        </a>
                        <a href="{{ route('archive.display') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <iframe src="{{ route('archive.download', $archive->id) }}" 
                                        width="100%" height="600px" style="border: 1px solid #ddd; border-radius: 5px;">
                                </iframe>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="archive-card card">
                                <div class="card-header">
                                    <h6 class="mb-0">Archive Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Title:</strong></td>
                                            <td>{{ $archive->title }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Center:</strong></td>
                                            <td>{{ $archive->center }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Category:</strong></td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $archive->category }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Edition:</strong></td>
                                            <td>{{ $archive->edition_name ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pages:</strong></td>
                                            <td>{{ $archive->page_number ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>File Size:</strong></td>
                                            <td>{{ $archive->file_size_formatted }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Upload Date:</strong></td>
                                            <td>{{ $archive->formatted_upload_date }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Uploaded By:</strong></td>
                                            <td>{{ $archive->uploaded_by ?: 'System' }}</td>
                                        </tr>
                                        @if($archive->is_matrix_edition)
                                        <tr>
                                            <td><strong>Type:</strong></td>
                                            <td><span class="badge bg-primary">Matrix Edition</span></td>
                                        </tr>
                                        @endif
                                        @if($archive->remarks)
                                        <tr>
                                            <td><strong>Remarks:</strong></td>
                                            <td>{{ $archive->remarks }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

