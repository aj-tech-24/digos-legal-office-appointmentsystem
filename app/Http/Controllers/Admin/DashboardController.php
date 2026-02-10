<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClientRecord;
use App\Models\Lawyer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_appointments' => Appointment::count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'today_appointments' => Appointment::whereDate('start_datetime', today())->count(),
            'total_lawyers' => Lawyer::count(),
            'pending_lawyers' => Lawyer::where('status', 'pending')->count(),
            'approved_lawyers' => Lawyer::where('status', 'approved')->count(),
            'total_clients' => ClientRecord::count(),
            'total_users' => User::count(),
        ];

        $recentAppointments = Appointment::with(['clientRecord', 'lawyer.user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $todayAppointments = Appointment::with(['clientRecord', 'lawyer.user'])
            ->whereDate('start_datetime', today())
            ->orderBy('start_datetime')
            ->get();

        $pendingLawyers = Lawyer::with('user')
            ->where('status', 'pending')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentAppointments',
            'todayAppointments',
            'pendingLawyers'
        ));
    }
}
