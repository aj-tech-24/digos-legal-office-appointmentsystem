<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of staff users
     */
    public function index(Request $request)
    {
        $query = User::role('staff');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $staffUsers = $query->orderBy('name')->paginate(15);

        return view('admin.staff.index', compact('staffUsers'));
    }

    /**
     * Show the form for creating a new staff user
     */
    public function create()
    {
        return view('admin.staff.create');
    }

    /**
     * Store a newly created staff user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('staff');

        return redirect()
            ->route('admin.staff.index')
            ->with('success', "Staff member '{$user->name}' has been created successfully.");
    }

    /**
     * Show the form for editing a staff user
     */
    public function edit(User $staff)
    {
        // Ensure the user has the staff role
        if (!$staff->hasRole('staff')) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'User is not a staff member.');
        }

        return view('admin.staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff user
     */
    public function update(Request $request, User $staff)
    {
        if (!$staff->hasRole('staff')) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'User is not a staff member.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($staff->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $staff->name = $validated['name'];
        $staff->email = $validated['email'];

        if (!empty($validated['password'])) {
            $staff->password = Hash::make($validated['password']);
        }

        $staff->save();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', "Staff member '{$staff->name}' has been updated successfully.");
    }

    /**
     * Remove the specified staff user
     */
    public function destroy(User $staff)
    {
        if (!$staff->hasRole('staff')) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'User is not a staff member.');
        }

        // Prevent deleting yourself
        if ($staff->id === auth()->id()) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $name = $staff->name;
        $staff->delete();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', "Staff member '{$name}' has been deleted.");
    }
}
