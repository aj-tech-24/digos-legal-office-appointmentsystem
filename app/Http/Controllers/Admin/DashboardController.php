<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClientRecord;
use App\Models\Lawyer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        // 1. Statistics Cards
        $stats = [
            'total_appointments'   => Appointment::count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'today_appointments'   => Appointment::whereDate('start_datetime', $today)->count(),
            'approved_lawyers'     => Lawyer::where('status', 'approved')->count(),
            'total_clients'        => ClientRecord::count(),
        ];

        // 2. Today's Appointments (WITH CORRECT RELATIONSHIPS)
        $todaysAppointments = Appointment::with(['clientRecord', 'lawyer.user']) // Check kani
            ->whereDate('start_datetime', $today)
            ->orderBy('start_datetime', 'asc')
            ->get();

        // 3. Upcoming Appointments
        $upcomingAppointments = Appointment::with(['clientRecord', 'lawyer.user']) // Check kani
            ->whereDate('start_datetime', '>', $today)
            ->whereIn('status', ['confirmed', 'ongoing', 'pending'])
            ->orderBy('start_datetime', 'asc')
            ->take(5)
            ->get();

        // 4. Pending Lawyers
        $pendingLawyers = Lawyer::with('user')
            ->where('status', 'pending')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'todaysAppointments',
            'upcomingAppointments',
            'pendingLawyers'
        ));
    }
}