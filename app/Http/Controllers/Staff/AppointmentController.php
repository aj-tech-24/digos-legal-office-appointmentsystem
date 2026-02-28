<?php

namespace App\Http\Controllers\Staff;

use App\Models\Lawyer;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\AppointmentConfirmation; 
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AppointmentController extends Controller
{

    public function queue()
{
    // 1. Fetch appointments for today
    $appointments = Appointment::with(['client', 'lawyer']) // Optional: Eager load para paspas
        ->whereDate('start_datetime', Carbon::today())
        ->orderBy('start_datetime', 'asc')
        ->get();

    // 2. Return the view WITH the appointments variable
    return view('admin.appointments.queue', compact('appointments'));
}
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['clientRecord', 'lawyer.user']);

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Date
        if ($request->filled('date')) {
            $query->whereDate('start_datetime', $request->date);
        }

        // Filter: Lawyer
        if ($request->filled('lawyer_id')) {
            $query->where('lawyer_id', $request->lawyer_id);
        }

        // Search: Client Name or Reference No.
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('clientRecord', function($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $appointments = $query->orderBy('start_datetime', 'desc')
                              ->paginate(10)
                              ->withQueryString();

        $lawyers = Lawyer::with('user')->get(); 

        return view('staff.appointments.index', compact('appointments', 'lawyers'));
    }

    /**
     * Show details.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['clientRecord.entries', 'lawyer.user', 'confirmedBy']);

        $clientHistory = [];
        if ($appointment->clientRecord) {
            $clientHistory = Appointment::where('client_record_id', $appointment->client_record_id)
                ->where('id', '!=', $appointment->id)
                ->orderBy('start_datetime', 'desc')
                ->get(['id', 'start_datetime', 'reference_number']);
        }

        return view('staff.appointments.show', compact('appointment', 'clientHistory'));
    }

    // --- ACTIONS ---

    // 1. Confirm Appointment
    public function confirm(Request $request, Appointment $appointment)
    {
        $request->validate([
            'instructions' => 'nullable|string',
            'requirements' => 'nullable|array'
        ]);

        $instructions = $request->input('instructions');
        $requirements = $request->input('requirements', []);

        $noteParts = [];
        if (!empty($requirements)) {
            $noteParts[] = "Required Docs: " . implode(', ', $requirements);
        }
        if ($instructions) {
            $noteParts[] = "Instructions: " . $instructions;
        }

        $appointment->update([
            'status' => 'confirmed',
            'document_checklist' => $requirements,
            'admin_notes' => implode("\n\n", $noteParts),
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now()
        ]);

        if ($appointment->clientRecord && $appointment->clientRecord->email) {
            try {
                Mail::to($appointment->clientRecord->email)
                    ->send(new AppointmentConfirmation($appointment, $instructions, $requirements));
            } catch (\Exception $e) {
                // Log error silently
            }
        }

        return back()->with('success', 'Appointment confirmed successfully.');
    }

    // 2. Decline Appointment
    public function decline(Request $request, Appointment $appointment)
    {
        $request->validate(['decline_reason' => 'required|string']);

        $appointment->update([
            'status' => 'cancelled',
            'admin_notes' => "Declined by Staff: " . $request->decline_reason,
            'cancelled_at' => now()
        ]);

        return back()->with('success', 'Appointment declined.');
    }

    // 3. Cancel Appointment
    public function cancel(Request $request, Appointment $appointment)
    {
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);
        return back()->with('success', 'Appointment cancelled.');
    }

    // 4. Check-In 
    public function checkIn(Appointment $appointment)
    {
        $appointment->update([
            'checked_in_at' => now(),
            'status' => 'confirmed'
        ]);

        return back()->with('success', 'Client checked in.');
    }

    // 5. Start Consultation
    public function start(Appointment $appointment)
    {
        $appointment->update([
            'status' => 'ongoing',
            'started_at' => now()
        ]);
        return back()->with('success', 'Consultation started.');
    }

    // 6. Complete Consultation 
    public function complete(Appointment $appointment)
    {
        $appointment->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
        
        if ($appointment->clientRecord) {
            $appointment->clientRecord->entries()->create([
                'appointment_id' => $appointment->id,
                'created_by' => Auth::id(),
                'entry_type' => 'appointment_completed',
                'title' => 'Consultation Completed',
                'content' => 'Staff marked this session as complete.',
            ]);
        }

        return back()->with('success', 'Appointment marked as completed.');
    }

    // 7. No Show
    public function noShow(Appointment $appointment)
    {
        $appointment->update(['status' => 'no_show']);
        return back()->with('success', 'Appointment marked as No-Show.');
    }

    // 8. Add Note
    public function addNote(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'linked_date' => 'nullable|date',
        ]);

        $content = $validated['content'];
        if (!empty($validated['linked_date'])) {
            $formattedDate = Carbon::parse($validated['linked_date'])->format('F j, Y');
            $content = "[Ref: Booking Date $formattedDate]\n" . $content;
        }

        if ($appointment->clientRecord) {
            $appointment->clientRecord->entries()->create([
                'appointment_id' => $appointment->id,
                'created_by' => Auth::id(),
                'entry_type' => 'case_note',
                'title' => $validated['title'],
                'content' => $content,
            ]);
        }

        return back()->with('success', 'Note added successfully.');
    }

    /**
     * API: Queue Data for Real-Time Sync
     */
    public function queueData()
    {
        return app('App\Http\Controllers\Admin\AppointmentController')->queueData();
    }
}