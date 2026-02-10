<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lawyer;
use App\Models\LawyerSchedule;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LawyerController extends Controller
{
    /**
     * Display a listing of lawyers
     */
    public function index(Request $request)
    {
        $query = Lawyer::with(['user', 'specializations']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by specialization
        if ($request->filled('specialization')) {
            $query->whereHas('specializations', function ($q) use ($request) {
                $q->where('specializations.id', $request->specialization);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('license_number', 'like', "%{$search}%");
        }

        $lawyers = $query->orderBy('created_at', 'desc')->paginate(15);
        $specializations = Specialization::active()->get();

        return view('admin.lawyers.index', compact('lawyers', 'specializations'));
    }

    /**
     * Show the form for creating a new lawyer
     */
    public function create()
    {
        $specializations = Specialization::active()->get();
        return view('admin.lawyers.create', compact('specializations'));
    }

    /**
     * Store a newly created lawyer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'license_number' => 'required|unique:lawyers,license_number',
            'bio' => 'nullable|string',
            'description' => 'nullable|string',
            'years_of_experience' => 'required|integer|min:0',
            'languages' => 'required|array|min:1',
            'specializations' => 'required|array|min:1',
            'primary_specialization' => 'required|exists:specializations,id',
            'max_daily_appointments' => 'required|integer|min:1|max:20',
            'default_consultation_duration' => 'required|integer|min:15|max:180',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('lawyer');

            // Create lawyer profile
            $lawyer = Lawyer::create([
                'user_id' => $user->id,
                'license_number' => $validated['license_number'],
                'bio' => $validated['bio'],
                'description' => $validated['description'],
                'years_of_experience' => $validated['years_of_experience'],
                'languages' => $validated['languages'],
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'max_daily_appointments' => $validated['max_daily_appointments'],
                'default_consultation_duration' => $validated['default_consultation_duration'],
            ]);

            // Attach specializations
            foreach ($validated['specializations'] as $specId) {
                $lawyer->specializations()->attach($specId, [
                    'is_primary' => $specId == $validated['primary_specialization'],
                ]);
            }

            // Create default schedule (Mon-Fri, 8am-5pm)
            for ($day = 1; $day <= 5; $day++) {
                LawyerSchedule::create([
                    'lawyer_id' => $lawyer->id,
                    'day_of_week' => $day,
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'is_available' => true,
                ]);
            }
        });

        return redirect()->route('admin.lawyers.index')
            ->with('success', 'Lawyer account created successfully.');
    }

    /**
     * Display the specified lawyer
     */
    public function show(Lawyer $lawyer)
    {
        $lawyer->load(['user', 'specializations', 'schedules', 'appointments' => function ($q) {
            $q->with('clientRecord')->orderBy('start_datetime', 'desc')->take(10);
        }]);

        return view('admin.lawyers.show', compact('lawyer'));
    }

    /**
     * Show the form for editing the specified lawyer
     */
    public function edit(Lawyer $lawyer)
    {
        $lawyer->load(['user', 'specializations', 'schedules']);
        $specializations = Specialization::active()->get();

        return view('admin.lawyers.edit', compact('lawyer', 'specializations'));
    }

    /**
     * Update the specified lawyer
     */
    public function update(Request $request, Lawyer $lawyer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $lawyer->user_id,
            'license_number' => 'required|unique:lawyers,license_number,' . $lawyer->id,
            'bio' => 'nullable|string',
            'description' => 'nullable|string',
            'years_of_experience' => 'required|integer|min:0',
            'languages' => 'required|array|min:1',
            'specializations' => 'required|array|min:1',
            'primary_specialization' => 'required|exists:specializations,id',
            'max_daily_appointments' => 'required|integer|min:1|max:20',
            'default_consultation_duration' => 'required|integer|min:15|max:180',
            'status' => 'required|in:pending,approved,suspended,rejected',
        ]);

        DB::transaction(function () use ($validated, $lawyer) {
            // Update user
            $lawyer->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            // Update lawyer profile
            $lawyer->update([
                'license_number' => $validated['license_number'],
                'bio' => $validated['bio'],
                'description' => $validated['description'],
                'years_of_experience' => $validated['years_of_experience'],
                'languages' => $validated['languages'],
                'status' => $validated['status'],
                'max_daily_appointments' => $validated['max_daily_appointments'],
                'default_consultation_duration' => $validated['default_consultation_duration'],
            ]);

            // Sync specializations
            $syncData = [];
            foreach ($validated['specializations'] as $specId) {
                $syncData[$specId] = ['is_primary' => $specId == $validated['primary_specialization']];
            }
            $lawyer->specializations()->sync($syncData);
        });

        return redirect()->route('admin.lawyers.index')
            ->with('success', 'Lawyer updated successfully.');
    }

    /**
     * Approve a pending lawyer
     */
    public function approve(Lawyer $lawyer)
    {
        $lawyer->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Lawyer approved successfully.');
    }

    /**
     * Reject a pending lawyer
     */
    public function reject(Request $request, Lawyer $lawyer)
    {
        $lawyer->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Lawyer rejected.');
    }

    /**
     * Suspend a lawyer
     */
    public function suspend(Lawyer $lawyer)
    {
        $lawyer->update([
            'status' => 'suspended',
        ]);

        return back()->with('success', 'Lawyer suspended.');
    }
}
