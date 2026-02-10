<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Summary - {{ now()->format('Y-m-d') }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white;
            font-size: 12pt;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .meta-info {
            font-size: 0.9em;
            color: #666;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            .table th {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
            }
            .badge {
                border: 1px solid #000;
                color: #000 !important;
                background: none !important;
                padding: 2px 5px;
            }
        }
    </style>
</head>
<body class="p-4">
    <div class="container-fluid">
        <!-- Control Bar -->
        <div class="no-print mb-4 d-flex justify-content-between align-items-center bg-light p-3 rounded">
            <div>
                <strong>Print Preview</strong>
                <span class="text-muted ms-2">Use your browser's print function to save as PDF.</span>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print / Save PDF
                </button>
                <button onclick="window.close()" class="btn btn-outline-secondary">Close</button>
            </div>
        </div>

        <!-- Report Header -->
        <div class="header d-flex justify-content-between align-items-end">
            <div>
                <h2 class="mb-0">{{ \App\Models\Setting::get('office_name', 'Digos City Legal Office') }}</h2>
                <div class="text-muted">Client Records Summary Report</div>
            </div>
            <div class="text-end">
                <div class="fw-bold">Generated: {{ now()->format('F j, Y g:i A') }}</div>
                <div class="meta-info">Total Clients: {{ $clients->count() }}</div>
            </div>
        </div>

        <!-- Filter Summary -->
        @if(request()->anyFilled(['status', 'search']))
        <div class="mb-4 p-3 bg-light border rounded">
            <h6 class="mb-2 fw-bold">Filters Applied:</h6>
            <ul class="list-inline mb-0">
                @if(request()->filled('status'))
                    <li class="list-inline-item me-3"><strong>Status:</strong> {{ ucfirst(request('status')) }}</li>
                @endif
                @if(request()->filled('search'))
                    <li class="list-inline-item"><strong>Search:</strong> "{{ request('search') }}"</li>
                @endif
            </ul>
        </div>
        @endif

        <!-- Table -->
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th style="width: 50px">#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Case Number</th>
                    <th>Appointments</th>
                    <th>Status</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $index => $client)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $client->full_name }}</strong>
                        @if($client->address)
                            <br><small class="text-muted">{{ Str::limit($client->address, 40) }}</small>
                        @endif
                    </td>
                    <td>{{ $client->email ?? 'N/A' }}</td>
                    <td>{{ $client->phone ?? 'N/A' }}</td>
                    <td>{{ $client->case_number ?? '—' }}</td>
                    <td class="text-center">{{ $client->appointments_count }}</td>
                    <td>
                        <span class="badge">{{ ucfirst($client->status) }}</span>
                    </td>
                    <td>{{ $client->created_at->format('M j, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">No client records found matching the criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer -->
        <div class="mt-5 pt-3 border-top text-center text-muted small">
            <p>End of Report &bull; {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
