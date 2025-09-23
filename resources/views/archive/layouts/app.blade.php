<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDF Archive Bot - @yield('title', 'Archive System')</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .archive-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .archive-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .archive-card:hover {
            transform: translateY(-2px);
        }
        .btn-archive {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            color: white;
            transition: all 0.3s;
        }
        .btn-archive:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: white;
        }
        .navbar-archive {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar-archive {
            background: #2c3e50;
            min-height: 100vh;
            padding: 20px 0;
        }
        .sidebar-archive .nav-link {
            color: #ecf0f1;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 2px 10px;
            transition: all 0.3s;
        }
        .sidebar-archive .nav-link:hover,
        .sidebar-archive .nav-link.active {
            background: #34495e;
            color: #3498db;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Archive Header -->
    <div class="archive-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2><i class="fas fa-archive"></i> PDF Archive Bot</h2>
                </div>
                <div class="col-md-6 text-end">
                    @if(session('archive_authenticated'))
                        <span class="me-3">
                            Welcome, {{ session('archive_full_name') ?: session('archive_username') }} 
                            ({{ session('archive_center') }})
                        </span>
                        <a href="{{ route('archive.profile') }}" class="btn btn-light btn-sm me-2">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="{{ route('archive.logout') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            @if(session('archive_authenticated') && !request()->routeIs('archive.login'))
            <!-- Sidebar -->
            <nav class="col-md-2 sidebar-archive">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('archive.index') ? 'active' : '' }}" 
                           href="{{ route('archive.index') }}">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('archive.display') ? 'active' : '' }}" 
                           href="{{ route('archive.display') }}">
                            <i class="fas fa-list"></i> View Archives
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('archive.upload') ? 'active' : '' }}" 
                           href="{{ route('archive.index') }}">
                            <i class="fas fa-upload"></i> Upload PDF
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('archive.search') ? 'active' : '' }}" 
                           href="{{ route('archive.display') }}">
                            <i class="fas fa-search"></i> Search
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('archive.statistics') ? 'active' : '' }}" 
                           href="{{ route('archive.display') }}">
                            <i class="fas fa-chart-bar"></i> Statistics
                        </a>
                    </li>
                </ul>
            </nav>
            @endif

            <!-- Main Content -->
            <main class="{{ session('archive_authenticated') && !request()->routeIs('archive.login') ? 'col-md-10' : 'col-12' }}">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script>
        // CSRF token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

