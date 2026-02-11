<?php

namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Lawyer;
use App\Models\LawyerSchedule;
use Illuminate\Http\Request;
use App\Mail\AppointmentApproved;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the lawyer dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $lawyer = $user->lawyer;

        if (!$lawyer) {
            return redirect()->route('login')->with('error', 'Lawyer profile not found.');
        }

        // Check if lawyer is approved
        if ($lawyer->status !== 'approved') {
            return view('lawyer.pending-approval', compact('lawyer'));
        }

        $today = Carbon::today();

        // Today's appointments
        $todaysAppointments = Appointment::where('lawyer_id', $lawyer->id)
            ->whereDate('start_datetime', $today)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->with('clientRecord')
            ->orderBy('start_datetime')
            ->get();

        // Upcoming appointments (next 7 days)
        $upcomingAppointments = Appointment::where('lawyer_id', $lawyer->id)
            ->whereDate('start_datetime', '>', $today)
            ->whereDate('start_datetime', '<=', $today->copy()->addDays(7))
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('clientRecord')
            ->orderBy('start_datetime')
            ->get();

        // Stats
        $stats = [
            'today_count' => $todaysAppointments->count(),
            'upcoming_count' => $upcomingAppointments->count(),
            'completed_this_month' => Appointment::where('lawyer_id', $lawyer->id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', $today->month)
                ->whereYear('completed_at', $today->year)
                ->count(),
            'pending_count' => Appointment::where('lawyer_id', $lawyer->id)
                ->where('status', 'pending')
                ->count(),
        ];

        return view('lawyer.dashboard', compact('lawyer', 'todaysAppointments', 'upcomingAppointments', 'stats'));
    }

    /**
     * Show lawyer's appointments
     */
    public function appointments(Request $request)
    {
        $lawyer = auth()->user()->lawyer;

        $query = Appointment::where('lawyer_id', $lawyer->id)
            ->with('clientRecord');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('start_datetime', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_datetime', '<=', $request->date_to);
        }

        $appointments = $query->orderBy('start_datetime', 'desc')->paginate(15);

        return view('lawyer.appointments.index', compact('appointments'));
    }

    /**
     * Show a specific appointment
     */
    public function showAppointment(Appointment $appointment)
    {
        $lawyer = auth()->user()->lawyer;

        // Ensure this appointment belongs to the lawyer
        if ($appointment->lawyer_id !== $lawyer->id) {
            abort(403, 'Unauthorized access.');
        }

        $appointment->load([
            'clientRecord.entries' => function ($q) use ($appointment) {
                $q->where('appointment_id', $appointment->id)->with('creator')->orderBy('created_at', 'desc');
            },
            'aiRecommendation.items',
        ]);

        return view('lawyer.appointments.show', compact('appointment'));
    }

    /**
     * Show schedule management
     */
    public function schedule()
    {
        $lawyer = auth()->user()->lawyer;
        
        $schedules = LawyerSchedule::where('lawyer_id', $lawyer->id)
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        $daysOfWeek = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return view('lawyer.schedule', compact('lawyer', 'schedules', 'daysOfWeek'));
    }

    /**
     * Update schedule
     */
    public function updateSchedule(Request $request)
    {
        $lawyer = auth()->user()->lawyer;
        
        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.day_of_week' => 'required|integer|between:0,6',
            'schedules.*.is_available' => 'boolean',
            'schedules.*.start_time' => 'nullable|date_format:H:i',
            'schedules.*.end_time' => 'nullable|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.max_appointments' => 'nullable|integer|min:1|max:20',
        ]);

        foreach ($validated['schedules'] as $scheduleData) {
            $isAvailable = isset($scheduleData['is_available']) && $scheduleData['is_available'];
            $dayOfWeek = $scheduleData['day_of_week'];

            if ($isAvailable) {
                // Create or update schedule for available days
                LawyerSchedule::updateOrCreate(
                    [
                        'lawyer_id' => $lawyer->id,
                        'day_of_week' => $dayOfWeek,
                    ],
                    [
                        'is_available' => true,
                        'start_time' => $scheduleData['start_time'] ?? '08:00',
                        'end_time' => $scheduleData['end_time'] ?? '17:00',
                        'max_appointments' => $scheduleData['max_appointments'] ?? 8,
                    ]
                );
            } else {
                // Delete schedule for unavailable days (or set to unavailable with default times)
                LawyerSchedule::where('lawyer_id', $lawyer->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->delete();
            }
        }

        return back()->with('success', 'Schedule updated successfully.');
    }

    public function addNote(Request $request, Appointment $appointment)
    {
        $lawyer = auth()->user()->lawyer;

        // Ensure this appointment belongs to the lawyer
        if ($appointment->lawyer_id !== $lawyer->id) {
            abort(403, 'Unauthorized access.');
        }

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
     * Start an appointment (mark as in progress)
     */
    public function startAppointment(Appointment $appointment)
    {
        $lawyer = auth()->user()->lawyer;

        if ($appointment->lawyer_id !== $lawyer->id) {
            abort(403, 'Unauthorized access.');
        }

        if ($appointment->status !== 'confirmed') {
            return back()->with('error', 'Can only start confirmed appointments.');
        }

        $appointment->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);        // Add timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'appointment_started',
            'title' => 'Consultation Started',
            'content' => 'The consultation has been started.',
        ]);

        return back()->with('success', 'Appointment started.');
    }

    /**
     * Complete an appointment
     */
    public function completeAppointment(Request $request, Appointment $appointment)
    {
        $lawyer = auth()->user()->lawyer;

        if ($appointment->lawyer_id !== $lawyer->id) {
            abort(403, 'Unauthorized access.');
        }

        if ($appointment->status !== 'in_progress') {
            return back()->with('error', 'Can only complete in-progress appointments.');
        }

        $validated = $request->validate([
            'resolution_notes' => 'nullable|string|max:2000',
        ]);

        $appointment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);        // Add timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'appointment_completed',
            'title' => 'Consultation Completed',
            'content' => $validated['resolution_notes'] ?? 'The consultation has been completed.',
        ]);        return back()->with('success', 'Appointment completed.');
    }

    /**
     * Confirm/Approve an appointment
     */
    public function confirmAppointment(Appointment $appointment)
    {
        $lawyer = auth()->user()->lawyer;

        if ($appointment->lawyer_id !== $lawyer->id) {
            abort(403, 'Unauthorized access.');
        }

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
            'content' => 'The appointment has been confirmed by Atty. ' . $lawyer->user->name,
        ]);

        // Send confirmation email to client
        if ($appointment->clientRecord->email) {
            Mail::to($appointment->clientRecord->email)
                ->send(new AppointmentApproved($appointment));
        }

        return back()->with('success', 'Appointment confirmed successfully. A confirmation email has been sent to the client.');
    }

    /**
     * Decline an appointment
     */
    public function declineAppointment(Request $request, Appointment $appointment)
    {
        $lawyer = auth()->user()->lawyer;

        if ($appointment->lawyer_id !== $lawyer->id) {
            abort(403, 'Unauthorized access.');
        }

        if ($appointment->status !== 'pending') {
            return back()->with('error', 'Can only decline pending appointments.');
        }

        $validated = $request->validate([
            'decline_reason' => 'required|string|max:500',
        ]);

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['decline_reason'],
            'cancelled_at' => now(),
        ]);

        // Add timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'appointment_cancelled',
            'title' => 'Appointment Declined',
            'content' => 'The appointment was declined. Reason: ' . $validated['decline_reason'],
        ]);

        return back()->with('success', 'Appointment declined.');
    }

    /**
     * Update lawyer profile
     */
    public function profile()
    {
        $lawyer = auth()->user()->lawyer;
        $lawyer->load('specializations');

        return view('lawyer.profile', compact('lawyer'));
    }

    /**
     * Update lawyer profile
     */
    public function updateProfile(Request $request)
    {
        $lawyer = auth()->user()->lawyer;

        $validated = $request->validate([
            'bio' => 'nullable|string|max:2000',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
        ]);

        $lawyer->update([
            'bio' => $validated['bio'] ?? $lawyer->bio,
            'languages' => $validated['languages'] ?? $lawyer->languages,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }
}
