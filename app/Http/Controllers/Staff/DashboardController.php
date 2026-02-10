<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\AppointmentApproved;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    /**
     * Display the staff dashboard
     */
    public function index()
    {
        $today = Carbon::today();

        // Today's appointments
        $todaysAppointments = Appointment::whereDate('start_datetime', $today)
            ->whereIn('status', ['confirmed', 'pending', 'in_progress'])
            ->with(['clientRecord', 'lawyer.user'])
            ->orderBy('start_datetime')
            ->get();

        // Upcoming appointments (today and future, pending/confirmed/in_progress)
        $upcomingAppointments = Appointment::whereDate('start_datetime', '>=', $today)
            ->whereIn('status', ['confirmed', 'pending', 'in_progress'])
            ->with(['clientRecord', 'lawyer.user'])
            ->orderBy('start_datetime')
            ->get();

        // Stats
        $stats = [
            'today_total' => $todaysAppointments->count(),
            'upcoming_total' => $upcomingAppointments->count(),
            'checked_in' => $todaysAppointments->whereNotNull('checked_in_at')->count(),
            'waiting' => $todaysAppointments->whereNotNull('checked_in_at')->where('status', 'confirmed')->count(),
            'in_progress' => $todaysAppointments->where('status', 'in_progress')->count(),
            'pending_confirmation' => Appointment::where('status', 'pending')->count(),
        ];

        return view('staff.dashboard', compact('todaysAppointments', 'upcomingAppointments', 'stats'));
    }

    /**
     * Show today's queue
     */
    public function queue()
    {
        $today = Carbon::today();

        $appointments = Appointment::whereDate('start_datetime', $today)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->whereNotNull('checked_in_at')
            ->with(['clientRecord', 'lawyer.user'])
            ->orderBy('checked_in_at')
            ->get();

        $waitingToCheckIn = Appointment::whereDate('start_datetime', $today)
            ->where('status', 'confirmed')
            ->whereNull('checked_in_at')
            ->with(['clientRecord', 'lawyer.user'])
            ->orderBy('start_datetime')
            ->get();

        // Upcoming appointments (future dates, pending or confirmed)
        $upcomingAppointments = Appointment::whereDate('start_datetime', '>', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['clientRecord', 'lawyer.user'])
            ->orderBy('start_datetime')
            ->get();

        return view('staff.queue', compact('appointments', 'waitingToCheckIn', 'upcomingAppointments'));
    }

    /**
     * List appointments
     */
    public function appointments(Request $request)
    {
        $query = Appointment::with(['clientRecord', 'lawyer.user']);

        // Apply date filters if provided, otherwise show all appointments
        if ($request->filled('date_from')) {
            $query->whereDate('start_datetime', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_datetime', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('start_datetime', 'desc')->paginate(20);

        return view('staff.appointments.index', compact('appointments'));
    }

    /**
     * Show appointment details
     */
    public function showAppointment(Appointment $appointment)
    {
        $appointment->load([
            'clientRecord.entries' => function ($q) use ($appointment) {
                $q->where('appointment_id', $appointment->id)->with('creator')->orderBy('created_at', 'desc');
            },
            'lawyer.user',
            'aiRecommendation',
        ]);

        return view('staff.appointments.show', compact('appointment'));
    }

    /**
     * Check in a client
     */
    public function checkIn(Appointment $appointment)
    {
        if ($appointment->status !== 'confirmed') {
            return back()->with('error', 'Can only check in confirmed appointments.');
        }

        if ($appointment->checked_in_at) {
            return back()->with('error', 'Client already checked in.');
        }

        // Assign queue number
        $lastQueue = Appointment::whereDate('start_datetime', Carbon::today())
            ->whereNotNull('queue_number')
            ->max('queue_number') ?? 0;

        $appointment->update([
            'checked_in_at' => now(),
            'queue_number' => $lastQueue + 1,
        ]);

        // Add timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'appointment_update',
            'title' => 'Client Checked In',
            'content' => 'Client has arrived and checked in. Queue number: ' . ($lastQueue + 1),
        ]);

        return back()->with('success', 'Client checked in successfully. Queue #' . ($lastQueue + 1));
    }    /**
     * Confirm an appointment
     */
    public function confirm(Appointment $appointment)
    {
        if ($appointment->status !== 'pending') {
            return back()->with('error', 'Can only confirm pending appointments.');
        }

        $appointment->update([
            'status' => 'confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        // Add timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'appointment_confirmed',
            'title' => 'Appointment Confirmed',
            'content' => 'The appointment has been confirmed by staff.',
        ]);

        // Send confirmation email to client
        if ($appointment->clientRecord->email) {
            Mail::to($appointment->clientRecord->email)
                ->send(new AppointmentApproved($appointment));
        }

        return back()->with('success', 'Appointment confirmed successfully. A confirmation email has been sent to the client.');
    }
}
