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
    public function index(Request $request)
    {
        $today = Carbon::today();

        // Applied Search and Status Filter for Today's Appointments
        $query = Appointment::whereDate('start_datetime', $today)
            ->with(['clientRecord', 'lawyer.user']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('clientRecord', function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        $todaysAppointments = $query->orderBy('start_datetime')->get();

        $stats = [
            'today_total' => Appointment::whereDate('start_datetime', $today)->count(),
            'pending_requests' => Appointment::where('status', 'pending')->count(),
            'waiting_checkin' => Appointment::whereDate('start_datetime', $today)
                ->where('status', 'confirmed')
                ->whereNull('checked_in_at')
                ->count(),
            'in_session' => Appointment::where('status', 'in_progress')->count(),
        ];

        return view('staff.dashboard', compact('todaysAppointments', 'stats'));
    }

    public function queue()
    {
        $today = Carbon::today();

        // Organized Queue List
        $appointments = Appointment::whereDate('start_datetime', $today)
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->whereNotNull('checked_in_at')
            ->with(['clientRecord', 'lawyer.user'])
            ->orderBy('queue_number', 'asc')
            ->get();

        $waitingToCheckIn = Appointment::whereDate('start_datetime', $today)
            ->where('status', 'confirmed')
            ->whereNull('checked_in_at')
            ->with(['clientRecord', 'lawyer.user'])
            ->orderBy('start_datetime')
            ->get();

        return view('staff.queue', compact('appointments', 'waitingToCheckIn'));
    }

    // Reuse existing showAppointment, checkIn, confirm logic...
    public function showAppointment(Appointment $appointment)
    {
        // Added lawyer and client history synchronization logic
        $appointment->load([
            'clientRecord.appointments' => function($q) {
                $q->orderBy('start_datetime', 'desc');
            },
            'lawyer.user',
            'aiRecommendation',
        ]);

        $clientHistory = $appointment->clientRecord->appointments;

        return view('staff.appointments.show', compact('appointment', 'clientHistory'));
    }
}