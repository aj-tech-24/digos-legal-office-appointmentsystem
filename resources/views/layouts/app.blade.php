<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Digos City Legal Office - Appointment System')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #012357;
            --secondary-color: #159895;
            --accent-color: #57c5b6;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .navbar-brand {
            font-weight: 700;
        }
        
        .bg-primary-custom {
            background-color: var(--primary-color) !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step-indicator .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 1;
            max-width: 120px;
        }
        
        .step-indicator .step::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 2px;
            background-color: #dee2e6;
            z-index: 0;
        }
        
        .step-indicator .step:last-child::before {
            display: none;
        }
        
        .step-indicator .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .step-indicator .step.active .step-number {
            background-color: var(--primary-color);
            color: white;
        }
        
        .step-indicator .step.completed .step-number {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .step-indicator .step-label {
            font-size: 11px;
            color: #6c757d;
            margin-top: 0.5rem;
            text-align: center;
        }
        
        .step-indicator .step.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .lawyer-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent !important;
        }
        
        .lawyer-card:hover {
            border-color: var(--accent-color) !important;
            transform: translateY(-2px);
        }
        
        .lawyer-card.selected {
            border-color: var(--primary-color) !important;
            background-color: rgba(26, 95, 122, 0.05);
        }
        
        .match-score {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .score-breakdown {
            font-size: 0.85rem;
        }
        
        .score-bar {
            height: 6px;
            border-radius: 3px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        
        .score-bar-fill {
            height: 100%;
            background-color: var(--secondary-color);
            transition: width 0.3s ease;
        }
        
        .time-slot {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .time-slot:hover {
            background-color: var(--accent-color);
            color: white;
        }
        
        .time-slot.selected {
            background-color: var(--primary-color);
            color: white;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .checklist-item {
            padding: 0.75rem;
            border-radius: 0.375rem;
            background-color: #f8f9fa;
            margin-bottom: 0.5rem;
        }
        
        .checklist-item.required {
            border-left: 3px solid var(--primary-color);
        }
        
        .checklist-item.optional {
            border-left: 3px solid #6c757d;
        }
        
        /* Custom Text Colors with High Specificity */
        a.nav-link.text-gov-yellow, 
        .text-gov-yellow {
            color: #f0ad4e !important; /* gov-yellow */
            transition: all 0.3s ease;
        }
        
        a.nav-link.text-gov-yellow:hover,
        .text-gov-yellow:hover {
            color: #facc15 !important; /* yellow-400 */
            transform: scale(1.05);
            display: inline-block;
        }

        @media (max-width: 768px) {
            .step-indicator .step-label {
                display: none;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-building me-2"></i>
                Digos City Legal Office
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-gov-yellow" href="/login">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-building me-2"></i>Digos City Legal Office</h5>
                    <p class="text-muted mb-0">Providing accessible legal services to the citizens of Digos City</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-1">
                        <i class="bi bi-geo-alt me-1"></i> City Hall, Digos City, Davao del Sur
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-telephone me-1"></i> (082) XXX-XXXX
                    </p>
                </div>
            </div>
            <hr class="my-3">
            <div class="text-center text-muted">
                <small>&copy; {{ date('Y') }} Digos City Government. Compliant with RA 10173 (Data Privacy Act)</small>
            </div>
        </div>
    </footer>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted" id="loading-message">Processing...</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global helper functions
        function showLoading(message = 'Processing...') {
            document.getElementById('loading-message').textContent = message;
            document.getElementById('loading-overlay').style.display = 'flex';
        }
        
        function hideLoading() {
            document.getElementById('loading-overlay').style.display = 'none';
        }
        
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').content;
        }
        
        function showErrors(errors) {
            // Clear previous errors
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            
            // Show new errors
            Object.keys(errors).forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = errors[field][0];
                    input.parentNode.appendChild(feedback);
                }
            });
        }
        
        function showAlert(message, type = 'danger') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.getElementById('step-container');
            if (container) {
                container.insertBefore(alertDiv, container.firstChild);
            }
        }

        // // Anti-tamper: Restore DOM if edited (global)
        // window.addEventListener('DOMContentLoaded', function() {
        //     const originalBody = document.body.innerHTML;
        //     const observer = new MutationObserver(function(mutations) {
        //         mutations.forEach(function(mutation) {
        //             if (document.body.innerHTML !== originalBody) {
        //                 document.body.innerHTML = originalBody;
        //             }
        //         });
        //     });
        //     observer.observe(document.body, { childList: true, subtree: true, characterData: true });
        // });
        // // Disable right-click globally
        // document.addEventListener('contextmenu', function(e) {
        //     e.preventDefault();
        // });
        // // Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U globally
        // document.addEventListener('keydown', function(e) {
        //     if (
        //         e.key === 'F12' ||
        //         (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J')) ||
        //         (e.ctrlKey && e.key === 'U')
        //     ) {
        //         e.preventDefault();
        //     }
        // });
    </script>
    
    @stack('scripts')
</body>
</html>
