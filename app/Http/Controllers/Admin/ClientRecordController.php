<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientRecord;
use Illuminate\Http\Request;

class ClientRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientRecord::withCount('appointments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.clients.index', compact('clients'));
    }

    public function summary(Request $request)
    {
        $query = ClientRecord::withCount('appointments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $clients = $query->orderBy('created_at', 'desc')->get();

        return view('admin.clients.summary', compact('clients'));
    }

    public function show(ClientRecord $clientRecord)
    {
        $clientRecord->load([
            'entries' => function ($q) {
                $q->with('creator', 'appointment.lawyer.user')->orderBy('created_at', 'desc');
            },
            'appointments' => function ($q) {
                $q->with('lawyer.user')->orderBy('start_datetime', 'desc');
            },
            'documents',
        ]);

        return view('admin.clients.show', compact('clientRecord'));
    }

    public function print(ClientRecord $clientRecord)
    {
        $clientRecord->load([
            'entries' => function ($q) {
                $q->with('creator', 'appointment.lawyer.user')->orderBy('created_at', 'desc');
            },
            'appointments' => function ($q) {
                $q->with('lawyer.user')->orderBy('start_datetime', 'desc');
            },
        ]);

        return view('admin.clients.print', compact('clientRecord'));
    }

    public function addNote(Request $request, ClientRecord $clientRecord)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'appointment_id' => 'nullable|exists:appointments,id',
        ]);

        $clientRecord->entries()->create([
            'appointment_id' => $validated['appointment_id'] ?? null,
            'created_by' => auth()->id(),
            'entry_type' => 'case_note',
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Note added successfully.');
    }

    public function updateStatus(Request $request, ClientRecord $clientRecord)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,closed,archived',
        ]);

        $oldStatus = $clientRecord->status;
        $clientRecord->update(['status' => $validated['status']]);

        $clientRecord->entries()->create([
            'created_by' => auth()->id(),
            'entry_type' => 'status_change',
            'title' => 'Status Changed',
            'content' => "Status changed from {$oldStatus} to {$validated['status']}.",
        ]);

        return back()->with('success', 'Client status updated.');
    }

    public function update(Request $request, ClientRecord $clientRecord)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'notes' => 'nullable|string',
        ]);

        $clientRecord->update($validated);

        return back()->with('success', 'Client information updated.');
    }
}
