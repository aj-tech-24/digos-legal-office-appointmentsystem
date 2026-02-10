<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Record - {{ $clientRecord->full_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: white; font-size: 11pt; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .section-title { font-weight: bold; font-size: 1.1em; border-bottom: 1px solid #ddd; margin-bottom: 10px; margin-top: 20px; padding-bottom: 5px; }
        .label { font-weight: bold; color: #555; width: 150px; display: inline-block; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .badge { border: 1px solid #000; color: #000 !important; background: none !important; padding: 2px 5px; }
            a { text-decoration: none; color: black; }
        }
    </style>
</head>
<body class="p-4">
    <div class="container-fluid">
        <!-- Controls -->
        <div class="no-print mb-4 d-flex justify-content-between align-items-center bg-light p-3 rounded">
            <div>
                <strong>Client Record Print View</strong>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print</button>
                <button onclick="window.close()" class="btn btn-outline-secondary">Close</button>
            </div>
        </div>

        <!-- Header -->
        <div class="header d-flex justify-content-between align-items-end">
            <div>
                <h2 class="mb-0">{{ \App\Models\Setting::get('office_name', 'Digos City Legal Office') }}</h2>
                <div class="text-muted">Client Case File</div>
            </div>
            <div class="text-end">
                <div class="display-6 fw-bold">#{{ $clientRecord->case_number ?? 'N/A' }}</div>
                <div class="small text-muted">Generated: {{ now()->format('M j, Y g:i A') }}</div>
            </div>
        </div>

        <!-- Client Info -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-2"><span class="label">Full Name:</span> {{ $clientRecord->full_name }}</div>
                <div class="mb-2"><span class="label">Status:</span> {{ ucfirst($clientRecord->status) }}</div>
                <div class="mb-2"><span class="label">Date of Birth:</span> {{ $clientRecord->date_of_birth ? $clientRecord->date_of_birth->format('M d, Y') : 'N/A' }}</div>
                <div class="mb-2"><span class="label">Gender:</span> {{ ucfirst($clientRecord->gender ?? 'N/A') }}</div>
            </div>
            <div class="col-md-6">
                <div class="mb-2"><span class="label">Email:</span> {{ $clientRecord->email ?? 'N/A' }}</div>
                <div class="mb-2"><span class="label">Phone:</span> {{ $clientRecord->phone ?? 'N/A' }}</div>
                <div class="mb-2"><span class="label">Address:</span> {{ $clientRecord->address ?? 'N/A' }}</div>
                <div class="mb-2"><span class="label">Registered:</span> {{ $clientRecord->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        @if($clientRecord->notes)
        <div class="mt-3 p-3 bg-light border rounded">
            <strong>Notes:</strong><br>
            {{ $clientRecord->notes }}
        </div>
        @endif

        <!-- Appointments -->
        <div class="section-title">Appointments History</div>
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Lawyer</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientRecord->appointments as $apt)
                <tr>
                    <td>{{ $apt->start_datetime->format('M d, Y g:i A') }}</td>
                    <td>{{ $apt->reference_number }}</td>
                    <td>{{ $apt->lawyer->user->name ?? 'Unassigned' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $apt->status)) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No appointments found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- Timeline / Case Notes -->
        <div class="section-title">Case Timeline & Notes</div>
        @forelse($clientRecord->entries as $entry)
        <div class="mb-3 border-bottom pb-3" style="page-break-inside: avoid;">
            <div class="d-flex justify-content-between">
                <strong>{{ $entry->title }}</strong>
                <small class="text-muted">{{ $entry->created_at->format('M d, Y g:i A') }}</small>
            </div>
            <div class="mt-1">{{ $entry->content }}</div>
            <div class="small text-muted mt-1">
                By: {{ $entry->creator->name ?? 'System' }}
                @if($entry->appointment)
                 | Ref: {{ $entry->appointment->reference_number }}
                @endif
            </div>
        </div>
        @empty
        <div class="text-muted fst-italic">No timeline entries recorded.</div>
        @endforelse

        <!-- Footer -->
        <div class="mt-5 pt-3 border-top text-center text-muted small">
            <p>Confidential Document &bull; {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
