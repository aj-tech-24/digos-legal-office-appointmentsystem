<?php

namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Mail\AppointmentConfirmation; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Display list of appointments for THIS lawyer only
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->lawyer) {
            abort(403, 'User is not associated with a lawyer record.');
        }

        $lawyerId = $user->lawyer->id;

        $query = Appointment::with(['clientRecord'])
                    ->where('lawyer_id', $lawyerId);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $appointments = $query->orderBy('start_datetime', 'desc')->paginate(15);
        
        // Dynamic status list handling
        $statuses = defined('App\Models\Appointment::STATUS_LABELS') 
            ? array_keys(Appointment::STATUS_LABELS) 
            : ['pending', 'confirmed', 'completed', 'cancelled', 'no_show', 'in_progress'];

        return view('lawyer.appointments.index', compact('appointments', 'statuses'));
    }

    /**
     * Show specific appointment
     */
    public function show(Appointment $appointment)
    {
        if ($appointment->lawyer_id !== Auth::user()->lawyer->id) {
            abort(403, 'Unauthorized access.');
        }

        $appointment->load(['clientRecord.entries', 'aiRecommendation']);

        return view('lawyer.appointments.show', compact('appointment'));
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
        $requirements = array_filter($request->input('requirements', []));

        $noteParts = [];
        if (!empty($requirements)) {
            $noteParts[] = "Requirements: " . implode(', ', $requirements);
        }
        if ($instructions) {
            $noteParts[] = "Instructions: " . $instructions;
        }

        $appointment->update([
            'status' => 'confirmed',
            'admin_notes' => implode("\n\n", $noteParts)
        ]);

        // Send Email
        if ($appointment->clientRecord && $appointment->clientRecord->email) {
            try {
                Mail::to($appointment->clientRecord->email)
                    ->send(new AppointmentConfirmation($appointment, $instructions, $requirements));
            } catch (\Exception $e) {
                // Log::error("Mail error: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Appointment confirmed.');
    }

    /**
     * Start Consultation
     */
    public function start(Appointment $appointment)
    {
        if ($appointment->lawyer_id !== Auth::user()->lawyer->id) abort(403);
        
        $appointment->update(['status' => 'in_progress']);

        return back()->with('success', 'Appointment started.');
    }

    /**
     * Complete Consultation
     */
    public function complete(Appointment $appointment)
    {
        if ($appointment->lawyer_id !== Auth::user()->lawyer->id) abort(403);

        $appointment->update(['status' => 'completed']);
        
        if ($appointment->clientRecord) {
            $appointment->clientRecord->entries()->create([
                'appointment_id' => $appointment->id,
                'created_by' => Auth::id(),
                'entry_type' => 'appointment_completed',
                'title' => 'Consultation Completed',
                'content' => 'Lawyer has marked this session as complete.',
            ]);
        }

        return back()->with('success', 'Appointment completed.');
    }

    /**
     * Add Case Note
     */
    public function addNote(Request $request, Appointment $appointment)
    {
        if ($appointment->lawyer_id !== Auth::user()->lawyer->id) abort(403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $appointment->clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'created_by' => Auth::id(),
            'entry_type' => 'note',
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Note added successfully.');
    }
    
    /**
     * Decline Appointment
     */
    public function decline(Request $request, Appointment $appointment)
    {
        if ($appointment->lawyer_id !== Auth::user()->lawyer->id) abort(403);

        $request->validate(['decline_reason' => 'required|string']);

        $appointment->update([
            'status' => 'cancelled',
            'admin_notes' => "Declined Reason: " . $request->decline_reason
        ]);

        return back()->with('success', 'Appointment declined.');
    }

    /**
     * Cancel Appointment
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        if ($appointment->lawyer_id !== Auth::user()->lawyer->id) abort(403);
        
        $appointment->update(['status' => 'cancelled']);
        return back()->with('success', 'Appointment cancelled.');
    }
    
    /**
     * No Show
     */
    public function noShow(Appointment $appointment)
    {
        if ($appointment->lawyer_id !== Auth::user()->lawyer->id) abort(403);
        
        $appointment->update(['status' => 'no_show']);
        return back()->with('success', 'Appointment marked as No-Show.');
    }

    /**
     * Check-In Appointment (Client arrived)
     */
    public function checkIn(Appointment $appointment)
    {
        if ($appointment->lawyer_id !== Auth::user()->lawyer->id) abort(403);

        // FIX: Update timestamp ONLY, not status to 'checked_in' (enum error fix)
        $appointment->update([
            'checked_in_at' => now(),
        ]);

        return back()->with('success', 'Client checked in successfully.');
    }
}