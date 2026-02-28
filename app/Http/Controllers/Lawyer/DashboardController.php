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
use App\Models\LawyerUnavailability;

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

        // 1. Today's appointments (Wala ni giusab)
        $todaysAppointments = Appointment::where('lawyer_id', $lawyer->id)
            ->whereDate('start_datetime', $today)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->with('clientRecord')
            ->orderBy('start_datetime')
            ->get();

        // 2. REPLACED: Upcoming Unavailabilities (Leaves/Hearings) instead of Appointments
        // Gikuha nato ang mga leave/hearing nga mas dako or equal sa karong adlawa
        $upcomingUnavailabilities = $lawyer->unavailabilities()
            ->where('unavailable_date', '>=', $today->toDateString())
            ->orderBy('unavailable_date', 'asc')
            ->take(5) // Limit to 5 para dili taas kaayo sa dashboard
            ->get();

        // 3. Stats (Updated)
        $stats = [
            'today_count' => $todaysAppointments->count(),
            
            // ILISAN: Count sa Upcoming Unavailabilities na ang ibutang dire
            'upcoming_unavailabilities_count' => $upcomingUnavailabilities->count(),
            
            'completed_this_month' => Appointment::where('lawyer_id', $lawyer->id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', $today->month)
                ->whereYear('completed_at', $today->year)
                ->count(),
            'pending_count' => Appointment::where('lawyer_id', $lawyer->id)
                ->where('status', 'pending')
                ->count(),
        ];

        // Gi-pass nato ang $upcomingUnavailabilities imbes nga appointments
        return view('lawyer.dashboard', compact('lawyer', 'todaysAppointments', 'upcomingUnavailabilities', 'stats'));
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
        ]);

        // Add timeline entry
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
        ]);

        // Add timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'appointment_completed',
            'title' => 'Consultation Completed',
            'content' => $validated['resolution_notes'] ?? 'The consultation has been completed.',
        ]);

        return back()->with('success', 'Appointment completed.');
    }

    /**
     * Confirm/Approve an appointment
     */
    public function confirmAppointment(Request $request, Appointment $appointment)
    {
        $lawyer = auth()->user()->lawyer;

        if ($appointment->lawyer_id !== $lawyer->id) {
            abort(403, 'Unauthorized access.');
        }

        if ($appointment->status !== 'pending') {
            return back()->with('error', 'Can only confirm pending appointments.');
        }

        // Validate checklist
        $request->validate([
            'admin_notes' => 'nullable|array',
        ]);

        $appointment->update([
            'status' => 'confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
            'admin_notes' => $request->admin_notes, // Save the checklist of requirements
        ]);

        // Add timeline entry
        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'entry_type' => 'appointment_confirmed',
            'title' => 'Appointment Confirmed',
            'content' => 'The appointment has been confirmed by ' . $lawyer->user->name,
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

    /**
     * Show schedule management
     */
    public function schedule()
    {
        $lawyer = auth()->user()->lawyer;
        
        // Load existing schedules
        $schedules = LawyerSchedule::where('lawyer_id', $lawyer->id)
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        // Load upcoming leaves/hearings
        $unavailabilities = $lawyer->unavailabilities()
            ->where('unavailable_date', '>=', now()->toDateString())
            ->orderBy('unavailable_date')
            ->get();

        $daysOfWeek = [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
        ];

        // Pass $unavailabilities to the view
        return view('lawyer.schedule', compact('lawyer', 'schedules', 'daysOfWeek', 'unavailabilities'));
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
                // Delete schedule for unavailable days
                LawyerSchedule::where('lawyer_id', $lawyer->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->delete();
            }
        }

        return back()->with('success', 'Schedule updated successfully.');
    }

    // New Function to store Leave/Hearing
    public function storeUnavailability(Request $request)
    {
        $request->validate([
            'unavailable_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:255',
            'type' => 'required|in:whole,partial',
            'start_time' => 'required_if:type,partial',
            'end_time' => 'required_if:type,partial|after:start_time',
        ]);

        $lawyer = auth()->user()->lawyer;

        LawyerUnavailability::create([
            'lawyer_id' => $lawyer->id,
            'unavailable_date' => $request->unavailable_date,
            'reason' => $request->reason,
            'is_whole_day' => $request->type === 'whole',
            'start_time' => $request->type === 'partial' ? $request->start_time : null,
            'end_time' => $request->type === 'partial' ? $request->end_time : null,
        ]);

        return back()->with('success', 'Unavailable date set successfully.');
    }

    // New Function to delete Leave
    public function destroyUnavailability($id)
    {
        $unavailability = LawyerUnavailability::findOrFail($id);
        
        // Security check
        if($unavailability->lawyer_id !== auth()->user()->lawyer->id){
            abort(403);
        }

        $unavailability->delete();
        return back()->with('success', 'Date removed from unavailable list.');
    }
}