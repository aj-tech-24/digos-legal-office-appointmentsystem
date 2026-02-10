<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Lawyer;
use App\Models\Specialization;
use Illuminate\Http\Request;
use App\Mail\AppointmentApproved;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['clientRecord', 'lawyer.user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by lawyer
        if ($request->filled('lawyer_id')) {
            $query->where('lawyer_id', $request->lawyer_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('start_datetime', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_datetime', '<=', $request->date_to);
        }

        // Filter by complexity
        if ($request->filled('complexity')) {
            $query->where('complexity_level', $request->complexity);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('clientRecord', function ($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $appointments = $query->orderBy('start_datetime', 'desc')->paginate(15);
        $lawyers = Lawyer::with('user')->approved()->get();
        $statuses = array_keys(Appointment::STATUS_LABELS);

        return view('admin.appointments.index', compact('appointments', 'lawyers', 'statuses'));
    }

    /**
     * Display a printable summary of appointments
     */
    public function summary(Request $request)
    {
        $query = Appointment::with(['clientRecord', 'lawyer.user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by lawyer
        if ($request->filled('lawyer_id')) {
            $query->where('lawyer_id', $request->lawyer_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('start_datetime', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_datetime', '<=', $request->date_to);
        }

        // Filter by complexity
        if ($request->filled('complexity')) {
            $query->where('complexity_level', $request->complexity);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('clientRecord', function ($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $appointments = $query->orderBy('start_datetime', 'asc')->get();
        
        return view('admin.appointments.summary', compact('appointments'));
    }

    /**
     * Display the specified appointment
     */
    public function show(Appointment $appointment)
    {
        $appointment->load([
            'clientRecord.entries' => function ($q) use ($appointment) {
                $q->where('appointment_id', $appointment->id)->with('creator')->orderBy('created_at', 'desc');
            },
            'lawyer.user',
            'lawyer.specializations',
            'aiRecommendation.items.lawyer.user',
        ]);

        return view('admin.appointments.show', compact('appointment'));
    }

    /**
     * Add a note to an appointment
     */
    public function addNote(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'case_note',
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Note added successfully.');
    }

    /**
     * Confirm an appointment
     */
    public function confirm(Appointment $appointment)
    {
        if ($appointment->status !== 'pending') {
            return back()->with('error', 'Only pending appointments can be confirmed.');
        }

        $appointment->confirm(auth()->id());

        // Create timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'appointment_confirmed',
            'title' => 'Appointment Confirmed',
            'content' => 'Appointment has been confirmed by staff.',
        ]);

        // Send confirmation email to client
        if ($appointment->clientRecord->email) {
            Mail::to($appointment->clientRecord->email)
                ->send(new AppointmentApproved($appointment));
        }

        return back()->with('success', 'Appointment confirmed successfully. A confirmation email has been sent to the client.');
    }

    /**
     * Cancel an appointment
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $appointment->cancel($request->cancellation_reason);

        // Create timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'appointment_cancelled',
            'title' => 'Appointment Cancelled',
            'content' => 'Reason: ' . $request->cancellation_reason,
        ]);

        return back()->with('success', 'Appointment cancelled.');
    }

    /**
     * Mark appointment as no-show
     */
    public function noShow(Appointment $appointment)
    {
        $appointment->update(['status' => 'no_show']);

        // Create timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'status_change',
            'title' => 'Marked as No-Show',
            'content' => 'Client did not appear for the scheduled appointment.',
        ]);

        return back()->with('success', 'Appointment marked as no-show.');
    }

    /**
     * Start an appointment
     */
    public function start(Appointment $appointment)
    {
        $appointment->start();

        return back()->with('success', 'Appointment started.');
    }

    /**
     * Complete an appointment
     */
    public function complete(Appointment $appointment)
    {
        $appointment->complete();

        // Create timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'appointment_completed',
            'title' => 'Appointment Completed',
            'content' => 'Consultation has been completed.',
        ]);

        return back()->with('success', 'Appointment completed.');
    }

    /**
     * Today's queue view
     */
    public function queue()
    {
        $appointments = Appointment::with(['clientRecord', 'lawyer.user'])
            ->whereDate('start_datetime', today())
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->orderBy('start_datetime')
            ->get();

        // Upcoming appointments (future dates, pending or confirmed)
        $upcomingAppointments = Appointment::with(['clientRecord', 'lawyer.user'])
            ->whereDate('start_datetime', '>', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_datetime')
            ->get();

        return view('admin.appointments.queue', compact('appointments', 'upcomingAppointments'));
    }

    /**
     * Check in a client
     */
    public function checkIn(Appointment $appointment)
    {
        // Get next queue number for today
        $lastQueue = Appointment::whereDate('start_datetime', today())
            ->whereNotNull('queue_number')
            ->max('queue_number') ?? 0;

        $appointment->checkIn($lastQueue + 1);

        return back()->with('success', 'Client checked in. Queue number: ' . ($lastQueue + 1));
    }
}
