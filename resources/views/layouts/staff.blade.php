<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Portal') - Digos City Legal Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
            --primary-color: #6f42c1; /* Purple for Staff */
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* Sidebar Styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, #5a32a3 100%);
            color: white;
            z-index: 1040; /* Corrected Z-index */
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            flex-shrink: 0;
        }

        .sidebar-nav {
            padding: 1rem 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            border-left: 3px solid white;
        }

        .sidebar-nav .nav-link i {
            width: 24px;
            margin-right: 10px;
        }

        /* Main Content & Header */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999; /* Lower than sidebar/modal */
        }

        .content-area {
            padding: 1.5rem;
            flex-grow: 1;
        }

        /* Cards */
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
        }

        .stat-card {
            border-left: 4px solid var(--primary-color);
        }
        
        /* Modal Fixes */
        .modal-backdrop {
            z-index: 1050;
        }
        .modal {
            z-index: 1055;
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="d-flex align-items-center">
                <i class="bi bi-person-badge fs-3 me-2"></i>
                <div>
                    <strong>Staff Portal</strong>
                    <br>
                    <small class="opacity-75">Digos City Legal Office</small>
                </div>
            </div>
        </div>

        <div class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}" 
                       href="{{ route('staff.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('staff.queue') ? 'active' : '' }}" 
                       href="{{ route('staff.queue') }}">
                        <i class="bi bi-people"></i> Queue Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('staff.appointments.*') ? 'active' : '' }}" 
                       href="{{ route('staff.appointments.index') }}">
                        <i class="bi bi-calendar-check"></i> Appointments
                    </a>
                </li>
            </ul>
        </div>

        <div class="mt-auto p-3 border-top border-white border-opacity-25">
            <div class="d-flex align-items-center text-white">
                <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-2" 
                     style="width: 36px; height: 36px;">
                    <i class="bi bi-person"></i>
                </div>
                <div class="flex-grow-1 text-truncate">
                    <small class="d-block">{{ auth()->user()->name }}</small>
                    <small class="opacity-75">Staff</small>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="d-inline ms-2">
                    @csrf
                    <button type="submit" class="btn btn-link text-white p-0" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <header class="top-header">
            <button class="btn btn-link d-lg-none me-3 p-0" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div class="ms-auto d-flex align-items-center">
                <span class="me-3 text-muted d-none d-md-inline">
                    <i class="bi bi-calendar3 me-1"></i>
                    {{ now()->format('l, F j, Y') }}
                </span>
                <div class="dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle text-decoration-none" 
                            type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        {{ auth()->user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });

        // FIX: Ensure Modals appear on top of everything by moving them to body
        // This is the secret to fixing the "Modal not clickable" issue
        document.addEventListener('DOMContentLoaded', function () {
            var modals = document.querySelectorAll('.modal');
            modals.forEach(function (modal) {
                document.body.appendChild(modal);
            });
        });
    </script>
    @stack('scripts')
</body>
</html>