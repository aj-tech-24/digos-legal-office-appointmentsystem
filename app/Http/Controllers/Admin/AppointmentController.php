<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClientRecord;
use App\Models\Lawyer;
use Illuminate\Http\Request;
use App\Mail\AppointmentConfirmation; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display list of ALL appointments (Admin View)
     */
    public function index(Request $request)
    {
        $query = Appointment::with([
            'clientRecord',
            'lawyer.user',
            'lawyer.specializations',
            // Load only the "appointment_completed" entry for the summary modal notes
            'clientRecord.entries' => function ($q) {
                $q->where('entry_type', 'appointment_completed')
                  ->orderBy('created_at', 'desc');
            },
        ]);

        // Filter by status
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

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('clientRecord', function ($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $appointments = $query->orderBy('start_datetime', 'desc')->paginate(15)->withQueryString();
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show', 'ongoing'];
        
        return view('admin.appointments.index', compact('appointments', 'statuses'));
    }

    /**
     * Show Queue (Today's Appointments)
     */
    public function queue()
    {
        // 1. Get Today's Appointments
        $appointments = Appointment::with(['clientRecord', 'lawyer.user'])
            ->whereDate('start_datetime', Carbon::today())
            ->orderBy('start_datetime', 'asc')
            ->get();

        // 2. Get Upcoming Appointments (Future)
        $upcomingAppointments = Appointment::with(['clientRecord', 'lawyer.user'])
            ->whereDate('start_datetime', '>', Carbon::today())
            ->orderBy('start_datetime', 'asc')
            ->take(5)
            ->get();

        return view('admin.appointments.queue', compact('appointments', 'upcomingAppointments'));
    }

    /**
     * Show Appointment Summary
     */
    public function summary()
    {
        $totalAppointments = Appointment::count();
        $pending = Appointment::where('status', 'pending')->count();
        $completed = Appointment::where('status', 'completed')->count();
        
        return view('admin.appointments.summary', compact('totalAppointments', 'pending', 'completed'));
    }

    /**
     * Show specific appointment
     */
    public function show(Appointment $appointment, Request $request)
    {
        // Load only entries that belong to THIS specific appointment
        $appointment->load([
            'lawyer.user',
            'clientRecord',
            'clientRecord.entries' => function ($q) use ($appointment) {
                $q->where('appointment_id', $appointment->id)
                  ->orderBy('created_at', 'desc');
            },
        ]);

        // Get all past/future booking dates for this client to populate the "Link to Booking" dropdown
        $clientBookingDates = [];
        if ($appointment->clientRecord) {
            $clientBookingDates = Appointment::where('client_record_id', $appointment->clientRecord->id)
                ->orderBy('start_datetime', 'desc')
                ->get(['id', 'start_datetime', 'reference_number']);
        }

        return view('admin.appointments.show', compact('appointment', 'clientBookingDates'));
    }

    /**
     * Confirm Appointment
     */
    public function confirm(Request $request, Appointment $appointment)
    {
        $request->validate([
            'instructions' => 'nullable|string',
            'requirements' => 'nullable|array'
        ]);

        $instructions = $request->input('instructions');
        $requirements = $request->input('requirements', []);

        // Combine notes for Admin internal view
        $noteParts = [];
        if (!empty($requirements)) {
            $noteParts[] = "Requirements to bring: " . implode(', ', $requirements);
        }
        if ($instructions) {
            $noteParts[] = "Additional Instructions: " . $instructions;
        }
        $finalAdminNotes = implode("\n\n", $noteParts);

        $appointment->update([
            'status' => 'confirmed',
            'document_checklist' => $requirements, // Ensure your DB has this column cast as array
            'admin_notes' => $finalAdminNotes,
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now()
        ]);

        // Log this action to history
        if ($appointment->clientRecord) {
            $appointment->clientRecord->entries()->create([
                'appointment_id' => $appointment->id,
                'created_by' => Auth::id(),
                'entry_type' => 'appointment_confirmed',
                'title' => 'Appointment Confirmed',
                'content' => 'Appointment confirmed. Instructions sent to client.',
                'linked_booking_date' => $appointment->start_datetime, 
            ]);
        }

        // Send Email if client has one
        if ($appointment->clientRecord && $appointment->clientRecord->email) {
            try {
                Mail::to($appointment->clientRecord->email)
                    ->send(new AppointmentConfirmation($appointment, $instructions, $requirements));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Email failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Appointment confirmed successfully.');
    }

    /**
     * Decline Appointment
     */
    public function decline(Request $request, Appointment $appointment)
    {
        $request->validate(['decline_reason' => 'required|string']);

        $appointment->update([
            'status' => 'cancelled',
            'admin_notes' => "Declined by Admin. Reason: " . $request->decline_reason,
            'cancelled_at' => now()
        ]);

        return back()->with('success', 'Appointment declined.');
    }

    /**
     * Cancel Appointment
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);
        return back()->with('success', 'Appointment cancelled.');
    }

    /**
     * Check-In Appointment (Client arrived)
     */
    public function checkIn(Appointment $appointment)
    {
        $appointment->update([
            'checked_in_at' => now(),
            // 'status' => 'confirmed' // Keep confirmed until started
        ]);
        return back()->with('success', 'Client checked in successfully.');
    }

    /**
     * Start Appointment
     */
    public function start(Appointment $appointment)
    {
        $appointment->update([
            'status' => 'ongoing',
            'started_at' => now()
        ]);
        return back()->with('success', 'Appointment started.');
    }

    /**
     * Complete Appointment
     */
    public function complete(Request $request, Appointment $appointment)
    {
        $request->validate([
            'resolution_notes' => 'nullable|string'
        ]);

        $appointment->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // Create a history entry with the notes
        if ($appointment->clientRecord) {
            $content = $request->resolution_notes ?? 'Admin marked this session as complete.';
            
            $appointment->clientRecord->entries()->create([
                'appointment_id' => $appointment->id,
                'created_by' => Auth::id(),
                'entry_type' => 'appointment_completed',
                'title' => 'Consultation Completed',
                'content' => $content,
                'linked_booking_date' => $appointment->start_datetime, // Automatically link to this session date
            ]);
        }

        return back()->with('success', 'Appointment marked as completed.');
    }

    /**
     * Mark as No-Show
     */
    public function noShow(Appointment $appointment)
    {
        $appointment->update(['status' => 'no_show']);
        return back()->with('success', 'Appointment marked as No-Show.');
    }

    /**
     * Add Note to Client Record via Appointment
     */
    public function addNote(Request $request, Appointment $appointment)
    {
        $request->validate([
            'content' => 'required|string',
            'linked_booking_date' => 'nullable|date', 
        ]);

        $content = $request->content;
        $linkedDate = $request->linked_booking_date; // This comes from the dropdown in View

        if ($appointment->clientRecord) {
            $appointment->clientRecord->entries()->create([
                'appointment_id' => $appointment->id,
                'created_by' => Auth::id(),
                'entry_type' => 'case_note',
                'title' => 'Note',
                'content' => $content,
                'linked_booking_date' => $linkedDate,
            ]);
        }

        return back()->with('success', 'Note added successfully.');
    }

    /**
     * API: Queue Data for Real-Time Sync
     */
    public function queueData()
    {
        $today = Carbon::today();
        $appointments = Appointment::with(['clientRecord', 'lawyer.user'])
            ->whereDate('start_datetime', $today)
            ->get();

        $stats = [
            'waiting' => $appointments->where('status', 'pending')->count(),
            'confirmed' => $appointments->where('status', 'confirmed')->count(),
            'ongoing' => $appointments->where('status', 'ongoing')->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'no_show' => $appointments->where('status', 'no_show')->count(),
        ];

        return response()->json([
            'appointments' => $appointments,
            'stats' => $stats,
        ]);
    }
}