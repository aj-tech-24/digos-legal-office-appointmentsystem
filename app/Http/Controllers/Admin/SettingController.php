<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Display a listing of settings grouped by category.
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update the specified setting in storage.
     */
    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        try {
            DB::transaction(function () use ($data) {
                foreach ($data as $key => $value) {
                    $setting = Setting::where('key', $key)->first();
                    if ($setting) {
                        $setting->update(['value' => $value]);
                    }
                }
            });

            return redirect()
                ->route('admin.settings.index')
                ->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
